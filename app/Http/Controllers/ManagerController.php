<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        $summary = [
            'revenue_by_region' => FactPenjualan::select(
                    'dim_pelanggan.region',
                    DB::raw('SUM(fact_penjualan.revenue) as revenue')
                )
                ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
                ->groupBy('dim_pelanggan.region')
                ->get(),

            'revenue_by_quarter' => FactPenjualan::select(
                    'dim_waktu.kuartal',
                    DB::raw('SUM(fact_penjualan.revenue) as revenue'),
                    DB::raw('SUM(fact_penjualan.profit) as profit')
                )
                ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
                ->where('dim_waktu.tahun', now()->year)
                ->groupBy('dim_waktu.kuartal')
                ->orderBy('dim_waktu.kuartal')
                ->get(),

            'top_product_lines' => FactPenjualan::select(
                    'dim_produk.product_line',
                    DB::raw('SUM(fact_penjualan.revenue) as revenue')
                )
                ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id')
                ->groupBy('dim_produk.product_line')
                ->orderByDesc('revenue')
                ->get(),
        ];

        return Inertia::render('Dashboard/Manager', ['summary' => $summary]);
    }
}