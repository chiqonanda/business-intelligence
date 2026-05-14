<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nike_reviews', function (Blueprint $table) {
            $table->id();
            $table->decimal('rating', 3, 1);
            $table->date('review_date')->nullable();
            $table->string('location')->nullable();
            $table->string('username')->nullable();
            $table->text('review')->nullable();
            $table->string('fit_feedback')->nullable();     // True to Size, Runs Small, Runs Big, Unknown
            $table->string('comfort_feedback')->nullable(); // Very Comfortable, Average, Uncomfortable, Unknown
            $table->string('recommend_feedback')->nullable(); // Yes, No, Unknown
            $table->string('product_title');
            $table->string('subtitle')->nullable();
            $table->string('color_description')->nullable();
            $table->decimal('full_price', 10, 2)->nullable();
            $table->boolean('discounted')->default(false);
            $table->decimal('current_price', 10, 2)->nullable();
            $table->boolean('is_promo_review')->default(false);
            $table->boolean('is_launch')->default(false);
            $table->string('pid')->nullable();
            $table->string('label')->nullable(); // IN_STOCK, BEST_SELLER
            $table->timestamps();

            $table->index('rating');
            $table->index('product_title');
            $table->index('review_date');
            $table->index('label');
            $table->index('fit_feedback');
            $table->index('comfort_feedback');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nike_reviews');
    }
};
