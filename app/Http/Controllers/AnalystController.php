<?php

namespace App\Http\Controllers;

use App\Models\NikeSale;
use App\Models\NikeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalystController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Analyst', [
            'years' => NikeSale::whereNotNull('order_date')
                ->select(DB::raw('YEAR(order_date) as year'))
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year'),
            'regions' => NikeSale::distinct()->whereNotNull('region')->orderBy('region')->pluck('region'),
        ]);
    }

    public function apiData(Request $request)
    {
        $year = $request->get('year');
        $region = $request->get('region');
        $search = $request->get('search');

        // Query Base
        $query = NikeSale::query();

        // Filters
        if ($year) $query->whereYear('order_date', $year);
        if ($region) $query->where('region', $region);
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_id', 'like', "%$search%")
                  ->orWhere('product_name', 'like', "%$search%");
            });
        }

        // Transactions (Paginated)
        $transactions = (clone $query)->orderByDesc('id')->paginate(10);

        // KPIs Calculation
        $stats = (clone $query)->select(
            DB::raw('SUM(revenue) as total_revenue'),
            DB::raw('SUM(profit) as total_profit'),
            DB::raw('COUNT(id) as total_orders'),
            DB::raw('AVG(revenue) as avg_order')
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
        $q = NikeSale::select(
            DB::raw('MONTH(order_date) as bulan'),
            DB::raw('SUM(revenue) as revenue'),
            DB::raw('SUM(profit) as profit')
        )
        ->whereNotNull('order_date')
        ->groupBy('bulan')
        ->orderBy('bulan');

        if ($year) $q->whereYear('order_date', $year);
        if ($region) $q->where('region', $region);

        $res = $q->get();
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        return [
            'labels' => $res->pluck('bulan')->map(fn($m) => $monthNames[$m-1]),
            'revenue' => $res->pluck('revenue'),
            'profit' => $res->pluck('profit'),
        ];
    }

    private function getTopProducts($year, $region)
    {
        $q = NikeSale::select('product_name', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(10);

        if ($year) $q->whereYear('order_date', $year);
        if ($region) $q->where('region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('product_name')->map(fn($n) => strlen($n) > 12 ? substr($n, 0, 12).'...' : $n),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getRegionSplit($year)
    {
        $q = NikeSale::select('region', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('region');

        if ($year) $q->whereYear('order_date', $year);

        $res = $q->get();
        return [
            'labels' => $res->pluck('region'),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getGenderSplit($year, $region)
    {
        $q = NikeSale::select('gender_category', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('gender_category');

        if ($year) $q->whereYear('order_date', $year);
        if ($region) $q->where('region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('gender_category'),
            'data' => $res->pluck('revenue'),
        ];
    }

    private function getChannelSplit($year, $region)
    {
        $q = NikeSale::select('sales_channel', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('sales_channel');

        if ($year) $q->whereYear('order_date', $year);
        if ($region) $q->where('region', $region);

        $res = $q->get();
        return [
            'labels' => $res->pluck('sales_channel'),
            'data' => $res->pluck('revenue'),
        ];
    }

    public function export(Request $request)
    {
        $data = NikeSale::all();
        $filename = 'nike_sales_export_' . now()->timestamp . '.csv';
        $headers = ["Content-Type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename"];

        return response()->stream(function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Date', 'Product', 'Region', 'Revenue', 'Profit']);
            foreach ($data as $row) {
                fputcsv($file, [$row->order_id, $row->order_date?->format('Y-m-d'), $row->product_name, $row->region, $row->revenue, $row->profit]);
            }
            fclose($file);
        }, 200, $headers);
    }
}