@extends('layouts.app')

@section('content')
<!-- Scroll Expansion Hero - Inspired by 21st.dev -->
<div class="relative min-h-screen overflow-hidden" 
     x-data="scrollExpansionHero()" 
     x-init="init()"
     @wheel.prevent="handleWheel($event)"
     @touchstart.prevent="handleTouchStart($event)"
     @touchmove.prevent="handleTouchMove($event)"
     @touchend="handleTouchEnd()">
    
    <!-- Background Image with Fade Effect -->
    <div class="absolute inset-0 z-0 h-full transition-opacity duration-100"
         :style="{ opacity: 1 - scrollProgress }">
        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
             alt="Luxury Hotel Background" 
             class="w-screen h-screen object-cover">
        <div class="absolute inset-0 bg-black/10"></div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto flex flex-col items-center justify-start relative z-10">
        <div class="flex flex-col items-center justify-center w-full h-screen relative">
            
            <!-- Central Media Frame -->
            <div class="absolute z-0 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rounded-2xl transition-none"
                 :style="{
                     width: mediaWidth + 'px',
                     height: mediaHeight + 'px',
                     maxWidth: '95vw',
                     maxHeight: '85vh',
                     boxShadow: '0px 0px 50px rgba(0, 0, 0, 0.3)'
                 }">
                
                <!-- Hotel Video/Image -->
                <div class="relative w-full h-full">
                    <video 
                        autoplay 
                        muted 
                        loop 
                        playsinline 
                        preload="auto"
                        class="w-full h-full object-cover rounded-xl"
                        controls="false">
                        <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4" type="video/mp4">
                    </video>
                    
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-black/30 rounded-xl transition-opacity duration-200"
                         :style="{ opacity: 0.5 - scrollProgress * 0.3 }"></div>
                </div>

                <!-- Text Below Media -->
                <div class="flex flex-col items-center text-center relative z-10 mt-4 transition-none">
                    <p class="text-2xl text-blue-200 font-medium"
                       :style="{ transform: 'translateX(-' + textTranslateX + 'vw)' }">
                        Cosmic Journey
                    </p>
                    <p class="text-blue-200 font-medium text-center"
                       :style="{ transform: 'translateX(' + textTranslateX + 'vw)' }">
                        Scroll to Expand Demo
                    </p>
                </div>
            </div>

            <!-- Main Title Text -->
            <div class="flex items-center justify-center text-center gap-4 w-full relative z-10 transition-none flex-col">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-blue-200 transition-none"
                    :style="{ transform: 'translateX(-' + textTranslateX + 'vw)' }">
                    Immersive
                </h2>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-center text-blue-200 transition-none"
                    :style="{ transform: 'translateX(' + textTranslateX + 'vw)' }">
                    Video Experience
                </h2>
            </div>
        </div>

        <!-- Content Section (appears when fully expanded) -->
        <div class="flex flex-col w-full px-8 py-10 md:px-16 lg:py-20 transition-opacity duration-700"
             :style="{ opacity: showContent ? 1 : 0 }">
            
            <!-- Search Form -->
            <div class="max-w-4xl mx-auto mb-16">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6">
                    <form class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Destination</label>
                            <input type="text" 
                                   placeholder="Where are you going?" 
                                   class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Check-in</label>
                            <input type="date" 
                                   class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Check-out</label>
                            <input type="date" 
                                   class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full bg-primary-green hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Featured Hotels Section -->
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Featured Hotels</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Discover our most popular destinations across the Philippines, each offering unique experiences and world-class hospitality.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredHotels as $hotel)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="relative h-64">
                            <img src="{{ $hotel->images[0] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}" 
                                 alt="{{ $hotel->name }}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-primary-green text-white px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $hotel->star_rating }}★
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $hotel->name }}</h3>
                            <p class="text-gray-600 mb-3">{{ $hotel->city }}, {{ $hotel->country }}</p>
                            <p class="text-gray-700 mb-4 line-clamp-2">{{ $hotel->description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach(array_slice($hotel->amenities, 0, 3) as $amenity)
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                                    {{ $amenity }}
                                </span>
                                @endforeach
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-primary-green">
                                    ₱{{ number_format($hotel->average_room_price, 0) }}
                                    <span class="text-sm font-normal text-gray-600">/night</span>
                                </span>
                                <a href="{{ route('hotels.show', $hotel->id) }}" 
                                   class="bg-primary-green text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Popular Destinations Section -->
            <div class="max-w-7xl mx-auto mt-20">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Popular Destinations</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Explore the most sought-after destinations in the Philippines, each with its own unique charm and attractions.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($popularDestinations as $destination)
                    <div class="relative group cursor-pointer">
                        <div class="relative h-64 rounded-2xl overflow-hidden">
                            <img src="{{ $destination['image'] }}" 
                                 alt="{{ $destination['name'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <h3 class="text-xl font-bold mb-1">{{ $destination['name'] }}</h3>
                                <p class="text-sm opacity-90">{{ $destination['count'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-2xl font-bold text-primary-green mb-4">Belmont Hotel</h3>
                <p class="text-gray-400 mb-4">Experience luxury redefined in the heart of the Philippines.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Our Hotels</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Careers</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Support</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Help Center</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Booking Guide</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Cancellation</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                <div class="space-y-2 text-gray-400">
                    <p>📧 info@belmonthotel.com</p>
                    <p>📞 +63 2 8888 9999</p>
                    <p>📍 Manila, Philippines</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
            <p>&copy; 2024 Belmont Hotel. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
function scrollExpansionHero() {
    return {
        scrollProgress: 0,
        showContent: false,
        mediaFullyExpanded: false,
        touchStartY: 0,
        isMobileState: false,
        mediaWidth: 300,
        mediaHeight: 400,
        textTranslateX: 0,

        init() {
            this.checkIfMobile();
            window.addEventListener('resize', () => this.checkIfMobile());
            window.addEventListener('scroll', () => this.handleScroll());
        },

        checkIfMobile() {
            this.isMobileState = window.innerWidth < 768;
        },

        handleWheel(e) {
            if (this.mediaFullyExpanded && e.deltaY < 0 && window.scrollY <= 5) {
                // Allow scrolling back up to reset the hero
                this.mediaFullyExpanded = false;
                this.scrollProgress = 0;
                this.updateDimensions();
                e.preventDefault();
            } else if (!this.mediaFullyExpanded) {
                // Prevent normal scrolling during expansion
                e.preventDefault();
                const scrollDelta = e.deltaY * 0.0009;
                const newProgress = Math.min(Math.max(this.scrollProgress + scrollDelta, 0), 1);
                this.scrollProgress = newProgress;
                this.updateDimensions();

                if (newProgress >= 1) {
                    this.mediaFullyExpanded = true;
                    this.showContent = true;
                } else if (newProgress < 0.75) {
                    this.showContent = false;
                }
            }
            // If media is fully expanded and scrolling down, do NOT prevent default - allow normal scroll
        },

        handleTouchStart(e) {
            this.touchStartY = e.touches[0].clientY;
        },

        handleTouchMove(e) {
            if (!this.touchStartY) return;

            const touchY = e.touches[0].clientY;
            const deltaY = this.touchStartY - touchY;

            if (this.mediaFullyExpanded && deltaY < -20 && window.scrollY <= 5) {
                // Allow scrolling back up to reset the hero
                this.mediaFullyExpanded = false;
                this.scrollProgress = 0;
                this.updateDimensions();
                e.preventDefault();
            } else if (!this.mediaFullyExpanded) {
                // Prevent normal scrolling during expansion
                e.preventDefault();
                const scrollFactor = deltaY < 0 ? 0.008 : 0.005;
                const scrollDelta = deltaY * scrollFactor;
                const newProgress = Math.min(Math.max(this.scrollProgress + scrollDelta, 0), 1);
                this.scrollProgress = newProgress;
                this.updateDimensions();

                if (newProgress >= 1) {
                    this.mediaFullyExpanded = true;
                    this.showContent = true;
                } else if (newProgress < 0.75) {
                    this.showContent = false;
                }

                this.touchStartY = touchY;
            }
            // If media is fully expanded and scrolling down, do NOT prevent default - allow normal scroll
        },

        handleTouchEnd() {
            this.touchStartY = 0;
        },

        handleScroll() {
            // Only prevent scrolling when media is not fully expanded
            if (!this.mediaFullyExpanded) {
                window.scrollTo(0, 0);
            }
        },

        updateDimensions() {
            this.mediaWidth = 300 + this.scrollProgress * (this.isMobileState ? 650 : 1250);
            this.mediaHeight = 400 + this.scrollProgress * (this.isMobileState ? 200 : 400);
            this.textTranslateX = this.scrollProgress * (this.isMobileState ? 180 : 150);
        },

        resetHero() {
            this.scrollProgress = 0;
            this.showContent = false;
            this.mediaFullyExpanded = false;
            this.updateDimensions();
        }
    }
}
</script>
@endsection