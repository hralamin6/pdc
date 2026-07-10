<?php

use App\Models\MonthlyTreasuryReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Financial Summary')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'reports' => MonthlyTreasuryReport::whereNotNull('published_at')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(12),
        ];
    }
};
