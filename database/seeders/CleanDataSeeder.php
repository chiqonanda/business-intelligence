<?php

namespace Database\Seeders;

use App\Models\NikeProduct;
use App\Models\NikeReview;
use App\Models\NikeSale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProducts();
        $this->seedReviews();
        $this->seedSales();
    }

    private function seedProducts()
    {
        $filePath = database_path('seeders/data/clean_product_data.csv');
        if (!file_exists($filePath)) return;

        $this->command->info('Seeding Nike Products...');
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) continue;
                $data = array_combine($header, $row);
                
                NikeProduct::updateOrCreate(
                    ['uniq_id' => $data['uniq_id'] ?? null],
                    [
                        'name' => $data['name'],
                        'sub_title' => $data['sub_title'] ?? null,
                        'brand' => $data['brand'] ?? 'Nike',
                        'model' => $data['model'] ?? null,
                        'color' => $data['color'] ?? null,
                        'price' => $this->toFloat($data['price']),
                        'currency' => $data['currency'] ?? 'USD',
                        'availability' => $data['availability'] ?? null,
                        'description' => $data['description'] ?? null,
                        'avg_rating' => $this->toFloat($data['avg_rating']),
                        'review_count' => $this->toInt($data['review_count']),
                        'images' => $data['images'] ?? null,
                        'available_sizes' => $data['available_sizes'] ?? null,
                        'url' => $data['url'] ?? null,
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding products: ' . $e->getMessage());
        }
        fclose($handle);
    }

    private function seedReviews()
    {
        $filePath = database_path('seeders/data/clean_review_data.csv');
        if (!file_exists($filePath)) return;

        $this->command->info('Seeding Nike Reviews...');
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) continue;
                $data = array_combine($header, $row);

                NikeReview::create([
                    'rating' => $this->toFloat($data['Rating']),
                    'review_date' => $this->parseDate($data['Review Date']),
                    'location' => $data['Location'] ?? null,
                    'username' => $data['Username'] ?? null,
                    'review' => $data['Review'] ?? null,
                    'fit_feedback' => $data['Fit Feedback'] ?? null,
                    'comfort_feedback' => $data['Comfort Feedback'] ?? null,
                    'recommend_feedback' => $data['Recommend Feedback'] ?? null,
                    'product_title' => $data['title'],
                    'subtitle' => $data['subtitle'] ?? null,
                    'color_description' => $data['colorDescription'] ?? null,
                    'full_price' => $this->toFloat($data['fullPrice']),
                    'discounted' => strtolower($data['discounted']) === 'true',
                    'current_price' => $this->toFloat($data['currentPrice']),
                    'is_promo_review' => strtolower($data['IsPromoReview']) === 'true',
                    'is_launch' => strtolower($data['isLaunch']) === 'true',
                    'pid' => $data['pid'] ?? null,
                    'label' => $data['label'] ?? null,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding reviews: ' . $e->getMessage());
        }
        fclose($handle);
    }

    private function seedSales()
    {
        $filePath = database_path('seeders/data/clean_sales_data.csv');
        if (!file_exists($filePath)) return;

        $this->command->info('Seeding Nike Sales...');
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) continue;
                $data = array_combine($header, $row);

                NikeSale::updateOrCreate(
                    ['order_id' => $data['Order_ID']],
                    [
                        'gender_category' => $data['Gender_Category'] ?? null,
                        'product_line' => $data['Product_Line'] ?? null,
                        'product_name' => $data['Product_Name'] ?? null,
                        'size' => $data['Size'] ?? null,
                        'units_sold' => $this->toFloat($data['Units_Sold']),
                        'mrp' => $this->toFloat($data['MRP']),
                        'discount_applied' => $this->toFloat($data['Discount_Applied']),
                        'revenue' => $this->toFloat($data['Revenue']),
                        'order_date' => $this->parseDate($data['Order_Date']),
                        'sales_channel' => $data['Sales_Channel'] ?? null,
                        'region' => $data['Region'] ?? null,
                        'profit' => $this->toFloat($data['Profit']),
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding sales: ' . $e->getMessage());
        }
        fclose($handle);
    }

    private function toFloat($value) {
        return (float) str_replace(['$', ','], '', $value);
    }

    private function toInt($value) {
        return (int) str_replace(',', '', $value);
    }

    private function parseDate($value) {
        if (empty($value)) return null;
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
