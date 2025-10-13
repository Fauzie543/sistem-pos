@extends('layouts.app')
@section('title', 'Langganan')
@section('header', 'Pilih Paket Langganan Anda')

@section('content')
<div class="max-w-7xl mx-auto">
    @php $company = auth()->user()->company; @endphp

    {{-- Tampilkan status jika sudah berlangganan --}}
    @if($company && $company->subscription_ends_at?->isFuture())
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-8" role="alert">
            <p class="font-bold">Anda adalah Pelanggan Aktif</p>
            <p>Paket **{{ $company->plan->name ?? 'N/A' }}** Anda aktif hingga **{{ $company->subscription_ends_at->format('d F Y') }}**.</p>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div id="plan-selection-form">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
                    <h3 class="text-2xl font-bold text-center">{{ $plan->name }}</h3>
                    <p class="text-gray-500 text-center mt-2 h-12">{{ $plan->description }}</p>

                    {{-- =============================================== --}}
                    {{-- KEMBALIKAN KODE DAFTAR FITUR DI SINI --}}
                    {{-- =============================================== --}}
                    <ul class="space-y-3 mt-6 flex-grow">
                        @foreach($plan->features as $feature)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8 space-y-4">
                        @php $monthlyPrice = $plan->tiers->firstWhere('duration_months', 1)->price ?? 0; @endphp
                        @foreach($plan->tiers->sortBy('duration_months') as $tier)
                            <label class="relative block border rounded-lg p-4 cursor-pointer hover:border-blue-500 tier-option">
                                <input type="radio" name="plan_tier" value="{{ $tier->id }}" class="absolute top-4 right-4">
                                
                                {{-- =============================================== --}}
                                {{-- KEMBALIKAN KODE DETAIL HARGA DI SINI --}}
                                {{-- =============================================== --}}
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
                    Swal.fire({
                        title: 'Pembayaran Berhasil!',
                        text: 'Langganan Anda sedang diaktifkan.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.reload();
                    });
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
});
</script>
@endpush