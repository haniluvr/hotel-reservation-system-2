<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('storage/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-black">
    <div class="min-h-screen">
        <!-- Navigation -->
        <div data-react-component="Navbar" data-react-props="{{ json_encode([
            'logoText' => 'Belmont Hotel', 
            'logoSrc' => asset('storage/logo.png'),
            'user' => auth()->check() ? [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ] : null
        ]) }}"></div>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer Component -->
        <x-footer />
    </div>

    <!-- Auth Modal -->
    <x-auth-modal />

    <!-- Search Modal -->
    <x-search-modal />

    <!-- Hotel Policy Modal -->
    <x-hotel-policy-modal />

    <!-- Custom JavaScript for scroll effects -->
    <script>
        // Wait for Alpine to be ready
        function waitForAlpine(callback) {
            if (window.Alpine && window.Alpine.version) {
                callback();
            } else {
                document.addEventListener('alpine:init', callback);
                // Also try after a short delay
                setTimeout(() => {
                    if (window.Alpine && window.Alpine.version) {
                        callback();
                    }
                }, 100);
            }
        }

        // Global function to open auth modal
        window.openAuthModal = function(type = 'login') {
            // Store current URL before opening modal
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                sessionStorage.setItem('auth_return_url', window.location.href);
            }
            // Try multiple methods with retries
            function tryOpen() {
                // Method 1: Direct function call
                if (window.authModalOpen && typeof window.authModalOpen === 'function') {
                    window.authModalOpen(type);
                    return true;
                }
                
                // Method 2: Alpine direct access
                const modalElement = document.getElementById('auth-modal');
                if (modalElement) {
                    // Check if Alpine is ready
                    if (window.Alpine && modalElement.hasAttribute('data-modal-ready')) {
                        try {
                            const alpineData = window.Alpine.$data(modalElement);
                            if (alpineData && typeof alpineData.openModal === 'function') {
                                alpineData.openModal(type);
                                return true;
                            }
                        } catch (e) {
                            // Silently handle errors
                        }
                    }
                }
                
                // Method 3: Dispatch event (most reliable fallback)
                const event = new CustomEvent('openAuthModal', { detail: { type } });
                window.dispatchEvent(event);
                return false;
            }
            
            // Try immediately
            if (!tryOpen()) {
                // If that didn't work, wait a bit and try again
                setTimeout(() => {
                    if (!tryOpen()) {
                        // Last resort: wait for Alpine
                        waitForAlpine(() => {
                            tryOpen();
                        });
                    }
                }, 100);
            }
        };

        // Open modal if there are auth errors (from failed login/register attempts)
        @if($errors->has('email') || $errors->has('password') || $errors->has('name'))
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    if (window.openAuthModal) {
                        window.openAuthModal('{{ $errors->has('name') ? 'register' : 'login' }}');
                    }
                }, 500);
            });
        @endif

        // Handle Sign In button clicks (vanilla JS fallback)
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Use event delegation on the document
            document.addEventListener('click', function(e) {
                var target = e.target;
                if (!target) return;
                
                // IMPORTANT: Don't intercept clicks inside the auth modal
                // Check if the click is inside the auth modal
                var authModal = document.getElementById('auth-modal');
                if (authModal && authModal.contains(target)) {
                    // This is a click inside the modal, let it proceed normally
                    return;
                }
                
                // Check multiple ways to identify the Sign In button
                var isSignInButton = false;
                var button = null;
                
                // Method 1: Check if target has data-sign-in attribute
                if (target.hasAttribute && target.hasAttribute('data-sign-in')) {
                    isSignInButton = true;
                    button = target;
                }
                // Method 2: Check if target is inside a button with data-sign-in
                else if (target.closest && target.closest('[data-sign-in]')) {
                    isSignInButton = true;
                    button = target.closest('[data-sign-in]');
                }
                // Method 3: Check if target or parent button contains "Sign In" text
                // BUT only if it's NOT inside a form (to avoid catching form submit buttons)
                else {
                    var parentButton = target.closest && target.closest('button');
                    if (parentButton && parentButton.textContent && parentButton.textContent.includes('Sign In')) {
                        // Make sure it's not inside a form (form submit buttons should work normally)
                        var isInForm = parentButton.closest && parentButton.closest('form');
                        // Make sure it's not the mobile menu button
                        if (!isInForm && !parentButton.textContent.includes('Create Account')) {
                            isSignInButton = true;
                            button = parentButton;
                        }
                    }
                }
                
                if (isSignInButton && button) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Try to open modal
                    if (window.openAuthModal) {
                        window.openAuthModal('login');
                    } else {
                        var event = new CustomEvent('openAuthModal', { detail: { type: 'login' } });
                        window.dispatchEvent(event);
                    }
                }
            }, true); // Use capture phase
        });

        // Global function to open search modal
        window.openSearchModal = function() {
            function tryOpen() {
                // Method 1: Direct function call
                if (window.searchModalOpen && typeof window.searchModalOpen === 'function') {
                    window.searchModalOpen();
                    return true;
                }
                
                // Method 2: Alpine direct access
                const modalElement = document.getElementById('search-modal');
                if (modalElement && window.Alpine && modalElement.hasAttribute('data-modal-ready')) {
                    try {
                        const alpineData = window.Alpine.$data(modalElement);
                        if (alpineData && typeof alpineData.openModal === 'function') {
                            alpineData.openModal();
                            return true;
                        }
                    } catch (e) {
                        // Silently handle errors
                    }
                }
                
                // Method 3: Dispatch event
                const event = new CustomEvent('openSearchModal');
                window.dispatchEvent(event);
                return false;
            }
            
            // Try immediately
            if (!tryOpen()) {
                setTimeout(() => {
                    if (!tryOpen()) {
                        waitForAlpine(() => {
                            tryOpen();
                        });
                    }
                }, 100);
            }
        };

        // Handle Search button clicks (vanilla JS fallback)
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                var target = e.target;
                if (!target) return;
                
                // Check if clicked element is the search button or inside it
                var isSearchButton = false;
                var button = null;
                
                // Method 1: Check for search icon SVG path
                if (target.tagName === 'path' && target.getAttribute('d') && target.getAttribute('d').includes('M21 21l-6-6m2-5a7')) {
                    button = target.closest('button') || target.closest('a');
                    if (button) {
                        // Only trigger if button is NOT inside any form (navbar search button is not in a form)
                        var form = button.closest('form');
                        if (!form) {
                            isSearchButton = true;
                        }
                    }
                }
                // Method 2: Check if button contains search icon
                else {
                    var parentButton = target.closest('button') || target.closest('a');
                    if (parentButton) {
                        var svg = parentButton.querySelector('svg path[d*="M21 21l-6-6m2-5a7"]');
                        if (svg) {
                            // Only trigger if button is NOT inside any form (navbar search button is not in a form)
                            var form = parentButton.closest('form');
                            if (!form) {
                                isSearchButton = true;
                                button = parentButton;
                            }
                        }
                    }
                }
                
                if (isSearchButton && button) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (window.openSearchModal) {
                        window.openSearchModal();
                    } else {
                        var event = new CustomEvent('openSearchModal');
                        window.dispatchEvent(event);
                    }
                }
            }, true);
        });

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

        // Footer scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            const footer = document.querySelector('footer');
            if (!footer) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        footer.classList.add('footer-visible');
                        observer.unobserve(footer);
                    }
                });
            }, {
                threshold: 0.1
            });

            observer.observe(footer);
        });
    </script>

    <!-- Check for pending reservation after successful authentication -->
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear return URL from sessionStorage since we're already authenticated
            sessionStorage.removeItem('auth_return_url');
            
            // Check if there's a pending reservation in sessionStorage
            const pendingReservation = sessionStorage.getItem('pendingReservation');
            if (pendingReservation) {
                try {
                    const formData = JSON.parse(pendingReservation);
                    sessionStorage.removeItem('pendingReservation');
                    
                    // Redirect to booking page with form data
                    const params = new URLSearchParams(formData);
                    window.location.href = '{{ route("booking.create") }}?' + params.toString();
                } catch (e) {
                    // Silently handle parsing errors
                    sessionStorage.removeItem('pendingReservation');
                }
            }
        });
    </script>
    @endauth
</body>
</html>