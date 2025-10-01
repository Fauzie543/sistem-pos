@extends('layouts.app')

{{-- Set Judul Halaman --}}
@section('header')
    Dashboard
@endsection

{{-- Konten Utama --}}
@section('content')
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-medium">
            Selamat Datang Kembali, {{ $user->name }}!
        </h3>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Anda login sebagai <span class="font-semibold">{{ Str::ucfirst($user->role->name) }}</span>.
        </p>

        {{-- Di sini Anda bisa menambahkan konten dashboard lainnya nanti --}}
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
            <p>
                Ini adalah halaman utama Anda. Belum ada konten tambahan saat ini.
            </p>
        </div>
    </div>
@endsection