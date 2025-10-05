@extends('layouts.app')
@section('title', 'Profil Perusahaan')
@section('header', 'Profil Perusahaan')

@section('content')
    <div class="bg-white p-6 rounded-md shadow-sm">

        <div class="flex justify-end mb-4">
            {{-- Tombol hanya aktif jika belum ada data, jika sudah ada, teksnya berubah --}}
            <button id="editCompanyBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ $company ? 'Edit Profil Perusahaan' : 'Tambah Profil Perusahaan' }}
            </button>
        </div>

        @if($company)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Logo --}}
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium text-gray-900">Logo</h3>
                    @if($company->logo)
                        <img src="{{ Storage::url($company->logo) }}" alt="Company Logo" class="mt-2 rounded-md border h-32 w-32 object-cover">
                    @else
                        <p class="text-gray-500 mt-2">Belum ada logo.</p>
                    @endif
                </div>

                {{-- Informasi Utama --}}
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Nama Perusahaan</h3>
                        <p class="text-lg text-gray-900">{{ $company->name }}</p>
                    </div>
                     <div>
                        <h3 class="text-sm font-medium text-gray-500">Alamat</h3>
                        <p class="text-lg text-gray-900">{{ $company->address ?? '-' }}</p>
                    </div>
                     <div>
                        <h3 class="text-sm font-medium text-gray-500">No. Telepon</h3>
                        <p class="text-lg text-gray-900">{{ $company->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Instagram</h3>
                        <p class="text-lg text-gray-900">{{ $company->instagram ?? '-' }}</p>
                    </div>
                     <div>
                        <h3 class="text-sm font-medium text-gray-500">Tiktok</h3>
                        <p class="text-lg text-gray-900">{{ $company->tiktok ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Lokasi Maps</h3>
                        <p class="text-lg text-gray-900">Lat: {{ $company->latitude ?? '-' }}, Long: {{ $company->longitude ?? '-' }}</p>
                    </div>
                     <div>
                        <h3 class="text-sm font-medium text-gray-500">Info WiFi</h3>
                        <p class="text-lg text-gray-900">SSID: {{ $company->wifi_ssid ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500">Data profil perusahaan belum diisi.</p>
                <p class="text-gray-500">Silakan klik tombol "Tambah Profil Perusahaan" untuk melengkapi.</p>
            </div>
        @endif
    </div>

    <div id="companyModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 id="modal_title" class="text-xl font-semibold">{{ $company ? 'Edit Profil' : 'Tambah Profil' }}</h3>
                <button class="text-gray-500 hover:text-gray-800 text-2xl close-modal">&times;</button>
            </div>

            <form id="companyForm" action="{{ route('company.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    {{-- Baris 1: Nama, Telp --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Perusahaan*</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $company->name ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" id="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>

                    {{-- Baris 2: Instagram, TikTok --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="instagram" class="block text-sm font-medium text-gray-700">Instagram (username)</label>
                            <input type="text" name="instagram" id="instagram" value="{{ old('instagram', $company->instagram ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="tiktok" class="block text-sm font-medium text-gray-700">TikTok (username)</label>
                            <input type="text" name="tiktok" id="tiktok" value="{{ old('tiktok', $company->tiktok ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    {{-- Baris 3: Latitude, Longitude --}}
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                            <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $company->latitude ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                            <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $company->longitude ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    {{-- Baris 4: WiFi --}}
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="wifi_ssid" class="block text-sm font-medium text-gray-700">Nama WiFi (SSID)</label>
                            <input type="text" name="wifi_ssid" id="wifi_ssid" value="{{ old('wifi_ssid', $company->wifi_ssid ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="wifi_password" class="block text-sm font-medium text-gray-700">Password WiFi</label>
                            <input type="text" name="wifi_password" id="wifi_password" value="{{ old('wifi_password', $company->wifi_password ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    {{-- Logo --}}
                    <div>
                         <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
                         <input type="file" name="logo" id="logo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                         @if($company && $company->logo)
                            <div class="mt-2">
                                <span class="text-xs text-gray-500">Logo saat ini:</span>
                                <img src="{{ Storage::url($company->logo) }}" alt="Current Logo" class="h-16 w-16 object-cover rounded-md border">
                            </div>
                         @endif
                    </div>
                </div>

                <div class="flex justify-end items-center border-t pt-4 mt-4">
                    <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2 close-modal">Batal</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $('#editCompanyBtn').on('click', function() {
            $('#companyModal').removeClass('hidden');
        });

        $('.close-modal').on('click', function() {
            $('#companyModal').addClass('hidden');
        });
        @if (session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif
    });
</script>
@endpush