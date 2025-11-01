{{-- /resources/views/layouts/partials/topnav.blade.php --}}


<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                {{-- Tombol Buka Sidebar (Mobile) --}}
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                
                {{-- =============================================== --}}
                {{-- AWAL PERUBAHAN LOGO & NAMA APLIKASI DINAMIS --}}
                {{-- =============================================== --}}
                <a href="{{ route('dashboard') }}" class="flex ms-2 md:me-24">
                    {{-- Gunakan variabel $appLogo yang sudah didefinisikan di atas --}}
                    <img src="{{ asset('assets/images/logo.png') }}" class="h-10 me-3 object-contain" alt="App Logo" />
                    
                    {{-- Gunakan variabel $appName yang sudah didefinisikan di atas --}}
                    <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">
                        KASLO POS
                    </span>
                </a>
                {{-- =============================================== --}}
                {{-- AKHIR PERUBAHAN --}}
                {{-- =============================================== --}}
            </div>
            
            {{-- Menu User (Kanan Atas) --}}
            <div class="flex items-center space-x-6 pr-4">
                @if(auth()->user()->company && auth()->user()->company->featureEnabled('multi_outlet'))
                    @php
                        $outlets = auth()->user()->company->outlets ?? collect();
                    @endphp

                    @if($outlets->count() > 0)
                        <form action="{{ route('outlet.switch') }}" method="POST" class="flex items-center">
                            @csrf
                            <select name="outlet_id" onchange="this.form.submit()"
                                class="border border-gray-300 rounded-md px-3 py-1.5 text-sm text-gray-700 
                                    dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}"
                                        {{ session('active_outlet_id', auth()->user()->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="text-sm text-gray-400 italic">Belum ada outlet</span>
                    @endif
                @endif
                <div class="flex items-center ms-3">
                    <div>
                        <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
                        </button>
                    </div>
                    <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600" id="dropdown-user">
                        <div class="px-4 py-3" role="none">
                            <p class="text-sm text-gray-900 dark:text-white" role="none">
                                {{ Auth::user()->name ?? 'Guest User' }}
                            </p>
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                                {{ Auth::user()->email ?? 'guest@example.com' }}
                            </p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Profil</a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); this.closest('form').submit();"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">
                                        Sign out
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>