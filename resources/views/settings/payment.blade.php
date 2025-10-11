@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran')
@section('header', 'Pengaturan Gateway Pembayaran')

@section('content')
    <form action="{{ route('settings.payment.update') }}" method="POST">
        @csrf
        <div class="space-y-6 max-w-2xl">

            {{-- Pilih Provider --}}
            <div>
                <label for="provider" class="block text-sm font-medium text-gray-700">Pilih Penyedia Gateway</label>
                <select name="payment_gateway_provider" id="provider" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Tidak Aktif --</option>
                    <option value="midtrans" @selected($company->payment_gateway_provider == 'midtrans')>Midtrans</option>
                    <option value="gopay" @selected($company->payment_gateway_provider == 'gopay')>GoPay (Contoh)</option>
                </select>
            </div>

            {{-- Pengaturan Midtrans --}}
            <div id="midtrans_settings" class="space-y-4 border-t pt-4" style="display: none;">
                <h3 class="text-lg font-medium">Konfigurasi Midtrans</h3>
                <div>
                    <label for="midtrans_server_key" class="block text-sm font-medium text-gray-700">Server Key</label>
                    <input type="text" name="keys[server_key]" id="midtrans_server_key" value="{{ $company->payment_gateway_keys['server_key'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="midtrans_client_key" class="block text-sm font-medium text-gray-700">Client Key</label>
                    <input type="text" name="keys[client_key]" id="midtrans_client_key" value="{{ $company->payment_gateway_keys['client_key'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            {{-- Pengaturan GoPay (sebagai contoh) --}}
            <div id="gopay_settings" class="space-y-4 border-t pt-4" style="display: none;">
                <h3 class="text-lg font-medium">Konfigurasi GoPay</h3>
                <div>
                    <label for="gopay_merchant_id" class="block text-sm font-medium text-gray-700">Merchant ID</label>
                    <input type="text" name="keys[merchant_id]" id="gopay_merchant_id" value="{{ $company->payment_gateway_keys['merchant_id'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            {{-- Mode Produksi --}}
            <div class="border-t pt-4">
                <label for="is_production" class="flex items-center">
                    <input type="hidden" name="payment_gateway_is_production" value="0">
                    <input type="checkbox" name="payment_gateway_is_production" id="is_production" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked($company->payment_gateway_is_production)>
                    <span class="ms-2 text-sm text-gray-600">Aktifkan Mode Produksi (Live)</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">Jika tidak dicentang, sistem akan menggunakan mode Sandbox/Testing.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Pengaturan</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(function() {
            function toggleSettings() {
                const provider = $('#provider').val();
                $('#midtrans_settings').hide();
                $('#gopay_settings').hide();

                if (provider) {
                    $(`#${provider}_settings`).show();
                }
            }

            // Tampilkan form yang sesuai saat halaman dimuat
            toggleSettings();

            // Tampilkan form yang sesuai saat pilihan berubah
            $('#provider').on('change', toggleSettings);
        });
    </script>
@endpush