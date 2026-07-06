<?php

use App\Models\DailyReport;
use App\Models\UserReportItem;
use App\Models\UserStreak;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Reports Dashboard')] #[Layout('layouts.app')] class extends Component
{
    public ?int $selectedReportId = null;
    public ?array $selectedReportDetails = null;

    public function mount()
    {
        // Auto-initialize default tracking items for user if none exist
        UserReportItem::ensureDefaultsForUser(auth()->id());
    }

    public function viewReport(int $reportId)
    {
        $report = DailyReport::with(['entries.item.template'])
            ->where('user_id', auth()->id())
            ->find($reportId);

        if (!$report) {
            return;
        }

        $this->selectedReportId = $report->id;
        $this->selectedReportDetails = [
            'id' => $report->id,
            'date' => $report->date->format('l, F j, Y'),
            'status' => $report->status,
            'privacy_level' => $report->privacy_level,
            'notes' => $report->notes,
            'completion_percentage' => $report->completion_percentage,
            'completed_count' => $report->completed_count,
            'total_items_count' => $report->total_items_count,
            'entries' => $report->entries->map(function ($entry) {
                $title = $entry->item?->custom_title ?? $entry->item?->template?->title ?? 'Activity Item';
                $category = $entry->item?->template?->category ?? 'General';
                $isDone = $entry->boolean_value
                    || ($entry->numeric_value !== null && $entry->numeric_value > 0)
                    || !empty(trim($entry->text_value ?? ''));

                return [
                    'title' => $title,
                    'category' => $category,
                    'is_done' => $isDone,
                    'boolean_value' => $entry->boolean_value,
                    'numeric_value' => $entry->numeric_value,
                    'text_value' => $entry->text_value,
                ];
            })->groupBy('category')->toArray(),
        ];
    }

    public function closeReportModal()
    {
        $this->selectedReportId = null;
        $this->selectedReportDetails = null;
    }

    public function with(): array
    {
        $user = auth()->user();

        // Ensure streak record exists
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $milestones = $streak->getMilestoneBadges();

        // Get past 30 days of reports
        $last30Days = collect();
        $submittedCount30Days = 0;
        $totalCompletedItems30Days = 0;
        $totalTrackedItems30Days = 0;

        $reportsByDate = DailyReport::with('entries')
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(29)->format('Y-m-d'))
            ->get()
            ->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        for ($i = 29; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $dateCarbon = Carbon::parse($dateStr);
            $report = $reportsByDate->get($dateStr);

            if ($report && $report->status === 'submitted') {
                $submittedCount30Days++;
                $totalCompletedItems30Days += $report->completed_count;
                $totalTrackedItems30Days += $report->total_items_count;
            }

            $last30Days->push([
                'date' => $dateCarbon,
                'status' => $report ? $report->status : 'missing',
                'is_today' => $i === 0,
                'report_id' => $report?->id,
                'completion_percentage' => $report?->completion_percentage ?? 0,
                'completed_count' => $report?->completed_count ?? 0,
                'total_count' => $report?->total_items_count ?? 0,
            ]);
        }

        $todayReport = $reportsByDate->get(now()->format('Y-m-d'));

        // Calculate Category Breakdown for past 30 days
        $categoryStats = [
            'Ibadah' => ['completed' => 0, 'total' => 0, 'icon' => 'o-sparkles', 'color' => 'primary'],
            'Quran & Adhkar' => ['completed' => 0, 'total' => 0, 'icon' => 'o-book-open', 'color' => 'secondary'],
            'Study & Action' => ['completed' => 0, 'total' => 0, 'icon' => 'o-academic-cap', 'color' => 'accent'],
            'Personal & Community' => ['completed' => 0, 'total' => 0, 'icon' => 'o-user-group', 'color' => 'info'],
        ];

        foreach ($reportsByDate as $report) {
            foreach ($report->entries as $entry) {
                $category = $entry->item?->template?->category ?? 'Personal & Community';
                if (!isset($categoryStats[$category])) {
                    $categoryStats[$category] = ['completed' => 0, 'total' => 0, 'icon' => 'o-list-bullet', 'color' => 'neutral'];
                }

                $categoryStats[$category]['total']++;
                $isDone = $entry->boolean_value
                    || ($entry->numeric_value !== null && $entry->numeric_value > 0)
                    || !empty(trim($entry->text_value ?? ''));
                if ($isDone) {
                    $categoryStats[$category]['completed']++;
                }
            }
        }

        $overall30DayRate = $submittedCount30Days > 0 ? (int) round(($submittedCount30Days / 30) * 100) : 0;

        return [
            'streak' => $streak,
            'milestones' => $milestones,
            'last30Days' => $last30Days,
            'todayReport' => $todayReport,
            'overall30DayRate' => $overall30DayRate,
            'categoryStats' => $categoryStats,
        ];
    }
};
