<?php

namespace App\Http\Controllers;

use App\Models\DimPelanggan;
use App\Models\DimProduk;
use App\Models\DimWaktu;
use App\Models\FactPenjualan;
use App\Models\UploadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
            'csv_file' => ['required', 'file', 'max:20480'], // maks 20MB
        ]);

        $file = $request->file('csv_file');
        $originalName = $file->getClientOriginalName();
        
        // 1. Create Initial Log
        $log = UploadLog::create([
            'filename'      => 'pending',
            'original_name' => $originalName,
            'status'        => 'PENDING',
            'user_id'       => auth()->id(),
        ]);

        try {
            $path = $file->store('csv', 'local');
            $fullPath = Storage::disk('local')->path($path);
            
            $log->update(['filename' => basename($path)]);

            if (!file_exists($fullPath)) {
                throw new \Exception("FILE SYSTEM ERROR: INGESTION POINT NOT REACHABLE");
            }

            // Count total rows (approx)
            $lineCount = count(file($fullPath)) - 1;
            $log->update(['rows_total' => $lineCount]);

            $result = $this->processEtl($fullPath, $log);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => "ETL COMPLETE: {$result['inserted']} NODES INGESTED",
                    'result' => $result
                ]);
            }

            return back()->with('success', "ETL selesai: {$result['inserted']} baris diproses.");
        } catch (\Exception $e) {
            Log::error('ETL Error: ' . $e->getMessage());
            $log->update(['status' => 'FAILED', 'error_message' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'SYSTEM FAILURE: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['csv_file' => 'Gagal memproses CSV: ' . $e->getMessage()]);
        }
    }

    public function truncate()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            FactPenjualan::truncate();
            DimProduk::truncate();
            DimPelanggan::truncate();
            DimWaktu::truncate();
            UploadLog::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json(['message' => 'DATABASE PURGED: ALL NODES WIPED']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'PURGE FAILED: ' . $e->getMessage()], 500);
        }
    }

    public function destroyFile($filename)
    {
        try {
            Storage::disk('local')->delete('csv/' . $filename);
            UploadLog::where('filename', $filename)->delete();
            return response()->json(['message' => 'NODE DELETED']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'DELETE FAILED'], 500);
        }
    }

    private function processEtl($filePath, $log)
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
        
        $header = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $header);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if (count($header) !== count($row)) {
                $skipped++;
                continue;
            }

            $data = array_combine($header, $row);
            
            $cleanNumber = function($val) {
                if (is_null($val) || $val === '') return 0;
                $cleaned = preg_replace('/[^\d.]/', '', $val);
                return (float) ($cleaned ?: 0);
            };

            $getVal = function($keys, $default = null) use ($data) {
                foreach ($keys as $k) {
                    if (isset($data[$k])) return $data[$k];
                    $normalizedK = strtolower(str_replace([' ', '_'], '', $k));
                    foreach ($data as $dk => $dv) {
                        $target = strtolower(str_replace([' ', '_'], '', $dk));
                        if ($target === $normalizedK) return $dv;
                    }
                }
                return $default;
            };

            $orderId    = trim($getVal(['Order ID', 'order_id'], ''));
            $orderDate  = trim($getVal(['Order Date', 'order_date'], ''));
            $productName= trim($getVal(['Product Name', 'product_name'], 'Unknown'));
            $productLine= trim($getVal(['Product Line', 'product_line'], 'General'));
            $mrp        = $cleanNumber($getVal(['MRP', 'mrp'], 0));
            $gender     = trim($getVal(['Gender', 'gender_category'], 'Unisex'));
            $region     = trim($getVal(['Region', 'region'], 'Global'));
            $revenue    = $cleanNumber($getVal(['Revenue', 'revenue'], 0));
            $profit     = $cleanNumber($getVal(['Profit', 'profit'], 0));
            $units      = (int) $cleanNumber($getVal(['Units Sold', 'units_sold'], 0));
            $discount   = $cleanNumber($getVal(['Discount', 'discount'], 0));
            $channel    = trim($getVal(['Sales Channel', 'sales_channel'], 'Direct'));
            $payment    = trim($getVal(['Payment Method', 'payment_method'], 'Standard'));

            if (empty($orderId) || FactPenjualan::where('order_id', $orderId)->exists()) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function() use ($productName, $productLine, $mrp, $gender, $region, $orderDate, $orderId, $revenue, $profit, $units, $discount, $channel, $payment, &$inserted) {
                    $produk = DimProduk::firstOrCreate(
                        ['product_name' => $productName],
                        ['product_line' => $productLine, 'mrp' => $mrp]
                    );

                    $pelanggan = DimPelanggan::firstOrCreate([
                        'gender_category' => $gender,
                        'region'          => $region,
                    ]);

                    $waktu = DimWaktu::fromDate($orderDate);

                    FactPenjualan::create([
                        'order_id'         => $orderId,
                        'dim_produk_id'    => $produk->id,
                        'dim_pelanggan_id' => $pelanggan->id,
                        'dim_waktu_id'     => $waktu->id,
                        'revenue'          => $revenue,
                        'profit'           => $profit,
                        'units_sold'       => $units,
                        'discount'         => $discount,
                        'sales_channel'    => $channel,
                        'payment_method'   => $payment,
                    ]);

                    $inserted++;
                });
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        fclose($handle);

        // Update Log status
        $finalStatus = 'SUCCESS';
        if ($skipped > 0 && $inserted > 0) $finalStatus = 'PARTIAL';
        if ($inserted === 0 && $skipped > 0) $finalStatus = 'FAILED';

        $log->update([
            'status'         => $finalStatus,
            'rows_inserted'  => $inserted,
            'rows_skipped'   => $skipped,
        ]);

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    private function getUploadHistory(): array
    {
        return UploadLog::orderBy('created_at', 'desc')->get()->map(function($log) {
            $fullPath = Storage::disk('local')->path('csv/' . $log->filename);
            $size = file_exists($fullPath) ? round(filesize($fullPath) / 1024, 1) . ' KB' : '0 KB';
            
            return [
                'id'            => $log->id,
                'filename'      => $log->filename,
                'original_name' => $log->original_name,
                'status'        => $log->status,
                'rows_total'    => $log->rows_total,
                'rows_inserted' => $log->rows_inserted,
                'rows_skipped'  => $log->rows_skipped,
                'size'          => $size,
                'uploaded_at'   => $log->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }
}