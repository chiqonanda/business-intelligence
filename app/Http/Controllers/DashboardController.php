<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): mixed
    {
        return Inertia::render('Dashboard/Overview', [
            'stats' => $this->getStats(),
            'latestTransactions' => $this->getLatestTransactions(),
            'topProducts' => $this->getTopProducts(),
        ]);
    }

    // Dipanggil juga oleh API /api/dashboard/stats
    public function stats()
    {
        return response()->json($this->getStats());
    }

    private function getLatestTransactions(): array
    {
        $isGuest = !auth()->check();
        $limit = $isGuest ? 5 : 10;

        return FactPenjualan::with(['produk', 'pelanggan', 'waktu'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn(FactPenjualan $row) => [
                'order_id' => $isGuest ? 'TRX-****' : $row->order_id,
                'order_date' => $row->waktu?->order_date,
                'product_name' => $row->produk?->product_name,
                'product_line' => $row->produk?->product_line,
                'region' => $row->pelanggan?->region,
                'sales_channel' => $row->sales_channel,
                'units_sold' => $row->units_sold,
                'revenue' => $row->revenue,
                'profit' => $row->profit,
            ])
            ->toArray();
    }

    private function getTopProducts(): array
    {
        return DB::table('fact_penjualan')
            ->join('dim_produk', 'fact_penjualan.dim_produk_id', '=', 'dim_produk.id')
            ->select(
                'dim_produk.product_name',
                'dim_produk.product_line',
                DB::raw('SUM(fact_penjualan.revenue) as total_revenue'),
                DB::raw('SUM(fact_penjualan.units_sold) as total_units'),
            )
            ->groupBy('fact_penjualan.dim_produk_id', 'dim_produk.product_name', 'dim_produk.product_line')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'product_name' => $row->product_name,
                'product_line' => $row->product_line,
                'revenue' => $row->total_revenue,
                'units' => $row->total_units,
            ])
            ->toArray();
    }

    private function getStats(): array
    {
        $totalRevenue = FactPenjualan::sum('revenue');
        $totalOrders = FactPenjualan::count();

        return [
            'total_revenue'   => $totalRevenue,
            'total_profit'    => FactPenjualan::sum('profit'),
            'total_orders'    => $totalOrders,
            'total_units'     => FactPenjualan::sum('units_sold'),
            'avg_order_value' => $totalOrders ? round($totalRevenue / $totalOrders, 2) : 0,
            'profit_margin'   => $this->profitMargin(),
            'top_channel'     => $this->topChannel(),
        ];
    }

    private function profitMargin(): float
    {
        $revenue = FactPenjualan::sum('revenue');
        $profit  = FactPenjualan::sum('profit');
        if ($revenue == 0) return 0;
        return round(($profit / $revenue) * 100, 2);
    }

    private function topChannel(): string
    {
        $result = FactPenjualan::select('sales_channel', DB::raw('SUM(revenue) as total'))
            ->groupBy('sales_channel')
            ->orderByDesc('total')
            ->first();

        return $result?->sales_channel ?? '-';
    }
}
