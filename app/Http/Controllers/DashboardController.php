<?php

namespace App\Http\Controllers;

use App\Models\NikeSale;
use App\Models\NikeProduct;
use App\Models\NikeReview;
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
            'latest_transactions' => $this->getLatestTransactions(),
            'top_products' => $this->getTopProducts(),
            'review_stats' => $this->getReviewStats(),
            'charts' => [
                'monthly' => $this->getMonthlyTrends(),
                'region' => $this->getRegionSales(),
                'channel' => $this->getChannelSales(),
                'reviews' => $this->getReviewSentiment(),
            ]
        ]);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    private function getLatestTransactions(): array
    {
        $isGuest = !auth()->check();
        $limit = $isGuest ? 5 : 10;

        return NikeSale::orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn(NikeSale $row) => [
                'order_id' => $isGuest ? 'TRX-****' : $row->order_id,
                'order_date' => $row->order_date?->format('Y-m-d'),
                'product_name' => $row->product_name,
                'product_line' => $row->product_line,
                'region' => $row->region,
                'sales_channel' => $row->sales_channel,
                'units_sold' => (float)$row->units_sold,
                'revenue' => $isGuest ? '***' : (float)$row->revenue,
                'profit' => $isGuest ? '***' : (float)$row->profit,
            ])
            ->toArray();
    }

    private function getTopProducts(): array
    {
        $isGuest = !auth()->check();
        return NikeSale::select(
                'product_name',
                'product_line',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(units_sold) as total_units'),
            )
            ->groupBy('product_name', 'product_line')
            ->orderByDesc('total_units')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'product_name' => $row->product_name,
                'product_line' => $row->product_line,
                'revenue' => $isGuest ? '***' : (float)$row->total_revenue,
                'units' => (float)$row->total_units,
            ])
            ->toArray();
    }

    private function getMonthlyTrends(): array
    {
        $isGuest = !auth()->check();
        $res = NikeSale::select(
            DB::raw('MONTH(order_date) as bulan'),
            DB::raw('SUM(revenue) as revenue')
        )
        ->whereNotNull('order_date')
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return [
            'labels' => $res->pluck('bulan')->map(fn($m) => $monthNames[$m-1]),
            'data' => $isGuest ? $res->pluck('revenue')->map(fn($v) => 0) : $res->pluck('revenue')->map(fn($v) => (float)$v),
        ];
    }

    private function getRegionSales(): array
    {
        $isGuest = !auth()->check();
        $res = NikeSale::select('region', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('region')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return [
            'labels' => $res->pluck('region'),
            'data' => $isGuest ? $res->pluck('revenue')->map(fn($v) => 0) : $res->pluck('revenue')->map(fn($v) => (float)$v),
        ];
    }

    private function getChannelSales(): array
    {
        $isGuest = !auth()->check();
        $res = NikeSale::select('sales_channel', DB::raw('SUM(revenue) as revenue'))
            ->groupBy('sales_channel')
            ->get();

        return [
            'labels' => $res->pluck('sales_channel'),
            'data' => $isGuest ? $res->pluck('revenue')->map(fn($v) => 0) : $res->pluck('revenue')->map(fn($v) => (float)$v),
        ];
    }

    private function getReviewStats(): array
    {
        return [
            'avg_rating' => round(NikeReview::avg('rating') ?? 0, 1),
            'total_reviews' => NikeReview::count(),
            'positive_reviews' => NikeReview::where('rating', '>=', 4)->count(),
        ];
    }

    private function getReviewSentiment(): array
    {
        $res = NikeReview::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        return [
            'labels' => $res->pluck('rating')->map(fn($r) => $r . ' Star'),
            'data' => $res->pluck('count'),
        ];
    }

    private function getStats(): array
    {
        $isGuest = !auth()->check();
        $totalRevenue = NikeSale::sum('revenue');
        $totalOrders = NikeSale::count();

        // Guest only gets count-based stats, not exact currency values
        return [
            'total_revenue'   => $isGuest ? 'HIDDEN' : (float)$totalRevenue,
            'total_profit'    => $isGuest ? 'HIDDEN' : (float)NikeSale::sum('profit'),
            'total_orders'    => $totalOrders,
            'total_units'     => (float)NikeSale::sum('units_sold'),
            'avg_order_value' => $isGuest ? 'HIDDEN' : ($totalOrders ? round($totalRevenue / $totalOrders, 2) : 0),
            'profit_margin'   => $isGuest ? 'HIDDEN' : $this->profitMargin(),
            'top_channel'     => $this->topChannel(),
            'total_products'  => NikeProduct::count(),
            'is_guest'        => $isGuest
        ];
    }

    private function profitMargin(): float
    {
        $revenue = NikeSale::sum('revenue');
        $profit  = NikeSale::sum('profit');
        if ($revenue == 0) return 0;
        return round(($profit / $revenue) * 100, 2);
    }

    private function topChannel(): string
    {
        $result = NikeSale::select('sales_channel', DB::raw('SUM(revenue) as total'))
            ->groupBy('sales_channel')
            ->orderByDesc('total')
            ->first();

        return $result?->sales_channel ?? '-';
    }
}
