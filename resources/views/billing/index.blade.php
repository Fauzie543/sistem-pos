@extends('layouts.app')

@section('title', 'Langganan')
@section('header', 'Pilih Paket Langganan Anda')

@section('content')
    <div class="max-w-7xl mx-auto">
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Masa Uji Coba Anda Telah Berakhir</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Pilih paket yang paling sesuai dengan kebutuhan bisnis Anda untuk melanjutkan.</p>
        </div>

        <form action="{{ route('subscribe.checkout') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($plans as $planKey => $plan)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">{{ $plan['name'] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-center mt-2 h-12">{{ $plan['description'] }}</p>
                        
                        {{-- Fitur --}}
                        <ul class="space-y-3 mt-6 flex-grow">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Pilihan Tier Harga --}}
                        <div class="mt-8 space-y-4">
                            @foreach($plan['tiers'] as $tierKey => $tier)
                                <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-blue-500">
                                    <input type="radio" name="plan" value="{{ $planKey }}_{{ $tierKey }}" class="absolute top-4 right-4">
                                    
                                    @if($tier['months'] == 12)
                                        <div class="absolute -top-3 right-4 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded-full">Paling Hemat</div>
                                    @endif

                                    <div class="font-bold text-lg text-gray-800 dark:text-white">{{ $tier['months'] }} Bulan</div>
                                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">Rp {{ number_format($tier['price'], 0, ',', '.') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Hemat Rp {{ number_format(($tier['months'] * $plan['tiers']['monthly']['price']) - $tier['price'], 0, ',', '.') }}</div>
                                </label>
                            @endforeach
                        </div>

                        <button type="submit" class="w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                            Pilih Paket {{ $plan['name'] }}
                        </button>
                    </div>
                @endforeach
            </div>
        </form>
    </div>
@endsection