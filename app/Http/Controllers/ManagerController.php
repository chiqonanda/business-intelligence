<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        $stats = FactPenjualan::select(
            DB::raw('IFNULL(SUM(revenue), 0) as total_revenue'),
            DB::raw('IFNULL(SUM(profit), 0) as total_profit'),
            DB::raw('IFNULL(SUM(units_sold), 0) as total_units'),
            DB::raw('IFNULL(AVG(revenue), 0) as avg_revenue')
        )->first();

        $regions = FactPenjualan::select(
                'dim_pelanggan.region',
                DB::raw('IFNULL(SUM(fact_penjualan.revenue), 0) as total_revenue')
            )
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->groupBy('dim_pelanggan.region')
            ->get();

        $quarters = FactPenjualan::select(
                'dim_waktu.kuartal',
                'dim_waktu.tahun',
                DB::raw('IFNULL(SUM(fact_penjualan.revenue), 0) as total_revenue'),
                DB::raw('IFNULL(SUM(fact_penjualan.profit), 0) as total_profit'),
                DB::raw('IFNULL(SUM(fact_penjualan.units_sold), 0) as total_units')
            )
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->where('dim_waktu.tahun', now()->year)
            ->groupBy('dim_waktu.kuartal', 'dim_waktu.tahun')
            ->orderBy('dim_waktu.kuartal')
            ->get();

        $product_lines = FactPenjualan::select(
                'dim_produk.product_line',
                DB::raw('IFNULL(SUM(fact_penjualan.revenue), 0) as total_revenue')
            )
            ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id')
            ->groupBy('dim_produk.product_line')
            ->orderByDesc('total_revenue')
            ->get();

        return Inertia::render('Dashboard/Manager', [
            'summary'       => $stats,
            'regions'       => $regions,
            'quarters'      => $quarters,
            'product_lines' => $product_lines
        ]);
    }
}