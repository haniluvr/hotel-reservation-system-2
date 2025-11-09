@extends('layouts.app')

@section('title', 'The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<!-- Scroll Expansion Hero with Integrated Content -->
<div 
    data-react-component="ScrollExpandMedia" 
    data-react-props="{{ json_encode([
        'mediaType' => 'video',
        'mediaSrc' => asset('storage/promo-vid.mp4'),
        'posterSrc' => '',
        'bgImageSrc' => asset(path: 'storage/hero-bg.jpg'),
        'title' => 'Discover Your Perfect',
        'date' => 'Getaway',
        'scrollToExpand' => 'Scroll to Explore',
        'textBlend' => false
    ]) }}"
    style="min-height: 100vh;"
>
    <!-- Hero Content that appears when video is fully expanded -->
    <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                Discover Your Perfect
                <span class="text-primary-green">Getaway</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-200 mb-12 max-w-3xl mx-auto">
                Experience luxury and comfort at Belmont Hotel El Nido, Palawan
            </p>
        </div>

        <!-- Search Form -->
        <div class="max-w-5xl mx-auto">
            <form action="{{ route('hotels.search') }}" method="GET" class="bg-black/80 backdrop-blur-md p-6 rounded-2xl shadow-2xl border border-gray-800">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Room Type -->
                    <div class="space-y-2">
                        <label for="room_type" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Room Type
                        </label>
                        <select id="room_type"
                                name="room_type" 
                                class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="">All Room Types</option>
                            @php
                                $roomTypes = \App\Models\Room::select('room_type')
                                    ->distinct()
                                    ->orderBy('room_type')
                                    ->pluck('room_type');
                            @endphp
                            @foreach($roomTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Check-in -->
                    <div class="space-y-2">
                        <label for="check_in" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Check-in
                        </label>
                        <input type="date" 
                               id="check_in"
                               name="check_in" 
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white"
                               value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Check-out -->
                    <div class="space-y-2">
                        <label for="check_out" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Check-out
                        </label>
                        <input type="date" 
                               id="check_out"
                               name="check_out" 
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white"
                               value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>

                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-primary-green hover:bg-primary-green-hover text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search Availability
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Why Choose Us Section -->
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl  font-bold text-white mb-4">
                Why Choose Belmont Hotel
            </h2>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Your trusted partner for unforgettable travel experiences
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-primary-green/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-primary-green transition-all duration-300 group-hover:scale-110">
                    <svg class="w-10 h-10 text-primary-green group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Best Price Guarantee</h3>
                <p class="text-gray-300">We guarantee the best rates for your stay, or we'll match the difference.</p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-primary-green/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-primary-green transition-all duration-300 group-hover:scale-110">
                    <svg class="w-10 h-10 text-primary-green group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">24/7 Support</h3>
                <p class="text-gray-300">Our customer service team is available around the clock to assist you.</p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-primary-green/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-primary-green transition-all duration-300 group-hover:scale-110">
                    <svg class="w-10 h-10 text-primary-green group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Secure Booking</h3>
                <p class="text-gray-300">Your personal information and payments are protected with industry-standard security.</p>
            </div>

            <!-- Feature 4 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-primary-green/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-primary-green transition-all duration-300 group-hover:scale-110">
                    <svg class="w-10 h-10 text-primary-green group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Free Cancellation</h3>
                <p class="text-gray-300">Cancel your booking up to 24 hours before check-in with no fees.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms Carousel -->
@if(isset($featuredRooms) && $featuredRooms->count() > 0)
<div 
    data-react-component="RoomsCarousel" 
    data-react-props="{{ json_encode(['rooms' => $featuredRooms, 'hotelId' => $hotel->id]) }}"
    style="min-height: 400px;"
>
    <!-- Loading fallback -->
    <div class="py-20 bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-300">Loading featured rooms...</p>
        </div>
    </div>
</div>
@else
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center py-12">
            <p class="text-gray-300 text-lg">No featured rooms available at the moment.</p>
        </div>
    </div>
</section>
@endif

<!-- Popular Attractions Section -->
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl  font-bold text-white mb-4">
                Popular Attractions
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Explore the breathtaking attractions near Belmont Hotel El Nido, each offering unique experiences and natural beauty.
            </p>
        </div>

        <!-- React Destination Cards Mount Point -->
        <div 
            id="destination-cards-mount" 
            data-react-component="DestinationCards" 
            data-react-props="{{ json_encode(['destinations' => $popularDestinations]) }}" 
            class="min-h-[450px]">
            <!-- Fallback content (will be replaced by React) -->
            <div class="text-center py-12 text-gray-400">
                <p>Loading attractions...</p>
                <p class="text-xs mt-2">If this message persists, check the browser console for errors.</p>
                </div>
            </div>

        <!-- Debug: Show raw data -->
        <script>
            console.log('Popular Destinations Data:', @json($popularDestinations));
        </script>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl  font-bold text-white mb-4">
                What Our Guests Say
            </h2>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Real experiences from travelers who booked with us
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-gray-900 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-800">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                <p class="text-gray-300 mb-6 italic">
                    "Absolutely amazing experience! The booking process was seamless and the hotel exceeded all our expectations. Highly recommend!"
                </p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-green rounded-full flex items-center justify-center text-white font-bold mr-4">
                        SM
                    </div>
                    <div>
                        <p class="font-semibold text-white">Sarah Martinez</p>
                        <p class="text-sm text-gray-400">Manila, Philippines</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-gray-900 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-800">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                <p class="text-gray-300 mb-6 italic">
                    "Great customer service and the best prices I found. The hotel was beautiful and the staff was incredibly helpful throughout our stay."
                </p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-green rounded-full flex items-center justify-center text-white font-bold mr-4">
                        JC
                    </div>
                    <div>
                        <p class="font-semibold text-white">John Cruz</p>
                        <p class="text-sm text-gray-400">Cebu, Philippines</p>
                    </div>
                </div>
                </div>

            <!-- Testimonial 3 -->
            <div class="bg-gray-900 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-800">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                            </div>
                        </div>
                <p class="text-gray-300 mb-6 italic">
                    "Easy booking, great prices, and wonderful hotels. This is now my go-to platform for all my travel bookings in the Philippines."
                </p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-green rounded-full flex items-center justify-center text-white font-bold mr-4">
                        ML
                    </div>
                    <div>
                        <p class="font-semibold text-white">Maria Lopez</p>
                        <p class="text-sm text-gray-400">Davao, Philippines</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-black">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl md:text-5xl  font-bold text-white mb-6">
            Ready to Plan Your Next Adventure?
        </h2>
        <p class="text-xl text-gray-300 mb-8">
            Start exploring our luxurious rooms at Belmont Hotel El Nido and find your perfect stay today.
        </p>
        <a href="{{ route('hotels.show', $hotel->id) }}" 
           class="inline-flex items-center px-8 py-4 bg-primary-green text-white font-semibold rounded-lg hover:bg-primary-green-hover transition-all duration-300 transform hover:scale-105 shadow-xl">
            View All Rooms
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>

@endsection











