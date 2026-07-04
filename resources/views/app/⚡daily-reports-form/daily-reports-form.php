<?php

use App\Models\DailyReport;
use App\Models\DailyReportEntry;
use App\Models\UserReportItem;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Fill Daily Report')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public $date;
    public $privacy_level = 'private';
    public $notes = '';
    public $entries = []; // user_report_item_id => ['boolean_value' => false, 'numeric_value' => null, 'text_value' => '']
    public $trackingItems = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $user = auth()->user();

        // Load active items the user is tracking
        $this->trackingItems = UserReportItem::with('template')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($this->trackingItems->isEmpty()) {
            $this->warning('You need to select items to track first.');
            return $this->redirect(route('app.daily-reports.settings'), navigate: true);
        }

        // Check if report already exists for today
        $existingReport = DailyReport::with('entries')
            ->where('user_id', $user->id)
            ->where('date', $this->date)
            ->first();

        if ($existingReport) {
            $this->privacy_level = $existingReport->privacy_level;
            $this->notes = $existingReport->notes;

            foreach ($existingReport->entries as $entry) {
                $this->entries[$entry->user_report_item_id] = [
                    'boolean_value' => $entry->boolean_value,
                    'numeric_value' => $entry->numeric_value,
                    'text_value' => $entry->text_value,
                ];
            }
        }

        // Initialize missing entries
        foreach ($this->trackingItems as $item) {
            if (!isset($this->entries[$item->id])) {
                $this->entries[$item->id] = [
                    'boolean_value' => false,
                    'numeric_value' => null,
                    'text_value' => '',
                ];
            }
        }
    }

    public function saveReport()
    {
        $this->validate([
            'date' => 'required|date|before_or_equal:today',
            'privacy_level' => 'required|in:private,mentor_only,public',
        ]);

        $user = auth()->user();
        
        $report = DailyReport::updateOrCreate(
            ['user_id' => $user->id, 'date' => $this->date],
            [
                'privacy_level' => $this->privacy_level,
                'notes' => $this->notes,
                'status' => 'submitted',
            ]
        );

        foreach ($this->entries as $itemId => $data) {
            DailyReportEntry::updateOrCreate(
                [
                    'daily_report_id' => $report->id,
                    'user_report_item_id' => $itemId,
                ],
                [
                    'boolean_value' => $data['boolean_value'] ?? false,
                    'numeric_value' => $data['numeric_value'] ?: null,
                    'text_value' => $data['text_value'] ?? null,
                ]
            );
        }

        // Update streak logic (simplified for now)
        $streak = \App\Models\UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        if (!$streak->last_report_date || Carbon::parse($streak->last_report_date)->isYesterday()) {
            $streak->increment('current_streak');
            if ($streak->current_streak > $streak->longest_streak) {
                $streak->longest_streak = $streak->current_streak;
            }
        } elseif (Carbon::parse($streak->last_report_date)->isBefore(now()->subDays(2))) {
            $streak->current_streak = 1;
        }
        $streak->last_report_date = now()->format('Y-m-d');
        $streak->increment('total_reports');
        $streak->save();

        $this->success('Daily report submitted successfully!');
        $this->redirect(route('app.daily-reports'), navigate: true);
    }
};
