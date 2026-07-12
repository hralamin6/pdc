<?php

use App\Models\DailyReport;
use App\Models\UserReportItem;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Title('My Daily Report Stats & Analytics')] #[Layout('layouts.web')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('daily-reports.view');
    }

    #[Computed]
    public function generalStats(): array
    {
        $userId = auth()->id();
        $reports = DailyReport::where('user_id', $userId)->where('status', 'submitted')->get();
        $totalReports = $reports->count();
        $avgCompletion = $totalReports > 0 ? (int) round($reports->avg('completion_percentage')) : 0;
        
        $thisMonth = DailyReport::where('user_id', $userId)->where('status', 'submitted')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
            
        $lastMonth = DailyReport::where('user_id', $userId)->where('status', 'submitted')
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->count();
            
        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (int) round((($thisMonth - $lastMonth) / $lastMonth) * 100);
        }

        return [
            'total_reports' => $totalReports,
            'avg_completion' => $avgCompletion,
            'this_month' => $thisMonth,
            'growth' => $growth,
        ];
    }

    #[Computed]
    public function itemTrends(): array
    {
        // Get the last 30 days of data for each active item
        $userId = auth()->id();
        $items = UserReportItem::with('template')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $trends = [];
        $startDate = now()->subDays(29)->format('Y-m-d');

        foreach ($items as $item) {
            $title = $item->custom_title ?? $item->template->title;
            $type = $item->type;
            $cat = $item->template->category ?? 'General';

            $entries = DB::table('daily_report_entries')
                ->join('daily_reports', 'daily_reports.id', '=', 'daily_report_entries.daily_report_id')
                ->where('daily_reports.user_id', $userId)
                ->where('daily_reports.date', '>=', $startDate)
                ->where('daily_reports.status', 'submitted')
                ->where('daily_report_entries.user_report_item_id', $item->id)
                ->get();

            $completedCount = 0;
            $totalCount = $entries->count();
            $sum = 0;

            foreach ($entries as $entry) {
                if ($type === 'number') {
                    $val = (int) $entry->numeric_value;
                    if ($val > 0) {
                        $completedCount++;
                        $sum += $val;
                    }
                } else {
                    if ($entry->boolean_value || !empty(trim($entry->text_value))) {
                        $completedCount++;
                    }
                }
            }

            $rate = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;
            $avg = $totalCount > 0 ? round($sum / $totalCount, 1) : 0;

            $trends[] = [
                'title' => $title,
                'category' => $cat,
                'type' => $type,
                'completion_rate' => $rate,
                'times_completed' => $completedCount,
                'total_sum' => $sum,
                'avg_value' => $avg,
            ];
        }

        // Sort by completion rate descending
        usort($trends, fn($a, $b) => $b['completion_rate'] <=> $a['completion_rate']);

        return collect($trends)->groupBy('category')->toArray();
    }
};
