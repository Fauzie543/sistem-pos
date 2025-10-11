<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Role::truncate();
        Schema::enableForeignKeyConstraints();

        $roles = [
            // TAMBAHKAN SUPERADMIN DI SINI (biasanya di paling atas)
            ['name' => 'superadmin', 'description' => 'Aplikasi Owner with God Mode'],
            ['name' => 'admin', 'description' => 'Administrator with full access per company'],
            ['name' => 'kasir', 'description' => 'Cashier for handling sales transactions'],
            ['name' => 'gudang', 'description' => 'Warehouse staff for managing stock'],
            ['name' => 'mekanik', 'description' => 'Mechanic for handling services'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}