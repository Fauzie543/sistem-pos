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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('mechanic_id')->nullable()->constrained('users'); // Mekanik yg mengerjakan
            $table->foreignId('user_id')->constrained('users'); // Kasir yg melayani
            $table->unsignedBigInteger('total_amount');
            $table->string('payment_method');
            $table->enum('status', ['lunas', 'belum bayar'])->default('lunas');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};