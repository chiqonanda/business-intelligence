<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();

            // Foreign keys ke dimensi
            $table->foreignId('dim_produk_id')->constrained('dim_produk')->onDelete('restrict');
            $table->foreignId('dim_pelanggan_id')->constrained('dim_pelanggan')->onDelete('restrict');
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu')->onDelete('restrict');

            // Measures / metrik
            $table->decimal('revenue', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            $table->integer('units_sold')->default(0);
            $table->decimal('discount', 5, 2)->default(0);  // dalam persen

            // Channel & info tambahan
            $table->string('sales_channel')->nullable();     // Online, Offline, Wholesale
            $table->string('payment_method')->nullable();

            $table->timestamps();

            // Index untuk query performa
            $table->index('dim_waktu_id');
            $table->index('dim_produk_id');
            $table->index('dim_pelanggan_id');
            $table->index('sales_channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_penjualan');
    }
};