<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupFiles();
        return view('backup.index', compact('backups'));
    }

    public function run(Request $request)
    {
        $type = $request->input('type', 'db');

        try {
            $option = match ($type) {
                'db'    => '--only-db',
                'files' => '--only-files',
                default => '',
            };

            Artisan::call(trim("backup:run {$option}"));

            $label = match ($type) {
                'db'    => 'Database',
                'files' => 'File Dokumen',
                default => 'Full (DB + Files)',
            };

            return back()->with('success', "Backup {$label} berhasil dibuat.");

        } catch (\Exception $e) {
            return back()->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $request->validate(['file' => 'required|string']);

        $fileName   = basename($request->file);
        $backupName = config('backup.backup.name');
        $filePath   = "{$backupName}/{$fileName}";

        if (! Storage::disk('local')->exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return Storage::disk('local')->download($filePath, $fileName);
    }

    public function delete(Request $request)
    {
        $request->validate(['file' => 'required|string']);

        $fileName   = basename($request->file);
        $backupName = config('backup.backup.name');
        $filePath   = "{$backupName}/{$fileName}";

        if (! Storage::disk('local')->exists($filePath)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($filePath);

        return back()->with('success', 'File backup berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file'          => 'required|string',
            'mode'          => 'required|in:replace,merge',
            'restore_db'    => 'nullable',
            'restore_files' => 'nullable',
        ]);

        $fileName   = basename($request->file);
        $backupName = config('backup.backup.name');
        $filePath   = "{$backupName}/{$fileName}";

        if (! Storage::disk('local')->exists($filePath)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        try {
            $fullPath  = Storage::disk('local')->path($filePath);
            $extractTo = storage_path('app/backup-temp/restore-' . time());

            $zip = new \ZipArchive();
            if ($zip->open($fullPath) !== true) {
                return back()->with('error', 'Gagal membuka file backup.');
            }
            $zip->extractTo($extractTo);
            $zip->close();

            // Restore DB
            if ($request->has('restore_db')) {
                $sqlFiles = glob($extractTo . '/db-dumps/*.sql');
                if (! empty($sqlFiles)) {
                    foreach ($sqlFiles as $sqlFile) {
                        $this->restoreDatabase($sqlFile, $request->mode);
                    }
                }
            }

            // Restore Files
            if ($request->has('restore_files')) {
                $documentsBackup = $extractTo . '/storage/app/public/documents';
                if (is_dir($documentsBackup)) {
                    $this->restoreFiles($documentsBackup, $request->mode);
                }
            }

            $this->deleteDirectory($extractTo);

            return back()->with('success', 'Restore backup berhasil dilakukan.');

        } catch (\Exception $e) {
            Log::error('Restore backup gagal: ' . $e->getMessage());
            return back()->with('error', 'Restore gagal: ' . $e->getMessage());
        }
    }

    private function restoreDatabase(string $sqlFile, string $mode): void
    {
        $dbConfig = config('database.connections.mysql');
        $host     = $dbConfig['host'];
        $port     = $dbConfig['port'];
        $dbName   = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $dumpPath = env('DB_DUMP_BINARY_PATH', '');

        if ($mode === 'replace') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $mysqlPath = rtrim($dumpPath, '/\\');
        $mysql     = $mysqlPath ? $mysqlPath . DIRECTORY_SEPARATOR . 'mysql' : 'mysql';
        $passFlag  = $password ? "-p\"{$password}\"" : '';
        $command   = "\"{$mysql}\" -h {$host} -P {$port} -u {$username} {$passFlag} {$dbName} < \"{$sqlFile}\"";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Gagal mengeksekusi SQL restore.');
        }
    }

    private function restoreFiles(string $sourcePath, string $mode): void
    {
        $destPath = storage_path('app/public/documents');

        if ($mode === 'replace') {
            $this->deleteDirectory($destPath);
            mkdir($destPath, 0755, true);
        }

        $this->copyDirectory($sourcePath, $destPath);
    }

    private function copyDirectory(string $src, string $dst): void
    {
        if (! is_dir($dst)) mkdir($dst, 0755, true);

        foreach (scandir($src) as $item) {
            if ($item === '.' || $item === '..') continue;
            $s = $src . DIRECTORY_SEPARATOR . $item;
            $d = $dst . DIRECTORY_SEPARATOR . $item;
            is_dir($s) ? $this->copyDirectory($s, $d) : copy($s, $d);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) return;
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') continue;
            $full = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($full) ? $this->deleteDirectory($full) : unlink($full);
        }
        rmdir($path);
    }

    private function getBackupFiles(): array
    {
        $backupName = config('backup.backup.name');

        try {
            $files = Storage::disk('local')->files($backupName);
        } catch (\Exception $e) {
            return [];
        }

        $backups = [];

        foreach ($files as $file) {
            if (! str_ends_with($file, '.zip')) continue;

            $size     = Storage::disk('local')->size($file);
            $lastMod  = Storage::disk('local')->lastModified($file);
            $fileName = basename($file);

            preg_match('/(\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2})/', $fileName, $matches);
            $date = isset($matches[1])
                ? \Carbon\Carbon::createFromFormat('Y-m-d-H-i-s', $matches[1])
                : \Carbon\Carbon::createFromTimestamp($lastMod);

            // Detect type from filename
            $type = 'db';
            if (str_contains(strtolower($fileName), 'full') || str_contains(strtolower($fileName), 'files')) {
                $type = 'full';
            } elseif (str_contains(strtolower($fileName), 'db') || str_contains(strtolower($fileName), 'database')) {
                $type = 'db';
            }

            $backups[] = [
                'file'       => $fileName,
                'size'       => $size,
                'size_human' => self::formatBytesStatic($size),
                'date'       => $date,
                'date_human' => $date->diffForHumans(),
                'type'       => $type,
            ];
        }

        usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);

        return $backups;
    }

    public static function formatBytesStatic(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    // Keep private alias for internal use
    private function formatBytes(int $bytes): string
    {
        return self::formatBytesStatic($bytes);
    }
}