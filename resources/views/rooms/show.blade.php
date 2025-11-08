@extends('layouts.app')

@section('title', $room->room_type . ' - ' . $hotel->name)

@section('content')
<!-- Room Header -->
<section class="pt-20 pb-12 bg-gradient-to-br from-primary/10 via-transparent to-secondary/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <div class="flex-1">
                <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <a href="{{ route('hotels.show', $hotel->id) }}" class="hover:text-primary">{{ $hotel->name }}</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-gray-900">{{ $room->room_type }}</span>
                </nav>
                
                <h1 class="text-3xl sm:text-4xl font-cormorant font-bold text-gray-900 mb-4">
                    {{ $room->room_type }}
                </h1>
                <p class="text-lg text-gray-600 mb-4">{{ $hotel->name }}, {{ $hotel->city }}</p>
                
                <!-- Room Details -->
                <div class="flex items-center space-x-6 text-gray-600 mb-6">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>{{ $room->max_guests }} guests</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <span>{{ $room->size ?? 'Standard Size' }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $room->available_quantity }} available</span>
                    </div>
                </div>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6">{{ $room->description }}</p>
            </div>
            
            <!-- Booking Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 w-full lg:w-96 sticky top-24">
                <div class="text-center mb-6">
                    <div class="text-3xl font-bold text-primary mb-2">
                        ₱{{ number_format($room->price_per_night, 0) }}
                    </div>
                    <div class="text-gray-600">per night</div>
                </div>
                
                @if($room->available_quantity > 0)
                    <form action="{{ route('booking.create') }}" method="GET" class="space-y-4">
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                            <input type="date" 
                                   name="check_in" 
                                   min="{{ date('Y-m-d') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                            <input type="date" 
                                   name="check_out" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                            <select name="guests" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                                @for($i = 1; $i <= $room->max_guests; $i++)
                                    <option value="{{ $i }}" {{ $i == 2 ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'Guest' : 'Guests' }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors duration-200">
                            Book This Room
                        </button>
                    </form>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Room Unavailable</h3>
                        <p class="text-gray-600 mb-4">This room is currently sold out</p>
                        <a href="{{ route('hotels.show', $hotel->id) }}" 
                           class="text-primary hover:text-primary/80 font-semibold">
                            View Other Rooms
                        </a>
                    </div>
                @endif
                
                <!-- Contact Info -->
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-3">Need Help?</h4>
                    <div class="space-y-2 text-sm text-gray-600">
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
</section>

<!-- Room Gallery -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Room Gallery</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Image -->
            <div class="md:col-span-2">
                <div class="relative h-96 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $room->id * 10 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="{{ $room->room_type }}" 
                         class="w-full h-full object-cover">
                </div>
            </div>
            
            <!-- Side Images -->
            <div class="space-y-6">
                <div class="h-44 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $room->id * 10 + 1 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Room Interior" 
                         class="w-full h-full object-cover">
                </div>
                <div class="h-44 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $room->id * 10 + 2 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Room Bathroom" 
                         class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Room Amenities -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Room Amenities</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($room->amenities ?? [] as $amenity)
            <div class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ $amenity }}</h3>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Room Details -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Room Information -->
            <div>
                <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Room Information</h2>
                
                <div class="space-y-6">
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Room Type</span>
                        <span class="text-gray-900">{{ $room->room_type }}</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Maximum Guests</span>
                        <span class="text-gray-900">{{ $room->max_guests }} guests</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Maximum Adults</span>
                        <span class="text-gray-900">{{ $room->max_adults }} adults</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Maximum Children</span>
                        <span class="text-gray-900">{{ $room->max_children }} children</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Room Size</span>
                        <span class="text-gray-900">{{ $room->size ?? 'Standard' }}</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Price per Night</span>
                        <span class="text-primary font-semibold">₱{{ number_format($room->price_per_night, 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Availability</span>
                        <span class="text-green-600 font-semibold">{{ $room->available_quantity }} rooms available</span>
                    </div>
                </div>
            </div>
            
            <!-- Hotel Policies -->
            <div>
                <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Hotel Policies</h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Check-in & Check-out</h3>
                        <div class="space-y-2 text-gray-600">
                            <p><strong>Check-in:</strong> 3:00 PM</p>
                            <p><strong>Check-out:</strong> 11:00 AM</p>
                            <p><strong>Early check-in:</strong> Subject to availability</p>
                            <p><strong>Late check-out:</strong> Subject to availability</p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Cancellation Policy</h3>
                        <div class="space-y-2 text-gray-600">
                            <p><strong>Free cancellation:</strong> Up to 24 hours before check-in</p>
                            <p><strong>Partial refund:</strong> 24-48 hours before check-in</p>
                            <p><strong>No refund:</strong> Less than 24 hours before check-in</p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Additional Information</h3>
                        <div class="space-y-2 text-gray-600">
                            <p><strong>Smoking:</strong> Non-smoking rooms only</p>
                            <p><strong>Pets:</strong> Not allowed</p>
                            <p><strong>Extra beds:</strong> Available upon request</p>
                            <p><strong>Accessibility:</strong> Wheelchair accessible rooms available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Rooms -->
@if($similarRooms->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Similar Rooms</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($similarRooms as $similarRoom)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                <!-- Room Image -->
                <div class="relative h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $similarRoom->id * 10 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         alt="{{ $similarRoom->room_type }}" 
                         class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                    
                    <!-- Availability Badge -->
                    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                        <span class="text-sm font-semibold {{ $similarRoom->available_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $similarRoom->available_quantity > 0 ? 'Available' : 'Sold Out' }}
                        </span>
                    </div>
                </div>

                <!-- Room Info -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $similarRoom->room_type }}</h3>
                    <p class="text-gray-600 text-sm mb-3">{{ Str::limit($similarRoom->description, 80) }}</p>
                    
                    <!-- Room Details -->
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
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
                        <div class="text-lg font-bold text-primary">
                            ₱{{ number_format($similarRoom->price_per_night, 0) }}
                        </div>
                        <a href="{{ route('rooms.show', [$hotel->id, $similarRoom->id]) }}" 
                           class="text-primary hover:text-primary/80 font-semibold text-sm">
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
@endsection



