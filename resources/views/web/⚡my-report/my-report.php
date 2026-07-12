<?php

use App\Models\DailyReport;
use App\Models\UserReportItem;
use App\Models\UserStreak;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Daily Report | Spiritual Dashboard')] #[Layout('layouts.web')] class extends Component
{
    public ?int $selectedReportId = null;
    public ?array $selectedReportDetails = null;

    public function mount(): void
    {
        $this->authorize('daily-reports.view');
        UserReportItem::ensureDefaultsForUser(auth()->id());
    }

    public function viewReport(int $reportId): void
    {
        $report = DailyReport::with(['entries.item.template'])
            ->where('user_id', auth()->id())
            ->find($reportId);

        if (!$report) return;

        $this->selectedReportId = $report->id;
        $this->selectedReportDetails = [
            'id'                    => $report->id,
            'date'                  => $report->date->format('l, F j, Y'),
            'status'                => $report->status,
            'privacy_level'         => $report->privacy_level,
            'notes'                 => $report->notes,
            'completion_percentage' => $report->completion_percentage,
            'completed_count'       => $report->completed_count,
            'total_items_count'     => $report->total_items_count,
            'entries'               => $report->entries->map(function ($entry) {
                $title    = $entry->item?->custom_title ?? $entry->item?->template?->title ?? 'Activity';
                $category = $entry->item?->template?->category ?? 'General';
                $isDone   = $entry->boolean_value
                    || ($entry->numeric_value !== null && $entry->numeric_value > 0)
                    || !empty(trim($entry->text_value ?? ''));
                return [
                    'title'         => $title,
                    'category'      => $category,
                    'is_done'       => $isDone,
                    'boolean_value' => $entry->boolean_value,
                    'numeric_value' => $entry->numeric_value,
                    'text_value'    => $entry->text_value,
                ];
            })->groupBy('category')->toArray(),
        ];
    }

    public function closeModal(): void
    {
        $this->selectedReportId = null;
        $this->selectedReportDetails = null;
    }

    #[Computed]
    public function streak()
    {
        return UserStreak::firstOrCreate(
            ['user_id' => auth()->id()],
            ['current_streak' => 0, 'longest_streak' => 0]
        );
    }

    #[Computed]
    public function milestones(): array
    {
        return $this->streak->getMilestoneBadges();
    }

    #[Computed]
    public function todayReport()
    {
        return DailyReport::with('entries')
            ->where('user_id', auth()->id())
            ->where('date', now()->format('Y-m-d'))
            ->first();
    }

    #[Computed]
    public function last30Days(): \Illuminate\Support\Collection
    {
        $reportsByDate = DailyReport::with('entries')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subDays(29)->format('Y-m-d'))
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $report  = $reportsByDate->get($dateStr);
            $days->push([
                'date'                  => Carbon::parse($dateStr),
                'status'                => $report ? $report->status : 'missing',
                'is_today'              => $i === 0,
                'report_id'             => $report?->id,
                'completion_percentage' => $report?->completion_percentage ?? 0,
                'completed_count'       => $report?->completed_count ?? 0,
                'total_count'           => $report?->total_items_count ?? 0,
            ]);
        }
        return $days;
    }

    #[Computed]
    public function summaryStats(): array
    {
        $submitted = $this->last30Days->where('status', 'submitted');
        $count     = $submitted->count();
        $rate      = (int) round(($count / 30) * 100);
        return [
            'submitted'  => $count,
            'rate'       => $rate,
            'avg_completion' => $count > 0
                ? (int) round($submitted->avg('completion_percentage'))
                : 0,
        ];
    }

    #[Computed]
    public function categoryStats(): array
    {
        $reportsByDate = DailyReport::with('entries.item.template')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subDays(29)->format('Y-m-d'))
            ->get();

        $stats = [
            'Ibadah'               => ['completed' => 0, 'total' => 0, 'icon' => 'o-sparkles',    'color' => 'emerald'],
            'Quran & Adhkar'       => ['completed' => 0, 'total' => 0, 'icon' => 'o-book-open',   'color' => 'indigo'],
            'Study & Action'       => ['completed' => 0, 'total' => 0, 'icon' => 'o-academic-cap','color' => 'amber'],
            'Personal & Community' => ['completed' => 0, 'total' => 0, 'icon' => 'o-user-group',  'color' => 'rose'],
        ];

        foreach ($reportsByDate as $report) {
            foreach ($report->entries as $entry) {
                $cat = $entry->item?->template?->category ?? 'Personal & Community';
                if (!isset($stats[$cat])) {
                    $stats[$cat] = ['completed' => 0, 'total' => 0, 'icon' => 'o-list-bullet', 'color' => 'slate'];
                }
                $stats[$cat]['total']++;
                $done = $entry->boolean_value
                    || ($entry->numeric_value !== null && $entry->numeric_value > 0)
                    || !empty(trim($entry->text_value ?? ''));
                if ($done) $stats[$cat]['completed']++;
            }
        }
        return $stats;
    }
};
