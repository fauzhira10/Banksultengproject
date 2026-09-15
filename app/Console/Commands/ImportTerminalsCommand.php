<?php

namespace App\Console\Commands;

use App\Services\TerminalImportService;
use Illuminate\Console\Command;

class ImportTerminalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminal:import 
                            {file : Path ke file Excel (.xlsx / .csv)}
                            {--no-update : Jangan perbarui data yang sudah ada (hanya buat baru)}
                            {--no-create-relations : Jangan buat otomatis Cabang atau Vendor baru jika tidak ditemukan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import master data Terminal ATM dari file Excel (.xlsx / .csv)';

    /**
     * Execute the console command.
     */
    public function handle(TerminalImportService $importService): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        $this->info("Memulai import data ATM dari file: {$file} ...");

        $updateExisting = ! $this->option('no-update');
        $autoCreateRelations = ! $this->option('no-create-relations');

        $stats = $importService->import($file, $updateExisting, $autoCreateRelations);

        $this->newLine();
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Baris Diproses', $stats['total']],
                ['Data Baru Dibuat', $stats['created']],
                ['Data Diperbarui (Update)', $stats['updated']],
                ['Data Dilewati (Skip)', $stats['skipped']],
                ['Baris Gagal / Error', count($stats['errors'])],
            ]
        );

        if (! empty($stats['errors'])) {
            $this->newLine();
            $this->warn('Daftar Baris Error:');
            foreach ($stats['errors'] as $row => $message) {
                $this->line(" - [Baris {$row}] {$message}");
            }
        }

        $this->info('Import data terminal selesai!');

        return self::SUCCESS;
    }
}
