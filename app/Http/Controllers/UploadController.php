<?php

namespace App\Http\Controllers;

use App\Models\NikeSale;
use App\Models\NikeProduct;
use App\Models\NikeReview;
use App\Models\FactPenjualan;
use App\Models\DimProduk;
use App\Models\DimPelanggan;
use App\Models\DimWaktu;
use App\Models\UploadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UploadController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/CSVUpload', [
            'stats' => $this->getStats(),
            'upload_history' => $this->getUploadHistory(),
        ]);
    }

    private function getStats(): array
    {
        return [
            'total_files' => UploadLog::count(),
            'total_rows' => UploadLog::sum('rows_total'),
            'data_valid' => UploadLog::sum('rows_inserted'),
            'data_error' => UploadLog::sum('rows_skipped'),
            'last_upload' => UploadLog::latest()->first()?->created_at?->diffForHumans() ?? 'Never',
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'max:51200'], // 50MB
            'data_type' => ['required', 'string', 'in:SALES,PRODUCTS,REVIEWS,SALES_STAR,AUTO-DETECT'],
        ]);

        $file = $request->file('csv_file');
        $originalName = $file->getClientOriginalName();
        $dataType = $request->data_type;
        
        $log = UploadLog::create([
            'filename'      => 'pending',
            'original_name' => $originalName,
            'data_type'     => $dataType,
            'status'        => 'PENDING',
            'user_id'       => auth()->id(),
        ]);

        try {
            $path = $file->store('csv', 'local');
            $fullPath = Storage::disk('local')->path($path);
            
            $log->update(['filename' => basename($path)]);

            $lineCount = 0;
            $handle = fopen($fullPath, "r");
            while(!feof($handle)){
                fgets($handle);
                $lineCount++;
            }
            fclose($handle);
            $lineCount = max(0, $lineCount - 1);
            
            $log->update(['rows_total' => $lineCount]);

            $result = $this->processEtl($fullPath, $log, $dataType);

            return back()->with('success', "ETL COMPLETE: " . $result['inserted'] . " NODES INGESTED");
        } catch (\Exception $e) {
            Log::error('ETL Error: ' . $e->getMessage());
            $log->update(['status' => 'FAILED', 'error_message' => $e->getMessage()]);
            return back()->withErrors(['csv_file' => 'SYSTEM FAILURE: ' . $e->getMessage()]);
        }
    }

    public function truncate()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            NikeSale::truncate();
            NikeProduct::truncate();
            NikeReview::truncate();
            FactPenjualan::truncate();
            DimProduk::truncate();
            DimPelanggan::truncate();
            DimWaktu::truncate();
            UploadLog::truncate();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Clear physical files
            $files = Storage::disk('local')->files('csv');
            Storage::disk('local')->delete($files);

            return back()->with('success', 'GLOBAL RESET: ALL DATA NODES AND PHYSICAL FILES PURGED');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'PURGE FAILED: ' . $e->getMessage()]);
        }
    }

    public function clearLogs()
    {
        try {
            UploadLog::truncate();
            $files = Storage::disk('local')->files('csv');
            Storage::disk('local')->delete($files);
            return back()->with('success', 'AUDIT TRAIL WIPED');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'FAILED TO CLEAR LOGS']);
        }
    }

    public function destroyFile($filename)
    {
        try {
            Storage::disk('local')->delete('csv/' . $filename);
            UploadLog::where('filename', $filename)->delete();
            return back()->with('success', 'NODE DELETED');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'DELETE FAILED']);
        }
    }

    private function processEtl($filePath, $log, $type)
    {
        $inserted = 0;
        $skipped = 0;

        $handle = fopen($filePath, "r");
        if ($handle === FALSE) throw new \Exception("COULD NOT OPEN FILE STREAM");

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new \Exception("INVALID CSV: NO HEADER DETECTED");
        }
        
        $header = array_map(fn($h) => trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)), $header);
        $headerLower = array_map('strtolower', $header);
        $detectedType = $type;

        // Auto-detect type if headers match a specific pattern
        if (in_array('uniq_id', $headerLower) || in_array('product_id', $headerLower)) {
            $detectedType = 'PRODUCTS';
        } elseif (in_array('rating', $headerLower) && (in_array('review', $headerLower) || in_array('review date', $headerLower) || in_array('review_date', $headerLower))) {
            $detectedType = 'REVIEWS';
        } elseif (in_array('order_id', $headerLower) || in_array('order id', $headerLower)) {
            if (strtoupper($type) !== 'SALES_STAR') {
                $detectedType = 'SALES';
            }
        }

        Log::info("ETL Processing", ['original_type' => $type, 'detected_type' => $detectedType]);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if (count($header) !== count($row)) {
                $skipped++;
                continue;
            }

            $data = array_combine($header, $row);
            
            try {
                DB::beginTransaction();
                
                $success = match(strtoupper($detectedType)) {
                    'SALES'      => $this->importSale($data),
                    'PRODUCTS'   => $this->importProduct($data),
                    'REVIEWS'    => $this->importReview($data),
                    'SALES_STAR' => $this->importStarSchema($data),
                    default      => false,
                };

                if ($success) {
                    DB::commit();
                    $inserted++;
                } else {
                    DB::rollBack();
                    $skipped++;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
            }
        }

        fclose($handle);

        $finalStatus = $inserted > 0 ? ($skipped > 0 ? 'PARTIAL' : 'SUCCESS') : 'FAILED';
        $log->update([
            'status'         => $finalStatus,
            'data_type'      => $detectedType,
            'rows_inserted'  => $inserted,
            'rows_skipped'   => $skipped,
        ]);

        return ['inserted' => $inserted, 'skipped' => $skipped, 'detected_type' => $detectedType];
    }

    private function importSale($data)
    {
        $orderId = $this->getVal($data, ['Order_ID', 'order_id']);
        if (!$orderId) return false;

        NikeSale::updateOrCreate(
            ['order_id' => $orderId],
            [
                'gender_category' => $this->getVal($data, ['Gender_Category', 'gender']),
                'product_line'    => $this->getVal($data, ['Product_Line', 'line']),
                'product_name'    => $this->getVal($data, ['Product_Name', 'name', 'product']),
                'size'            => $this->getVal($data, ['Size', 'size']),
                'units_sold'      => $this->valFloat($data, ['Units_Sold', 'units']),
                'mrp'             => $this->valFloat($data, ['MRP', 'mrp']),
                'discount_applied'=> $this->valFloat($data, ['Discount_Applied', 'discount']),
                'revenue'         => $this->valFloat($data, ['Revenue', 'revenue']),
                'order_date'      => $this->valDate($data, ['Order_Date', 'date']),
                'sales_channel'   => $this->getVal($data, ['Sales_Channel', 'channel']),
                'region'          => $this->getVal($data, ['Region', 'region', 'location']),
                'profit'          => $this->valFloat($data, ['Profit', 'profit']),
            ]
        );
        return true;
    }

    private function importProduct($data)
    {
        $uniqId = $this->getVal($data, ['uniq_id', 'id', 'pid']);
        if (!$uniqId) return false;

        NikeProduct::updateOrCreate(
            ['uniq_id' => $uniqId],
            [
                'name'            => $this->getVal($data, ['name', 'product_name', 'title']) ?? 'Unknown',
                'sub_title'       => $this->getVal($data, ['sub_title', 'subtitle']),
                'brand'           => $this->getVal($data, ['brand']) ?? 'Nike',
                'model'           => $this->getVal($data, ['model']),
                'color'           => $this->getVal($data, ['color', 'color_description']),
                'price'           => $this->valFloat($data, ['price', 'current_price']),
                'currency'        => $this->getVal($data, ['currency']) ?? 'USD',
                'availability'    => $this->getVal($data, ['availability']),
                'description'     => $this->getVal($data, ['description']),
                'avg_rating'      => $this->valFloat($data, ['avg_rating', 'rating']),
                'review_count'    => (int) $this->valFloat($data, ['review_count', 'reviews']),
                'images'          => $this->getVal($data, ['images']),
                'available_sizes' => $this->getVal($data, ['available_sizes', 'sizes']),
                'url'             => $this->getVal($data, ['url']),
            ]
        );
        return true;
    }

    private function importReview($data)
    {
        NikeReview::create([
            'rating'             => $this->valFloat($data, ['Rating', 'rating']),
            'review_date'        => $this->valDate($data, ['Review Date', 'review_date', 'date']),
            'location'           => $this->getVal($data, ['Location', 'location', 'region']),
            'username'           => $this->getVal($data, ['Username', 'username', 'user']),
            'review'             => $this->getVal($data, ['Review', 'review', 'comment']),
            'fit_feedback'       => $this->getVal($data, ['Fit Feedback', 'fit_feedback']),
            'comfort_feedback'   => $this->getVal($data, ['Comfort Feedback', 'comfort_feedback']),
            'recommend_feedback' => $this->getVal($data, ['Recommend Feedback', 'recommend_feedback']),
            'product_title'      => $this->getVal($data, ['title', 'product_title', 'name']) ?? 'Unknown',
            'subtitle'           => $this->getVal($data, ['subtitle', 'sub_title']),
            'color_description'  => $this->getVal($data, ['colorDescription', 'color_description']),
            'full_price'         => $this->valFloat($data, ['fullPrice', 'full_price']),
            'discounted'         => filter_var($this->getVal($data, ['discounted']) ?? false, FILTER_VALIDATE_BOOLEAN),
            'current_price'      => $this->valFloat($data, ['currentPrice', 'current_price']),
            'is_promo_review'    => filter_var($this->getVal($data, ['IsPromoReview', 'is_promo_review']) ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_launch'          => filter_var($this->getVal($data, ['isLaunch', 'is_launch']) ?? false, FILTER_VALIDATE_BOOLEAN),
            'pid'                => $this->getVal($data, ['pid', 'id']),
            'label'              => $this->getVal($data, ['label']),
        ]);
        return true;
    }

    private function importStarSchema($data)
    {
        $orderId = $this->getVal($data, ['Order_ID', 'order_id']);
        if (!$orderId || FactPenjualan::where('order_id', $orderId)->exists()) return false;

        $produk = DimProduk::firstOrCreate(
            ['product_name' => $this->getVal($data, ['Product_Name', 'name']) ?? 'Unknown'],
            ['product_line' => $this->getVal($data, ['Product_Line', 'line']) ?? 'General', 'mrp' => $this->valFloat($data, ['MRP', 'mrp'])]
        );

        $pelanggan = DimPelanggan::firstOrCreate([
            'gender_category' => $this->getVal($data, ['Gender_Category', 'gender']) ?? 'Unisex',
            'region'          => $this->getVal($data, ['Region', 'region']) ?? 'Global',
        ]);

        $waktu = DimWaktu::fromDate($this->valDate($data, ['Order_Date', 'date']));

        FactPenjualan::create([
            'order_id'         => $orderId,
            'dim_produk_id'    => $produk->id,
            'dim_pelanggan_id' => $pelanggan->id,
            'dim_waktu_id'     => $waktu->id,
            'revenue'          => $this->valFloat($data, ['Revenue', 'revenue']),
            'profit'           => $this->valFloat($data, ['Profit', 'profit']),
            'units_sold'       => (int) $this->valFloat($data, ['Units_Sold', 'units']),
            'discount'         => $this->valFloat($data, ['Discount_Applied', 'discount']),
            'sales_channel'    => $this->getVal($data, ['Sales_Channel', 'channel']) ?? 'Direct',
            'payment_method'   => $this->getVal($data, ['Payment_Method', 'payment']) ?? 'Standard',
        ]);
        return true;
    }

    private function getVal($data, $keys)
    {
        $normalizedData = [];
        foreach ($data as $k => $v) {
            $nk = strtolower(str_replace([' ', '_', '-'], '', $k));
            $normalizedData[$nk] = $v;
        }
        foreach ($keys as $k) {
            if (isset($data[$k]) && $data[$k] !== '') return $data[$k];
            $nk = strtolower(str_replace([' ', '_', '-'], '', $k));
            if (isset($normalizedData[$nk]) && $normalizedData[$nk] !== '') return $normalizedData[$nk];
        }
        return null;
    }

    private function valFloat($data, $keys)
    {
        $val = $this->getVal($data, $keys);
        if ($val === null) return 0;
        return (float) preg_replace('/[^-0-9.]/', '', (string)$val);
    }

    private function valDate($data, $keys)
    {
        $val = $this->getVal($data, $keys);
        if (!$val) return null;
        try {
            return Carbon::parse((string)$val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getUploadHistory(): array
    {
        $logs = UploadLog::orderBy('created_at', 'desc')->get();
        $history = [];

        foreach ($logs as $log) {
            $fullPath = Storage::disk('local')->path('csv/' . $log->filename);
            $size = file_exists($fullPath) ? round(filesize($fullPath) / 1024, 1) . ' KB' : '0 KB';
            
            $history[] = [
                'id'            => $log->id,
                'filename'      => $log->filename,
                'original_name' => $log->original_name,
                'data_type'     => $log->data_type,
                'status'        => $log->status,
                'rows_total'    => $log->rows_total,
                'rows_inserted' => $log->rows_inserted,
                'rows_skipped'  => $log->rows_skipped,
                'size'          => $size,
                'uploaded_at'   => $log->created_at->format('Y-m-d H:i'),
            ];
        }

        return $history;
    }
}