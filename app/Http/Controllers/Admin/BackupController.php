<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Setting;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends BaseController
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Mendapatkan status database dan daftar riwayat file cadangan
     */
    public function index()
    {
        $info = $this->backupService->getDatabaseInfo();
        $backups = $this->backupService->getBackupsList();

        return $this->success([
            'info' => $info,
            'backups' => $backups,
        ]);
    }

    /**
     * Membuat file backup baru secara manual
     */
    public function store(Request $request)
    {
        try {
            $newBackup = $this->backupService->createBackup('manual');
            return $this->success($newBackup, 'Cadangan database berhasil dibuat!');
        } catch (\Throwable $e) {
            return $this->error('Gagal membuat cadangan database: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mengunduh file backup
     */
    public function download(string $filename)
    {
        try {
            $filePath = $this->backupService->getSafeBackupPath($filename);
            return response()->download($filePath, basename($filePath));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Menghapus file backup dari server
     */
    public function destroy(string $filename)
    {
        try {
            $this->backupService->deleteBackup($filename);
            return $this->success(null, 'File cadangan berhasil dihapus.');
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus file cadangan: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Memulihkan (restore) database dari file tersimpan atau upload
     */
    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // max 50MB
        ]);

        try {
            if ($request->hasFile('file')) {
                $this->backupService->restoreFromUpload($request->file('file'));
            } elseif ($request->filled('filename')) {
                $this->backupService->restoreBackup($request->input('filename'));
            } else {
                return $this->error('Pilih file dari server atau unggah file .sql terlebih dahulu.', 422);
            }

            return $this->success(null, 'Database berhasil dipulihkan!');
        } catch (\Throwable $e) {
            return $this->error('Gagal memulihkan database: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menyimpan pengaturan jadwal auto-backup
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'schedule' => 'required|in:none,daily,weekly',
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        Setting::updateOrCreate(['key' => 'auto_backup_schedule'], ['value' => $request->input('schedule')]);
        Setting::updateOrCreate(['key' => 'backup_retention_days'], ['value' => (string)$request->input('retention_days')]);

        return $this->success(null, 'Pengaturan cadangan otomatis berhasil disimpan.');
    }
}
