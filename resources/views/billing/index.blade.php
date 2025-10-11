@extends('layouts.app')
@section('title', 'Langganan')
@section('header', 'Langganan & Pembayaran')

@section('content')
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    <div class="text-center">
        <h2 class="text-2xl font-bold">Masa Uji Coba Anda Telah Berakhir</h2>
        <p class="text-gray-600 mt-2">Silakan pilih paket langganan untuk terus menggunakan layanan kami.</p>
        {{-- Nanti Anda bisa tambahkan daftar paket harga di sini --}}
        <a href="#" class="mt-6 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Lihat Paket Langganan
        </a>
    </div>
@endsection