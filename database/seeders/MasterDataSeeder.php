<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Nonaktifkan pengecekan foreign key untuk truncate
        Schema::disableForeignKeyConstraints();
        
        // Kosongkan tabel secara berurutan (dari anak ke induk)
        Vehicle::truncate();
        Product::truncate();
        Service::truncate();
        Customer::truncate();
        Supplier::truncate();
        Category::truncate();

        // Aktifkan kembali
        Schema::enableForeignKeyConstraints();

        // 1. Seed Categories
        $this->command->info('Seeding Categories...');
        $categories = [
            ['name' => 'Oli & Cairan'], ['name' => 'Ban & Velg'], ['name' => 'Aki'],
            ['name' => 'Rem'], ['name' => 'Suku Cadang Mesin'], ['name' => 'Jasa Servis Rutin'],
            ['name' => 'Jasa Perbaikan'],
        ];
        foreach ($categories as $cat) { Category::create($cat); }

        // 2. Seed Services (tergantung Kategori)
        $this->command->info('Seeding Services...');
        $servisRutin = Category::where('name', 'Jasa Servis Rutin')->first();
        $servisPerbaikan = Category::where('name', 'Jasa Perbaikan')->first();
        $services = [
            ['category_id' => $servisRutin->id, 'name' => 'Ganti Oli Mesin', 'price' => 50000],
            ['category_id' => $servisRutin->id, 'name' => 'Servis Rem Lengkap', 'price' => 75000],
            ['category_id' => $servisRutin->id, 'name' => 'Tune Up Mesin Injeksi', 'price' => 150000],
            ['category_id' => $servisPerbaikan->id, 'name' => 'Tambal Ban Tubeless', 'price' => 25000],
        ];
        foreach ($services as $serv) { Service::create($serv); }

        // 3. Seed Suppliers (menggunakan factory)
        $this->command->info('Seeding Suppliers...');
        Supplier::factory(10)->create();

        // 4. Seed Products (menggunakan factory)
        $this->command->info('Seeding Products...');
        Product::factory(50)->create();

        // 5. Seed Customers & their Vehicles (menggunakan factory)
        $this->command->info('Seeding Customers and Vehicles...');
        Customer::factory(30)->create()->each(function ($customer) {
            $customer->vehicles()->saveMany(
                Vehicle::factory(rand(1, 2))->make()
            );
        });
    }
}