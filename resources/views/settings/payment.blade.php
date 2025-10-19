@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran')
@section('header', 'Pengaturan Gateway Pembayaran Midtrans')

@section('content')
    <form action="{{ route('settings.payment.update') }}" method="POST">
        @csrf
        <div class="space-y-6 max-w-2xl mx-auto">

            {{-- Input tersembunyi untuk selalu mengirim 'midtrans' sebagai provider --}}
            <input type="hidden" name="payment_gateway_provider" value="midtrans">

            {{-- Pengaturan Midtrans (sekarang selalu tampil) --}}
            <div class="space-y-4">
                <div>
                    <label for="midtrans_merchant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Merchant ID</label>
                    <input type="text" name="keys[merchant_id]" id="midtrans_merchant_id" value="{{ $company->payment_gateway_keys['merchant_id'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Contoh: Gxxxxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">ID unik merchant Anda dari dashboard Midtrans.</p>
                </div>
                <div>
                    <label for="midtrans_client_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Key</label>
                    <input type="text" name="keys[client_key]" id="midtrans_client_key" value="{{ $company->payment_gateway_keys['client_key'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan Client Key Anda">
                </div>
                <div>
                    <label for="midtrans_server_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Server Key</label>
                    <input type="password" name="keys[server_key]" id="midtrans_server_key" value="{{ $company->payment_gateway_keys['server_key'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan Server Key Anda">
                     <p class="text-xs text-gray-500 mt-1">Jika Anda hanya ingin menonaktifkan, kosongkan semua kolom di atas.</p>
                </div>
            </div>

            {{-- Mode Produksi --}}
            <div class="border-t pt-4 dark:border-gray-700">
                <label for="is_production" class="flex items-center">
                    <input type="hidden" name="payment_gateway_is_production" value="0">
                    <input type="checkbox" name="payment_gateway_is_production" id="is_production" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked($company->payment_gateway_is_production)>
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Aktifkan Mode Produksi (Live)</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">Jika tidak dicentang, sistem akan menggunakan mode Sandbox/Testing.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Pengaturan</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Script ini akan berjalan setelah halaman dimuat
        $(function() {
            
            // 1. Cek jika ada pesan 'success' dari controller
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 2500, // Alert akan hilang setelah 2.5 detik
                    showConfirmButton: false
                });
            @endif

            // 2. Cek jika ada error validasi dari controller
            @if ($errors->any())
                @php
                    // Gabungkan semua pesan error menjadi sebuah list HTML
                    $errorList = '<ul>';
                    foreach ($errors->all() as $error) {
                        $errorList .= '<li>' . $error . '</li>';
                    }
                    $errorList .= '</ul>';
                @endphp

                Swal.fire({
                    icon: 'error',
                    title: 'Oops... Terjadi Kesalahan!',
                    // Gunakan properti 'html' untuk menampilkan list
                    html: '{!! $errorList !!}',
                });
            @endif

        });
    </script>
@endpush