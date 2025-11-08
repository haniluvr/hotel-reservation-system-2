<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Payment;
use App\Models\TransactionLog;
use App\DataStructures\BookingQueue;
use App\DataStructures\HashTable;
use App\DataStructures\TransactionStack;
use Carbon\Carbon;

/**
 * ReservationService - ACID-Compliant Reservation Management
 * 
 * Handles all reservation operations with atomic transactions,
 * room inventory management, and rollback capabilities.
 */
class ReservationService
{
    private BookingQueue $bookingQueue;
    private HashTable $roomAvailability;
    private TransactionStack $transactionStack;

    public function __construct()
    {
        $this->bookingQueue = new BookingQueue();
        $this->roomAvailability = new HashTable();
        $this->transactionStack = new TransactionStack();
    }

    /**
     * Create a new reservation with ACID compliance
     */
    public function createReservation(array $reservationData): array
    {
        return DB::transaction(function () use ($reservationData) {
            try {
                // Step 1: Validate reservation data
                $this->validateReservationData($reservationData);

                // Step 2: Check room availability atomically
                $room = $this->checkRoomAvailability($reservationData['room_id'], $reservationData['check_in_date'], $reservationData['check_out_date']);

                if (!$room) {
                    throw new \Exception('Room not available for the selected dates');
                }

                // Step 3: Reserve room inventory
                $this->reserveRoomInventory($room->id, 1, 'reservation_created');

                // Step 4: Create reservation record
                $reservation = $this->createReservationRecord($reservationData);

                // Step 5: Log transaction
                $this->logTransaction('reserve', $reservation->id, $room->id, [
                    'before_quantity' => $room->available_quantity + 1,
                    'after_quantity' => $room->available_quantity,
                    'change' => -1
                ]);

                return [
                    'success' => true,
                    'reservation' => $reservation,
                    'message' => 'Reservation created successfully'
                ];

            } catch (\Exception $e) {
                // Rollback any changes
                $this->rollbackTransaction();
                
                Log::error('Reservation creation failed: ' . $e->getMessage());
                
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Confirm a reservation and process payment
     */
    public function confirmReservation(int $reservationId, array $paymentData): array
    {
        return DB::transaction(function () use ($reservationId, $paymentData) {
            try {
                $reservation = Reservation::findOrFail($reservationId);
                
                if ($reservation->status !== 'pending') {
                    throw new \Exception('Reservation cannot be confirmed');
                }

                // Create payment record
                $payment = $this->createPaymentRecord($reservationId, $paymentData);

                // Update reservation status
                $reservation->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Log transaction
                $this->logTransaction('confirm', $reservationId, $reservation->room_id, [
                    'status_change' => 'pending -> confirmed',
                    'payment_id' => $payment->id
                ]);

                return [
                    'success' => true,
                    'reservation' => $reservation->fresh(),
                    'payment' => $payment,
                    'message' => 'Reservation confirmed successfully'
                ];

            } catch (\Exception $e) {
                Log::error('Reservation confirmation failed: ' . $e->getMessage());
                
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Cancel a reservation and restore inventory
     */
    public function cancelReservation(int $reservationId, string $reason = ''): array
    {
        return DB::transaction(function () use ($reservationId, $reason) {
            try {
                $reservation = Reservation::findOrFail($reservationId);
                
                if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                    throw new \Exception('Reservation cannot be cancelled');
                }

                // Restore room inventory
                $this->restoreRoomInventory($reservation->room_id, 1, 'reservation_cancelled');

                // Update reservation status
                $reservation->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);

                // Log transaction
                $this->logTransaction('cancel', $reservationId, $reservation->room_id, [
                    'status_change' => $reservation->status . ' -> cancelled',
                    'reason' => $reason
                ]);

                return [
                    'success' => true,
                    'reservation' => $reservation->fresh(),
                    'message' => 'Reservation cancelled successfully'
                ];

            } catch (\Exception $e) {
                Log::error('Reservation cancellation failed: ' . $e->getMessage());
                
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Process checkout and complete reservation
     */
    public function processCheckout(int $reservationId): array
    {
        return DB::transaction(function () use ($reservationId) {
            try {
                $reservation = Reservation::findOrFail($reservationId);
                
                if ($reservation->status !== 'confirmed') {
                    throw new \Exception('Only confirmed reservations can be checked out');
                }

                // Update reservation status
                $reservation->update([
                    'status' => 'completed',
                ]);

                // Log transaction
                $this->logTransaction('checkout', $reservationId, $reservation->room_id, [
                    'status_change' => 'confirmed -> completed'
                ]);

                return [
                    'success' => true,
                    'reservation' => $reservation->fresh(),
                    'message' => 'Checkout processed successfully'
                ];

            } catch (\Exception $e) {
                Log::error('Checkout processing failed: ' . $e->getMessage());
                
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Check room availability for specific dates
     */
    public function checkAvailability(int $roomId, string $checkIn, string $checkOut): bool
    {
        $room = Room::find($roomId);
        
        if (!$room || $room->available_quantity <= 0) {
            return false;
        }

        // Check for overlapping reservations
        $overlappingReservations = Reservation::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                          ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->count();

        return $overlappingReservations < $room->available_quantity;
    }

    /**
     * Get reservation statistics
     */
    public function getReservationStats(): array
    {
        $stats = Reservation::selectRaw('
            status,
            COUNT(*) as count,
            SUM(total_amount) as total_revenue
        ')
        ->groupBy('status')
        ->get()
        ->keyBy('status');

        return [
            'total_reservations' => Reservation::count(),
            'pending' => $stats->get('pending', (object)['count' => 0, 'total_revenue' => 0]),
            'confirmed' => $stats->get('confirmed', (object)['count' => 0, 'total_revenue' => 0]),
            'cancelled' => $stats->get('cancelled', (object)['count' => 0, 'total_revenue' => 0]),
            'completed' => $stats->get('completed', (object)['count' => 0, 'total_revenue' => 0]),
            'total_revenue' => Reservation::where('status', '!=', 'cancelled')->sum('total_amount'),
        ];
    }

    /**
     * Process booking queue (for handling concurrent requests)
     */
    public function processBookingQueue(): array
    {
        $processed = 0;
        $errors = [];

        while (!$this->bookingQueue->isEmpty()) {
            $bookingRequest = $this->bookingQueue->dequeue();
            
            try {
                $result = $this->createReservation($bookingRequest);
                
                if ($result['success']) {
                    $processed++;
                } else {
                    $errors[] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_errors' => count($errors)
        ];
    }

    /**
     * Add booking request to queue
     */
    public function queueBookingRequest(array $reservationData): bool
    {
        return $this->bookingQueue->enqueue($reservationData);
    }

    // Private helper methods

    private function validateReservationData(array $data): void
    {
        $required = ['user_id', 'room_id', 'check_in_date', 'check_out_date', 'adults', 'total_amount'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Required field '{$field}' is missing");
            }
        }

        // Validate dates
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);
        
        if ($checkIn->isPast()) {
            throw new \Exception('Check-in date cannot be in the past');
        }
        
        if ($checkOut->lte($checkIn)) {
            throw new \Exception('Check-out date must be after check-in date');
        }
    }

    private function checkRoomAvailability(int $roomId, string $checkIn, string $checkOut): ?Room
    {
        $room = Room::find($roomId);
        
        if (!$room || $room->available_quantity <= 0) {
            return null;
        }

        if (!$this->checkAvailability($roomId, $checkIn, $checkOut)) {
            return null;
        }

        return $room;
    }

    private function reserveRoomInventory(int $roomId, int $quantity, string $reason): void
    {
        $room = Room::find($roomId);
        
        if (!$room) {
            throw new \Exception('Room not found');
        }

        if ($room->available_quantity < $quantity) {
            throw new \Exception('Insufficient room availability');
        }

        // Atomic update
        $updated = Room::where('id', $roomId)
            ->where('available_quantity', '>=', $quantity)
            ->update([
                'available_quantity' => DB::raw("available_quantity - {$quantity}")
            ]);

        if (!$updated) {
            throw new \Exception('Failed to reserve room inventory');
        }

        // Add to transaction stack for rollback
        $this->transactionStack->push(
            TransactionStack::createRoomInventoryAction($roomId, -$quantity, $reason)
        );
    }

    private function restoreRoomInventory(int $roomId, int $quantity, string $reason): void
    {
        $room = Room::find($roomId);
        
        if (!$room) {
            throw new \Exception('Room not found');
        }

        // Atomic update
        Room::where('id', $roomId)
            ->update([
                'available_quantity' => DB::raw("available_quantity + {$quantity}")
            ]);

        // Add to transaction stack for rollback
        $this->transactionStack->push(
            TransactionStack::createRoomInventoryAction($roomId, $quantity, $reason)
        );
    }

    private function createReservationRecord(array $data): Reservation
    {
        $reservationNumber = $this->generateReservationNumber();
        
        return Reservation::create([
            'user_id' => $data['user_id'],
            'room_id' => $data['room_id'],
            'reservation_number' => $reservationNumber,
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'adults' => $data['adults'],
            'children' => $data['children'] ?? 0,
            'total_amount' => $data['total_amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'promo_code' => $data['promo_code'] ?? null,
            'status' => 'pending',
            'special_requests' => $data['special_requests'] ?? null,
            'guest_details' => $data['guest_details'] ?? null,
        ]);
    }

    private function createPaymentRecord(int $reservationId, array $paymentData): Payment
    {
        return Payment::create([
            'reservation_id' => $reservationId,
            'xendit_invoice_id' => $paymentData['xendit_invoice_id'] ?? 'TEMP_' . uniqid(),
            'payment_method' => $paymentData['payment_method'] ?? 'credit_card',
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'PHP',
            'status' => 'pending',
            'payment_url' => $paymentData['payment_url'] ?? null,
            'payment_details' => $paymentData['payment_details'] ?? null,
            'expires_at' => now()->addHours(24),
        ]);
    }

    private function logTransaction(string $action, int $reservationId, int $roomId, array $details): void
    {
        TransactionLog::create([
            'reservation_id' => $reservationId,
            'room_id' => $roomId,
            'action' => $action,
            'before_state' => $details['before_state'] ?? null,
            'after_state' => $details['after_state'] ?? null,
            'quantity_change' => $details['change'] ?? 0,
            'description' => $details['description'] ?? "Reservation {$action}",
            'performed_by' => auth()->id() ?? 'system',
        ]);
    }

    private function generateReservationNumber(): string
    {
        return 'BEL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function rollbackTransaction(): void
    {
        $rollbackResults = $this->transactionStack->rollbackAll();
        
        foreach ($rollbackResults as $result) {
            if (!$result['success']) {
                Log::error('Rollback failed: ' . ($result['error'] ?? 'Unknown error'));
            }
        }
    }
}



