<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Pastikan foreign key dideklarasikan dulu sebelum unique
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('cascade');

            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unique(['name', 'company_id', 'outlet_id'], 'categories_unique_name_company_outlet');
            $table->index(['company_id', 'outlet_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};