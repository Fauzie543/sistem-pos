<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">
            <li>
                {{-- Contoh Link Aktif: tambahkan kelas 'bg-gray-100 dark:bg-gray-700' jika route-nya cocok --}}
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                   <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                       <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                       <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                   </svg>
                   <span class="ms-3">Dashboard</span>
                </a>
             </li>
             <li class="pt-2">
                <span class="px-2 text-xs font-semibold text-gray-500 uppercase">Data Master</span>
            </li>

            {{-- MENU KATEGORI --}}
            <li>
                <a href="{{ route('categories.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('categories.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                   {{-- Ganti dengan icon yang sesuai (contoh: folder) --}}
                   <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M18 5.923A1.001 1.001 0 0 0 17 5h-4V4a4 4 0 1 0-8 0v1H1a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h15.84A2 2 0 0 0 20 17.846L18.515 5.923ZM9 4a2 2 0 0 1 4 0v1H9V4Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Kategori</span>
                </a>
             </li>
            
            {{-- MENU PRODUK --}}
            <li>
                <a href="{{ route('products.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('products.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                   {{-- Ganti dengan icon yang sesuai (contoh: box) --}}
                   <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 7.5a2.5 2.5 0 0 0-2.5-2.5h- момент истины-.75A2.5 2.5 0 0 0 10 2.5a2.5 2.5 0 0 0-4.75 0h-.75A2.5 2.5 0 0 0 2 7.5v9A2.5 2.5 0 0 0 4.5 19h11a2.5 2.5 0 0 0 2.5-2.5v-9ZM10 4.5a.5.5 0 0 1 .5.5v2.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5ZM7.5 5a.5.5 0 0 1 1 0v2.5a.5.5 0 0 1-1 0V5Zm5 0a.5.5 0 0 1 1 0v2.5a.5.5 0 0 1-1 0V5Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Produk</span>
                </a>
             </li>
            {{-- MENU LAYANAN --}}
            <li>
                <a href="{{ route('services.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('services.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                {{-- Ganti dengan icon yang sesuai (contoh: wrench) --}}
                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M7.58209 8.96025 9.8136 11.1917l-1.61782 1.6178c-1.08305-.1811-2.23623.1454-3.07364.9828-1.1208 1.1208-1.32697 2.8069-.62368 4.1363.14842.2806.42122.474.73509.5213.06726.0101.1347.0133.20136.0098-.00351.0666-.00036.1341.00977.2013.04724.3139.24069.5867.52125.7351 1.32944.7033 3.01552.4971 4.13627-.6237.8375-.8374 1.1639-1.9906.9829-3.0736l4.8107-4.8108c1.0831.1811 2.2363-.1454 3.0737-.9828 1.1208-1.1208 1.3269-2.80688.6237-4.13632-.1485-.28056-.4213-.474-.7351-.52125-.0673-.01012-.1347-.01327-.2014-.00977.0035-.06666.0004-.13409-.0098-.20136-.0472-.31386-.2406-.58666-.5212-.73508-1.3294-.70329-3.0155-.49713-4.1363.62367-.8374.83741-1.1639 1.9906-.9828 3.07365l-1.7788 1.77875-2.23152-2.23148-1.41419 1.41424Zm1.31056-3.1394c-.04235-.32684-.24303-.61183-.53647-.76186l-1.98183-1.0133c-.38619-.19746-.85564-.12345-1.16234.18326l-.86321.8632c-.3067.3067-.38072.77616-.18326 1.16235l1.0133 1.98182c.15004.29345.43503.49412.76187.53647l1.1127.14418c.3076.03985.61628-.06528.8356-.28461l.86321-.8632c.21932-.21932.32446-.52801.2846-.83561l-.14417-1.1127ZM19.4448 16.4052l-3.1186-3.1187c-.7811-.781-2.0474-.781-2.8285 0l-.1719.172c-.7811.781-.7811 2.0474 0 2.8284l3.1186 3.1187c.7811.781 2.0474.781 2.8285 0l.1719-.172c.7811-.781.7811-2.0474 0-2.8284Z"/>
                    </svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Jasa</span>
                </a>
            </li>
            {{-- MENU SUPPLIER --}}
            <li>
                <a href="{{ route('suppliers.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('suppliers.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                {{-- Ganti dengan icon yang sesuai (contoh: truck) --}}
                <svg class="w-5 h-5 text-gray-500..." ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Supplier</span>
                </a>
            </li>
            {{-- MENU PELANGGAN --}}
            <li>
                <a href="{{ route('customers.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('customers.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                {{-- Ganti dengan icon yang sesuai (contoh: users) --}}
                <svg class="w-5 h-5 text-gray-500..." ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Pelanggan</span>
                </a>
            </li>
            <li class="pt-2">
                <span class="px-2 text-xs font-semibold text-gray-500 uppercase">Transactions</span>
            </li>

            <li>
                <a href="{{ route('purchases.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover-bg-gray-700 group {{ request()->routeIs('purchases.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                {{-- Ganti dengan icon yang sesuai (contoh: cart arrow down) --}}
                <svg class="w-5 h-5 text-gray-500..." ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Pembelian</span>
                </a>
            </li>
            {{-- PEMBATAS MENU (TRANSAKSI) --}}
            <li class="pt-2">
                <span class="px-2 text-xs font-semibold text-gray-500 uppercase">Transactions</span>
            </li>
            <li>
                <a href="{{ route('pos.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('pos.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <svg ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">POS / Kasir</span>
                </a>
            </li>
            <li>
                <a href="{{ route('purchases.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('purchases.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <svg ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Pembelian</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales.history.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('sales.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <svg ...>...</svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Riwayat Penjualan</span>
                </a>
            </li>
        </ul>
    </div>
</aside>