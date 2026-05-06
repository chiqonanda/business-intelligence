<?php

namespace Database\Seeders;

use App\Models\DimPelanggan;
use App\Models\DimProduk;
use App\Models\DimWaktu;
use App\Models\FactPenjualan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NikeDataSeeder extends Seeder
{
    // ── Mapping normalisasi Region ─────────────────────────────────────────────
    private array $regionMap = [
        'bengaluru'  => 'Bangalore',
        'bangalore'  => 'Bangalore',
        'hyd'        => 'Hyderabad',
        'hyderabad'  => 'Hyderabad',
        'hyderbad'   => 'Hyderabad',  // typo di data asli
        'mumbai'     => 'Mumbai',
        'pune'       => 'Pune',
        'delhi'      => 'Delhi',
        'kolkata'    => 'Kolkata',
    ];

    public function run(): void
    {
        $filePath = database_path('seeders/data/Nike_Sales_Uncleaned.csv');

        if (! file_exists($filePath)) {
            $this->command->error("❌ File tidak ditemukan: $filePath");
            $this->command->info("   Letakkan Nike_Sales_Uncleaned.csv di database/seeders/data/");
            return;
        }

        $this->command->info('🔄 Memulai ETL Nike Sales Data...');

        $handle   = fopen($filePath, 'r');
        $rawHeader = fgetcsv($handle);
        $header   = array_map(fn($h) => trim($h), $rawHeader);

        $inserted  = 0;
        $skipped   = 0;
        $nullDate  = 0;
        $duplicate = 0;
        $errors    = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) {
                    $skipped++;
                    continue;
                }

                $data = array_combine($header, $row);
                $data = array_map('trim', $data);

                // ── 1. Order_ID wajib ada ─────────────────────────────────────
                $orderId = $data['Order_ID'] ?? null;
                if (empty($orderId)) {
                    $skipped++;
                    continue;
                }

                // Prefix agar jadi string unik: "NK-2000"
                $orderIdStr = 'NK-' . $orderId;

                // ── 2. Skip duplikat ──────────────────────────────────────────
                if (FactPenjualan::where('order_id', $orderIdStr)->exists()) {
                    $duplicate++;
                    continue;
                }

                // ── 3. Parse & normalisasi Order_Date ─────────────────────────
                $orderDate = $this->parseDate($data['Order_Date'] ?? '');
                if (! $orderDate) {
                    $nullDate++;
                    // Tetap insert dengan tanggal default agar data tidak hilang
$orderDate = Carbon::now()->startOfYear()->toDateString();
                }

                // ── 4. Normalisasi Region ─────────────────────────────────────
                $rawRegion = strtolower(trim($data['Region'] ?? ''));
                $region    = $this->regionMap[$rawRegion] ?? ucfirst($rawRegion);

                // ── 5. Nilai numerik — handle null & string kosong ────────────
                $unitsSold = $this->toInt($data['Units_Sold'] ?? null, default: 1);
                $mrp       = $this->toFloat($data['MRP'] ?? null, default: 0.0);
                $discount  = $this->toFloat($data['Discount_Applied'] ?? null, default: 0.0);
                $revenue   = $this->toFloat($data['Revenue'] ?? null, default: 0.0);
                $profit    = $this->toFloat($data['Profit'] ?? null, default: 0.0);

                // ── 6. Clamp nilai negatif Revenue (bisa jadi retur/error data)
                // Kita simpan apa adanya agar analisis tetap akurat,
                // tapi bisa di-filter di query level.

                // ── 7. Upsert Dimensi ─────────────────────────────────────────

                $produk = DimProduk::firstOrCreate(
                    ['product_name' => $data['Product_Name']],
                    [
                        'product_line' => $data['Product_Line'],
                        'mrp'          => $mrp > 0 ? $mrp : null,
                    ]
                );

                // Update MRP jika sebelumnya null dan sekarang ada datanya
                if ($produk->mrp === null && $mrp > 0) {
                    $produk->update(['mrp' => $mrp]);
                }

                $pelanggan = DimPelanggan::firstOrCreate([
                    'gender_category' => $data['Gender_Category'],
                    'region'          => $region,
                ]);

                $waktu = DimWaktu::fromDate(
    $orderDate instanceof \Carbon\Carbon ? $orderDate->toDateString() : $orderDate
);

                // ── 8. Insert Fact ────────────────────────────────────────────
                FactPenjualan::create([
                    'order_id'         => $orderIdStr,
                    'dim_produk_id'    => $produk->id,
                    'dim_pelanggan_id' => $pelanggan->id,
                    'dim_waktu_id'     => $waktu->id,
                    'revenue'          => $revenue,
                    'profit'           => $profit,
                    'units_sold'       => $unitsSold,
                    'discount'         => $discount,
                    'sales_channel'    => $data['Sales_Channel'] ?? null,
                    'payment_method'   => null, // tidak ada di CSV ini
                ]);

                $inserted++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ ETL gagal: ' . $e->getMessage());
            return;
        } finally {
            fclose($handle);
        }

        // ── Laporan hasil ─────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ ETL selesai!');
        $this->command->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Berhasil diinsert', $inserted],
                ['Duplikat dilewati', $duplicate],
                ['Tanggal null/invalid (pakai default)', $nullDate],
                ['Baris rusak dilewati', $skipped],
            ]
        );

        // ── Summary data yang masuk ───────────────────────────────────────────
        $totalRevenue = FactPenjualan::sum('revenue');
        $totalProfit  = FactPenjualan::sum('profit');
        $this->command->info("💰 Total Revenue: " . number_format($totalRevenue, 2));
        $this->command->info("📈 Total Profit : " . number_format($totalProfit, 2));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Parse tanggal dengan 3 format yang ada di CSV:
     *   2024-03-09  (Y-m-d)
     *   04-10-2024  (d-m-Y)
     *   2024/09/12  (Y/m/d)
     */
    private function parseDate(?string $raw): ?Carbon
    {
        if (empty($raw) || strtolower($raw) === 'nan') {
            return null;
        }

        $raw = trim($raw);

        // Format Y-m-d atau Y/m/d
        if (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $raw, $m)) {
            try {
                return Carbon::createFromDate((int)$m[1], (int)$m[2], (int)$m[3]);
            } catch (\Exception) {
                return null;
            }
        }

        // Format d-m-Y
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $raw, $m)) {
            try {
                return Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1]);
            } catch (\Exception) {
                return null;
            }
        }

        // Fallback: biarkan Carbon coba parse sendiri
        try {
            return Carbon::parse($raw);
        } catch (\Exception) {
            return null;
        }
    }

    private function toFloat(?string $value, float $default = 0.0): float
    {
        if ($value === null || $value === '' || strtolower($value) === 'nan') {
            return $default;
        }
        $clean = str_replace([',', ' '], '', $value);
        return is_numeric($clean) ? (float) $clean : $default;
    }

    private function toInt(?string $value, int $default = 0): int
    {
        if ($value === null || $value === '' || strtolower($value) === 'nan') {
            return $default;
        }
        $clean = str_replace([',', ' '], '', $value);
        return is_numeric($clean) ? (int) round((float) $clean) : $default;
    }
}