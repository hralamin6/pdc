<?php

use App\Models\DailyReport;
use App\Models\DailyReportEntry;
use App\Models\DailyReportTemplate;
use App\Models\UserReportItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Reports Analytics')] #[Layout('layouts.app')] class extends Component
{
    public function mount()
    {
        $this->authorize('daily-reports.manage');
    }

    public function with(): array
    {
        // 1. General totals
        $totalUsers = User::count();
        $today = now()->format('Y-m-d');
        $submittedToday = DailyReport::where('date', $today)->where('status', 'submitted')->count();
        
        // 2. Submission rate trend for the last 7 days
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $submitted = DailyReport::where('date', $dateStr)->where('status', 'submitted')->count();
            $rate = $totalUsers > 0 ? round(($submitted / $totalUsers) * 100) : 0;
            $last7Days[] = [
                'label' => $date->format('M d'),
                'rate' => $rate,
                'count' => $submitted
            ];
        }

        // 3. Stats by template items (Top 5 completed & Bottom 5 completed)
        // We will calculate completion rate for each DailyReportTemplate based on submitted reports
        $allTemplates = DailyReportTemplate::all();
        $templateStats = [];

        foreach ($allTemplates as $template) {
            // Count total entries pointing to this template in submitted reports
            $entries = DB::table('daily_report_entries')
                ->join('daily_reports', 'daily_reports.id', '=', 'daily_report_entries.daily_report_id')
                ->join('user_report_items', 'user_report_items.id', '=', 'daily_report_entries.user_report_item_id')
                ->where('daily_reports.status', 'submitted')
                ->where('user_report_items.daily_report_template_id', $template->id)
                ->select('daily_report_entries.boolean_value', 'daily_report_entries.numeric_value', 'daily_report_entries.text_value')
                ->get();

            $total = $entries->count();
            if ($total > 0) {
                $completed = $entries->filter(function($entry) use ($template) {
                    if ($template->type === 'number') {
                        return (int)$entry->numeric_value > 0;
                    }
                    return $entry->boolean_value || !empty(trim($entry->text_value ?? ''));
                })->count();

                $rate = round(($completed / $total) * 100);
                $templateStats[] = [
                    'title' => $template->title,
                    'category' => $template->category,
                    'rate' => $rate,
                    'completed' => $completed,
                    'total' => $total
                ];
            }
        }

        // Sort by rate descending
        usort($templateStats, fn($a, $b) => $b['rate'] <=> $a['rate']);
        $top5 = array_slice($templateStats, 0, 5);
        $bottom5 = array_reverse(array_slice($templateStats, -5));

        // 4. Category breakdown
        $categoryBreakdown = [];
        $groupedTemplates = collect($templateStats)->groupBy('category');
        foreach ($groupedTemplates as $category => $items) {
            $avgRate = round($items->avg('rate'));
            $categoryBreakdown[] = [
                'name' => $category,
                'rate' => $avgRate
            ];
        }

        return [
            'stats' => [
                'total_users' => $totalUsers,
                'submitted_today' => $submittedToday,
                'rate_today' => $totalUsers > 0 ? round(($submittedToday / $totalUsers) * 100) : 0,
            ],
            'last7Days' => $last7Days,
            'top5' => $top5,
            'bottom5' => $bottom5,
            'categoryBreakdown' => $categoryBreakdown
        ];
    }
};
