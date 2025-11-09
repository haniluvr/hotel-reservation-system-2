@extends('layouts.app')

@section('title', 'Cancel Booking - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('bookings.show', $booking->id) }}" class="text-primary-green hover:text-primary-green-hover mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Booking Details
            </a>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Cancel Reservation</h1>
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

        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
            <!-- Warning Banner -->
            <div class="bg-red-900/20 border-b border-red-800 p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-400 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h2 class="text-xl font-bold text-red-300 mb-2">Are you sure you want to cancel this reservation?</h2>
                        <p class="text-red-200">This action cannot be undone. Please review the cancellation policy below.</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Booking Summary -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4">Reservation Details</h3>
                    <div class="bg-gray-800 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Hotel:</span>
                            <span class="font-medium text-white">{{ $booking->room->hotel->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Room Type:</span>
                            <span class="font-medium text-white">{{ $booking->room->room_type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Check-in:</span>
                            <span class="font-medium text-white">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Check-out:</span>
                            <span class="font-medium text-white">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Total Amount:</span>
                            <span class="font-medium text-white">₱{{ number_format($booking->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cancellation Policy -->
                <div class="mb-8 p-4 bg-blue-900/20 border border-blue-800 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-300 mb-3">Cancellation Policy</h3>
                    <ul class="space-y-2 text-sm text-blue-200">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>7+ days before check-in:</strong> Full refund (100%)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>3-6 days before check-in:</strong> Partial refund (50%)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Less than 3 days before check-in:</strong> No refund</span>
                        </li>
                    </ul>
                </div>

                @php
                    $checkInDate = \Carbon\Carbon::parse($booking->check_in_date);
                    $daysUntilCheckIn = now()->diffInDays($checkInDate, false);
                @endphp

                <!-- Refund Information -->
                @if($refundAmount && $refundAmount > 0)
                <div class="mb-8 p-4 bg-green-900/20 border border-green-800 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-300">Refund Information</h3>
                    </div>
                    <p class="text-green-200 mb-2">
                        Based on our cancellation policy, you are eligible for a refund:
                    </p>
                    <p class="text-2xl font-bold text-green-400">
                        ₱{{ number_format($refundAmount, 2) }}
                    </p>
                    <p class="text-sm text-green-300 mt-2">
                        Refunds will be processed within 5-7 business days to your original payment method.
                    </p>
                </div>
                @elseif($booking->payment && $booking->payment->status === 'paid')
                <div class="mb-8 p-4 bg-yellow-900/20 border border-yellow-800 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-yellow-300">No Refund Available</h3>
                    </div>
                    <p class="text-yellow-200">
                        Based on our cancellation policy, cancellations made less than 3 days before check-in are not eligible for a refund.
                    </p>
                </div>
                @endif

                <!-- Cancellation Form -->
                <form action="{{ route('bookings.cancel.process', $booking->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-300 mb-2">
                            Reason for Cancellation <span class="text-red-400">*</span>
                        </label>
                        <textarea 
                            id="reason" 
                            name="reason" 
                            rows="4" 
                            class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500"
                            placeholder="Please tell us why you're cancelling this reservation..."
                            required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start">
                        <input 
                            type="checkbox" 
                            id="confirm" 
                            name="confirm" 
                            value="1"
                            class="mt-1 rounded border-gray-600 bg-gray-800 text-primary-green focus:ring-primary-green focus:ring-offset-0"
                            required>
                        <label for="confirm" class="ml-3 text-sm text-gray-300">
                            I understand that this cancellation is final and cannot be undone. 
                            @if($refundAmount && $refundAmount > 0)
                                I acknowledge that I will receive a refund of ₱{{ number_format($refundAmount, 2) }} within 5-7 business days.
                            @elseif($booking->payment && $booking->payment->status === 'paid')
                                I acknowledge that I am not eligible for a refund based on the cancellation policy.
                            @endif
                        </label>
                    </div>
                    @error('confirm')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="flex gap-4 pt-4">
                        <button 
                            type="submit" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            Confirm Cancellation
                        </button>
                        <a 
                            href="{{ route('bookings.show', $booking->id) }}" 
                            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors text-center">
                            Keep Reservation
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

