<?php


use App\Jobs\ProcessBackupJob;
use App\Jobs\ScheduledBackupJob;
use App\Models\Backup;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Backups')] #[Layout('layouts.app')] class  extends Component
{
    use WithPagination, Toast;

    // Component properties
    public $backupType = 'both'; // 'both', 'database', 'files'
    public $showCreateModal = false;
    public $showCleanupModal = false;
    public $selectedBackups = [];
    public $selectAll = false;
    public $search = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';

    // Cleanup settings
    public $cleanupDays = 30;
    public $cleanupType = 'all';

    // Real-time updates
    public $listeners = [
        'backup-updated' => '$refresh',
    ];

    public function mount()
    {
        $this->authorize('backups.view');
    }
    #[Computed]
    public function backups(){
        return $this->getBackups();
    }
        #[Computed]
    public function stats(){
        return $this->getBackupStats();
    }

    // Create Manual Backup
    public function createBackup()
    {
        $this->authorize('backups.create');

        try {
            $includes = [];
            if ($this->backupType === 'both') {
                $includes = ['database', 'files'];
            } elseif ($this->backupType === 'database') {
                $includes = ['database'];
            } elseif ($this->backupType === 'files') {
                $includes = ['files'];
            }

            $backup = Backup::create([
                'type' => 'manual',
                'status' => 'pending',
                'includes' => $includes,
                'created_by' => Auth::id(),
            ]);

            $options = [];
            if ($this->backupType === 'database') {
                $options['only_db'] = true;
            } elseif ($this->backupType === 'files') {
                $options['only_files'] = true;
            }

            ProcessBackupJob::dispatch($backup, $options);

            $this->success('Backup started successfully! You will be notified when it completes.');
            $this->showCreateModal = false;
            $this->reset(['backupType']);

        } catch (\Exception $e) {
            $this->error('Failed to start backup: ' . $e->getMessage());
        }
    }

    // Download Backup
    public function downloadBackup($backupId): ?StreamedResponse
    {
        $this->authorize('backups.download');

        try {
            $backup = Backup::findOrFail($backupId);

            if (!$backup->exists()) {
                $this->error('Backup file not found on disk.');
                return null;
            }

            return $backup->download();

        } catch (\Exception $e) {
            $this->error('Failed to download backup: ' . $e->getMessage());
            return null;
        }
    }

    // Delete Single Backup
    public function deleteBackup($backupId): void
    {
        $this->authorize('backups.delete');

        try {
            $backup = Backup::findOrFail($backupId);
            $backup->delete();

            $this->success('Backup deleted successfully.');

        } catch (\Exception $e) {
            $this->error('Failed to delete backup: ' . $e->getMessage());
        }
    }

    // Bulk Actions
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBackups = $this->getBackups()->pluck('id')->toArray();
        } else {
            $this->selectedBackups = [];
        }
    }

    public function deleteSelected()
    {
        $this->authorize('backups.delete');

        if (empty($this->selectedBackups)) {
            $this->warning('No backups selected.');
            return;
        }

        try {
            $count = 0;
            foreach ($this->selectedBackups as $backupId) {
                $backup = Backup::find($backupId);
                if ($backup) {
                    $backup->delete();
                    $count++;
                }
            }

            $this->success("Successfully deleted {$count} backup(s).");
            $this->selectedBackups = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            $this->error('Failed to delete selected backups: ' . $e->getMessage());
        }
    }

    // Cleanup Old Backups
    public function showCleanupModal()
    {
        $this->authorize('backups.cleanup');
        $this->showCleanupModal = true;
    }

    public function cleanupOldBackups()
    {
        $this->authorize('backups.cleanup');

        try {
            $query = Backup::where('created_at', '<', now()->subDays($this->cleanupDays));

            if ($this->cleanupType !== 'all') {
                $query->where('type', $this->cleanupType);
            }

            $backups = $query->get();
            $count = 0;

            foreach ($backups as $backup) {
                $backup->delete();
                $count++;
            }

            $this->success("Successfully cleaned up {$count} old backup(s).");
            $this->showCleanupModal = false;

        } catch (\Exception $e) {
            $this->error('Failed to cleanup old backups: ' . $e->getMessage());
        }
    }

    // Schedule Management
    public function enableScheduledBackups()
    {
        $this->authorize('backups.manage-schedules');

        try {
            // This would typically update a settings table or config
            // For now, we'll dispatch a scheduled backup to demonstrate
            ScheduledBackupJob::dispatch('daily');

            $this->success('Scheduled backup queued successfully!');

        } catch (\Exception $e) {
            $this->error('Failed to schedule backup: ' . $e->getMessage());
        }
    }

    // Real-time Updates
    public function handleBackupUpdate($event)
    {
        $this->dispatch('backup-updated');
    }

    // Helper Methods
    protected function getBackups()
    {
        $query = Backup::with('creator')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter !== 'all', function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(10);
    }

    protected function getBackupStats()
    {
        return [
            'total' => Backup::count(),
            'completed' => Backup::completed()->count(),
            'failed' => Backup::failed()->count(),
            'running' => Backup::running()->count(),
            'total_size' => Backup::completed()->sum('file_size'),
            'recent' => Backup::recent()->count(),
        ];
    }


    // Refresh component
    public function refresh()
    {
        // This method can be called to refresh the component
    }
};
