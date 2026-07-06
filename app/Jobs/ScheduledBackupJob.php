<?php

namespace App\Jobs;
use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduledBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $scheduleType = 'daily' // daily, weekly, monthly
    ) {}

    public function handle(): void
    {
        // Create backup record
        $backup = Backup::create([
            'type' => 'scheduled',
            'status' => 'pending',
            'includes' => ['database', 'files'],
        ]);

        // Dispatch the actual backup job
        ProcessBackupJob::dispatch($backup, [
            'schedule_type' => $this->scheduleType
        ]);

        // Clean up old backups based on retention policy
        $this->cleanupOldBackups();
    }

    protected function cleanupOldBackups(): void
    {
        // Keep last 7 daily backups
        $oldDailyBackups = Backup::where('type', 'scheduled')
            ->where('status', 'completed')
            ->where('created_at', '<', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->skip(7)
            ->get();

        foreach ($oldDailyBackups as $backup) {
            $backup->delete();
        }

        // Keep last 4 weekly backups (older than 1 month)
        $oldWeeklyBackups = Backup::where('type', 'scheduled')
            ->where('status', 'completed')
            ->where('created_at', '<', now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->skip(4)
            ->get();

        foreach ($oldWeeklyBackups as $backup) {
            $backup->delete();
        }

        // Keep last 12 monthly backups (older than 1 year)
        $oldMonthlyBackups = Backup::where('type', 'scheduled')
            ->where('status', 'completed')
            ->where('created_at', '<', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->skip(12)
            ->get();

        foreach ($oldMonthlyBackups as $backup) {
            $backup->delete();
        }
    }
}
