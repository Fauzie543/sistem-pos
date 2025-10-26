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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('sku')->comment('Stock Keeping Unit');
            $table->unique(['sku', 'company_id', 'outlet_id']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('purchase_price');
            $table->unsignedBigInteger('selling_price');
            $table->integer('stock')->default(0);
            $table->string('unit')->comment('e.g., pcs, liter, set');
            $table->string('storage_location')->nullable();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->index('company_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};