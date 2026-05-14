<?php

namespace App\Http\Controllers;

use App\Models\NikeSale;
use App\Models\NikeReview;
use App\Models\NikeProduct;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        // Summary KPIs
        $summary = NikeSale::selectRaw('
            IFNULL(SUM(revenue),0)    as total_revenue,
            IFNULL(SUM(profit),0)     as total_profit,
            IFNULL(SUM(units_sold),0) as total_units,
            IFNULL(AVG(revenue),0)    as avg_revenue,
            COUNT(*)                  as total_orders
        ')->first();

        // Revenue by product line
        $byLine = NikeSale::selectRaw('product_line, SUM(revenue) as revenue, SUM(profit) as profit, COUNT(*) as orders')
            ->whereNotNull('product_line')
            ->groupBy('product_line')
            ->orderByDesc('revenue')
            ->get();

        // Revenue by region
        $byRegion = NikeSale::selectRaw('region, SUM(revenue) as revenue, COUNT(*) as orders')
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('revenue')
            ->get();

        // Revenue by gender
        $byGender = NikeSale::selectRaw('gender_category, SUM(revenue) as revenue, SUM(units_sold) as units')
            ->whereNotNull('gender_category')
            ->groupBy('gender_category')
            ->get();

        // Year-over-year comparison
        $yearlyComparison = NikeSale::selectRaw('
            YEAR(order_date) as year,
            SUM(revenue)     as revenue,
            SUM(profit)      as profit,
            COUNT(*)         as orders,
            SUM(units_sold)  as units
        ')
            ->whereNotNull('order_date')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Top 10 products by revenue
        $topProducts = NikeSale::selectRaw('product_name, product_line, SUM(revenue) as revenue, SUM(units_sold) as units, COUNT(*) as orders')
            ->whereNotNull('product_name')
            ->groupBy('product_name', 'product_line')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Channel performance
        $channelPerf = NikeSale::selectRaw('sales_channel, SUM(revenue) as revenue, SUM(profit) as profit, COUNT(*) as orders')
            ->whereNotNull('sales_channel')
            ->groupBy('sales_channel')
            ->get();

        // Review sentiment summary
        $reviewSummary = NikeReview::selectRaw('
            AVG(rating)                                                as avg_rating,
            COUNT(*)                                                   as total_reviews,
            SUM(CASE WHEN label="BEST_SELLER" THEN 1 ELSE 0 END)      as best_sellers,
            SUM(CASE WHEN recommend_feedback="Yes" THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN fit_feedback="True to Size" THEN 1 ELSE 0 END) as true_to_size
        ')->first();

        // Monthly trend (all time)
        $monthlyTrend = NikeSale::selectRaw("DATE_FORMAT(order_date,'%Y-%m') as month, SUM(revenue) as revenue, SUM(profit) as profit")
            ->whereNotNull('order_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Dashboard/Manager', [
            'summary'          => $summary,
            'byLine'           => $byLine,
            'byRegion'         => $byRegion,
            'byGender'         => $byGender,
            'yearlyComparison' => $yearlyComparison,
            'topProducts'      => $topProducts,
            'channelPerf'      => $channelPerf,
            'reviewSummary'    => $reviewSummary,
            'monthlyTrend'     => [
                'labels'  => $monthlyTrend->pluck('month'),
                'revenue' => $monthlyTrend->pluck('revenue')->map(fn($v) => (float)$v),
                'profit'  => $monthlyTrend->pluck('profit')->map(fn($v) => (float)$v),
            ],
        ]);
    }
}
