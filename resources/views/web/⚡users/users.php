<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Title('Community Members')]
#[Layout('layouts.web')]
class extends Component
{
    use WithPagination;

    #[Url(as: 's')]
    public string $search = '';

    #[Url(as: 'role')]
    public ?string $roleFilter = null;

    #[Url(as: 'sort')]
    public string $sortBy = 'latest';

    public string $viewMode = 'grid'; // 'grid' or 'list'

    public bool $showFilters = false;

    /**
     * Get filtered and paginated users
     */
    #[Computed]
    public function users()
    {
        $query = User::with(['roles', 'media', 'posts'])
            ->withCount('posts');

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Role filter
        if ($this->roleFilter) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        // Sorting
        match ($this->sortBy) {
            'name' => $query->orderBy('name', 'asc'),
            'oldest' => $query->oldest('created_at'),
            default => $query->latest('created_at'),
        };

        $perPage = $this->viewMode === 'grid' ? 12 : 20;

        return $query->paginate($perPage);
    }

    /**
     * Get available roles with user counts
     */
    #[Computed]
    public function availableRoles()
    {
        return \Spatie\Permission\Models\Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();
    }

    /**
     * Get stats
     */
    #[Computed]
    public function stats()
    {
        return [
            'total' => User::count(),
            'roles' => \Spatie\Permission\Models\Role::count(),
            'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get top contributors (users with most posts)
     */
    #[Computed]
    public function topContributors()
    {
        return User::whereHas('posts')
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(6)
            ->get();
    }

    /**
     * Get role distribution analytics
     */
    #[Computed]
    public function roleDistribution()
    {
        return \Spatie\Permission\Models\Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get()
            ->map(function ($role) {
                $total = User::count();

                return [
                    'name' => $role->name,
                    'count' => $role->users_count,
                    'percentage' => $total > 0 ? round(($role->users_count / $total) * 100, 1) : 0,
                ];
            });
    }

    /**
     * Get member growth analytics (last 6 months)
     */
    #[Computed]
    public function memberGrowth()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M'),
                'count' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        return collect($months);
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'roleFilter', 'sortBy']);
        $this->resetPage();
    }

    /**
     * Update search and reset pagination
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update role filter and reset pagination
     */
    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Update sort and reset pagination
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle view mode
     */
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }
};
