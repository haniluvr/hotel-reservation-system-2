@extends('layouts.app')

@section('title', 'Accommodations - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black pt-20">
    <!-- Hero Section -->
    <section class="py-20 bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                    Our <span class="text-primary-green">Accommodations</span>
                </h1>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                    Discover our luxurious rooms and suites, each designed to provide the perfect blend of comfort and elegance
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Filters Section -->
        <div class="bg-black/80 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-800 p-6 mb-8">
            <form method="GET" action="{{ route('accommodations.index') }}" class="space-y-4">
                <!-- First Row: Filters -->
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-[200px] max-w-md">
                        <label class="flex items-center text-sm font-medium text-gray-300 mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by room type or description..."
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                    </div>

                    <!-- Adults Filter -->
                    <div class="flex-shrink-0">
                        <label class="flex items-center text-sm font-medium text-gray-300 mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Adults
                        </label>
                        <select name="adults" 
                                class="w-40 px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="">Any</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('adults') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Children -->
                    <div class="flex-shrink-0">
                        <label class="flex items-center text-sm font-medium text-gray-300 mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Children
                        </label>
                        <select name="children" 
                                class="w-40 px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white">
                            <option value="">Any</option>
                            @for($i = 0; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ request('children') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="flex items-center text-sm font-medium text-gray-300 mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Price Range
                        </label>
                        <div class="flex gap-2">
                            <input type="number" 
                                   name="min_price" 
                                   value="{{ request('min_price') }}"
                                   placeholder="Min"
                                   class="flex-1 px-3 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                            <input type="number" 
                                   name="max_price" 
                                   value="{{ request('max_price') }}"
                                   placeholder="Max"
                                   class="flex-1 px-3 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                        </div>
                    </div>
                </div>

                <!-- Second Row: Sort and Apply Filters -->
                <div class="flex items-center gap-4">
                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-3">
                        <label class="flex items-center text-sm font-medium text-gray-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            Sort:
                        </label>
                        <select name="sort" 
                                id="sort-dropdown"
                                class="bg-transparent border-0 text-white focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="popularity" {{ request('sort') == 'popularity' || !request('sort') ? 'selected' : '' }}>Popularity</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex gap-3 ml-auto">
                        @php
                            $hasFilters = request('search') || request('adults') || request('children') || request('min_price') || request('max_price');
                        @endphp
                        <button type="submit" 
                                class="px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white rounded-lg transition-colors font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Apply Filters
                        </button>
                        @if($hasFilters)
                            <a href="{{ route('accommodations.index', ['sort' => request('sort', 'popularity')]) }}" 
                               class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg transition-colors font-medium">
                                Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="mb-6">
            <p class="text-gray-300">
                Showing <span class="font-semibold text-white">{{ $rooms->count() }}</span> of <span class="font-semibold text-white">{{ $rooms->total() }}</span> accommodations
            </p>
        </div>

        <!-- Rooms Grid -->
        @if($rooms->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($rooms as $room)
                    <div class="bg-gray-900 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow border border-gray-800 flex flex-col h-full">
                        <!-- Room Image -->
                        <div class="relative h-64 bg-gradient-to-br from-primary-green/20 to-primary-green/10 flex-shrink-0">
                            @php
                                $roomImages = $room->getRoomImages();
                                $firstImage = !empty($roomImages) ? $roomImages[0] : null;
                            @endphp
                            @if($firstImage)
                                <img src="{{ $firstImage }}" 
                                     alt="{{ $room->room_type }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center" style="display: {{ $firstImage ? 'none' : 'flex' }};">
                                <div class="text-center text-white">
                                    <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <p class="text-sm opacity-75">Room Image</p>
                                </div>
                            </div>
                            
                            <!-- Availability Badge -->
                            <div class="absolute top-4 right-4">
                                @if($room->available_quantity > 0)
                                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                        {{ $room->available_quantity }} Available
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">
                                        Sold Out
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Room Details -->
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-xl font-bold text-white mb-2">{{ $room->room_type }}</h3>
                            
                            <p class="text-gray-300 text-sm mb-4 line-clamp-2">
                                {{ Str::limit($room->description, 100) }}
                            </p>

                            <!-- Room Info -->
                            <div class="flex flex-wrap gap-3 mb-4 text-sm text-gray-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span>{{ $room->max_guests }} Guests</span>
                                </div>
                                @if($room->max_adults)
                                    <div class="flex items-center">
                                        <span>•</span>
                                        <span class="ml-1">{{ $room->max_adults }} Adults</span>
                                    </div>
                                @endif
                                @if($room->max_children)
                                    <div class="flex items-center">
                                        <span>•</span>
                                        <span class="ml-1">{{ $room->max_children }} Children</span>
                                    </div>
                                @endif
                                @if($room->size)
                                    <div class="flex items-center">
                                        <span>•</span>
                                        <span class="ml-1">{{ $room->size }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Amenities Preview -->
                            @if($room->amenities && count($room->amenities) > 0)
                                <div class="mb-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_slice($room->amenities, 0, 3) as $amenity)
                                            <span class="px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded border border-gray-700">
                                                {{ $amenity }}
                                            </span>
                                        @endforeach
                                        @if(count($room->amenities) > 3)
                                            <span class="px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded border border-gray-700">
                                                +{{ count($room->amenities) - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Price and CTA -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-800 mt-auto">
                                <div>
                                    <div class="text-2xl font-bold text-primary-green">
                                        ₱{{ number_format($room->price_per_night, 0) }}
                                    </div>
                                    <div class="text-xs text-gray-400">per night</div>
                                </div>
                                <a href="{{ route('rooms.show', $room->slug) }}" 
                                   class="px-6 py-2 bg-primary-green hover:bg-primary-green-hover text-white rounded-lg transition-colors font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $rooms->links() }}
            </div>
        @else
            <!-- No Results -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No accommodations found</h3>
                <p class="text-gray-300 mb-6">Try adjusting your filters to see more results.</p>
                <a href="{{ route('accommodations.index') }}" 
                   class="inline-block px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white rounded-lg transition-colors font-medium">
                    Clear All Filters
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    /* Style the sort dropdown options (the list that appears) */
    #sort-dropdown option {
        background-color: #1e3a5f !important; /* Dark blue */
        color: white !important;
        padding: 8px 12px;
    }
    
    /* Keep the select element itself transparent */
    #sort-dropdown {
        background-color: transparent !important;
    }
    
    /* Style the dropdown when open (for webkit browsers) */
    #sort-dropdown:focus {
        outline: 1px solid #b9aa87; /* Gold border */
        outline-offset: -1px;
        background-color: transparent !important;
    }
    
    /* For Firefox */
    #sort-dropdown option:checked {
        background-color: #2d4a6b; /* Slightly lighter blue for selected */
    }
</style>

<script>
    // Ensure the dropdown stays transparent
    document.addEventListener('DOMContentLoaded', function() {
        const sortDropdown = document.getElementById('sort-dropdown');
        if (sortDropdown) {
            // Keep background transparent at all times
            sortDropdown.style.backgroundColor = 'transparent';
            
            // Only add gold border on focus, no background change
            sortDropdown.addEventListener('focus', function() {
                this.style.border = '1px solid #b9aa87';
                this.style.borderRadius = '0.5rem';
                this.style.backgroundColor = 'transparent';
            });
            
            sortDropdown.addEventListener('blur', function() {
                this.style.border = 'none';
                this.style.backgroundColor = 'transparent';
            });
        }
    });
</script>
@endsection

