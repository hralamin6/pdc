<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\User;
use App\Notifications\BackupNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;

    public function __construct(
        protected Backup $backup,
        protected array $options = []
    ) {}

    public function handle(): void
    {
        try {
            $this->backup->markAsStarted();

            // Generate unique filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $type = '';

            if (isset($this->options['only_db']) && $this->options['only_db']) {
                $type = '_database';
            } elseif (isset($this->options['only_files']) && $this->options['only_files']) {
                $type = '_files';
            }

            $filename = "backup{$type}_{$timestamp}.zip";

            // Build artisan command
            $command = 'backup:run';
            $commandOptions = [
                '--only-to-disk' => 'local',
                '--filename' => $filename
            ];

            // Add specific backup options
            if (isset($this->options['only_db']) && $this->options['only_db']) {
                $commandOptions['--only-db'] = true;
            }

            if (isset($this->options['only_files']) && $this->options['only_files']) {
                $commandOptions['--only-files'] = true;
            }

            // Execute backup command
            $exitCode = Artisan::call($command, $commandOptions);

            if ($exitCode === 0) {
                // The spatie backup package stores files in: storage/app/{APP_NAME}/
                $appName = config('backup.backup.name');
                $backupPath = "{$appName}/{$filename}";

                // Check if file exists
                if (Storage::disk('local')->exists($backupPath)) {
                    $fileSize = Storage::disk('local')->size($backupPath);

                    $this->backup->update([
                        'name' => $filename,
                        'path' => $backupPath,
                        'disk' => 'local'
                    ]);

                    $this->backup->markAsCompleted($fileSize);

                    // Send success notification
                    $this->sendNotification('success');
                } else {
                    throw new \Exception('Backup file was not created at expected location: ' . $backupPath);
                }
            } else {
                $output = Artisan::output();
                throw new \Exception('Backup command failed with exit code: ' . $exitCode . '. Output: ' . $output);
            }

        } catch (\Exception $e) {
            $this->backup->markAsFailed($e->getMessage());

            // Send failure notification
            $this->sendNotification('failed', $e->getMessage());

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->backup->markAsFailed($exception->getMessage());
        $this->sendNotification('failed', $exception->getMessage());
    }

    protected function sendNotification(string $status, string $errorMessage = null): void
    {
        if (!$this->backup->created_by) return;

        $user = User::find($this->backup->created_by);
        if (!$user) return;

        // Send notification instead of broadcasting event
        $user->notify(new BackupNotification($this->backup, $status, $errorMessage));
    }
}
