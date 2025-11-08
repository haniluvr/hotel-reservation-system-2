@extends('layouts.app')

@section('title', $hotel->name . ' - Belmont Hotel')

@section('content')
<!-- Hotel Header -->
<section class="pt-20 pb-12 bg-gradient-to-br from-primary/10 via-transparent to-secondary/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <div class="flex-1">
                <h1 class="text-3xl sm:text-4xl font-cormorant font-bold text-gray-900 mb-4">
                    {{ $hotel->name }}
                </h1>
                <p class="text-lg text-gray-600 mb-4">{{ $hotel->city }}, {{ $hotel->country }}</p>
                
                <!-- Star Rating -->
                <div class="flex items-center space-x-1 mb-6">
                    @for($i = 0; $i < $hotel->star_rating; $i++)
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span class="text-lg font-semibold text-gray-700 ml-2">{{ $hotel->star_rating }} stars</span>
                </div>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6">{{ $hotel->description }}</p>
                
                <!-- Address -->
                <div class="flex items-start space-x-3 text-gray-600">
                    <svg class="w-5 h-5 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $hotel->address }}</span>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-2xl shadow-lg p-6 w-full lg:w-80">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hotel Overview</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Rooms</span>
                        <span class="font-semibold">{{ $hotel->rooms->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Available Rooms</span>
                        <span class="font-semibold text-green-600">{{ $hotel->rooms->sum('available_quantity') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Average Price</span>
                        <span class="font-semibold text-primary">₱{{ number_format($hotel->rooms->avg('price_per_night'), 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Occupancy Rate</span>
                        <span class="font-semibold">{{ number_format($hotel->getOccupancyRate(), 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hotel Gallery -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Hotel Gallery</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Image -->
            <div class="md:col-span-2">
                <div class="relative h-96 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="{{ $hotel->name }}" 
                         class="w-full h-full object-cover">
                </div>
            </div>
            
            <!-- Side Images -->
            <div class="space-y-6">
                <div class="h-44 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Hotel Interior" 
                         class="w-full h-full object-cover">
                </div>
                <div class="h-44 rounded-2xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Hotel Amenities" 
                         class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Amenities -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Hotel Amenities</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($hotel->amenities ?? [] as $amenity)
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

<!-- Available Rooms -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Available Rooms</h2>
        
        <div class="space-y-6">
            @foreach($hotel->rooms as $room)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col lg:flex-row">
                    <!-- Room Image -->
                    <div class="lg:w-80 h-64 lg:h-auto">
                        <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $loop->index * 50 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="{{ $room->room_type }}" 
                             class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Room Info -->
                    <div class="flex-1 p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $room->room_type }}</h3>
                                <p class="text-gray-600 mb-3">{{ $room->description }}</p>
                                
                                <!-- Room Details -->
                                <div class="flex items-center space-x-6 text-sm text-gray-600 mb-4">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>{{ $room->max_guests }} guests</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                        </svg>
                                        <span>{{ $room->size ?? 'Standard' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $room->available_quantity }} available</span>
                                    </div>
                                </div>
                                
                                <!-- Room Amenities -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach(array_slice($room->amenities ?? [], 0, 5) as $amenity)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Price and Action -->
                            <div class="text-right">
                                <div class="text-2xl font-bold text-primary mb-2">
                                    ₱{{ number_format($room->price_per_night, 0) }}
                                </div>
                                <div class="text-sm text-gray-500 mb-4">per night</div>
                                
                                @if($room->available_quantity > 0)
                                    <a href="{{ route('rooms.show', [$hotel->id, $room->id]) }}" 
                                       class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors duration-200">
                                        Book Now
                                    </a>
                                @else
                                    <button disabled 
                                            class="bg-gray-300 text-gray-500 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                                        Sold Out
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Related Hotels -->
@if($relatedHotels->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-cormorant font-bold text-gray-900 mb-8">Similar Hotels in {{ $hotel->city }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedHotels as $relatedHotel)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                <!-- Hotel Image -->
                <div class="relative h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $loop->index * 100 }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         alt="{{ $relatedHotel->name }}" 
                         class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                    
                    <!-- Star Rating -->
                    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                        <div class="flex items-center space-x-1">
                            @for($i = 0; $i < $relatedHotel->star_rating; $i++)
                                <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Hotel Info -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $relatedHotel->name }}</h3>
                    <p class="text-gray-600 text-sm mb-3">{{ $relatedHotel->city }}</p>
                    
                    <!-- Price -->
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-bold text-primary">
                            From ₱{{ number_format($relatedHotel->rooms->min('price_per_night'), 0) }}
                        </div>
                        <a href="{{ route('hotels.show', $relatedHotel->id) }}" 
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



