<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        // Definisikan semua paket dan harga di sini
        $plans = [
            'bengkel' => [
                'name' => 'POS Bengkel',
                'description' => 'Paket lengkap untuk manajemen bengkel modern.',
                'features' => [
                    'Manajemen Produk & Stok',
                    'Manajemen Jasa & Mekanik',
                    'Manajemen Pelanggan & Kendaraan',
                    'Transaksi Kasir (POS)',
                    'Pembayaran QRIS Dinamis',
                    'Manajemen Pembelian',
                    'Laporan Penjualan',
                ],
                'tiers' => [
                    'monthly' => ['price' => 150000, 'months' => 1],
                    'semi_annually' => ['price' => 750000, 'months' => 6], // Bayar 5 bulan, gratis 1
                    'annually' => ['price' => 1500000, 'months' => 12], // Bayar 10 bulan, gratis 2
                ],
            ],
            'cafe' => [
                'name' => 'POS F&B',
                'description' => 'Sistem kasir optimal untuk Cafe, Resto, atau Booth Minuman.',
                'features' => [
                    'Manajemen Menu & Stok Bahan',
                    'Manajemen Pelanggan',
                    'Transaksi Kasir (POS)',
                    'Pembayaran QRIS Dinamis',
                    'Manajemen Pembelian Bahan',
                    'Laporan Penjualan',
                    'Manajemen Meja & Dapur (Contoh)',
                ],
                'tiers' => [
                    'monthly' => ['price' => 120000, 'months' => 1],
                    'semi_annually' => ['price' => 600000, 'months' => 6], // Bayar 5 bulan, gratis 1
                    'annually' => ['price' => 1200000, 'months' => 12], // Bayar 10 bulan, gratis 2
                ],
            ],
        ];

        return view('billing.index', compact('plans'));
    }

    // Nanti, kita akan buat logika untuk memproses pembayaran di sini
    public function processSubscription(Request $request)
    {
        // Fungsi ini akan menangani logika saat user memilih paket
        // Contoh: membuat checkout session di Midtrans
        dd($request->all()); // Untuk testing: Tampilkan data yang dipilih
    }
}