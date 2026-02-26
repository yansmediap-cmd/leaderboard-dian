<?php

namespace App\Console\Commands;

use App\Imports\LeaderboardExcelImport;
use App\Models\LeaderboardItem;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportLeaderboardExcelCommand extends Command
{
    protected $signature = 'leaderboard:import
                            {file : Path file Excel (.xlsx/.xls/.csv)}
                            {--append : Tambah data tanpa menghapus data lama}';

    protected $description = 'Import manual Excel untuk data leaderboard display';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (! is_file($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        if (! $this->option('append')) {
            LeaderboardItem::query()->truncate();
        }

        $import = new LeaderboardExcelImport(basename($file));
        Excel::import($import, $file);

        $this->info("Import selesai. {$import->importedRows} baris diproses.");

        return self::SUCCESS;
    }
}
