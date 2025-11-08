@extends('layouts.app')

@section('title', 'Search Hotels - Belmont Hotel')

@section('content')
<!-- Search Header -->
<section class="pt-20 pb-12 bg-gradient-to-br from-primary/10 via-transparent to-secondary/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl sm:text-4xl font-cormorant font-bold text-gray-900 mb-4">
                Search Results
            </h1>
            <p class="text-lg text-gray-600">
                Found {{ $hotels->total() }} hotels matching your criteria
            </p>
        </div>
    </div>
</section>

<!-- Search Filters -->
<section class="py-8 bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Search Form -->
            <div class="flex-1">
                <form action="{{ route('hotels.search') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <input type="text" 
                           name="destination" 
                           value="{{ request('destination') }}"
                           placeholder="Destination"
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                    
                    <input type="date" 
                           name="check_in" 
                           value="{{ request('check_in') }}"
                           min="{{ date('Y-m-d') }}"
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                    
                    <input type="date" 
                           name="check_out" 
                           value="{{ request('check_out') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                    
                    <select name="guests" 
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        <option value="">Guests</option>
                        <option value="1" {{ request('guests') == '1' ? 'selected' : '' }}>1 Guest</option>
                        <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2 Guests</option>
                        <option value="3" {{ request('guests') == '3' ? 'selected' : '' }}>3 Guests</option>
                        <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>4 Guests</option>
                        <option value="5" {{ request('guests') == '5' ? 'selected' : '' }}>5+ Guests</option>
                    </select>
                    
                    <button type="submit" 
                            class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors duration-200">
                        Search
                    </button>
                </form>
            </div>
            
            <!-- Filter Toggle -->
            <div class="lg:hidden">
                <button onclick="toggleFilters()" 
                        class="flex items-center space-x-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                    </svg>
                    <span>Filters</span>
                </button>
            </div>
        </div>
        
        <!-- Advanced Filters -->
        <div id="advancedFilters" class="hidden lg:block mt-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                    <select name="price_range" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        <option value="">Any Price</option>
                        <option value="0-3000" {{ request('price_range') == '0-3000' ? 'selected' : '' }}>₱0 - ₱3,000</option>
                        <option value="3000-5000" {{ request('price_range') == '3000-5000' ? 'selected' : '' }}>₱3,000 - ₱5,000</option>
                        <option value="5000-8000" {{ request('price_range') == '5000-8000' ? 'selected' : '' }}>₱5,000 - ₱8,000</option>
                        <option value="8000+" {{ request('price_range') == '8000+' ? 'selected' : '' }}>₱8,000+</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Star Rating</label>
                    <select name="stars" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        <option value="">Any Rating</option>
                        <option value="5" {{ request('stars') == '5' ? 'selected' : '' }}>5 Stars</option>
                        <option value="4" {{ request('stars') == '4' ? 'selected' : '' }}>4+ Stars</option>
                        <option value="3" {{ request('stars') == '3' ? 'selected' : '' }}>3+ Stars</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                    <select name="amenities" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        <option value="">Any Amenities</option>
                        <option value="Pool" {{ request('amenities') == 'Pool' ? 'selected' : '' }}>Pool</option>
                        <option value="Spa" {{ request('amenities') == 'Spa' ? 'selected' : '' }}>Spa</option>
                        <option value="WiFi" {{ request('amenities') == 'WiFi' ? 'selected' : '' }}>WiFi</option>
                        <option value="Parking" {{ request('amenities') == 'Parking' ? 'selected' : '' }}>Parking</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select name="sort" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Rating</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($hotels->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Results List -->
                <div class="lg:col-span-2 space-y-6">
                    @foreach($hotels as $hotel)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="flex flex-col md:flex-row">
                            <!-- Hotel Image -->
                            <div class="md:w-80 h-64 md:h-auto">
                                <img src="https://images.unsplash.com/photo-{{ 1566073771259 + $loop->index }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                     alt="{{ $hotel->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            
                            <!-- Hotel Info -->
                            <div class="flex-1 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $hotel->name }}</h3>
                                        <p class="text-gray-600 mb-2">{{ $hotel->city }}, {{ $hotel->country }}</p>
                                        
                                        <!-- Star Rating -->
                                        <div class="flex items-center space-x-1 mb-3">
                                            @for($i = 0; $i < $hotel->star_rating; $i++)
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-sm text-gray-500 ml-2">{{ $hotel->star_rating }} stars</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-primary">
                                            ₱{{ number_format($hotel->rooms->min('price_per_night'), 0) }}
                                        </div>
                                        <div class="text-sm text-gray-500">per night</div>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <p class="text-gray-600 mb-4 line-clamp-2">{{ Str::limit($hotel->description, 150) }}</p>
                                
                                <!-- Amenities -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach(array_slice($hotel->amenities ?? [], 0, 4) as $amenity)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex justify-between items-center">
                                    <div class="text-sm text-gray-500">
                                        {{ $hotel->rooms->sum('available_quantity') }} rooms available
                                    </div>
                                    <a href="{{ route('hotels.show', $hotel->id) }}" 
                                       class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition-colors duration-200">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $hotels->appends(request()->query())->links() }}
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Search Tips</h3>
                        <div class="space-y-4 text-sm text-gray-600">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium">Best Prices</div>
                                    <div>Book early for better rates</div>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium">Free Cancellation</div>
                                    <div>Cancel up to 24 hours before</div>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium">24/7 Support</div>
                                    <div>We're here to help anytime</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="font-semibold text-gray-900 mb-3">Need Help?</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>+63 2 1234 5678</span>
                                </div>
                                <div class="flex items-center space-x-2 text-gray-600">
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
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-900 mb-4">No hotels found</h3>
                <p class="text-gray-600 mb-8">Try adjusting your search criteria or browse our featured hotels</p>
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors duration-200">
                    Browse Featured Hotels
                </a>
            </div>
        @endif
    </div>
</section>

<script>
function toggleFilters() {
    const filters = document.getElementById('advancedFilters');
    filters.classList.toggle('hidden');
}
</script>
@endsection



