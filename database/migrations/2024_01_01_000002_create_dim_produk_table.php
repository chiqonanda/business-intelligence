<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_produk', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_line')->nullable();   // Footwear, Apparel, Equipment
            $table->decimal('mrp', 10, 2)->nullable();   // Maximum Retail Price
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_produk');
    }
};