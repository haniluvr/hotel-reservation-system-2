@extends('layouts.app')

@section('title', 'Edit Booking - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('bookings.show', $booking->id) }}" class="text-primary-green hover:text-primary-green-hover mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Booking Details
            </a>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Modify Reservation</h1>
            <p class="text-gray-300 text-lg">Reservation #{{ $booking->reservation_number }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-900/20 border border-red-800 text-red-300 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
            <!-- Current Booking Info -->
            <div class="mb-6 p-4 bg-gray-800 rounded-lg">
                <h3 class="text-lg font-semibold text-white mb-3">Current Booking</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-300">Check-in:</span>
                        <span class="font-medium text-white ml-2">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-300">Check-out:</span>
                        <span class="font-medium text-white ml-2">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-300">Nights:</span>
                        <span class="font-medium text-white ml-2">{{ $booking->getTotalNights() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-300">Total Amount:</span>
                        <span class="font-medium text-white ml-2">₱{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Modification Notice -->
            <div class="mb-6 p-4 bg-yellow-900/20 border border-yellow-800 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-400 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-300 mb-2">Modification Policy</h3>
                        <ul class="text-sm text-yellow-200 space-y-1">
                            <li>• Changes made more than 7 days before check-in may incur a 10% modification fee</li>
                            <li>• Price will be recalculated based on new dates and current room rates</li>
                            <li>• If the new dates are unavailable, modification will be rejected</li>
                            <li>• Promo codes will be revalidated for the new amount</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form action="{{ route('bookings.update', $booking->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Check-in Date -->
                    <div>
                        <label for="check_in_date" class="block text-sm font-medium text-gray-300 mb-2">
                            New Check-in Date <span class="text-red-400">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="check_in_date" 
                            name="check_in_date" 
                            value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}"
                            min="{{ now()->format('Y-m-d') }}"
                            class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white"
                            required>
                        @error('check_in_date')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Check-out Date -->
                    <div>
                        <label for="check_out_date" class="block text-sm font-medium text-gray-300 mb-2">
                            New Check-out Date <span class="text-red-400">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="check_out_date" 
                            name="check_out_date" 
                            value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}"
                            class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white"
                            required>
                        @error('check_out_date')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-primary-green hover:bg-primary-green-hover text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Update Reservation
                    </button>
                    <a 
                        href="{{ route('bookings.show', $booking->id) }}" 
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
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');

    checkInInput.addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        checkOutInput.min = checkInDate.toISOString().split('T')[0];
        
        if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
            checkOutInput.value = '';
        }
    });
});
</script>
@endsection

