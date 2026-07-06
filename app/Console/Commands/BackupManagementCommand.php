<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Jobs\ProcessBackupJob;
use App\Jobs\ScheduledBackupJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:manage
                            {action : The action to perform (status|create|cleanup|list)}
                            {--type=both : Backups type (database|files|both)}
                            {--days=30 : Days for cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage backup system operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'status':
                $this->showBackupStatus();
                break;
            case 'create':
                $this->createBackup();
                break;
            case 'cleanup':
                $this->cleanupBackups();
                break;
            case 'list':
                $this->listBackups();
                break;
            default:
                $this->error('Invalid action. Use: status, create, cleanup, or list');
        }
    }

    private function showBackupStatus()
    {
        $stats = [
            'Total Backups' => Backup::count(),
            'Completed' => Backup::completed()->count(),
            'Failed' => Backup::failed()->count(),
            'Running' => Backup::running()->count(),
            'Recent (7 days)' => Backup::recent()->count(),
            'Total Size' => $this->formatBytes(Backup::completed()->sum('file_size')),
        ];

        $this->info('📊 Backups System Status');
        $this->newLine();

        foreach ($stats as $label => $value) {
            $this->line("  {$label}: <fg=yellow>{$value}</>");
        }

        $this->newLine();

        // Show recent failed backups
        $failedBackups = Backup::failed()->recent()->limit(5)->get();
        if ($failedBackups->count() > 0) {
            $this->warn('⚠️  Recent Failed Backups:');
            foreach ($failedBackups as $backup) {
                $this->line("  - {$backup->created_at->format('M j, H:i')}: {$backup->error_message}");
            }
        }
    }

    private function createBackup()
    {
        $type = $this->option('type');

        if (!in_array($type, ['database', 'files', 'both'])) {
            $this->error('Invalid backup type. Use: database, files, or both');
            return;
        }

        $includes = $type === 'both' ? ['database', 'files'] : [$type];

        $backup = Backup::create([
            'type' => 'manual',
            'status' => 'pending',
            'includes' => $includes,
        ]);

        $options = [];
        if ($type === 'database') {
            $options['only_db'] = true;
        } elseif ($type === 'files') {
            $options['only_files'] = true;
        }

        ProcessBackupJob::dispatch($backup, $options);

        $this->info("✅ Backups job dispatched successfully!");
        $this->line("   Type: {$type}");
        $this->line("   Backups ID: {$backup->id}");
        $this->line("   Status: {$backup->status}");
    }

    private function cleanupBackups()
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('Days must be greater than 0');
            return;
        }

        $oldBackups = Backup::where('created_at', '<', now()->subDays($days))->get();
        $count = 0;

        foreach ($oldBackups as $backup) {
            $backup->delete();
            $count++;
        }

        $this->info("🗑️  Cleaned up {$count} backup(s) older than {$days} days");
    }

    private function listBackups()
    {
        $backups = Backup::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($backups->isEmpty()) {
            $this->info('No backups found.');
            return;
        }

        $this->info('📋 Recent Backups:');
        $this->newLine();

        $headers = ['ID', 'Name', 'Type', 'Status', 'Size', 'Created', 'Creator'];
        $rows = [];

        foreach ($backups as $backup) {
            $rows[] = [
                $backup->id,
                $backup->name ?: 'Backups #' . $backup->id,
                ucfirst($backup->type),
                ucfirst($backup->status),
                $backup->formatted_file_size,
                $backup->created_at->format('M j, H:i'),
                $backup->creator?->name ?? 'System',
            ];
        }

        $this->table($headers, $rows);
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size == 0) return '0 B';

        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}
