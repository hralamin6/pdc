<?php

use App\Models\Activity;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Title('My Activities')]
#[Layout('layouts.app')]
class extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, today, week, month

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.my')) {
            abort(403, 'Unauthorized access to activities.');
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    #[Computed]
    public function activities()
    {
        $query = Activity::query()
            ->with(['subject'])
            ->causedBy(auth()->user())
            ->orderBy('created_at', 'desc');

        match ($this->filter) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            default => null,
        };

        return $query->paginate(15);
    }
};
