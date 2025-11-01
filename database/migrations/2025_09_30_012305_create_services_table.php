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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name')->unique();
            $table->unsignedBigInteger('price');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->index(['company_id', 'outlet_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};