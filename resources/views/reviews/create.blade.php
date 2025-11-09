@extends('layouts.app')

@section('title', 'Write a Review - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('bookings.show', $reservation->id) }}" class="text-primary-green hover:text-primary-green-hover mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Booking
            </a>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Write a Review</h1>
            <p class="text-gray-300">Share your experience with {{ $reservation->room->hotel->name }}</p>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
            <!-- Booking Summary -->
            <div class="mb-6 p-4 bg-gray-800 rounded-lg">
                <h3 class="text-lg font-semibold text-white mb-3">Your Stay</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Hotel:</span>
                        <span class="font-medium text-white">{{ $reservation->room->hotel->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Room:</span>
                        <span class="font-medium text-white">{{ $reservation->room->room_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Check-in:</span>
                        <span class="font-medium text-white">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('reviews.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">
                        Rating <span class="text-red-400">*</span>
                    </label>
                    <div class="flex items-center space-x-2" id="rating-container">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    data-rating="{{ $i }}"
                                    class="star-button w-12 h-12 text-gray-400 hover:text-yellow-400 transition-colors focus:outline-none">
                                <svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="5" required>
                    @error('rating')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comment -->
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-300 mb-2">
                        Your Review
                    </label>
                    <textarea 
                        id="comment" 
                        name="comment" 
                        rows="6" 
                        class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500"
                        placeholder="Share your experience...">{{ old('comment') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
                    @error('comment')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-primary-green hover:bg-primary-green-hover text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Submit Review
                    </button>
                    <a 
                        href="{{ route('bookings.show', $reservation->id) }}" 
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-button');
    const ratingInput = document.getElementById('rating-input');
    let selectedRating = 5;

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            selectedRating = index + 1;
            ratingInput.value = selectedRating;
            updateStars();
        });

        star.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
    });

    document.getElementById('rating-container').addEventListener('mouseleave', function() {
        updateStars();
    });

    function updateStars() {
        stars.forEach((star, index) => {
            if (index < selectedRating) {
                star.classList.remove('text-gray-400');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-400');
            }
        });
    }

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-400');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-400');
            }
        });
    }

    updateStars();
});
</script>
@endsection

