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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Oli', 'Aki', 'Ban', 'Mesin', 'Rem', 'Transmisi', 'Kemudi', 'Suspensi', 'Filter', 'Busi', 'Cairan', 'Aksesoris', 'Lainnya']);
            $table->string('brand');
            $table->integer('stock')->default(0);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('price', 15, 2);
            $table->foreignId('distributor_id')->constrained('distributors')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
