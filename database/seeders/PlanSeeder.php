<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Feature;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar tidak duplikat
        Plan::query()->delete();

        // Ambil semua fitur dari DB untuk dihubungkan
        $features = Feature::all()->keyBy('key');

        // --- 1. PAKET JASA BASIC ---
        $jasaBasic = Plan::create([
            'key' => 'jasa_basic',
            'name' => 'POS Jasa Basic',
            'description' => 'Solusi esensial untuk bengkel, salon, atau laundry.',
            'is_active' => true,
        ]);
        $jasaBasic->features()->sync([
            $features['pos_transaction']->id,
            $features['product_management']->id,
            $features['customer_management']->id,
            $features['qris_payment']->id,
            $features['basic_reports']->id,
            $features['service_management']->id, // Fitur khusus Jasa
        ]);
        $jasaBasic->tiers()->createMany([
            ['key' => 'jasa_basic_monthly', 'price' => 149000, 'duration_months' => 1],
            ['key' => 'jasa_basic_semi_annually', 'price' => 745000, 'duration_months' => 6],
            ['key' => 'jasa_basic_annually', 'price' => 1490000, 'duration_months' => 12],
        ]);

        // --- 2. PAKET JASA PRO ---
        $jasaPro = Plan::create([
            'key' => 'jasa_pro',
            'name' => 'POS Jasa Pro',
            'description' => 'Manajemen bisnis jasa lengkap dengan laporan analitik.',
            'is_active' => true,
        ]);
        $jasaPro->features()->sync([
            $features['pos_transaction']->id,
            $features['product_management']->id,
            $features['customer_management']->id,
            $features['qris_payment']->id,
            $features['basic_reports']->id,
            $features['service_management']->id,
            $features['purchase_management']->id, // Fitur Pro
            $features['inventory_control']->id, // Fitur Pro
            $features['advanced_reports']->id,  // Fitur Pro
            $features['employee_management']->id, // Fitur Pro
        ]);
        $jasaPro->tiers()->createMany([
            ['key' => 'jasa_pro_monthly', 'price' => 249000, 'duration_months' => 1],
            ['key' => 'jasa_pro_semi_annually', 'price' => 1245000, 'duration_months' => 6],
            ['key' => 'jasa_pro_annually', 'price' => 2490000, 'duration_months' => 12],
        ]);

        // --- 3. PAKET F&B BASIC ---
        $fnbBasic = Plan::create([
            'key' => 'fnb_basic',
            'name' => 'POS F&B Basic',
            'description' => 'Kasir modern untuk cafe, resto, dan booth minuman.',
            'is_active' => true,
        ]);
        $fnbBasic->features()->sync([
            $features['pos_transaction']->id,
            $features['product_management']->id,
            $features['customer_management']->id,
            $features['qris_payment']->id,
            $features['basic_reports']->id,
            $features['recipe_management']->id, // Fitur khusus F&B
        ]);
        $fnbBasic->tiers()->createMany([
            ['key' => 'fnb_basic_monthly', 'price' => 129000, 'duration_months' => 1],
            ['key' => 'fnb_basic_semi_annually', 'price' => 645000, 'duration_months' => 6],
            ['key' => 'fnb_basic_annually', 'price' => 1290000, 'duration_months' => 12],
        ]);

        // --- 4. PAKET F&B PRO ---
        $fnbPro = Plan::create([
            'key' => 'fnb_pro',
            'name' => 'POS F&B Pro',
            'description' => 'Kelola operasional F&B dengan inventaris dan analitik.',
            'is_active' => true,
        ]);
        $fnbPro->features()->sync([
            $features['pos_transaction']->id,
            $features['product_management']->id,
            $features['customer_management']->id,
            $features['qris_payment']->id,
            $features['basic_reports']->id,
            $features['recipe_management']->id,
            $features['purchase_management']->id, // Fitur Pro
            $features['inventory_control']->id, // Fitur Pro
            $features['advanced_reports']->id,  // Fitur Pro
            $features['employee_management']->id, // Fitur Pro
        ]);
        $fnbPro->tiers()->createMany([
            ['key' => 'fnb_pro_monthly', 'price' => 229000, 'duration_months' => 1],
            ['key' => 'fnb_pro_semi_annually', 'price' => 1145000, 'duration_months' => 6],
            ['key' => 'fnb_pro_annually', 'price' => 2290000, 'duration_months' => 12],
        ]);
    }
}