<?php

use App\Models\Activity;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new
#[Title('Activity Feed')]
#[Layout('layouts.app')]
class  extends Component
{
    use WithPagination, Toast;

    public $filters = [
        'log_name' => '',
        'event' => '',
        'search' => '',
        'date_from' => '',
        'date_to' => '',
        'causer_id' => '',
    ];

    // Dashboard stats
    public $timeRange = '7'; // days for stats
    public $showStats = true; // toggle stats visibility

    // Clear functionality properties
    public $clearFilters = [
        'days' => 90,
        'log_name' => '',
        'event' => '',
    ];
    public $confirmDelete = false;
    public $showClearModal = false;

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.feed')) {
            abort(403, 'Unauthorized access to activity feed.');
        }
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filters = [
            'log_name' => '',
            'event' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'causer_id' => '',
        ];
        $this->resetPage();
    }

    public function openClearModal()
    {
        // Check permission
        if (!auth()->user()->can('activity.delete')) {
            $this->error('Unauthorized access to clear activities.');
            return;
        }

        $this->showClearModal = true;
        $this->confirmDelete = false;
    }

    public function closeClearModal()
    {
        $this->showClearModal = false;
        $this->confirmDelete = false;
    }

    public function getPreviewCount()
    {
        $query = Activity::query();

        if ($this->clearFilters['days']) {
            $date = now()->subDays($this->clearFilters['days']);
            $query->where('created_at', '<', $date);
        }

        if ($this->clearFilters['log_name']) {
            $query->where('log_name', $this->clearFilters['log_name']);
        }

        if ($this->clearFilters['event']) {
            $query->where('event', $this->clearFilters['event']);
        }

        return $query->count();
    }

    public function clearActivities()
    {
        // Check permission
        if (!auth()->user()->can('activity.delete')) {
            $this->error('Unauthorized access to clear activities.');
            return;
        }

        if (!$this->confirmDelete) {
            $this->error('Please confirm deletion by checking the box.');
            return;
        }

        $query = Activity::query();

        if ($this->clearFilters['days']) {
            $date = now()->subDays($this->clearFilters['days']);
            $query->where('created_at', '<', $date);
        }

        if ($this->clearFilters['log_name']) {
            $query->where('log_name', $this->clearFilters['log_name']);
        }

        if ($this->clearFilters['event']) {
            $query->where('event', $this->clearFilters['event']);
        }

        $count = $query->count();
        $query->delete();

        // Log the cleanup action
        \App\Services\ActivityLogger::logSystem('Activities cleared from UI', [
            'count' => $count,
            'days' => $this->clearFilters['days'],
            'log_name' => $this->clearFilters['log_name'],
            'event' => $this->clearFilters['event'],
        ]);

        $this->confirmDelete = false;
        $this->showClearModal = false;
        $this->success("Successfully deleted {$count} activities!");
        $this->resetPage();
    }

    public function clearAllActivities()
    {
        // Check permission
        if (!auth()->user()->can('activity.delete')) {
            $this->error('Unauthorized access to clear activities.');
            return;
        }

        if (!$this->confirmDelete) {
            $this->error('Please confirm deletion by checking the box.');
            return;
        }

        $count = Activity::count();
        Activity::truncate();

        // Log the cleanup action
        \App\Services\ActivityLogger::logSystem('All activities cleared from UI', [
            'count' => $count,
        ]);

        $this->confirmDelete = false;
        $this->showClearModal = false;
        $this->success("Successfully deleted all {$count} activities!");
        $this->resetPage();
    }

    public function getClearStats()
    {
        return [
            'total' => Activity::count(),
            'oldest' => Activity::orderBy('created_at', 'asc')->first()?->created_at,
            'newest' => Activity::orderBy('created_at', 'desc')->first()?->created_at,
            'by_log' => Activity::select('log_name', DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    public function toggleStats()
    {
        $this->showStats = !$this->showStats;
    }

    public function getActivityStats()
    {
        $startDate = now()->subDays($this->timeRange);

        return [
            'total' => Activity::where('created_at', '>=', $startDate)->count(),
            'unique_users' => Activity::where('created_at', '>=', $startDate)
                ->whereNotNull('causer_id')
                ->distinct('causer_id')
                ->count('causer_id'),
            'by_log' => Activity::where('created_at', '>=', $startDate)
                ->select('log_name', DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->orderBy('count', 'desc')
                ->get(),
            'by_event' => Activity::where('created_at', '>=', $startDate)
                ->select('event', DB::raw('count(*) as count'))
                ->groupBy('event')
                ->orderBy('count', 'desc')
                ->get(),
        ];
    }

    public function getEventColor($event)
    {
        return match($event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'error',
            'login' => 'primary',
            'logout' => 'warning',
            'failed_login' => 'error',
            default => 'base-300',
        };
    }

    public function getEventIcon($event)
    {
        return match($event) {
            'created' => 'o-plus-circle',
            'updated' => 'o-pencil-square',
            'deleted' => 'o-trash',
            'login' => 'o-arrow-right-on-rectangle',
            'logout' => 'o-arrow-left-on-rectangle',
            'failed_login' => 'o-exclamation-triangle',
            default => 'o-document-text',
        };
    }

    // Computed properties for data binding
    #[Computed]
    public function activities()
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->filters['log_name'], fn($q) => $q->where('log_name', $this->filters['log_name']))
            ->when($this->filters['event'], fn($q) => $q->where('event', $this->filters['event']))
            ->when($this->filters['causer_id'], fn($q) => $q->where('causer_id', $this->filters['causer_id']))
            ->when($this->filters['search'], fn($q) => $q->where('description', 'like', '%' . $this->filters['search'] . '%'))
            ->when($this->filters['date_from'], fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to'], fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    #[Computed]
    public function logNames()
    {
        return Activity::distinct()->pluck('log_name')->filter();
    }

    #[Computed]
    public function events()
    {
        return Activity::distinct()->pluck('event')->filter();
    }

    #[Computed]
    public function stats()
    {
        return $this->showStats ? $this->getActivityStats() : null;
    }

    #[Computed]
    public function previewCount()
    {
        return $this->showClearModal ? $this->getPreviewCount() : 0;
    }

    #[Computed]
    public function clearStats()
    {
        return $this->showClearModal ? $this->getClearStats() : null;
    }
};
