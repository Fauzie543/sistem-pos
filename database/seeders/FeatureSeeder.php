<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        Feature::truncate();

        // Fitur Inti (umum untuk semua)
        Feature::create(['key' => 'pos_transaction', 'name' => 'Transaksi Kasir (POS)']);
        Feature::create(['key' => 'product_management', 'name' => 'Manajemen Produk/Menu']);
        Feature::create(['key' => 'customer_management', 'name' => 'Manajemen Pelanggan']);
        Feature::create(['key' => 'qris_payment', 'name' => 'Pembayaran QRIS Dinamis']);
        Feature::create(['key' => 'basic_reports', 'name' => 'Laporan Penjualan Dasar']);

        // Fitur tambahan sesuai sidebar
        Feature::create(['key' => 'service_management', 'name' => 'Manajemen Jasa & Kendaraan']); 
        Feature::create(['key' => 'purchase_management', 'name' => 'Manajemen Pembelian']);
        Feature::create(['key' => 'inventory_control', 'name' => 'Kontrol Inventaris & Stok']);
        Feature::create(['key' => 'employee_management', 'name' => 'Manajemen Pegawai']);
        Feature::create(['key' => 'promo_discount', 'name' => 'Diskon & Promo Otomatis']);
        Feature::create(['key' => 'multi_outlet', 'name' => 'Multi Outlet']);
    }
}