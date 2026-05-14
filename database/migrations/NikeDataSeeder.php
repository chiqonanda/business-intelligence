<?php

namespace Database\Seeders;

use App\Models\NikeProduct;
use App\Models\NikeReview;
use App\Models\NikeSale;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NikeDataSeeder extends Seeder
{
    private array $regionMap = [
        'bengaluru' => 'Bangalore',
        'bangalore' => 'Bangalore',
        'hyd'       => 'Hyderabad',
        'hyderabad' => 'Hyderabad',
        'hyderbad'  => 'Hyderabad',
        'mumbai'    => 'Mumbai',
        'pune'      => 'Pune',
        'delhi'     => 'Delhi',
        'kolkata'   => 'Kolkata',
    ];

    public function run(): void
    {
        $this->importSales();
        $this->importProducts();
        $this->importReviews();
    }

    // ── SALES ─────────────────────────────────────────────────────────────────
    private function importSales(): void
    {
        $path = base_path('database/seeders/data/clean_sales_data.csv');
        if (!file_exists($path)) {
            $this->command->warn("Sales CSV not found at $path — skipping.");
            return;
        }

        $handle = fopen($path, 'r');
        $header = array_map('trim', fgetcsv($handle));
        $inserted = 0; $skipped = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) { $skipped++; continue; }
                $d = array_combine($header, array_map('trim', $row));

                $orderId = 'NK-' . ($d['Order_ID'] ?? '');
                if (NikeSale::where('order_id', $orderId)->exists()) { $skipped++; continue; }

                $rawRegion = strtolower($d['Region'] ?? '');
                $region = $this->regionMap[$rawRegion] ?? ucfirst($rawRegion);

                $orderDate = null;
                if (!empty($d['Order_Date'])) {
                    try { $orderDate = Carbon::parse($d['Order_Date'])->toDateString(); } catch (\Exception) {}
                }

                NikeSale::create([
                    'order_id'        => $orderId,
                    'gender_category' => $d['Gender_Category'] ?? null,
                    'product_line'    => $d['Product_Line'] ?? null,
                    'product_name'    => $d['Product_Name'] ?? null,
                    'size'            => $d['Size'] ?? null,
                    'units_sold'      => $this->toFloat($d['Units_Sold'] ?? null),
                    'mrp'             => $this->toFloat($d['MRP'] ?? null),
                    'discount_applied'=> $this->toFloat($d['Discount_Applied'] ?? null),
                    'revenue'         => $this->toFloat($d['Revenue'] ?? null),
                    'order_date'      => $orderDate,
                    'sales_channel'   => $d['Sales_Channel'] ?? null,
                    'region'          => $region,
                    'profit'          => $this->toFloat($d['Profit'] ?? null),
                ]);
                $inserted++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack(); throw $e;
        } finally {
            fclose($handle);
        }
        $this->command->info("✅ Sales: $inserted inserted, $skipped skipped");
    }

    // ── PRODUCTS ──────────────────────────────────────────────────────────────
    private function importProducts(): void
    {
        $path = base_path('database/seeders/data/clean_product_data.csv');
        if (!file_exists($path)) {
            $this->command->warn("Products CSV not found at $path — skipping.");
            return;
        }

        $handle = fopen($path, 'r');
        $header = array_map('trim', fgetcsv($handle));
        $inserted = 0; $skipped = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) { $skipped++; continue; }
                $d = array_combine($header, array_map('trim', $row));

                $uniqId = $d['uniq_id'] ?? null;
                if ($uniqId && NikeProduct::where('uniq_id', $uniqId)->exists()) { $skipped++; continue; }

                NikeProduct::create([
                    'uniq_id'         => $uniqId,
                    'name'            => $d['name'] ?? 'Unknown',
                    'sub_title'       => $d['sub_title'] ?? null,
                    'brand'           => $d['brand'] ?? 'Nike',
                    'model'           => $d['model'] ?? null,
                    'color'           => $d['color'] ?? null,
                    'price'           => $this->toFloat($d['price'] ?? null),
                    'currency'        => $d['currency'] ?? 'USD',
                    'availability'    => $d['availability'] ?? null,
                    'description'     => substr($d['description'] ?? '', 0, 2000),
                    'avg_rating'      => $this->toFloat($d['avg_rating'] ?? null),
                    'review_count'    => (int) $this->toFloat($d['review_count'] ?? null),
                    'images'          => $d['images'] ?? null,
                    'available_sizes' => $d['available_sizes'] ?? null,
                    'url'             => $d['url'] ?? null,
                ]);
                $inserted++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack(); throw $e;
        } finally {
            fclose($handle);
        }
        $this->command->info("✅ Products: $inserted inserted, $skipped skipped");
    }

    // ── REVIEWS ───────────────────────────────────────────────────────────────
    private function importReviews(): void
    {
        $path = base_path('database/seeders/data/clean_review_data.csv');
        if (!file_exists($path)) {
            $this->command->warn("Reviews CSV not found at $path — skipping.");
            return;
        }

        $handle = fopen($path, 'r');
        $header = array_map('trim', fgetcsv($handle));
        $inserted = 0; $skipped = 0;
        $batchSize = 200;
        $batch = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) { $skipped++; continue; }
            $d = array_combine($header, array_map('trim', $row));

            $reviewDate = null;
            if (!empty($d['Review Date'])) {
                try { $reviewDate = Carbon::parse($d['Review Date'])->toDateString(); } catch (\Exception) {}
            }

            $batch[] = [
                'rating'             => $this->toFloat($d['Rating'] ?? null, 0),
                'review_date'        => $reviewDate,
                'location'           => $d['Location'] ?? null,
                'username'           => $d['Username'] ?? null,
                'review'             => substr($d['Review'] ?? '', 0, 1000),
                'fit_feedback'       => $d['Fit Feedback'] ?? null,
                'comfort_feedback'   => $d['Comfort Feedback'] ?? null,
                'recommend_feedback' => $d['Recommend Feedback'] ?? null,
                'product_title'      => $d['title'] ?? 'Unknown',
                'subtitle'           => $d['subtitle'] ?? null,
                'color_description'  => $d['colorDescription'] ?? null,
                'full_price'         => $this->toFloat($d['fullPrice'] ?? null),
                'discounted'         => ($d['discounted'] ?? 'False') === 'True' ? 1 : 0,
                'current_price'      => $this->toFloat($d['currentPrice'] ?? null),
                'is_promo_review'    => ($d['IsPromoReview'] ?? 'False') === 'True' ? 1 : 0,
                'is_launch'          => ($d['isLaunch'] ?? 'False') === 'True' ? 1 : 0,
                'pid'                => $d['pid'] ?? null,
                'label'              => $d['label'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
            $inserted++;

            if (count($batch) >= $batchSize) {
                DB::table('nike_reviews')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('nike_reviews')->insert($batch);
        }
        fclose($handle);

        $this->command->info("✅ Reviews: $inserted inserted, $skipped skipped");
    }

    private function toFloat($value, float $default = 0.0): float
    {
        if ($value === null || $value === '' || strtolower((string)$value) === 'nan') {
            return $default;
        }
        $clean = str_replace([',', ' '], '', (string)$value);
        return is_numeric($clean) ? (float)$clean : $default;
    }
}
