<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
        
        {{-- Memanggil Topnav dan Sidebar --}}
        @include('layouts.partials.topnav')
        @include('layouts.partials.sidebar')

        {{-- Area Konten Utama --}}
        <div class="p-4 sm:ml-64">
            {{-- mt-14 memberi jarak dari topnav yang fixed --}}
            <main class="mt-14">
                
                {{-- Header Halaman (Opsional) --}}
                @hasSection('header')
                    <header class="p-4 bg-white rounded-lg shadow-sm mb-4">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                            @yield('header')
                        </h1>
                    </header>
                @endif
                
                {{-- Konten Dinamis dari setiap halaman --}}
                <div class="p-4 bg-white rounded-lg shadow-sm">
                    @yield('content')
                </div>

            </main>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
        @stack('scripts')
    </body>
</html>