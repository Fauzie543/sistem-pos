<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Company::truncate();
        Schema::enableForeignKeyConstraints();

        // --- DATA UNTUK PEMILIK APLIKASI (LANDLORD) ---
        $superadminRole = Role::where('name', 'superadmin')->first();
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'), // Ganti dengan password super aman Anda
            'role_id' => $superadminRole->id,
            'company_id' => null, // PENTING: Super Admin tidak terikat pada company manapun
        ]);


        // --- DATA CONTOH UNTUK KLIEN PERTAMA (TENANT) ---
        $defaultCompany = Company::create([
            'name' => 'Bengkel POS Default',
            'address' => 'Surabaya, Indonesia',
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();

        User::create([
            'name' => 'Admin Klien',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'company_id' => $defaultCompany->id, // User ini milik Bengkel POS Default
        ]);

        User::create([
            'name' => 'Kasir Klien',
            'email' => 'kasir@example.com',
            'password' => Hash::make('password'),
            'role_id' => $kasirRole->id,
            'company_id' => $defaultCompany->id, // User ini juga milik Bengkel POS Default
        ]);
    }
}