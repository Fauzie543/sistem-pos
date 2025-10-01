<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel roles terlebih dahulu untuk menghindari duplikasi
        Schema::disableForeignKeyConstraints();
        Role::truncate();
        Schema::enableForeignKeyConstraints();

        $roles = [
            ['name' => 'admin', 'description' => 'Administrator with full access'],
            ['name' => 'kasir', 'description' => 'Cashier for handling sales transactions'],
            ['name' => 'gudang', 'description' => 'Warehouse staff for managing stock'],
            ['name' => 'mekanik', 'description' => 'Mechanic for handling services'],
        ];

        // Masukkan data ke dalam tabel
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}