<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->reservations()->with(['room.hotel', 'payment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('check_in_from')) {
            $query->where('check_in_date', '>=', $request->check_in_from);
        }

        if ($request->filled('check_in_to')) {
            $query->where('check_in_date', '<=', $request->check_in_to);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
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
            'promo_code' => 'nullable|string',
        ]);

        try {
            $reservationData = [
                'user_id' => Auth::id(),
                'room_id' => $request->room_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'special_requests' => $request->special_requests,
                'promo_code' => $request->promo_code,
            ];

            $result = $this->reservationService->createReservation($reservationData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully',
                    'data' => $result['reservation']->load(['room.hotel', 'payment']),
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to create reservation',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Reservation API creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating your reservation',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Reservation::with(['room.hotel', 'payment', 'user'])
            ->findOrFail($id);

        // Check if user is authorized (owner or admin)
        if (Auth::check() && (Auth::id() === $reservation->user_id || Auth::user()->email === 'admin@belmonthotel.com')) {
            return response()->json([
                'success' => true,
                'data' => $reservation,
            ]);
        }

        // Public access with limited info (for confirmation pages)
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'status' => $reservation->status,
                'check_in_date' => $reservation->check_in_date,
                'check_out_date' => $reservation->check_out_date,
                'total_amount' => $reservation->total_amount,
                'room' => [
                    'room_type' => $reservation->room->room_type,
                    'hotel' => [
                        'name' => $reservation->room->hotel->name,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check authorization
        if (Auth::id() !== $reservation->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'check_in_date' => 'sometimes|date|after_or_equal:today',
            'check_out_date' => 'sometimes|date|after:check_in_date',
            'adults' => 'sometimes|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        try {
            // Only allow updates for pending reservations
            if ($reservation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending reservations can be modified',
                ], 400);
            }

            $reservation->fill($request->only([
                'check_in_date',
                'check_out_date',
                'adults',
                'children',
                'special_requests',
            ]));

            // Recalculate total if dates changed
            if ($request->has('check_in_date') || $request->has('check_out_date')) {
                $checkIn = \Carbon\Carbon::parse($reservation->check_in_date);
                $checkOut = \Carbon\Carbon::parse($reservation->check_out_date);
                $nights = $checkIn->diffInDays($checkOut);
                $reservation->total_amount = $reservation->room->price_per_night * $nights;
            }

            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully',
                'data' => $reservation->load(['room.hotel', 'payment']),
            ]);
        } catch (\Exception $e) {
            Log::error('Reservation API update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your reservation',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (Cancel reservation).
     */
    public function destroy(string $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check authorization
        if (Auth::id() !== $reservation->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $result = $this->reservationService->cancelReservation($reservation->id, 'Cancelled by user via API');

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reservation cancelled successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to cancel reservation',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Reservation API cancellation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling your reservation',
            ], 500);
        }
    }
}

