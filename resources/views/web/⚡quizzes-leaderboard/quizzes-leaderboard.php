<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Leaderboard | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        // 1. Fetch podium top 3
        $podium = User::where('gamification_points', '>', 0)
            ->orderByDesc('gamification_points')
            ->take(3)
            ->get();

        // 2. Fetch full list of ranked users
        $query = User::where('gamification_points', '>', 0);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $leaderboard = $query->orderByDesc('gamification_points')
            ->orderBy('name')
            ->paginate(15);

        // Map rank positions inside paginated results
        $currentPage = $leaderboard->currentPage();
        $perPage = $leaderboard->perPage();

        $leaderboard->getCollection()->transform(function ($user, $index) use ($currentPage, $perPage) {
            $user->rank = (($currentPage - 1) * $perPage) + $index + 1;
            return $user;
        });

        return [
            'podium' => $podium,
            'leaderboard' => $leaderboard,
        ];
    }
}
