<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Create database backup file (daily scheduler ready)';

    public function handle()
    {
        $connection = config('database.default');
        $timestamp = now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        if ($connection === 'sqlite') {
            $source = config('database.connections.sqlite.database');
            $target = $backupDir.DIRECTORY_SEPARATOR."sqlite_backup_{$timestamp}.sqlite";
            File::copy($source, $target);
            $this->info("SQLite backup created: {$target}");

            return self::SUCCESS;
        }

        if ($connection !== 'mysql') {
            $this->error("DB connection [{$connection}] is not supported by this command.");

            return self::FAILURE;
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $file = $backupDir.DIRECTORY_SEPARATOR."mysql_backup_{$db}_{$timestamp}.sql";

        $command = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            "--password={$pass}",
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            $db,
        ];

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('mysqldump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        File::put($file, $process->getOutput());
        $this->info("MySQL backup created: {$file}");

        return self::SUCCESS;
    }
}
