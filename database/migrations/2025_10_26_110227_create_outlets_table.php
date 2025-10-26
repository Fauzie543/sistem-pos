<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === TABEL UTAMA CABANG / OUTLET ===
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // === Tambahkan kolom outlet_id ke tabel penting ===

        // Products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        // Categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        Schema::table('sale_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_details', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });
        Schema::table('purchase_details', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_details', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        // Suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
            }
        });

        // Promos (jika promo bisa berbeda per outlet)
        if (Schema::hasTable('promos')) {
            Schema::table('promos', function (Blueprint $table) {
                if (!Schema::hasColumn('promos', 'outlet_id')) {
                    $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->cascadeOnDelete();
                }
            });
        }

        // Users (agar kasir/admin bisa dikaitkan ke outlet)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        $tables = [
            'products', 'categories', 'sales', 'purchases', 'suppliers', 'transactions', 'promos', 'users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'outlet_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('outlet_id');
                });
            }
        }

        Schema::dropIfExists('outlets');
    }
};