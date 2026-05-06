<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimProduk extends Model
{
    protected $table = 'dim_produk';

    protected $fillable = [
        'product_name',
        'product_line',
        'mrp',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(FactPenjualan::class, 'dim_produk_id');
    }
}