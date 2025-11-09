<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Payment;
use App\Models\TransactionLog;
use App\DataStructures\BookingQueue;
use App\DataStructures\HashTable;
use App\DataStructures\TransactionStack;
use App\Mail\BookingConfirmation;
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

                // Step 3: Reserve room inventory (only if check-in is today)
                $checkInDate = Carbon::parse($reservationData['check_in_date']);
                $isToday = $checkInDate->isToday();
                
                if ($isToday) {
                    $this->reserveRoomInventory($room->id, 1, 'reservation_created', $checkInDate);
                }

                // Step 4: Create reservation record
                $reservation = $this->createReservationRecord($reservationData);

                // Step 5: Log transaction
                $room->refresh(); // Refresh to get updated quantity
                $this->logTransaction('reserve', $reservation->id, $room->id, [
                    'before_quantity' => $isToday ? $room->available_quantity + 1 : $room->available_quantity,
                    'after_quantity' => $room->available_quantity,
                    'change' => $isToday ? -1 : 0,
                    'check_in_date' => $reservationData['check_in_date'],
                    'availability_decremented' => $isToday
                ]);

                // Step 6: Send confirmation email
                try {
                    Mail::to($reservation->user->email)->send(new BookingConfirmation($reservation));
                } catch (\Exception $e) {
                    Log::warning('Failed to send booking confirmation email: ' . $e->getMessage());
                }

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

                // Restore room inventory (only if availability was already decremented)
                // Availability is only decremented if check-in date was today or in the past
                $checkInDate = Carbon::parse($reservation->check_in_date);
                $isTodayOrPast = $checkInDate->isToday() || $checkInDate->isPast();
                
                if ($isTodayOrPast) {
                    $this->restoreRoomInventory($reservation->room_id, 1, 'reservation_cancelled');
                }

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

                // Calculate refund amount if payment was made
                $refundAmount = null;
                if ($reservation->payment && $reservation->payment->status === 'paid') {
                    // Calculate refund based on cancellation policy
                    $checkInDate = Carbon::parse($reservation->check_in_date);
                    $daysUntilCheckIn = now()->diffInDays($checkInDate, false);
                    
                    if ($daysUntilCheckIn >= 7) {
                        // Full refund if cancelled 7+ days before check-in
                        $refundAmount = $reservation->payment->amount;
                    } elseif ($daysUntilCheckIn >= 3) {
                        // 50% refund if cancelled 3-6 days before check-in
                        $refundAmount = $reservation->payment->amount * 0.5;
                    }
                    // No refund if cancelled less than 3 days before check-in
                }

                // Send cancellation email
                try {
                    Mail::to($reservation->user->email)->send(new \App\Mail\BookingCancellation($reservation->fresh(), $refundAmount));
                } catch (\Exception $e) {
                    Log::warning('Failed to send cancellation email: ' . $e->getMessage());
                }

                return [
                    'success' => true,
                    'reservation' => $reservation->fresh(),
                    'refund_amount' => $refundAmount,
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
     * Modify a reservation (change dates, recalculate price)
     */
    public function modifyReservation(int $reservationId, array $modificationData): array
    {
        return DB::transaction(function () use ($reservationId, $modificationData) {
            try {
                $reservation = Reservation::findOrFail($reservationId);
                
                if (!$reservation->canBeModified()) {
                    throw new \Exception('Reservation cannot be modified');
                }

                $oldCheckIn = $reservation->check_in_date;
                $oldCheckOut = $reservation->check_out_date;
                $oldAmount = $reservation->total_amount;

                // Update dates if provided
                if (isset($modificationData['check_in_date'])) {
                    $reservation->check_in_date = $modificationData['check_in_date'];
                }
                if (isset($modificationData['check_out_date'])) {
                    $reservation->check_out_date = $modificationData['check_out_date'];
                }

                // Validate new dates
                $checkIn = Carbon::parse($reservation->check_in_date);
                $checkOut = Carbon::parse($reservation->check_out_date);
                
                if ($checkIn->isPast()) {
                    throw new \Exception('Check-in date cannot be in the past');
                }
                
                if ($checkOut->lte($checkIn)) {
                    throw new \Exception('Check-out date must be after check-in date');
                }

                // Check room availability for new dates
                if (!$this->checkAvailability($reservation->room_id, $reservation->check_in_date, $reservation->check_out_date)) {
                    throw new \Exception('Room not available for the selected dates');
                }

                // Recalculate total amount
                $nights = $checkIn->diffInDays($checkOut);
                $room = Room::find($reservation->room_id);
                $newAmount = $room->price_per_night * $nights;

                // Apply discount if promo code exists
                if ($reservation->promo_code) {
                    $promoCode = \App\Models\PromoCode::where('code', $reservation->promo_code)
                        ->available()
                        ->first();
                    
                    if ($promoCode && $promoCode->canBeUsedFor($newAmount)) {
                        $discountAmount = $promoCode->calculateDiscount($newAmount);
                        $newAmount = max(0, $newAmount - $discountAmount);
                        $reservation->discount_amount = $discountAmount;
                    } else {
                        $reservation->discount_amount = 0;
                        $reservation->promo_code = null;
                    }
                }

                // Calculate modification fee (if dates changed significantly)
                $modificationFee = 0;
                if ($oldCheckIn != $reservation->check_in_date || $oldCheckOut != $reservation->check_out_date) {
                    $daysChanged = abs($checkIn->diffInDays(Carbon::parse($oldCheckIn)));
                    if ($daysChanged > 7) {
                        $modificationFee = $newAmount * 0.1; // 10% fee for changes more than 7 days
                    }
                }

                $reservation->total_amount = $newAmount + $modificationFee;

                // Log transaction
                $this->logTransaction('modify', $reservationId, $reservation->room_id, [
                    'old_check_in' => $oldCheckIn,
                    'new_check_in' => $reservation->check_in_date,
                    'old_check_out' => $oldCheckOut,
                    'new_check_out' => $reservation->check_out_date,
                    'old_amount' => $oldAmount,
                    'new_amount' => $reservation->total_amount,
                    'modification_fee' => $modificationFee,
                ]);

                $reservation->save();

                return [
                    'success' => true,
                    'reservation' => $reservation->fresh(),
                    'modification_fee' => $modificationFee,
                    'message' => 'Reservation modified successfully'
                ];

            } catch (\Exception $e) {
                Log::error('Reservation modification failed: ' . $e->getMessage());
                
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

                // Restore room availability (room becomes available again after checkout)
                $this->restoreRoomInventory($reservation->room_id, 1, 'checkout_completed');

                // Update reservation status
                $reservation->update([
                    'status' => 'completed',
                ]);

                // Log transaction
                $this->logTransaction('checkout', $reservationId, $reservation->room_id, [
                    'status_change' => 'confirmed -> completed',
                    'availability_restored' => true
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

    private function reserveRoomInventory(int $roomId, int $quantity, string $reason, ?Carbon $checkInDate = null): void
    {
        $room = Room::find($roomId);
        
        if (!$room) {
            throw new \Exception('Room not found');
        }

        // Only decrement availability if check-in date is today
        // For future dates, availability will be decremented when check-in date arrives
        if ($checkInDate && !$checkInDate->isToday()) {
            // Don't decrement for future dates
            return;
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

    /**
     * Process reservations with check-in date = today and decrement availability
     * This should be called daily (via scheduled job) or on page loads
     * 
     * Only processes reservations that were created BEFORE today (future reservations that are now due)
     * Reservations created today with check-in today already had availability decremented during creation
     */
    public function processTodayCheckIns(): void
    {
        $today = Carbon::today();
        
        // Get all confirmed/pending reservations with check-in date = today
        // that were created BEFORE today (future reservations that are now due)
        $reservations = Reservation::where('check_in_date', $today->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('created_at', '<', $today->toDateString()) // Only process reservations created before today
            ->with('room')
            ->get();

        foreach ($reservations as $reservation) {
            try {
                // Try to decrement availability
                // This will only decrement if check-in date is today (which it is)
                $this->reserveRoomInventory(
                    $reservation->room_id, 
                    1, 
                    'check_in_date_arrived',
                    Carbon::parse($reservation->check_in_date)
                );
                
                Log::info("Processed check-in for reservation {$reservation->id}, decremented availability for room {$reservation->room_id}");
            } catch (\Exception $e) {
                // If it fails, availability might have already been decremented
                // or there's insufficient availability (shouldn't happen for confirmed reservations)
                // Silently continue - this is expected if already processed
                Log::debug("Check-in processing for reservation {$reservation->id}: " . $e->getMessage());
            }
        }
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



