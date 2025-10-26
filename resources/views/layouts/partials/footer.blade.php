<footer class="p-4 mt-6">
    <div class="w-full mx-auto max-w-screen-xl">
        {{-- Margin vertikal (my) pada <hr> dikurangi agar tidak terlalu tinggi --}}
        <hr class="my-4 border-gray-200 sm:mx-auto dark:border-gray-700" />
        <div class="text-center">
            <span class="block text-sm text-gray-500 dark:text-gray-400">
                © {{ date('Y') }} <a href="{{ route('dashboard') }}" class="hover:underline">KASLO POS™</a>. All Rights Reserved.
            </span>
            
            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-2">
                Created by <a href="https://www.instagram.com/lemedo.it/" target="_blank" rel="noopener noreferrer" class="hover:underline font-semibold">lemedoit</a>
            </span>
        </div>
    </div>
</footer>