<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('gender_category')->nullable();  // Male, Female, Unisex
            $table->string('region')->nullable();           // North, South, East, West
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_pelanggan');
    }
};