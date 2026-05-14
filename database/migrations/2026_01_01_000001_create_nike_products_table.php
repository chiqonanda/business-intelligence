<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nike_products', function (Blueprint $table) {
            $table->id();
            $table->string('uniq_id')->unique()->nullable();
            $table->string('name');
            $table->string('sub_title')->nullable();
            $table->string('brand')->default('Nike');
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('availability')->nullable();
            $table->text('description')->nullable();
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->integer('review_count')->default(0);
            $table->text('images')->nullable();
            $table->string('available_sizes')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('avg_rating');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nike_products');
    }
};
