<?php

use App\Models\DailyReport;
use App\Models\UserStreak;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Reports')] #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $user = auth()->user();
        
        // Ensure streak record exists
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        // Get last 7 days of reports
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $report = DailyReport::where('user_id', $user->id)->where('date', $date)->first();
            $last7Days->push([
                'date' => Carbon::parse($date),
                'status' => $report ? $report->status : 'missing',
                'is_today' => $i === 0
            ]);
        }

        $todayReport = DailyReport::where('user_id', $user->id)->where('date', now()->format('Y-m-d'))->first();

        return [
            'streak' => $streak,
            'last7Days' => $last7Days,
            'todayReport' => $todayReport,
        ];
    }
};
