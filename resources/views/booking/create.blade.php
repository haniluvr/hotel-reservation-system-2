@extends('layouts.app')

@section('title', 'Complete Your Reservation - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

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
                
                <!-- Step 2: Reservation (Current) -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-primary-green flex items-center justify-center mb-1.5 ring-2 ring-primary-green/30">
                        <span class="text-black text-[10px] font-semibold">2</span>
                    </div>
                    <span class="text-[10px] text-primary-green font-medium text-center">Reservation</span>
                </div>
                
                <!-- Connector Line 2 -->
                <div class="flex-1 h-0.5 bg-gray-700 mx-1.5 -mt-4"></div>
                
                <!-- Step 3: Payment -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center mb-1.5">
                        <span class="text-gray-400 text-[10px] font-semibold">3</span>
                    </div>
                    <span class="text-[10px] text-gray-400 text-center">Payment</span>
                </div>
                
                <!-- Connector Line 3 -->
                <div class="flex-1 h-0.5 bg-gray-700 mx-1.5 -mt-4"></div>
                
                <!-- Step 4: Confirmation -->
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center mb-1.5">
                        <span class="text-gray-400 text-[10px] font-semibold">4</span>
                    </div>
                    <span class="text-[10px] text-gray-400 text-center">Confirmation</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 pt-4">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Complete Your Reservation</h1>
            <p class="text-gray-300 text-lg">Review your reservation details and complete your reservation</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Reservation Form -->
            <div class="lg:col-span-2">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 mb-6">
                    <h2 class="text-xl font-semibold text-white mb-6">Guest Information</h2>
                    
                    <form action="{{ route('booking.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <input type="hidden" name="check_in_date" value="{{ $checkIn }}">
                        <input type="hidden" name="check_out_date" value="{{ $checkOut }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="adults" class="block text-sm font-medium text-gray-300 mb-2">
                                    Adults <span class="text-red-400">*</span>
                                </label>
                                <select name="adults" id="adults" required
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                                    @for($i = 1; $i <= min(6, $room->max_adults); $i++)
                                        <option value="{{ $i }}" {{ $i == ($adults ?? 2) ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'Adult' : 'Adults' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label for="children" class="block text-sm font-medium text-gray-300 mb-2">
                                    Children
                                </label>
                                <select name="children" id="children"
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                                    @for($i = 0; $i <= min(4, $room->max_children); $i++)
                                        <option value="{{ $i }}" {{ $i == ($children ?? 0) ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'Child' : 'Children' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="mt-6">
                            <label for="promo_code" class="block text-sm font-medium text-gray-300 mb-2">
                                Promo Code (Optional)
                            </label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       name="promo_code" 
                                       id="promo_code"
                                       placeholder="Enter promo code"
                                       class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                                <button type="button" 
                                        id="apply-promo-btn"
                                        class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors duration-200 border border-gray-700">
                                    Apply
                                </button>
                            </div>
                            <div id="promo-message" class="mt-2 text-sm"></div>
                        </div>

                        <div class="mt-6">
                            <label for="special_requests" class="block text-sm font-medium text-gray-300 mb-2">
                                Special Requests (Optional)
                            </label>
                            <textarea name="special_requests" id="special_requests" rows="4"
                                placeholder="Any special requests or notes for your stay..."
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500"></textarea>
                        </div>

                        <div class="mt-8">
                            <button type="submit"
                                class="w-full bg-primary-green hover:bg-primary-green-hover text-white font-semibold py-4 px-6 rounded-lg transition-colors duration-200">
                                Confirm Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reservation Summary -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 sticky top-24">
                    <h2 class="text-xl font-semibold text-white mb-6">Summary</h2>

                    <!-- Room Info -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-white mb-2">{{ $room->room_type }}</h3>
                        <p class="text-sm text-gray-400 mb-4">{{ $hotel->name }}</p>
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex justify-between">
                                <span>Check-in:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($checkIn)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Check-out:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($checkOut)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Nights:</span>
                                <span class="font-medium">{{ $nights }} {{ $nights == 1 ? 'night' : 'nights' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Guests:</span>
                                <span class="font-medium">
                                    @php
                                        $totalAdults = $adults ?? 2;
                                        $totalChildren = $children ?? 0;
                                        $totalGuests = $totalAdults + $totalChildren;
                                    @endphp
                                    {{ $totalGuests }} {{ $totalGuests == 1 ? 'guest' : 'guests' }}
                                    ({{ $totalAdults }} {{ $totalAdults == 1 ? 'adult' : 'adults' }}{{ $totalChildren > 0 ? ', ' . $totalChildren . ' ' . ($totalChildren == 1 ? 'child' : 'children') : '' }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-800 pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-300">Price per night</span>
                            <span class="font-medium text-white">₱{{ number_format($room->price_per_night, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-300">Total ({{ $nights }} {{ $nights == 1 ? 'night' : 'nights' }})</span>
                            <span class="font-medium text-white" id="subtotal-amount">₱{{ number_format($room->price_per_night * $nights, 2) }}</span>
                        </div>
                        <div id="discount-row" class="hidden flex justify-between items-center mb-4">
                            <span class="text-gray-300">Discount</span>
                            <span class="font-medium text-green-400" id="discount-amount">-₱0.00</span>
                        </div>
                        <div class="border-t border-gray-800 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-white">Total Amount</span>
                                <span class="text-2xl font-bold text-primary-green" id="total-amount">₱{{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>
                        <input type="hidden" name="promo_code" id="promo_code_value" value="">
                        <input type="hidden" name="discount_amount" id="discount_amount_value" value="0">
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-800">
                        <p class="text-xs text-gray-400">
                            By confirming this reservation, you agree to our terms and conditions. 
                            Payment will be processed upon confirmation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const promoInput = document.getElementById('promo_code');
    const applyBtn = document.getElementById('apply-promo-btn');
    const promoMessage = document.getElementById('promo-message');
    const promoCodeValue = document.getElementById('promo_code_value');
    const discountRow = document.getElementById('discount-row');
    const discountAmount = document.getElementById('discount-amount');
    const discountAmountValue = document.getElementById('discount_amount_value');
    const totalAmount = document.getElementById('total-amount');
    const subtotalAmount = document.getElementById('subtotal-amount');
    
    const baseTotal = {{ $totalAmount }};
    const subtotal = {{ $room->price_per_night * $nights }};
    
    applyBtn.addEventListener('click', async function() {
        const code = promoInput.value.trim().toUpperCase();
        
        if (!code) {
            promoMessage.innerHTML = '<span class="text-red-600">Please enter a promo code</span>';
            return;
        }
        
        try {
            const response = await fetch(`/api/promo-codes/validate?code=${code}&amount=${subtotal}`);
            const data = await response.json();
            
            if (data.success) {
                promoMessage.innerHTML = `<span class="text-green-600">✓ ${data.message || 'Promo code applied!'}</span>`;
                promoCodeValue.value = code;
                discountAmountValue.value = data.discount;
                
                // Show discount row
                discountRow.classList.remove('hidden');
                discountAmount.textContent = `-₱${parseFloat(data.discount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                
                // Update total
                const finalTotal = subtotal - parseFloat(data.discount);
                totalAmount.textContent = `₱${finalTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            } else {
                promoMessage.innerHTML = `<span class="text-red-600">${data.message || 'Invalid promo code'}</span>`;
                promoCodeValue.value = '';
                discountAmountValue.value = '0';
                discountRow.classList.add('hidden');
                totalAmount.textContent = `₱${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
        } catch (error) {
            promoMessage.innerHTML = '<span class="text-red-600">Error validating promo code. Please try again.</span>';
        }
    });
});
</script>
@endsection

