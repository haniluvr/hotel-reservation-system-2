<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Belmont Hotel') }} - {{ $title ?? 'Welcome' }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('storage/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('storage/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-[#667f5f] via-[#4a5d3a] to-[#3d4d2f]">
            <div class="w-full max-w-md px-6 py-8">
                <!-- Logo/Branding -->
                <div class="text-center mb-8">
                    <a href="/" class="inline-block">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-2xl font-bold text-[#667f5f]">BH</span>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-white mb-2">Belmont Hotel</h1>
                        <p class="text-gray-200 text-sm">Palawan, Philippines</p>
                    </a>
                </div>

                <!-- Auth Card -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="text-center mt-6">
                    <a href="/" class="text-white/80 hover:text-white text-sm transition-colors">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
