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
        Schema::table('companies', function (Blueprint $table) {
            // Kolom untuk menyimpan nama provider, misal: 'midtrans', 'gopay'
            $table->string('payment_gateway_provider')->nullable()->after('features');

            // Kolom JSON untuk menyimpan semua kunci API secara terenkripsi
            $table->json('payment_gateway_keys')->nullable()->after('payment_gateway_provider');

            // Kolom untuk mode produksi/sandbox
            $table->boolean('payment_gateway_is_production')->default(false)->after('payment_gateway_keys');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway_provider', 'payment_gateway_keys', 'payment_gateway_is_production']);
        });
    }
};