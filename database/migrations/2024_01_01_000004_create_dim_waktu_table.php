<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_waktu', function (Blueprint $table) {
            $table->id();
            $table->date('order_date')->unique();
            $table->tinyInteger('hari');        // 1-31
            $table->tinyInteger('bulan');       // 1-12
            $table->smallInteger('tahun');      // 2020, 2021, ...
            $table->tinyInteger('kuartal');     // 1-4
            $table->string('nama_bulan');       // January, February, ...
            $table->string('nama_hari');        // Monday, Tuesday, ...
            $table->boolean('is_weekend')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_waktu');
    }
};