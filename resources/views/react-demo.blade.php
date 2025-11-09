<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>React Footer Demo - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="relative flex min-h-screen flex-col">
        <!-- Content Section -->
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-green/20 to-blue-50">
            <div class="text-center px-4">
                <h1 class="font-mono text-4xl md:text-6xl font-bold text-gray-900 mb-4">
                    Scroll Down!
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    React Footer Component Demo
                </p>
                <div class="animate-bounce">
                    <svg class="w-8 h-8 mx-auto text-primary-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- React Footer Component -->
        <div data-react-component="Footer" data-react-props="{}"></div>
    </div>
</body>
</html>

