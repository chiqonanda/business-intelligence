<?php

namespace App\Http\Controllers;

use App\Models\FactPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Overview', [
            'stats' => $this->getStats(),
        ]);
    }

    // Dipanggil juga oleh API /api/dashboard/stats
    public function stats()
    {
        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        return [
            'total_revenue'   => FactPenjualan::sum('revenue'),
            'total_profit'    => FactPenjualan::sum('profit'),
            'total_orders'    => FactPenjualan::count(),
            'total_units'     => FactPenjualan::sum('units_sold'),
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