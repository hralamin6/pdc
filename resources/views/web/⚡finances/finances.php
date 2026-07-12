<?php

use App\Models\Donation;
use App\Models\Expense;
use App\Models\MonthlyTreasuryReport;
use App\Models\DonationCampaign;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Financial Transparency | Community Fund')] #[Layout('layouts.web')] class extends Component
{
    // Bar chart data points
    #[Computed]
    public function last6MonthsStats(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $y = $date->year;
            $m = $date->month;

            $income = (float) Donation::where('status', 'confirmed')
                ->whereYear('donated_at', $y)
                ->whereMonth('donated_at', $m)
                ->sum('amount');

            $expense = (float) Expense::where('status', 'confirmed')
                ->whereYear('expense_date', $y)
                ->whereMonth('expense_date', $m)
                ->sum('amount');

            $months[] = [
                'label' => $date->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }
        return $months;
    }

    #[Computed]
    public function totalStats(): array
    {
        $confirmed = Donation::where('status', 'confirmed');
        $totalIncome = (float) (clone $confirmed)->sum('amount');

        $totalExpense = (float) Expense::where('status', 'confirmed')->sum('amount');

        // Breakdown by type
        $byType = (clone $confirmed)
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $totalIncome - $totalExpense,
            'total_donors' => (clone $confirmed)->distinct('user_id')->count('user_id'),
            'by_type' => $byType,
        ];
    }

    #[Computed]
    public function publishedReports()
    {
        return MonthlyTreasuryReport::whereNotNull('published_at')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();
    }

    #[Computed]
    public function activeCampaigns()
    {
        return DonationCampaign::where('status', 'active')
            ->orderByDesc('collected_amount')
            ->take(3)
            ->get();
    }
};
