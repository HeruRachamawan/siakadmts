<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup {--type=auto : Tipe backup (manual atau auto)}';
    protected $description = 'Cadangkan database SIAKAD MTS secara otomatis';

    public function handle(DatabaseBackupService $backupService): int
    {
        $type = $this->option('type') ?: 'auto';
        $this->info("Memulai proses pencadangan database SIAKAD MTS (tipe: {$type})...");

        try {
            $backup = $backupService->createBackup($type);
            $this->info("Cadangan database berhasil dibuat: {$backup['filename']} ({$backup['size_formatted']})");

            $retentionDays = (int)(Setting::getSettings()['backup_retention_days'] ?? 30);
            $deleted = $backupService->cleanOldBackups($retentionDays);
            if ($deleted > 0) {
                $this->info("Membersihkan {$deleted} file cadangan lama yang melebihi {$retentionDays} hari.");
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal mencadangkan database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
