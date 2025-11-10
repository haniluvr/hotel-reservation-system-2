@extends('layouts.app')

@section('title', 'Booking Details - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black pt-24 pb-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('account.index') }}" class="text-primary-green hover:text-primary-green-hover mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Account
            </a>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Booking Details</h1>
            <p class="text-gray-300 text-lg">Reservation #{{ $booking->reservation_number }}</p>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
            <!-- Status Banner -->
            <div class="bg-gradient-to-r from-primary-green to-primary-green-hover p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">{{ $booking->room->room_type }}</h2>
                        <p class="text-white/90">Belmont Hotel</p>
                    </div>
                    <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-medium">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <!-- Booking Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Reservation Details</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Reservation Number:</span>
                                <span class="font-medium text-white">{{ $booking->reservation_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Check-in Date:</span>
                                <span class="font-medium text-white">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Check-out Date:</span>
                                <span class="font-medium text-white">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Nights:</span>
                                <span class="font-medium text-white">
                                    {{ \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) }} 
                                    {{ \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) == 1 ? 'night' : 'nights' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Guests:</span>
                                <span class="font-medium text-white">
                                    {{ $booking->adults }} {{ $booking->adults == 1 ? 'adult' : 'adults' }}
                                    @if($booking->children > 0)
                                        , {{ $booking->children }} {{ $booking->children == 1 ? 'child' : 'children' }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Booking Date:</span>
                                <span class="font-medium text-white">{{ $booking->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Pricing</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Price per night:</span>
                                <span class="font-medium text-white">₱{{ number_format($booking->room->price_per_night, 2) }}</span>
                            </div>
                            @if($booking->discount_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Discount:</span>
                                    <span class="font-medium text-green-400">-₱{{ number_format($booking->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="border-t border-gray-800 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-white">Total Amount:</span>
                                    <span class="text-2xl font-bold text-primary-green">₱{{ number_format($booking->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Requests -->
                @if($booking->special_requests)
                    <div class="mb-8 p-4 bg-gray-800 rounded-lg border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-2">Special Requests</h3>
                        <p class="text-gray-300">{{ $booking->special_requests }}</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row gap-4">
                    @if($booking->status == 'pending')
                        <a href="{{ route('payments.checkout', $booking->id) }}" class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors duration-200 text-center">
                            Complete Payment
                        </a>
                    @endif
                    <a href="{{ route('rooms.show', $booking->room->slug) }}"
                        class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors duration-200 text-center">
                        View Room Details
                    </a>
                    @if(in_array($booking->status, ['pending', 'confirmed']))
                        <a href="{{ route('bookings.cancel', $booking->id) }}" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors duration-200 text-center ml-auto">
                            Cancel Booking
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

