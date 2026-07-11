<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Halaqahs & Courses')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $activeTab = 'upcoming'; // upcoming, series, past

    public function mount(): void
    {
        $this->authorize('halaqahs.view');
    }

    public function with(): array
    {
        $upcomingQuery = Halaqah::with(['speaker', 'series', 'quizzes'])
            ->whereIn('status', ['published'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc');
            
        $pastQuery = Halaqah::with(['speaker', 'series', 'quizzes'])
            ->whereIn('status', ['published', 'completed'])
            ->where('scheduled_at', '<', now())
            ->orderBy('scheduled_at', 'desc');

        return [
            'upcoming' => $upcomingQuery->paginate(9, ['*'], 'upcomingPage'),
            'past' => $pastQuery->paginate(9, ['*'], 'pastPage'),
            'series' => HalaqahSeries::withCount(['halaqahs', 'quizzes'])->where('status', 'active')->paginate(9, ['*'], 'seriesPage'),
        ];
    }
};
