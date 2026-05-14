<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nike_sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('gender_category')->nullable();  // Men, Women, Kids
            $table->string('product_line')->nullable();     // Running, Basketball, etc.
            $table->string('product_name')->nullable();
            $table->string('size')->nullable();
            $table->decimal('units_sold', 8, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('discount_applied', 5, 4)->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->date('order_date')->nullable();
            $table->string('sales_channel')->nullable();    // Online, Retail
            $table->string('region')->nullable();           // Mumbai, Delhi, etc.
            $table->decimal('profit', 12, 2)->default(0);
            $table->timestamps();

            $table->index('order_date');
            $table->index('region');
            $table->index('product_line');
            $table->index('gender_category');
            $table->index('sales_channel');
            $table->index('product_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nike_sales');
    }
};
