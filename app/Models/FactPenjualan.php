<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactPenjualan extends Model
{
    protected $table = 'fact_penjualan';

    protected $fillable = [
        'order_id',
        'dim_produk_id',
        'dim_pelanggan_id',
        'dim_waktu_id',
        'revenue',
        'profit',
        'units_sold',
        'discount',
        'sales_channel',
        'payment_method',
    ];

    protected $casts = [
        'revenue'  => 'decimal:2',
        'profit'   => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    // ── Relasi ke dimensi ─────────────────────────────────────────

    public function produk(): BelongsTo
    {
        return $this->belongsTo(DimProduk::class, 'dim_produk_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(DimPelanggan::class, 'dim_pelanggan_id');
    }

    public function waktu(): BelongsTo
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    // ── Scope umum ────────────────────────────────────────────────

    public function scopeByYear($query, int $year)
    {
        return $query->whereHas('waktu', fn($q) => $q->where('tahun', $year));
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->whereHas('pelanggan', fn($q) => $q->where('region', $region));
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('sales_channel', $channel);
    }
}