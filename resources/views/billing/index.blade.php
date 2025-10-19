@extends('layouts.app')
@section('title', 'Langganan')
@section('header', 'Langganan & Pembayaran')

@section('content')
<div class="max-w-7xl mx-auto">
    @php $company = auth()->user()->company; @endphp

    {{-- Tampilkan status jika sudah berlangganan --}}
    @if($company && $company->subscription_ends_at?->isFuture())
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Status Langganan Anda</h2>
            <p class="mt-4 text-lg">
                <span class="font-semibold text-gray-800 dark:text-gray-200">Paket Aktif:</span>
                <span class="font-bold text-blue-600">{{ $company->plan->name ?? 'N/A' }}</span>
            </p>
            <p class="mt-2 text-lg">
                <span class="font-semibold text-gray-800 dark:text-gray-200">Berlaku Hingga:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $company->subscription_ends_at->format('d F Y') }}</span>
            </p>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- BAGIAN PILIHAN PAKET DENGAN LOGIKA BARU --}}
    {{-- =================================================================== --}}
    <div x-data="{ open: {{ ($company && $company->subscription_ends_at?->isFuture()) ? 'false' : 'true' }} }">
        
        {{-- Jika sudah berlangganan, tampilkan judul ini sebagai akordeon --}}
        @if($company && $company->subscription_ends_at?->isFuture())
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md flex justify-between items-center cursor-pointer" @click="open = !open">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pilihan Paket</h3>
                <svg class="w-6 h-6 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        {{-- Jika belum berlangganan, tampilkan judul ini --}}
        @else
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Masa Uji Coba Anda Telah Berakhir</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Pilih paket yang paling sesuai dengan kebutuhan bisnis Anda untuk melanjutkan.</p>
            </div>
        @endif

        {{-- Bagian Paket Harga yang Bisa Dilipat/Ditampilkan --}}
        <div x-show="open" x-transition class="mt-4">
            {{-- FORM DIBUNGKUS DI SINI --}}
            <form action="{{ route('subscribe.checkout') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    @foreach($plans as $plan)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
                            <h3 class="text-2xl font-bold text-center">{{ $plan->name }}</h3>
                            <p class="text-gray-500 text-center mt-2 h-12">{{ $plan->description }}</p>

                            <ul class="space-y-3 mt-6 flex-grow">
                                @foreach(($plan->features ?? []) as $feature)
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>{{ $feature->name }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-8 space-y-4">
                                @php $monthlyPrice = $plan->tiers->firstWhere('duration_months', 1)->price ?? 0; @endphp
                                @foreach($plan->tiers->sortBy('duration_months') as $tier)
                                    <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-blue-500 tier-option">
                                        <input type="radio" name="plan_tier" value="{{ $tier->id }}" class="absolute top-4 right-4">
                                        
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

                            <button type="button" class="pay-button w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                                Pilih & Bayar Paket
                            </button>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ... (Script Anda tidak perlu diubah) ... --}}
<script>
$(function() {
    $('.pay-button').on('click', function(e) {
        e.preventDefault();
        const selectedTierId = $(this).closest('.flex-col').find('input[name="plan_tier"]:checked').val();
        
        if (!selectedTierId) {
            Swal.fire('Pilih Durasi', 'Silakan pilih durasi langganan (1, 6, atau 12 bulan).', 'warning');
            return;
        }
        const button = $(this);
        button.prop('disabled', true).text('Memproses...');
        $.post("{{ route('subscribe.checkout') }}", {
            _token: "{{ csrf_token() }}",
            plan_tier_id: selectedTierId
        })
        .done(function(data) {
            window.snap.pay(data.snap_token, {
                onSuccess: function(result){
                    window.dispatchEvent(new CustomEvent('payment:success'));
                },
                onPending: function(result){
                    Swal.fire('Menunggu Pembayaran', 'Silakan selesaikan pembayaran Anda.', 'info');
                    button.prop('disabled', false).text('Pilih & Bayar Paket');
                },
                onError: function(result){
                    Swal.fire('Pembayaran Gagal', 'Terjadi kesalahan saat memproses pembayaran.', 'error');
                    button.prop('disabled', false).text('Pilih & Bayar Paket');
                },
                onClose: function(){
                    button.prop('disabled', false).text('Pilih & Bayar Paket');
                }
            });
        })
        .fail(function() {
            Swal.fire('Error', 'Gagal mendapatkan token pembayaran. Silakan coba lagi.', 'error');
            button.prop('disabled', false).text('Pilih & Bayar Paket');
        });
    });
    window.addEventListener('payment:success', function() {
        // Karena kode ini ada di halaman utama, ia bisa mengakses 'Swal'
        Swal.fire({
            title: 'Pembayaran Berhasil!',
            text: 'Langganan Anda sedang diaktifkan. Halaman akan dimuat ulang.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false,
            allowOutsideClick: false,
        }).then(() => {
            window.location.reload(); // Muat ulang halaman untuk melihat status baru
        });
    });
});
</script>
@endpush