<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel users terlebih dahulu
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil ID dari setiap role
        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();
        $gudangRole = Role::where('name', 'gudang')->first();

        // Buat user admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // ganti 'password' dengan password yang aman
            'role_id' => $adminRole->id,
        ]);

        // Buat user kasir
        User::create([
            'name' => 'Kasir User',
            'email' => 'kasir@example.com',
            'password' => Hash::make('password'),
            'role_id' => $kasirRole->id,
        ]);
        
        // Buat user gudang
        User::create([
            'name' => 'Gudang User',
            'email' => 'gudang@example.com',
            'password' => Hash::make('password'),
            'role_id' => $gudangRole->id,
        ]);

        // Anda bisa menambahkan user lain sesuai kebutuhan
    }
}