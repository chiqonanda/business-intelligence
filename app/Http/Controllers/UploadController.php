<?php

namespace App\Http\Controllers;

use App\Models\DimPelanggan;
use App\Models\DimProduk;
use App\Models\DimWaktu;
use App\Models\FactPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UploadController extends Controller
{
    public function index()
    {
        return Inertia::render('Upload/CsvUpload', [
            'upload_history' => $this->getUploadHistory(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // maks 10MB
        ]);

        $file = $request->file('csv_file');
        $path = $file->store('csv', 'local'); // storage/app/csv/

        try {
            $result = $this->processEtl(storage_path("app/$path"));

            return back()->with('success', "ETL selesai: {$result['inserted']} baris diproses, {$result['skipped']} dilewati.");
        } catch (\Exception $e) {
            Log::error('ETL Error: ' . $e->getMessage());

            return back()->withErrors(['csv_file' => 'Gagal memproses CSV: ' . $e->getMessage()]);
        }
    }

    // ── ETL Process ───────────────────────────────────────────────────────────
    private function processEtl(string $filePath): array
    {
        $handle   = fopen($filePath, 'r');
        $header   = fgetcsv($handle); // baca baris header
        $header   = array_map('trim', $header);

        $inserted = 0;
        $skipped  = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $data = array_map('trim', $data);

                // Skip baris kosong
                if (empty($data['Order ID'] ?? $data['order_id'] ?? null)) {
                    $skipped++;
                    continue;
                }

                // Normalize field names (CSV bisa pakai spasi atau underscore)
                $orderId    = $data['Order ID']         ?? $data['order_id']         ?? null;
                $orderDate  = $data['Order Date']       ?? $data['order_date']       ?? null;
                $productName= $data['Product Name']     ?? $data['product_name']     ?? 'Unknown';
                $productLine= $data['Product Line']     ?? $data['product_line']     ?? null;
                $mrp        = $data['MRP']              ?? $data['mrp']              ?? 0;
                $gender     = $data['Gender']           ?? $data['gender_category']  ?? null;
                $region     = $data['Region']           ?? $data['region']           ?? null;
                $revenue    = $data['Revenue']          ?? $data['revenue']          ?? 0;
                $profit     = $data['Profit']           ?? $data['profit']           ?? 0;
                $units      = $data['Units Sold']       ?? $data['units_sold']       ?? 0;
                $discount   = $data['Discount']         ?? $data['discount']         ?? 0;
                $channel    = $data['Sales Channel']    ?? $data['sales_channel']    ?? null;
                $payment    = $data['Payment Method']   ?? $data['payment_method']   ?? null;

                // Skip jika order_id sudah ada
                if (FactPenjualan::where('order_id', $orderId)->exists()) {
                    $skipped++;
                    continue;
                }

                // ── Dimensi Produk ────────────────────────────────────────────
                $produk = DimProduk::firstOrCreate(
                    ['product_name' => $productName],
                    ['product_line' => $productLine, 'mrp' => (float) $mrp]
                );

                // ── Dimensi Pelanggan ─────────────────────────────────────────
                $pelanggan = DimPelanggan::firstOrCreate([
                    'gender_category' => $gender,
                    'region'          => $region,
                ]);

                // ── Dimensi Waktu ─────────────────────────────────────────────
                $waktu = DimWaktu::fromDate($orderDate);

                // ── Fact Penjualan ────────────────────────────────────────────
                FactPenjualan::create([
                    'order_id'         => $orderId,
                    'dim_produk_id'    => $produk->id,
                    'dim_pelanggan_id' => $pelanggan->id,
                    'dim_waktu_id'     => $waktu->id,
                    'revenue'          => (float) str_replace(',', '', $revenue),
                    'profit'           => (float) str_replace(',', '', $profit),
                    'units_sold'       => (int) $units,
                    'discount'         => (float) $discount,
                    'sales_channel'    => $channel,
                    'payment_method'   => $payment,
                ]);

                $inserted++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    private function getUploadHistory(): array
    {
        // Sederhana: ambil dari storage/app/csv
        $files = glob(storage_path('app/csv/*.csv'));
        $history = [];

        foreach ($files as $f) {
            $history[] = [
                'filename'   => basename($f),
                'size'       => round(filesize($f) / 1024, 1) . ' KB',
                'uploaded_at'=> date('Y-m-d H:i', filemtime($f)),
            ];
        }

        return array_reverse($history); // terbaru dulu
    }
}