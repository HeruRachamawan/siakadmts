<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Mendapatkan path executable mysqldump
     */
    public function getMysqldumpPath(): ?string
    {
        // 1. Cek dari env bila dikonfigurasi
        $envPath = env('MYSQLDUMP_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        // 2. Cek direktori Laragon di Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $laragonMysql = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe');
            if (!empty($laragonMysql) && file_exists($laragonMysql[0])) {
                return $laragonMysql[0];
            }
            $xampp = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            if (file_exists($xampp)) {
                return $xampp;
            }
        }

        // 3. Cek standard command where/which
        $out = [];
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump 2>NUL' : 'which mysqldump 2>/dev/null';
        @exec($cmd, $out);
        if (!empty($out) && file_exists(trim($out[0]))) {
            return trim($out[0]);
        }

        return null;
    }

    /**
     * Mendapatkan path executable mysql (untuk restore)
     */
    public function getMysqlPath(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $laragonMysql = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysql.exe');
            if (!empty($laragonMysql) && file_exists($laragonMysql[0])) {
                return $laragonMysql[0];
            }
            $xampp = 'C:\\xampp\\mysql\\bin\\mysql.exe';
            if (file_exists($xampp)) {
                return $xampp;
            }
        }

        $out = [];
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where mysql 2>NUL' : 'which mysql 2>/dev/null';
        @exec($cmd, $out);
        if (!empty($out) && file_exists(trim($out[0]))) {
            return trim($out[0]);
        }

        return null;
    }

    /**
     * Mendapatkan informasi status database dan server
     */
    public function getDatabaseInfo(): array
    {
        $dbName = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        // Hitung total tabel dan ukuran database dalam MB
        $tables = DB::select('SHOW TABLES');
        $totalTables = count($tables);

        $sizeResult = DB::select(
            "SELECT table_schema AS `database`, 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS `size_mb` 
             FROM information_schema.TABLES 
             WHERE table_schema = ? 
             GROUP BY table_schema",
            [$dbName]
        );
        $sizeMb = !empty($sizeResult) ? (float)$sizeResult[0]->size_mb : 0.0;

        $backups = $this->getBackupsList();
        $lastBackup = !empty($backups) ? $backups[0]['created_at'] : null;

        $settings = Setting::getSettings();
        $autoBackupSchedule = $settings['auto_backup_schedule'] ?? 'daily';
        $retentionDays = (int)($settings['backup_retention_days'] ?? 30);

        return [
            'database_name' => $dbName,
            'host' => $host,
            'port' => $port,
            'total_tables' => $totalTables,
            'size_mb' => $sizeMb,
            'total_backups' => count($backups),
            'last_backup_at' => $lastBackup,
            'auto_backup_schedule' => $autoBackupSchedule,
            'retention_days' => $retentionDays,
            'has_mysqldump' => !is_null($this->getMysqldumpPath()),
        ];
    }

    /**
     * Mendapatkan daftar riwayat file backup yang tersimpan
     */
    public function getBackupsList(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $result = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['sql', 'gz'])) {
                continue;
            }

            $sizeBytes = $file->getSize();
            $sizeFormatted = $this->formatBytes($sizeBytes);
            $filename = $file->getFilename();

            $result[] = [
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'size_formatted' => $sizeFormatted,
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'type' => str_contains($filename, 'auto_') ? 'Otomatis' : 'Manual',
            ];
        }

        // Urutkan dari yang terbaru
        usort($result, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $result;
    }

    /**
     * Mengeksekusi backup database dan menyimpannya ke storage
     */
    public function createBackup(string $type = 'manual'): array
    {
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $prefix = $type === 'auto' ? 'auto_siakad_backup_' : 'siakad_backup_';
        $timestamp = date('Y-m-d_H-i-s');
        $sqlFilename = "{$prefix}{$timestamp}.sql";
        $sqlPath = $this->backupDir . DIRECTORY_SEPARATOR . $sqlFilename;

        $mysqldump = $this->getMysqldumpPath();

        if ($mysqldump) {
            // Gunakan mysqldump resmi untuk performa & integritas maksimal
            $passParam = !empty($pass) ? "-p" . escapeshellarg($pass) : "";
            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s %s > "%s"',
                $mysqldump,
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($user),
                $passParam,
                escapeshellarg($dbName),
                $sqlPath
            );

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($sqlPath) || filesize($sqlPath) === 0) {
                Log::warning("mysqldump gagal (code {$returnVar}), beralih ke generator PHP native.");
                $this->fallbackPhpDump($sqlPath, $dbName);
            }
        } else {
            // Fallback PHP native dump jika mysqldump tidak tersedia di sistem
            $this->fallbackPhpDump($sqlPath, $dbName);
        }

        // Kompresi ke .gz bila modul zlib aktif untuk menghemat ruang disk hingga 85%
        $finalPath = $sqlPath;
        $finalFilename = $sqlFilename;

        if (function_exists('gzencode') && file_exists($sqlPath)) {
            $gzPath = $sqlPath . '.gz';
            $sqlContent = file_get_contents($sqlPath);
            $compressed = gzencode($sqlContent, 9);
            if ($compressed !== false && file_put_contents($gzPath, $compressed)) {
                @unlink($sqlPath); // Hapus sql uncompressed
                $finalPath = $gzPath;
                $finalFilename = $sqlFilename . '.gz';
            }
        }

        $sizeBytes = file_exists($finalPath) ? filesize($finalPath) : 0;

        return [
            'filename' => $finalFilename,
            'size_bytes' => $sizeBytes,
            'size_formatted' => $this->formatBytes($sizeBytes),
            'created_at' => date('Y-m-d H:i:s'),
            'type' => $type === 'auto' ? 'Otomatis' : 'Manual',
        ];
    }

    /**
     * Fallback dump menggunakan PHP native PDO
     */
    protected function fallbackPhpDump(string $outputPath, string $dbName): void
    {
        $handle = fopen($outputPath, 'w');
        if (!$handle) {
            throw new \RuntimeException("Tidak dapat membuat file backup di {$outputPath}");
        }

        fwrite($handle, "-- SIAKAD MTS Database Backup (PHP Native Fallback)\n");
        fwrite($handle, "-- Database: `{$dbName}`\n");
        fwrite($handle, "-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = DB::select('SHOW TABLES');
        $prop = "Tables_in_" . $dbName;

        foreach ($tables as $tObj) {
            $table = $tObj->$prop ?? array_values((array)$tObj)[0];

            // Dapatkan skema CREATE TABLE
            $createTableRes = DB::select("SHOW CREATE TABLE `{$table}`");
            $createTableSql = $createTableRes[0]->{'Create Table'} ?? '';

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createTableSql . ";\n\n");

            // Dump data baris per baris secara chunk
            DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $table) {
                if ($rows->isEmpty()) {
                    return;
                }
                $first = true;
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    if ($first) {
                        $columns = array_keys($rowArray);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                        fwrite($handle, "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES \n");
                        $first = false;
                    } else {
                        fwrite($handle, ",\n");
                    }

                    $values = array_map(function ($val) {
                        if (is_null($val)) return 'NULL';
                        return DB::getPdo()->quote($val);
                    }, array_values($rowArray));

                    fwrite($handle, "(" . implode(', ', $values) . ")");
                }
                fwrite($handle, ";\n\n");
            });
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * Memvalidasi nama file dan mengembalikan path absolut
     */
    public function getSafeBackupPath(string $filename): string
    {
        $cleanName = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $cleanName)) {
            throw new \InvalidArgumentException("Format nama file tidak valid.");
        }

        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanName;
        if (!file_exists($fullPath)) {
            throw new \InvalidArgumentException("File backup tidak ditemukan.");
        }

        return $fullPath;
    }

    /**
     * Menghapus file backup
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getSafeBackupPath($filename);
        return @unlink($path);
    }

    /**
     * Merestore database dari file backup yang ada di server
     */
    public function restoreBackup(string $filename): bool
    {
        $path = $this->getSafeBackupPath($filename);
        return $this->executeSqlFile($path);
    }

    /**
     * Merestore database dari file yang diunggah
     */
    public function restoreFromUpload($uploadedFile): bool
    {
        $ext = strtolower($uploadedFile->getClientOriginalExtension());
        if (!in_array($ext, ['sql', 'gz'])) {
            throw new \InvalidArgumentException("Hanya file berformat .sql atau .gz yang diperbolehkan.");
        }

        $tempPath = $uploadedFile->getRealPath();
        return $this->executeSqlFile($tempPath, $ext === 'gz');
    }

    /**
     * Mengeksekusi file SQL ke database
     */
    protected function executeSqlFile(string $filePath, bool $isGz = false): bool
    {
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        if (str_ends_with($filePath, '.gz') || $isGz) {
            $uncompressed = gzdecode(file_get_contents($filePath));
            if ($uncompressed === false) {
                throw new \RuntimeException("Gagal mendekompresi file backup .gz.");
            }
            $tempSql = tempnam(sys_get_temp_dir(), 'restore_') . '.sql';
            file_put_contents($tempSql, $uncompressed);
            $targetSqlPath = $tempSql;
        } else {
            $targetSqlPath = $filePath;
        }

        $mysql = $this->getMysqlPath();

        if ($mysql) {
            $passParam = !empty($pass) ? "-p" . escapeshellarg($pass) : "";
            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s %s < "%s"',
                $mysql,
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($user),
                $passParam,
                escapeshellarg($dbName),
                $targetSqlPath
            );

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if (isset($tempSql) && file_exists($tempSql)) {
                @unlink($tempSql);
            }

            if ($returnVar === 0) {
                return true;
            }
        }

        // Fallback: Eksekusi SQL via PDO statement
        $sql = file_get_contents($targetSqlPath);
        if (isset($tempSql) && file_exists($tempSql)) {
            @unlink($tempSql);
        }

        DB::unprepared("SET FOREIGN_KEY_CHECKS=0;");
        DB::unprepared($sql);
        DB::unprepared("SET FOREIGN_KEY_CHECKS=1;");

        return true;
    }

    /**
     * Membersihkan file backup yang melebihi batas retensi
     */
    public function cleanOldBackups(int $days = 30): int
    {
        $files = File::files($this->backupDir);
        $threshold = strtotime("-{$days} days");
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                @unlink($file->getRealPath());
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
