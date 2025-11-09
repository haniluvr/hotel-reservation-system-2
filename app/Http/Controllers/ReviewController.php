<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for a hotel.
     */
    public function index(Request $request, $hotelId)
    {
        $reviews = Review::with(['user', 'room'])
            ->where('hotel_id', $hotelId)
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reviews.index', compact('reviews', 'hotelId'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create($reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'review'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        // Check if reservation is completed
        if ($reservation->status !== 'completed') {
            return redirect()->route('bookings.show', $reservation->id)
                ->withErrors(['error' => 'You can only review completed reservations.']);
        }

        // Check if review already exists
        if ($reservation->review) {
            return redirect()->route('reviews.edit', $reservation->review->id)
                ->with('info', 'You have already reviewed this reservation.');
        }

        return view('reviews.create', compact('reservation'));
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request)
    {
        $reservation = Reservation::with(['room.hotel'])
            ->where('user_id', Auth::id())
            ->findOrFail($request->reservation_id);

        // Check if reservation is completed
        if ($reservation->status !== 'completed') {
            return back()->withErrors(['error' => 'You can only review completed reservations.']);
        }

        // Check if review already exists
        if ($reservation->review) {
            return back()->withErrors(['error' => 'You have already reviewed this reservation.']);
        }

        try {
            $review = Review::create([
                'user_id' => Auth::id(),
                'reservation_id' => $reservation->id,
                'hotel_id' => $reservation->room->hotel_id,
                'room_id' => $reservation->room_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending', // Requires admin approval
            ]);

            return redirect()->route('bookings.show', $reservation->id)
                ->with('success', 'Thank you for your review! It will be published after moderation.');
        } catch (\Exception $e) {
            Log::error('Review creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while submitting your review. Please try again.']);
        }
    }

    /**
     * Show the form for editing a review.
     */
    public function edit($id)
    {
        $review = Review::with(['reservation.room.hotel'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review.
     */
    public function update(StoreReviewRequest $request, $id)
    {
        $review = Review::with(['reservation'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Only allow editing if pending or approved
        if ($review->status === 'rejected') {
            return back()->withErrors(['error' => 'This review cannot be edited.']);
        }

        try {
            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending', // Reset to pending after edit
            ]);

            return redirect()->route('bookings.show', $review->reservation_id)
                ->with('success', 'Review updated successfully. It will be republished after moderation.');
        } catch (\Exception $e) {
            Log::error('Review update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while updating your review. Please try again.']);
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy($id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);

        try {
            $reservationId = $review->reservation_id;
            $review->delete();

            return redirect()->route('bookings.show', $reservationId)
                ->with('success', 'Review deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Review deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while deleting your review. Please try again.']);
        }
    }
}
