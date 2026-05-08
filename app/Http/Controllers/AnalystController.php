<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use App\Models\DimProduk;
use App\Models\DimPelanggan;
use App\Models\DimWaktu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalystController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Analyst', [
            'years' => DimWaktu::distinct()->orderByDesc('tahun')->pluck('tahun'),
            'regions' => DimPelanggan::distinct()->orderBy('region')->pluck('region'),
        ]);
    }

    public function apiData(Request $request)
    {
        $year = $request->get('year');
        $region = $request->get('region');
        $search = $request->get('search');

        // Query Base
        $query = FactPenjualan::with(['produk', 'pelanggan', 'waktu'])
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id');

        // Filters
        if ($year) $query->where('dim_waktu.tahun', $year);
        if ($region) $query->where('dim_pelanggan.region', $region);
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('fact_penjualan.order_id', 'like', "%$search%")
                  ->orWhere('dim_produk.product_name', 'like', "%$search%");
            });
        }

        // Transactions (Paginated)
        $transactions = (clone $query)->orderByDesc('fact_penjualan.id')->paginate(10);

        // KPIs Calculation
        $stats = (clone $query)->select(
            DB::raw('SUM(fact_penjualan.revenue) as total_revenue'),
            DB::raw('SUM(fact_penjualan.profit) as total_profit'),
            DB::raw('COUNT(fact_penjualan.id) as total_orders'),
            DB::raw('AVG(fact_penjualan.revenue) as avg_order')
        )->first();

        $margin = $stats->total_revenue > 0 ? ($stats->total_profit / $stats->total_revenue) * 100 : 0;

        $kpis = [
            ['label' => 'TOTAL REVENUE', 'value' => '$' . number_format($stats->total_revenue / 1000, 1) . 'K'],
            ['label' => 'TOTAL PROFIT', 'value' => '$' . number_format($stats->total_profit / 1000, 1) . 'K'],
            ['label' => 'TOTAL ORDERS', 'value' => number_format($stats->total_orders)],
            ['label' => 'AVG ORDER', 'value' => '$' . number_format($stats->avg_order)],
            ['label' => 'PROFIT MARGIN', 'value' => number_format($margin, 1) . '%'],
        ];

        // Charts Data
        $charts = [
            'trends' => $this->getTrends($year, $region),
            'products' => $this->getTopProducts($year, $region),
            'regions' => $this->getRegionSplit($year),
            'gender' => $this->getGenderSplit($year, $region),
            'channel' => $this->getChannelSplit($year, $region),
        ];

        return response()->json([
            'transactions' => $transactions,
            'charts' => $charts,
            'kpis' => $kpis,
        ]);
    }

    private function getTrends($year, $region)
    {
        $q = FactPenjualan::select(
            'dim_waktu.nama_bulan',
            DB::raw('SUM(fact_penjualan.revenue) as revenue'),
            DB::raw('SUM(fact_penjualan.profit) as profit')
        )
        ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
        ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
        ->groupBy('dim_waktu.bulan', 'dim_waktu.nama_bulan')
        ->orderBy('dim_waktu.bulan');

        if ($year) $q->where('dim_waktu.tahun', $year);
        if ($region) $q->where('dim_pelanggan.region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('nama_bulan')->map(fn($m) => substr($m, 0, 3)),
            'revenue' => $res->pluck('revenue'),
            'profit' => $res->pluck('profit'),
        ];
    }

    private function getTopProducts($year, $region)
    {
        $q = FactPenjualan::select('dim_produk.product_name', DB::raw('SUM(fact_penjualan.revenue) as revenue'))
            ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id')
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->groupBy('dim_produk.product_name')
            ->orderByDesc('revenue')
            ->limit(10);

        if ($year) $q->where('dim_waktu.tahun', $year);
        if ($region) $q->where('dim_pelanggan.region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('product_name')->map(fn($n) => strlen($n) > 12 ? substr($n, 0, 12).'...' : $n),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getRegionSplit($year)
    {
        $q = FactPenjualan::select('dim_pelanggan.region', DB::raw('SUM(fact_penjualan.revenue) as revenue'))
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->groupBy('dim_pelanggan.region');

        if ($year) $q->where('dim_waktu.tahun', $year);

        $res = $q->get();
        return [
            'labels' => $res->pluck('region'),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getGenderSplit($year, $region)
    {
        $q = FactPenjualan::select('dim_pelanggan.gender_category', DB::raw('SUM(fact_penjualan.revenue) as revenue'))
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->groupBy('dim_pelanggan.gender_category');

        if ($year) $q->where('dim_waktu.tahun', $year);
        if ($region) $q->where('dim_pelanggan.region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('gender_category'),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getChannelSplit($year, $region)
    {
        $q = FactPenjualan::select('sales_channel', DB::raw('SUM(revenue) as revenue'))
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->groupBy('sales_channel');

        if ($year) $q->where('dim_waktu.tahun', $year);
        if ($region) $q->where('dim_pelanggan.region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('sales_channel'),
            'data' => $res->pluck('revenue'),
        ];
    }

    public function export(Request $request)
    {
        $data = FactPenjualan::with(['produk', 'pelanggan', 'waktu'])->get();
        $filename = 'analytics_export_' . now()->timestamp . '.csv';
        $headers = ["Content-Type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename"];

        return response()->stream(function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Date', 'Product', 'Region', 'Revenue', 'Profit']);
            foreach ($data as $row) {
                fputcsv($file, [$row->order_id, $row->waktu?->order_date, $row->produk?->product_name, $row->pelanggan?->region, $row->revenue, $row->profit]);
            }
            fclose($file);
        }, 200, $headers);
    }
}