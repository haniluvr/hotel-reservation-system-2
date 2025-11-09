@extends('layouts.app')

@section('title', 'Payment Checkout - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Complete Payment</h1>
            <p class="text-gray-300 text-lg">Reservation #{{ $reservation->reservation_number }}</p>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-900/20 border border-green-800 text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-900/20 border border-red-800 text-red-300 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 mb-6">
                    <h2 class="text-xl font-semibold text-white mb-6">Payment Method</h2>
                    
                    <form action="{{ route('payments.process', $reservation->id) }}" method="POST" id="payment-form">
                        @csrf
                        
                        <!-- Payment Methods -->
                        <div class="space-y-4 mb-6">
                            <label class="flex items-center p-4 border-2 border-gray-700 rounded-lg cursor-pointer hover:border-primary-green transition-colors bg-gray-800">
                                <input type="radio" name="payment_method" value="credit_card" class="mr-4 text-primary-green focus:ring-primary-green" required checked>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">Credit Card</div>
                                    <div class="text-sm text-gray-400">Visa, Mastercard, Amex</div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </label>

                            <label class="flex items-center p-4 border-2 border-gray-700 rounded-lg cursor-pointer hover:border-primary-green transition-colors bg-gray-800">
                                <input type="radio" name="payment_method" value="debit_card" class="mr-4 text-primary-green focus:ring-primary-green" required>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">Debit Card</div>
                                    <div class="text-sm text-gray-400">Direct bank debit</div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </label>

                            <label class="flex items-center p-4 border-2 border-gray-700 rounded-lg cursor-pointer hover:border-primary-green transition-colors bg-gray-800">
                                <input type="radio" name="payment_method" value="e_wallet" class="mr-4 text-primary-green focus:ring-primary-green" required>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">E-Wallet</div>
                                    <div class="text-sm text-gray-400">GCash, PayMaya, GrabPay</div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </label>

                            <label class="flex items-center p-4 border-2 border-gray-700 rounded-lg cursor-pointer hover:border-primary-green transition-colors bg-gray-800">
                                <input type="radio" name="payment_method" value="bank_transfer" class="mr-4 text-primary-green focus:ring-primary-green" required>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">Bank Transfer</div>
                                    <div class="text-sm text-gray-400">Direct bank transfer</div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M8 6l4-4 4 4M3 10V8a2 2 0 012-2h14a2 2 0 012 2v2"></path>
                                </svg>
                            </label>
                        </div>

                        <!-- Security Notice -->
                        <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <div class="text-sm text-blue-300">
                                    <p class="font-semibold mb-1">Secure Payment</p>
                                    <p>Your payment information is encrypted and secure. We use industry-standard security measures to protect your data.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-primary-green hover:bg-primary-green-hover text-white font-semibold py-4 px-6 rounded-lg transition-colors duration-200">
                            Pay ₱{{ number_format($reservation->total_amount, 2) }}
                        </button>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Need Help?</h3>
                    <div class="space-y-3 text-sm text-gray-300">
                        <p>If you encounter any issues with payment, please contact our support team:</p>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+63 2 1234 5678</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>support@belmonthotel.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 sticky top-24">
                    <h2 class="text-xl font-semibold text-white mb-6">Booking Summary</h2>

                    <!-- Room Info -->
                    <div class="mb-6 pb-6 border-b border-gray-800">
                        <h3 class="font-semibold text-white mb-2">{{ $reservation->room->room_type }}</h3>
                        <p class="text-sm text-gray-400 mb-4">{{ $reservation->room->hotel->name }}</p>
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex justify-between">
                                <span>Check-in:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Check-out:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Nights:</span>
                                <span class="font-medium">
                                    {{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} 
                                    {{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) == 1 ? 'night' : 'nights' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Guests:</span>
                                <span class="font-medium">
                                    {{ $reservation->adults }} {{ $reservation->adults == 1 ? 'adult' : 'adults' }}
                                    @if($reservation->children > 0)
                                        , {{ $reservation->children }} {{ $reservation->children == 1 ? 'child' : 'children' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="border-t border-gray-800 pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-300">Subtotal</span>
                            <span class="font-medium text-white">₱{{ number_format($reservation->room->price_per_night * \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)), 2) }}</span>
                        </div>
                        @if($reservation->discount_amount > 0)
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-300">Discount</span>
                                <span class="font-medium text-green-400">-₱{{ number_format($reservation->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-800 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-white">Total Amount</span>
                                <span class="text-2xl font-bold text-primary-green">₱{{ number_format($reservation->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-800">
                        <a href="{{ route('bookings.show', $reservation->id) }}" 
                           class="block text-center text-sm text-primary-green hover:text-primary-green-hover font-medium">
                            ← Back to Booking Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

