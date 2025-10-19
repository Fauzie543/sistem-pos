<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        Feature::truncate();

        // Fitur Inti (untuk semua paket)
        Feature::create(['key' => 'pos_transaction', 'name' => 'Transaksi Kasir (POS)']);
        Feature::create(['key' => 'product_management', 'name' => 'Manajemen Produk/Menu']);
        Feature::create(['key' => 'customer_management', 'name' => 'Manajemen Pelanggan']);
        Feature::create(['key' => 'qris_payment', 'name' => 'Pembayaran QRIS Dinamis']);
        Feature::create(['key' => 'basic_reports', 'name' => 'Laporan Penjualan Dasar']);

        // Fitur Spesifik Industri
        Feature::create(['key' => 'service_management', 'name' => 'Manajemen Jasa & Kendaraan']); // Untuk Bengkel/Jasa
        Feature::create(['key' => 'recipe_management', 'name' => 'Manajemen Resep & Bahan Baku']); // Untuk F&B

        // Fitur Pro (untuk semua paket Pro)
        Feature::create(['key' => 'purchase_management', 'name' => 'Manajemen Pembelian']);
        Feature::create(['key' => 'inventory_control', 'name' => 'Kontrol Inventaris & Stok']);
        Feature::create(['key' => 'advanced_reports', 'name' => 'Laporan Analitik Lanjutan']);
        Feature::create(['key' => 'employee_management', 'name' => 'Manajemen Pegawai']);
    }
}