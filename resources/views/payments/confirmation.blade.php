@extends('layouts.app')

@section('title', 'Reservation Confirmed - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black pt-24 pb-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-xl mx-auto">
                <!-- Step 1: Select Room -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-primary-green flex items-center justify-center mb-1.5">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-[10px] text-gray-400 text-center">Select Room</span>
                </div>
                
                <!-- Connector Line 1 -->
                <div class="flex-1 h-0.5 bg-primary-green mx-1.5 -mt-4"></div>
                
                <!-- Step 2: Reservation -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-primary-green flex items-center justify-center mb-1.5">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-[10px] text-gray-400 text-center">Reservation</span>
                </div>
                
                <!-- Connector Line 2 -->
                <div class="flex-1 h-0.5 bg-primary-green mx-1.5 -mt-4"></div>
                
                <!-- Step 3: Payment -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-primary-green flex items-center justify-center mb-1.5">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-[10px] text-gray-400 text-center">Payment</span>
                </div>
                
                <!-- Connector Line 3 -->
                <div class="flex-1 h-0.5 bg-primary-green mx-1.5 -mt-4"></div>
                
                <!-- Step 4: Confirmation (Current) -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-primary-green flex items-center justify-center mb-1.5 ring-2 ring-primary-green/30">
                        <span class="text-black text-[10px] font-semibold">4</span>
                    </div>
                    <span class="text-[10px] text-primary-green font-medium text-center">Confirmation</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 pt-4 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 text-primary-green mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Reservation Confirmed!</h1>
            <p class="text-gray-300 text-lg">Reservation #{{ $reservation->reservation_number }}</p>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
            <!-- Status Banner -->
            <div class="bg-gradient-to-r from-primary-green to-primary-green-hover p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">{{ $reservation->room->room_type }}</h2>
                        <p class="text-white/90">{{ $reservation->room->hotel->name }}</p>
                    </div>
                    <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-medium">
                        @if($reservation->payment->status === 'paid')
                            Confirmed
                        @else
                            Pending Payment
                        @endif
                    </span>
                </div>
            </div>

            <div class="p-6">
                <!-- Payment Notice -->
                @if($reservation->payment->payment_method === 'cash')
                <div class="bg-yellow-900/20 border border-yellow-800 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-300 mb-2">Cash Payment Required</h3>
                            <p class="text-yellow-200">Please pay in cash when you arrive at the hotel. Your reservation will be confirmed upon payment completion.</p>
                        </div>
                    </div>
                </div>
                @elseif($reservation->payment->status === 'paid')
                <div class="bg-green-900/20 border border-green-800 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-green-300 mb-2">Payment Successful!</h3>
                            <p class="text-green-200">Your payment has been processed successfully. Your reservation is now confirmed.</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-300 mb-2">Payment Pending</h3>
                            <p class="text-blue-200">Your payment is being processed. You will receive a confirmation email once it is confirmed.</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Reservation Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Reservation Details</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Reservation Number:</span>
                                <span class="font-medium text-white">{{ $reservation->reservation_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Check-in Date:</span>
                                <span class="font-medium text-white">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Check-out Date:</span>
                                <span class="font-medium text-white">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Nights:</span>
                                <span class="font-medium text-white">
                                    {{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} 
                                    {{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) == 1 ? 'night' : 'nights' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Guests:</span>
                                <span class="font-medium text-white">
                                    {{ $reservation->adults }} {{ $reservation->adults == 1 ? 'adult' : 'adults' }}
                                    @if($reservation->children > 0)
                                        , {{ $reservation->children }} {{ $reservation->children == 1 ? 'child' : 'children' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Pricing</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Price per night:</span>
                                <span class="font-medium text-white">₱{{ number_format($reservation->room->price_per_night, 2) }}</span>
                            </div>
                            @if($reservation->discount_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Discount:</span>
                                    <span class="font-medium text-green-400">-₱{{ number_format($reservation->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="border-t border-gray-800 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-white">Total Amount:</span>
                                    <span class="text-2xl font-bold text-primary-green">₱{{ number_format($reservation->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Requests -->
                @if($reservation->special_requests)
                    <div class="mb-8 p-4 bg-gray-800 rounded-lg border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-2">Special Requests</h3>
                        <p class="text-gray-300">{{ $reservation->special_requests }}</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row gap-4">
                    @if($reservation->payment && $reservation->payment->status === 'paid')
                    <a href="{{ route('invoices.download', $reservation->id) }}" 
                       class="px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white font-medium rounded-lg transition-colors duration-200 text-center inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Receipt
                    </a>
                    @endif
                    <a href="{{ route('bookings.show', $reservation->id) }}" 
                       class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors duration-200 text-center">
                        View Reservation Details
                    </a>
                    <a href="{{ route('home') }}" 
                       class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors duration-200 text-center">
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

