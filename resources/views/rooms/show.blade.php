@extends('layouts.app')

@section('title', $room->room_type . ' - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<!-- Breadcrumbs -->
<section class="pt-20 pb-6 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-400">
            <a href="{{ route('home') }}" class="hover:text-primary-green">Home</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('accommodations.index') }}" class="hover:text-primary-green">Accommodations</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-300">{{ $room->room_type }}</span>
        </nav>
    </div>
</section>

<!-- Two Column Layout: Gallery + Room Info (Left) | Reservation Container (Right) -->
@php
    $roomImages = $room->getRoomImages();
    // Fallback to at least one placeholder if no images found
    if (empty($roomImages)) {
        $roomImages = ['https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'];
    }
    // Ensure all URLs use forward slashes and are properly formatted
    $roomImages = array_map(function($url) {
        return str_replace('\\', '/', $url);
    }, $roomImages);
@endphp

<script>
    window.roomGalleryImages = @json($roomImages);
</script>

<section class="py-6 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Gallery and Room Information -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Room Gallery -->
                <div class="relative" 
                     x-data="{
                         selectedImage: 0,
                         images: window.roomGalleryImages || [],
                         init() {
                             if (!this.images || this.images.length === 0) {
                                 this.images = ['https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'];
                             }
                         }
                     }">
                    <!-- Main Image -->
                    <div class="relative h-96 md:h-[500px] rounded-2xl overflow-hidden mb-4 bg-gray-900">
                        <img :src="images[selectedImage]" 
                             :alt="'{{ $room->room_type }} - Image ' + (selectedImage + 1)"
                             class="w-full h-full object-cover transition-opacity duration-300"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';">
                    </div>
                    
                    <!-- Thumbnails -->
                    <div class="flex gap-3 overflow-x-auto pb-2">
                        <template x-for="(image, index) in images" :key="index">
                            <button @click="selectedImage = index"
                                    :class="selectedImage === index ? 'ring-2 ring-primary-green' : 'opacity-60 hover:opacity-100'"
                                    class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden transition-all duration-200">
                                <img :src="image" 
                                     :alt="'Thumbnail ' + (index + 1)"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.style.display='none';">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Room Name and Location -->
                <div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4">
                        {{ $room->room_type }}
                    </h1>
                    <p class="text-lg text-gray-300 mb-6">Belmont Hotel, El Nido, Palawan</p>
                    
                    <!-- Description -->
                    <p class="text-gray-300 mb-8 leading-relaxed">{{ $room->description }}</p>
                </div>
                
                <!-- Room Information -->
                <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">Room Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Room Type</span>
                            <span class="text-white">{{ $room->room_type }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Maximum Guests</span>
                            <span class="text-white">{{ $room->max_guests }} guests</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Maximum Adults</span>
                            <span class="text-white">{{ $room->max_adults }} adults</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Maximum Children</span>
                            <span class="text-white">{{ $room->max_children }} children</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Room Size</span>
                            <span class="text-white">{{ $room->size ?? 'Standard' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-800">
                            <span class="font-medium text-gray-300">Price per Night</span>
                            <span class="text-primary-green font-semibold">₱{{ number_format($room->price_per_night, 0) }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium text-gray-300">Availability</span>
                            <span class="text-green-400 font-semibold">{{ $room->available_quantity }} rooms available</span>
                        </div>
                    </div>
                </div>
                
                <!-- Room Amenities (Tags/Badges) -->
                @if($room->amenities && count($room->amenities) > 0)
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">Room Amenities</h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach($room->amenities as $amenity)
                        <span class="inline-flex items-center px-4 py-2 bg-gray-900 border border-gray-800 text-gray-300 rounded-full text-sm font-medium hover:border-primary-green/50 transition-colors">
                            {{ $amenity }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Reviews Section -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-white">Guest Reviews</h2>
                        @if($averageRating > 0)
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <span class="text-2xl font-bold text-white mr-2">{{ number_format($averageRating, 1) }}</span>
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($averageRating))
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            <span class="text-gray-400 text-sm">({{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }})</span>
                        </div>
                        @endif
                    </div>

                    @if($reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div class="flex text-yellow-400 mr-3">
                                                @for($i = 0; $i < $review->rating; $i++)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-white font-semibold">{{ $review->user->name ?? 'Anonymous' }}</span>
                                        </div>
                                        <p class="text-sm text-gray-400">{{ $review->created_at->format('F d, Y') }}</p>
                                    </div>
                                </div>
                                
                                @if($review->comment)
                                    <p class="text-gray-300 leading-relaxed">{{ $review->comment }}</p>
                                @else
                                    <p class="text-gray-500 italic">No comment provided.</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-8 text-center">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <p class="text-gray-400 text-lg">No reviews yet. Be the first to review this room!</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Right Column: Sticky Reservation Container -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 sticky top-24">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-primary-green mb-2">
                            ₱{{ number_format($room->price_per_night, 0) }}
                        </div>
                        <div class="text-gray-400">per night</div>
                    </div>
                    
                    @if($room->available_quantity > 0)
                        <form id="reserve-form" action="{{ route('booking.create') }}" method="GET" class="space-y-4">
                            <input type="hidden" name="room_id" value="{{ $room->id }}">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Check-in Date</label>
                                <input type="date" 
                                       name="check_in" 
                                       id="check_in"
                                       min="{{ date('Y-m-d') }}"
                                       required
                                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Check-out Date</label>
                                <input type="date" 
                                       name="check_out" 
                                       id="check_out"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       required
                                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Adults</label>
                                    <select name="adults" 
                                            id="adults"
                                            required
                                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                                        @for($i = 1; $i <= min(6, $room->max_adults); $i++)
                                            <option value="{{ $i }}" {{ $i == 2 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Children</label>
                                    <select name="children" 
                                            id="children"
                                            required
                                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                                        @for($i = 0; $i <= 4; $i++)
                                            <option value="{{ $i }}" {{ $i == 0 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            @auth
                                <button type="submit" 
                                        class="w-full bg-primary-green hover:bg-primary-green-hover text-white py-3 rounded-lg font-semibold transition-colors duration-200">
                                    Reserve
                                </button>
                            @else
                                <button type="button" 
                                        id="reserve-button"
                                        class="w-full bg-primary-green hover:bg-primary-green-hover text-white py-3 rounded-lg font-semibold transition-colors duration-200">
                                    Reserve
                                </button>
                            @endauth
                        </form>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-white mb-2">Room Unavailable</h3>
                            <p class="text-gray-400 mb-4">This room is currently sold out</p>
                            <a href="{{ route('accommodations.index') }}" 
                               class="text-primary-green hover:text-primary-green-hover font-semibold">
                                View Other Rooms
                            </a>
                        </div>
                    @endif
                    
                    <!-- Hotel Policy Agreement -->
                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-400">
                            By reserving a room, you agree to the 
                            <button type="button" 
                                    onclick="window.openHotelPolicyModal()"
                                    class="text-primary-green hover:text-primary-green-hover font-medium underline transition-colors">
                                Hotel's Policies
                            </button>
                        </p>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="mt-6 pt-6 border-t border-gray-800">
                        <h4 class="font-semibold text-white mb-3">Need Help?</h4>
                        <div class="space-y-2 text-sm text-gray-400">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>+63 2 1234 5678</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>info@belmonthotel.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Rooms -->
@if($similarRooms->count() > 0)
<section class="py-12 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-8">Similar Rooms</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($similarRooms as $similarRoom)
            <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                <!-- Room Image -->
                <div class="relative h-48 overflow-hidden">
                    @php
                        $similarRoomImages = $similarRoom->getRoomImages();
                        $similarFirstImage = !empty($similarRoomImages) ? $similarRoomImages[0] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
                    @endphp
                    <img src="{{ $similarFirstImage }}" 
                         alt="{{ $similarRoom->room_type }}" 
                         class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                    
                    <!-- Availability Badge -->
                    <div class="absolute top-4 right-4 bg-gray-900/90 backdrop-blur-sm px-3 py-1 rounded-full border border-gray-700">
                        <span class="text-sm font-semibold {{ $similarRoom->available_quantity > 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $similarRoom->available_quantity > 0 ? 'Available' : 'Sold Out' }}
                        </span>
                    </div>
                </div>

                <!-- Room Info -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-white mb-2">{{ $similarRoom->room_type }}</h3>
                    <p class="text-gray-300 text-sm mb-3">{{ Str::limit($similarRoom->description, 80) }}</p>
                    
                    <!-- Room Details -->
                    <div class="flex items-center space-x-4 text-sm text-gray-400 mb-4">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>{{ $similarRoom->max_guests }} guests</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <span>{{ $similarRoom->size ?? 'Standard' }}</span>
                        </div>
                    </div>
                    
                    <!-- Price and Action -->
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-bold text-primary-green">
                            ₱{{ number_format($similarRoom->price_per_night, 0) }}
                        </div>
                        <a href="{{ route('rooms.show', $similarRoom->slug) }}" 
                           class="text-primary-green hover:text-primary-green-hover font-semibold text-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@guest
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reserveButton = document.getElementById('reserve-button');
        const reserveForm = document.getElementById('reserve-form');
        
        if (reserveButton && reserveForm) {
            reserveButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get form elements
                const checkIn = document.getElementById('check_in');
                const checkOut = document.getElementById('check_out');
                const adults = document.getElementById('adults');
                const children = document.getElementById('children');
                
                // Store form data in sessionStorage to preserve it after login
                // Use current values or defaults
                const formData = {
                    room_id: reserveForm.querySelector('input[name="room_id"]').value,
                    check_in: checkIn ? checkIn.value : '',
                    check_out: checkOut ? checkOut.value : '',
                    adults: adults ? adults.value : '2',
                    children: children ? children.value : '0'
                };
                
                sessionStorage.setItem('pendingReservation', JSON.stringify(formData));
                
                // Open auth modal - no alerts, just open the modal
                if (window.openAuthModal) {
                    window.openAuthModal('login');
                } else {
                    const event = new CustomEvent('openAuthModal', { detail: { type: 'login' } });
                    window.dispatchEvent(event);
                }
            });
        }
    });
</script>
@endguest
@endsection
