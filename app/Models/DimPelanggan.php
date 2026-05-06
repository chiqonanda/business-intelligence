<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimPelanggan extends Model
{
    protected $table = 'dim_pelanggan';

    protected $fillable = [
        'gender_category',
        'region',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(FactPenjualan::class, 'dim_pelanggan_id');
    }
}