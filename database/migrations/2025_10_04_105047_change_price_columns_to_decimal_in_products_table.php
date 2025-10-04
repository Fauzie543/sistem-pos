<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi decimal (total 15 digit, 2 di belakang koma)
            $table->decimal('purchase_price', 15, 2)->change();
            $table->decimal('selling_price', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Mengembalikan ke tipe semula jika di-rollback
            $table->unsignedBigInteger('purchase_price')->change();
            $table->unsignedBigInteger('selling_price')->change();
        });
    }
};