<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class DimWaktu extends Model
{
    protected $table = 'dim_waktu';

    protected $fillable = [
        'order_date',
        'hari',
        'bulan',
        'tahun',
        'kuartal',
        'nama_bulan',
        'nama_hari',
        'is_weekend',
    ];

    protected $casts = [
        'order_date'  => 'date',
        'is_weekend'  => 'boolean',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(FactPenjualan::class, 'dim_waktu_id');
    }

    // Helper: buat / ambil record DimWaktu dari sebuah tanggal
    public static function fromDate(?string $dateString): self
    {
        $date = Carbon::parse($dateString ?? now())->startOfDay();

    return self::firstOrCreate(
        [
            'order_date' => $date->toDateTimeString()
        ],
        [
            'hari'       => $date->day,
            'bulan'      => $date->month,
            'tahun'      => $date->year,
            'kuartal'    => $date->quarter,
            'nama_bulan' => $date->format('F'),
            'nama_hari'  => $date->format('l'),
            'is_weekend' => $date->isWeekend(),
        ]
    );
}
}