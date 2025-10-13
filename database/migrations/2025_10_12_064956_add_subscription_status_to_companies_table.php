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
            // Kolom untuk menyimpan ID paket yang dipilih
            $table->foreignId('plan_id')->nullable()->constrained('plans')->after('trial_ends_at');

            // Kolom untuk menyimpan tanggal langganan berakhir
            $table->timestamp('subscription_ends_at')->nullable()->after('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'subscription_ends_at']);
        });
    }
};