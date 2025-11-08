<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation - Only visible after hero expansion -->
        <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" 
             x-data="{ scrolled: false, mobileMenuOpen: false }" 
             @scroll.window="scrolled = window.scrollY > 50"
             x-show="scrolled"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-full"
             class="bg-white/95 backdrop-blur-md border-b border-gray-200">
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-primary-green">
                            Belmont Hotel
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-8">
                            <a href="{{ route('home') }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-green transition-colors">
                                Home
                            </a>
                            <a href="{{ route('hotels.search') }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-green transition-colors">
                                Hotels
                            </a>
                            <a href="#" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-green transition-colors">
                                About
                            </a>
                            <a href="#" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-green transition-colors">
                                Contact
                            </a>
                        </div>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Search Icon -->
                        <button class="text-gray-700 hover:text-primary-green transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center space-x-2 text-gray-700 hover:text-primary-green transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="hidden sm:block text-sm font-medium">Account</span>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign In</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Register</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Bookings</a>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                    class="text-gray-700 hover:text-primary-green transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="md:hidden bg-white border-t border-gray-200">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">Home</a>
                        <a href="{{ route('hotels.search') }}" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">Hotels</a>
                        <a href="#" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">About</a>
                        <a href="#" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">Contact</a>
                        <div class="border-t border-gray-200 pt-4">
                            <a href="#" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">Sign In</a>
                            <a href="#" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-green">Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Custom JavaScript for scroll effects -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll event listener for hero expansion effect
        window.addEventListener('scroll', function() {
            const hero = document.querySelector('.hero-expand');
            if (hero) {
                const scrolled = window.scrollY > 50;
                hero.classList.toggle('scrolled', scrolled);
            }
        });
    </script>
</body>
</html>