<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $company->name ?? 'Lemedoit' }} | @yield('title', 'Dashboard')</title>
        @if($company && $company->logo)
            <link rel="icon" href="{{ Storage::url($company->logo) }}">
            <link rel="apple-touch-icon" href="{{ Storage::url($company->logo) }}">
        @else
            {{-- Pastikan ada file 'favicon.ico' di folder /public --}}
            <link rel="icon" href="{{ asset('favicon.ico') }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                {{-- PERUBAHAN DI SINI: Ganti <x-application-logo> dengan <h1> --}}
                <a href="/">
                    <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300">Sistem POS</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>