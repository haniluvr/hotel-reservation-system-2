@extends('layouts.app')

@section('title', 'Search Results - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                Search Results
            </h1>
            <p class="text-lg text-gray-300">
                Found {{ $rooms->total() }} {{ $rooms->total() == 1 ? 'room' : 'rooms' }} matching your criteria
            </p>
        </div>

        <!-- Search Form -->
        <div class="bg-black/80 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-800 p-6 mb-8">
            <form action="{{ route('hotels.search') }}" method="GET" class="space-y-4">
                <!-- First Row: Check-in, Check-out, Price Range -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                               value="{{ request('check_in', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
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
                               value="{{ request('check_out', date('Y-m-d', strtotime('+1 day'))) }}"
                               min="{{ request('check_in', date('Y-m-d')) }}"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                    </div>

                    <!-- Price Range Min -->
                    <div class="space-y-2">
                        <label for="min_price" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Min Price
                        </label>
                        <input type="number" 
                               id="min_price"
                               name="min_price" 
                               value="{{ request('min_price') }}"
                               placeholder="Min"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                    </div>

                    <!-- Price Range Max -->
                    <div class="space-y-2">
                        <label for="max_price" class="flex items-center text-sm font-medium text-gray-300">
                            Max Price
                        </label>
                        <input type="number" 
                               id="max_price"
                               name="max_price" 
                               value="{{ request('max_price') }}"
                               placeholder="Max"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                    </div>
                </div>

                <!-- Second Row: Adults, Children, Sort, Search Button -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Adults -->
                    <div class="space-y-2">
                        <label for="adults" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Adults
                        </label>
                        <select name="adults" 
                                id="adults"
                                class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="">Any</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('adults') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Children -->
                    <div class="space-y-2">
                        <label for="children" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Children
                        </label>
                        <select name="children" 
                                id="children"
                                class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="">Any</option>
                            @for($i = 0; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ request('children') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="space-y-2">
                        <label for="sort" class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            Sort
                        </label>
                        <select name="sort" 
                                id="sort"
                                class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="popularity" {{ request('sort') == 'popularity' || !request('sort') ? 'selected' : '' }}>Popularity</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-primary-green hover:bg-primary-green-hover text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        @if($rooms->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Results List -->
                <div class="lg:col-span-2 space-y-6">
                    @foreach($rooms as $room)
                    <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="flex flex-col md:flex-row">
                            <!-- Room Image -->
                            <div class="md:w-80 h-64 md:h-auto">
                                @php
                                    $roomImages = $room->getRoomImages();
                                    $roomImage = !empty($roomImages) ? $roomImages[0] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
                                @endphp
                                <img src="{{ $roomImage }}" 
                                     alt="{{ $room->room_type }}" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'">
                            </div>
                            
                            <!-- Room Info -->
                            <div class="flex-1 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-white mb-2">{{ $room->room_type }}</h3>
                                        <p class="text-gray-400 mb-2">{{ $room->hotel->name }}, {{ $room->hotel->city }}</p>
                                        
                                        <!-- Room Details -->
                                        <div class="flex items-center space-x-4 mb-3 text-sm text-gray-400">
                                            <span>{{ $room->max_adults }} {{ $room->max_adults == 1 ? 'adult' : 'adults' }}</span>
                                            @if($room->max_children > 0)
                                                <span>• {{ $room->max_children }} {{ $room->max_children == 1 ? 'child' : 'children' }}</span>
                                            @endif
                                            <span>• {{ $room->max_guests }} {{ $room->max_guests == 1 ? 'guest' : 'guests' }} max</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-primary-green">
                                            ₱{{ number_format($room->price_per_night, 0) }}
                                        </div>
                                        <div class="text-sm text-gray-400">per night</div>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <p class="text-gray-300 mb-4 line-clamp-2">{{ Str::limit($room->description, 150) }}</p>
                                
                                <!-- Amenities -->
                                @if($room->amenities && count($room->amenities) > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach(array_slice($room->amenities, 0, 4) as $amenity)
                                        <span class="px-2 py-1 bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-full">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                                @endif
                                
                                <!-- Actions -->
                                <div class="flex justify-between items-center">
                                    <div class="text-sm text-gray-400">
                                        {{ $room->available_quantity }} {{ $room->available_quantity == 1 ? 'room' : 'rooms' }} available
                                    </div>
                                    <a href="{{ route('rooms.show', $room->slug) }}" 
                                       class="bg-primary-green hover:bg-primary-green-hover text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $rooms->appends(request()->query())->links() }}
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6 sticky top-24">
                        <h3 class="text-lg font-semibold text-white mb-4">Search Tips</h3>
                        <div class="space-y-4 text-sm text-gray-300">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-white">Best Prices</div>
                                    <div>Book early for better rates</div>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-white">Free Cancellation</div>
                                    <div>Cancel up to 24 hours before</div>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-white">24/7 Support</div>
                                    <div>We're here to help anytime</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-800">
                            <h4 class="font-semibold text-white mb-3">Need Help?</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2 text-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>+63 2 1234 5678</span>
                                </div>
                                <div class="flex items-center space-x-2 text-gray-300">
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
        @else
            <!-- No Results -->
            <div class="text-center py-12">
                <svg class="w-24 h-24 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-2xl font-semibold text-white mb-4">No rooms found</h3>
                <p class="text-gray-300 mb-8">Try adjusting your search criteria or browse our accommodations</p>
                <a href="{{ route('accommodations.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white font-semibold rounded-lg transition-colors duration-200">
                    Browse All Accommodations
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
