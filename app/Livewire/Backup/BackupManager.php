<?php

namespace App\Livewire\Backup;

use Carbon\Carbon;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupManager extends Component
{
    public string $note = '';

    public bool $showRestore = false;

    public string $restoreFile = '';

    public string $restoreName = '';

    public function takeBackup(): void
    {
        $dbPath = database_path('database.sqlite');

        if (! file_exists($dbPath)) {
            session()->flash('error', 'SQLite database file not found.');

            return;
        }

        // Ensure directory exists
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $note = $this->note
            ? '_'.preg_replace('/[^a-zA-Z0-9\-]/', '', str_replace(' ', '-', $this->note))
            : '';
        $filename = "backup_{$timestamp}{$note}.sqlite";
        $destination = $backupDir.DIRECTORY_SEPARATOR.$filename;

        copy($dbPath, $destination);

        if ($this->note) {
            file_put_contents($destination.'.note.txt', $this->note);
        }

        $this->note = '';
        session()->flash('success', "Backup created: {$filename}");
    }

    public function confirmRestore(string $filename): void
    {
        $this->restoreFile = $filename;
        $this->restoreName = $filename;
        $this->showRestore = true;
    }

    public function restore(): void
    {
        $backupDir = storage_path('app/backups');
        $backupPath = $backupDir.DIRECTORY_SEPARATOR.$this->restoreFile;
        $dbPath = database_path('database.sqlite');

        if (! file_exists($backupPath)) {
            session()->flash('error', 'Backup file not found.');
            $this->showRestore = false;

            return;
        }

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Auto-backup before restore
        $autoName = 'pre_restore_'.now()->format('Y-m-d_H-i-s').'.sqlite';
        copy($dbPath, $backupDir.DIRECTORY_SEPARATOR.$autoName);
        file_put_contents(
            $backupDir.DIRECTORY_SEPARATOR.$autoName.'.note.txt',
            'Auto-backup before restore on '.now()->format('d/m/Y H:i')
        );

        copy($backupPath, $dbPath);

        $this->showRestore = false;
        session()->flash('success', 'Database restored. Auto-backup saved before restoring.');
    }

    public function getBackups(): array
    {
        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            return [];
        }

        $files = glob($backupDir.DIRECTORY_SEPARATOR.'*.sqlite');

        if (empty($files)) {
            return [];
        }

        $backups = [];
        foreach ($files as $fullPath) {
            $filename = basename($fullPath);
            $size = filesize($fullPath);
            $noteFile = $fullPath.'.note.txt';
            $note = file_exists($noteFile) ? file_get_contents($noteFile) : null;

            // Parse timestamp from filename
            preg_match('/(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/', $filename, $matches);
            $datetime = null;
            if (isset($matches[1], $matches[2])) {
                try {
                    $datetime = Carbon::createFromFormat(
                        'Y-m-d H-i-s',
                        $matches[1].' '.$matches[2]
                    );
                } catch (\Exception $e) {
                    $datetime = null;
                }
            }

            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatBytes($size),
                'note' => $note ? trim($note) : null,
                'datetime' => $datetime,
                'is_auto' => str_starts_with($filename, 'pre_restore_'),
            ];
        }

        usort($backups, fn ($a, $b) => ($b['datetime'] ?? now())->timestamp <=> ($a['datetime'] ?? now())->timestamp
        );

        return $backups;
    }

    public function download(string $filename)
    {
        $filePath = storage_path('app/backups/'.$filename);

        if (! file_exists($filePath)) {
            session()->flash('error', 'Backup file not found.');

            return;
        }

        return response()->streamDownload(function () use ($filePath) {
            readfile($filePath);
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => filesize($filePath),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function render()
    {
        return view('livewire.backup.backup-manager', [
            'backups' => $this->getBackups(),
        ]);
    }
}
