<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use App\Models\DimProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalystController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Analyst');
    }

    // ── Chart: tren revenue per bulan ─────────────────────────────────────────
    public function revenueTrend(Request $request)
    {
        $year = $request->get('year', now()->year);

        $data = FactPenjualan::select(
                'dim_waktu.bulan',
                'dim_waktu.nama_bulan',
                DB::raw('SUM(fact_penjualan.revenue) as revenue'),
                DB::raw('SUM(fact_penjualan.profit) as profit')
            )
            ->join('dim_waktu', 'fact_penjualan.dim_waktu_id', '=', 'dim_waktu.id')
            ->where('dim_waktu.tahun', $year)
            ->groupBy('dim_waktu.bulan', 'dim_waktu.nama_bulan')
            ->orderBy('dim_waktu.bulan')
            ->get();

        return response()->json($data);
    }

    // ── Chart: top 10 produk by revenue ───────────────────────────────────────
    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);

        $data = FactPenjualan::select(
                'dim_produk.product_name',
                'dim_produk.product_line',
                DB::raw('SUM(fact_penjualan.revenue) as revenue'),
                DB::raw('SUM(fact_penjualan.units_sold) as units')
            )
            ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id')
            ->groupBy('dim_produk.product_name', 'dim_produk.product_line')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return response()->json($data);
    }

    // ── Chart: revenue per region ─────────────────────────────────────────────
    public function regionSplit()
    {
        $data = FactPenjualan::select(
                'dim_pelanggan.region',
                DB::raw('SUM(fact_penjualan.revenue) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->groupBy('dim_pelanggan.region')
            ->orderByDesc('revenue')
            ->get();

        return response()->json($data);
    }

    // ── Chart: gender split ───────────────────────────────────────────────────
    public function genderSplit()
    {
        $data = FactPenjualan::select(
                'dim_pelanggan.gender_category',
                DB::raw('SUM(fact_penjualan.revenue) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->join('dim_pelanggan', 'fact_penjualan.dim_pelanggan_id', '=', 'dim_pelanggan.id')
            ->groupBy('dim_pelanggan.gender_category')
            ->get();

        return response()->json($data);
    }

    // ── Chart: channel split ──────────────────────────────────────────────────
    public function channelSplit()
    {
        $data = FactPenjualan::select(
                'sales_channel',
                DB::raw('SUM(revenue) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('sales_channel')
            ->get();

        return response()->json($data);
    }

    // ── Tabel transaksi dengan filter & pagination ────────────────────────────
    public function transactions(Request $request)
    {
        $query = FactPenjualan::with(['produk', 'pelanggan', 'waktu'])
            ->when($request->region, fn($q) => $q->byRegion($request->region))
            ->when($request->channel, fn($q) => $q->byChannel($request->channel))
            ->when($request->year, fn($q) => $q->byYear($request->year));

        return response()->json(
            $query->orderByDesc('created_at')->paginate(20)
        );
    }

    // ── Export CSV ────────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $data = FactPenjualan::with(['produk', 'pelanggan', 'waktu'])
            ->when($request->year, fn($q) => $q->byYear($request->year))
            ->get();

        $filename = 'nike_penjualan_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'Order ID', 'Tanggal', 'Produk', 'Product Line',
                'Region', 'Gender', 'Channel', 'Units', 'Revenue', 'Profit', 'Discount',
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->order_id,
                    $row->waktu?->order_date,
                    $row->produk?->product_name,
                    $row->produk?->product_line,
                    $row->pelanggan?->region,
                    $row->pelanggan?->gender_category,
                    $row->sales_channel,
                    $row->units_sold,
                    $row->revenue,
                    $row->profit,
                    $row->discount,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}