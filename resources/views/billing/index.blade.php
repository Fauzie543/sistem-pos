@extends('layouts.app')
@section('title', 'Langganan')
@section('header', 'Pilih Paket Langganan Anda')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- ... (Pesan error dan header tidak berubah) ... --}}

    <form action="{{ route('subscribe.checkout') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
                    <h3 class="text-2xl font-bold text-center">{{ $plan->name }}</h3>
                    <p class="text-gray-500 text-center mt-2 h-12">{{ $plan->description }}</p>

                    <ul class="space-y-3 mt-6 flex-grow">
                        @foreach($plan->features as $feature)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8 space-y-4">
                        @php
                            // Ambil harga bulanan sebagai dasar perbandingan hemat
                            $monthlyPrice = $plan->tiers->firstWhere('duration_months', 1)->price ?? 0;
                        @endphp
                        @foreach($plan->tiers->sortBy('duration_months') as $tier)
                            <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-blue-500">
                                <input type="radio" name="plan_tier_id" value="{{ $tier->id }}" class="absolute top-4 right-4">

                                @if($tier->duration_months == 12)
                                    <div class="absolute -top-3 right-4 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded-full">Paling Hemat</div>
                                @endif

                                <div class="font-bold text-lg">{{ $tier->duration_months }} Bulan</div>
                                <div class="text-2xl font-extrabold mt-1">Rp {{ number_format($tier->price, 0, ',', '.') }}</div>
                                @if($monthlyPrice > 0 && $tier->duration_months > 1)
                                    <div class="text-sm text-gray-500">Hemat Rp {{ number_format(($tier->duration_months * $monthlyPrice) - $tier->price, 0, ',', '.') }}</div>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                        Pilih Paket {{ $plan->name }}
                    </button>
                </div>
            @endforeach
        </div>
    </form>
</div>
@endsection