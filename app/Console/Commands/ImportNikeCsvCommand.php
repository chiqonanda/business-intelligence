<?php

namespace App\Console\Commands;

use App\Services\CsvImportService;
use Illuminate\Console\Command;

class ImportNikeCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nike-csv {--file= : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Nike sales data from CSV file to database';

    /**
     * Execute the console command.
     */
    public function handle(CsvImportService $csvImportService): int
    {
        $filePath = $this->option('file')
            ?? database_path('seeders/data/Nike_Sales_Uncleaned.csv');

        $this->info('=== ETL NIKE SALES CSV ===');
        $this->line("File: $filePath");

        try {
            $stats = $csvImportService->import($filePath);

            $this->newLine();
            $this->info('=== HASIL IMPORT ===');
            $this->line("Total baris CSV: {$stats['rowCount']}");
            $this->line("Berhasil diinsert : {$stats['inserted']}");
            $this->line("Duplikat dilewati: {$stats['duplicate']}");
            $this->line("Baris rusak      : {$stats['skipped']}");
            $this->line("Tanggal invalid  : {$stats['nullDate']}");

            $this->newLine();
            $totalRevenue = number_format(\App\Models\FactPenjualan::sum('revenue'), 2);
            $totalProfit = number_format(\App\Models\FactPenjualan::sum('profit'), 2);
            $this->line("Total revenue: $totalRevenue");
            $this->line("Total profit : $totalProfit");

            $this->newLine();
            $this->info('Selesai.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ETL gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
