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

        @vite(['resources/css/app.css'])
        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
        
        {{-- Memanggil Topnav dan Sidebar --}}
        @include('layouts.partials.topnav')
        @include('layouts.partials.sidebar')

        {{-- Area Konten Utama --}}
        <div id="main-content" class="p-4 sm:ml-64 transition-all duration-300">
            {{-- mt-14 memberi jarak dari topnav yang fixed --}}
            <main class="mt-14">
                @auth
                    @if(auth()->user()->company && auth()->user()->company->trial_ends_at?->isFuture())
                        <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
                            Masa uji coba Anda akan berakhir dalam 
                            <strong>{{ floor(now()->diffInDays(auth()->user()->company->trial_ends_at, false)) + 1 }} hari</strong>. 
                            <a href="{{ route('billing.index') }}" class="font-semibold underline hover:text-yellow-900">Langganan Sekarang</a>.
                        </div>
                    @endif
                @endauth
                {{-- Konten Dinamis dari setiap halaman --}}
                <div class="p-4 bg-white rounded-lg shadow-sm">
                    <header class="px-4 py-3 bg-white rounded-lg shadow-sm mb-4 border-b border-gray-200 dark:border-gray-700">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                            @yield('header')
                        </h1>
                    </header>
                    @yield('content')
                </div>

            </main>
        </div>
        @include('layouts.partials.footer')
        
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('logo-sidebar');
                const mainContent = document.getElementById('main-content');
                const toggleButton = document.getElementById('sidebar-toggle');
                const iconMinimize = document.getElementById('icon-minimize');
                const iconExpand = document.getElementById('icon-expand');
                const sidebarTexts = document.querySelectorAll('.sidebar-text');

                // Fungsi untuk mengubah state sidebar
                function toggleSidebar(minimize) {
                    sidebar.classList.toggle('w-64', !minimize);
                    sidebar.classList.toggle('w-20', minimize);
                    mainContent.classList.toggle('sm:ml-64', !minimize);
                    mainContent.classList.toggle('sm:ml-20', minimize);
                    iconMinimize.classList.toggle('hidden', minimize);
                    iconExpand.classList.toggle('hidden', !minimize);
                    sidebarTexts.forEach(text => {
                        text.classList.toggle('hidden', minimize);
                    });
                    localStorage.setItem('sidebarMinimized', minimize);
                }

                // Event listener untuk tombol toggle
                toggleButton.addEventListener('click', () => {
                    const isMinimized = !sidebar.classList.contains('w-64');
                    toggleSidebar(!isMinimized);
                });

                // Cek state dari localStorage saat halaman dimuat
                if (localStorage.getItem('sidebarMinimized') === 'true') {
                    toggleSidebar(true);
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>