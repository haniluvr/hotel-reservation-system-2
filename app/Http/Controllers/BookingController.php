<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Hotel;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Show the booking form
     */
    public function create(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Store the booking intent in session
            $request->session()->put('booking_intent', [
                'room_id' => $request->input('room_id'),
                'check_in' => $request->input('check_in'),
                'check_out' => $request->input('check_out'),
                'adults' => $request->input('adults'),
                'children' => $request->input('children', 0),
            ]);

            // Redirect to login with return URL
            return redirect()->route('login')->with('message', 'Please sign in to complete your booking.');
        }

        // Validate required parameters
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1|max:6',
            'children' => 'nullable|integer|min:0|max:4',
        ]);

        $room = Room::with('hotel')->findOrFail($request->room_id);
        
        $adults = (int) $request->input('adults', 1);
        $children = (int) $request->input('children', 0);
        $totalGuests = $adults + $children;
        
        // Validate adults count
        if ($adults > $room->max_adults) {
            return back()->withErrors(['adults' => "This room can only accommodate up to {$room->max_adults} adults."]);
        }
        
        // Validate children count
        if ($children > $room->max_children) {
            return back()->withErrors(['children' => "This room can only accommodate up to {$room->max_children} children."]);
        }
        
        // Validate total guest count
        if ($totalGuests > $room->max_guests) {
            return back()->withErrors(['adults' => "This room can only accommodate up to {$room->max_guests} guests total (adults + children)."]);
        }

        // Calculate total amount
        $checkIn = \Carbon\Carbon::parse($request->check_in);
        $checkOut = \Carbon\Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $room->price_per_night * $nights;

        return view('booking.create', [
            'room' => $room,
            'hotel' => $room->hotel,
            'checkIn' => $request->check_in,
            'checkOut' => $request->check_out,
            'adults' => $adults,
            'children' => $children,
            'nights' => $nights,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Store the booking/reservation
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $room = Room::findOrFail($request->room_id);

        // Validate guest count
        $totalGuests = $request->adults + ($request->children ?? 0);
        if ($totalGuests > $room->max_guests) {
            return back()->withErrors(['adults' => "This room can only accommodate up to {$room->max_guests} guests."]);
        }

        // Calculate total amount
        $checkIn = \Carbon\Carbon::parse($request->check_in_date);
        $checkOut = \Carbon\Carbon::parse($request->check_out_date);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $room->price_per_night * $nights;

        try {
            // Handle promo code if provided
            $discountAmount = 0;
            $promoCode = null;
            
            if ($request->filled('promo_code')) {
                $promoCodeModel = \App\Models\PromoCode::where('code', strtoupper($request->promo_code))
                    ->available()
                    ->first();
                
                if ($promoCodeModel && $promoCodeModel->canBeUsedFor($totalAmount)) {
                    $discountAmount = $promoCodeModel->calculateDiscount($totalAmount);
                    $totalAmount = max(0, $totalAmount - $discountAmount);
                    $promoCode = $promoCodeModel->code;
                }
            }

            // Use discount_amount from request if provided (from JavaScript calculation)
            if ($request->filled('discount_amount')) {
                $discountAmount = floatval($request->discount_amount);
                $totalAmount = max(0, $totalAmount - $discountAmount);
            }

            $reservationData = [
                'user_id' => Auth::id(),
                'room_id' => $request->room_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'promo_code' => $promoCode,
                'special_requests' => $request->special_requests,
            ];

            $result = $this->reservationService->createReservation($reservationData);
            
            // Increment promo code usage if used
            if ($promoCode && $result['success'] && isset($promoCodeModel)) {
                $promoCodeModel->incrementUsage();
            }

            if ($result['success']) {
                // Clear booking intent from session if exists
                $request->session()->forget('booking_intent');

                // Redirect to payment page
                return redirect()->route('payments.checkout', $result['reservation']->id)
                    ->with('success', 'Reservation created successfully! Please complete payment.');
            } else {
                return back()->withErrors(['error' => $result['error'] ?? 'Failed to create reservation.']);
            }
        } catch (\Exception $e) {
            Log::error('Booking creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while creating your reservation. Please try again.']);
        }
    }

    /**
     * Show user's bookings
     */
    public function index()
    {
        $bookings = Auth::user()->reservations()
            ->with(['room.hotel'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('booking.index', compact('bookings'));
    }

    /**
     * Show a specific booking
     */
    public function show($id)
    {
        $booking = Auth::user()->reservations()
            ->with(['room.hotel', 'payment'])
            ->findOrFail($id);

        return view('booking.show', compact('booking'));
    }

    /**
     * Show cancellation page
     */
    public function cancel($id)
    {
        $booking = Auth::user()->reservations()
            ->with(['room.hotel', 'payment'])
            ->findOrFail($id);

        if (!$booking->canBeCancelled()) {
            return redirect()->route('bookings.show', $booking->id)
                ->withErrors(['error' => 'This reservation cannot be cancelled.']);
        }

        // Calculate potential refund
        $refundAmount = null;
        if ($booking->payment && $booking->payment->status === 'paid') {
            $checkInDate = \Carbon\Carbon::parse($booking->check_in_date);
            $daysUntilCheckIn = now()->diffInDays($checkInDate, false);
            
            if ($daysUntilCheckIn >= 7) {
                $refundAmount = $booking->payment->amount;
            } elseif ($daysUntilCheckIn >= 3) {
                $refundAmount = $booking->payment->amount * 0.5;
            }
        }

        return view('booking.cancel', compact('booking', 'refundAmount'));
    }

    /**
     * Show edit booking form
     */
    public function edit($id)
    {
        $booking = Auth::user()->reservations()
            ->with(['room.hotel', 'payment'])
            ->findOrFail($id);

        if (!$booking->canBeModified()) {
            return redirect()->route('bookings.show', $booking->id)
                ->withErrors(['error' => 'This reservation cannot be modified.']);
        }

        return view('booking.edit', compact('booking'));
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id)
    {
        $booking = Auth::user()->reservations()
            ->with(['room.hotel', 'payment'])
            ->findOrFail($id);

        if (!$booking->canBeModified()) {
            return redirect()->route('bookings.show', $booking->id)
                ->withErrors(['error' => 'This reservation cannot be modified.']);
        }

        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        try {
            $result = $this->reservationService->modifyReservation($booking->id, [
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
            ]);

            if ($result['success']) {
                $message = 'Reservation updated successfully.';
                if (isset($result['modification_fee']) && $result['modification_fee'] > 0) {
                    $message .= ' A modification fee of ₱' . number_format($result['modification_fee'], 2) . ' has been applied.';
                }
                
                return redirect()->route('bookings.show', $booking->id)
                    ->with('success', $message);
            } else {
                return back()->withErrors(['error' => $result['error'] ?? 'Failed to update reservation.']);
            }
        } catch (\Exception $e) {
            Log::error('Booking update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while updating your reservation. Please try again.']);
        }
    }

    /**
     * Process cancellation
     */
    public function processCancellation(Request $request, $id)
    {
        $booking = Auth::user()->reservations()
            ->with(['room.hotel', 'payment'])
            ->findOrFail($id);

        if (!$booking->canBeCancelled()) {
            return redirect()->route('bookings.show', $booking->id)
                ->withErrors(['error' => 'This reservation cannot be cancelled.']);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'confirm' => 'required|accepted',
        ]);

        try {
            $result = $this->reservationService->cancelReservation($booking->id, $request->reason);

            if ($result['success']) {
                // Process refund if applicable
                if (isset($result['refund_amount']) && $result['refund_amount'] > 0 && $booking->payment) {
                    // Process refund through Xendit
                    $refundResult = app(\App\Services\XenditPaymentService::class)
                        ->createRefund($booking->payment->xendit_invoice_id, $result['refund_amount'], 'Cancellation refund');
                    
                    if (!$refundResult['success']) {
                        Log::warning('Refund processing failed: ' . ($refundResult['error'] ?? 'Unknown error'));
                    }
                }

                return redirect()->route('bookings.show', $booking->id)
                    ->with('success', 'Reservation cancelled successfully. ' . 
                        (isset($result['refund_amount']) && $result['refund_amount'] > 0 
                            ? "A refund of ₱" . number_format($result['refund_amount'], 2) . " will be processed within 5-7 business days."
                            : ''));
            } else {
                return back()->withErrors(['error' => $result['error'] ?? 'Failed to cancel reservation.']);
            }
        } catch (\Exception $e) {
            Log::error('Cancellation processing failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while cancelling your reservation. Please try again.']);
        }
    }
}

