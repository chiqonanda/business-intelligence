<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DimPelanggan;
use App\Models\DimProduk;
use App\Models\DimWaktu;
use App\Models\FactPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class CsvImportService
{
    private array $regionMap = [
        'bengaluru'  => 'Bangalore',
        'bangalore'  => 'Bangalore',
        'hyd'        => 'Hyderabad',
        'hyderabad'  => 'Hyderabad',
        'hyderbad'   => 'Hyderabad',
        'mumbai'     => 'Mumbai',
        'pune'       => 'Pune',
        'delhi'      => 'Delhi',
        'kolkata'    => 'Kolkata',
    ];

    private array $stats = [
        'inserted' => 0,
        'skipped' => 0,
        'duplicate' => 0,
        'nullDate' => 0,
        'rowCount' => 0,
    ];

    /**
     * Import Nike sales data from CSV file
     *
     * @param string $filePath Path to CSV file
     * @return array Statistics about the import
     * @throws \Exception
     */
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File tidak ditemukan: $filePath");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Tidak dapat membuka file: $filePath");
        }

        try {
            $rawHeader = fgetcsv($handle);
            $header = array_map('trim', $rawHeader ?: []);

            if (empty($header)) {
                throw new \Exception("CSV header tidak ditemukan atau file kosong.");
            }

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $this->stats['rowCount']++;

                if (count($row) !== count($header)) {
                    $this->stats['skipped']++;
                    continue;
                }

                $this->processRow($row, $header);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return $this->stats;
    }

    /**
     * Process a single CSV row
     */
    private function processRow(array $row, array $header): void
    {
        $data = array_combine($header, $row);
        $data = array_map(fn($value) => trim((string) $value), $data);

        $orderId = $data['Order_ID'] ?? '';
        if ($orderId === '') {
            $this->stats['skipped']++;
            return;
        }

        $orderIdStr = 'NK-' . $orderId;

        if (FactPenjualan::where('order_id', $orderIdStr)->exists()) {
            $this->stats['duplicate']++;
            return;
        }

        $orderDate = $this->parseDate($data['Order_Date'] ?? '');
        if ($orderDate === null) {
            $this->stats['nullDate']++;
            $orderDate = Carbon::now()->startOfYear()->toDateString();
        }

        $rawRegion = strtolower($data['Region'] ?? '');
        $region = $this->regionMap[$rawRegion] ?? ($rawRegion !== '' ? ucfirst($rawRegion) : null);

        $unitsSold = $this->toInt($data['Units_Sold'] ?? null, 1);
        $mrp = $this->toFloat($data['MRP'] ?? null, 0.0);
        $discount = $this->toFloat($data['Discount_Applied'] ?? null, 0.0);
        $revenue = $this->toFloat($data['Revenue'] ?? null, 0.0);
        $profit = $this->toFloat($data['Profit'] ?? null, 0.0);

        $produk = DimProduk::firstOrCreate(
            ['product_name' => $data['Product_Name'] ?? ''],
            [
                'product_line' => $data['Product_Line'] ?? null,
                'mrp' => $mrp > 0 ? $mrp : null,
            ]
        );

        if ($produk->mrp === null && $mrp > 0) {
            $produk->update(['mrp' => $mrp]);
        }

        $pelanggan = DimPelanggan::firstOrCreate([
            'gender_category' => $data['Gender_Category'] ?? null,
            'region' => $region,
        ]);

        $waktu = DimWaktu::fromDate($orderDate);

        FactPenjualan::create([
            'order_id' => $orderIdStr,
            'dim_produk_id' => $produk->id,
            'dim_pelanggan_id' => $pelanggan->id,
            'dim_waktu_id' => $waktu->id,
            'revenue' => $revenue,
            'profit' => $profit,
            'units_sold' => $unitsSold,
            'discount' => $discount,
            'sales_channel' => $data['Sales_Channel'] ?? null,
            'payment_method' => null,
        ]);

        $this->stats['inserted']++;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim($raw);
        if ($raw === '' || strcasecmp($raw, 'nan') === 0) {
            return null;
        }

        if (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $raw, $m)) {
            return Carbon::createFromDate((int) $m[1], (int) $m[2], (int) $m[3])->toDateString();
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $raw, $m)) {
            return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->toDateString();
        }

        return null;
    }

    /**
     * Convert value to integer
     */
    private function toInt($value, int $default = 0): int
    {
        if ($value === null || trim((string) $value) === '' || strcasecmp((string) $value, 'nan') === 0) {
            return $default;
        }

        return (int) round((float) $value);
    }

    /**
     * Convert value to float
     */
    private function toFloat($value, float $default = 0.0): float
    {
        if ($value === null || trim((string) $value) === '' || strcasecmp((string) $value, 'nan') === 0) {
            return $default;
        }

        return (float) $value;
    }
}
