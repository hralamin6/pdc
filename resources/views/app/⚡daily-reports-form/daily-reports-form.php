<?php

use App\Models\DailyReport;
use App\Models\DailyReportEntry;
use App\Models\UserReportItem;
use App\Models\UserStreak;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Fill Daily Report')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public string $date = '';
    public string $privacy_level = 'private';
    public string $notes = '';
    public array $entries = []; // user_report_item_id => ['boolean_value' => false, 'numeric_value' => null, 'text_value' => '']
    public $trackingItems = [];
    public array $availableDates = [];

    public function mount()
    {
        $this->authorize('daily-reports.view');
        $user = auth()->user();
        UserReportItem::ensureDefaultsForUser($user->id);

        $this->date = now()->format('Y-m-d');

        // Populate last 7 available dates for backfilling
        for ($i = 0; $i < 7; $i++) {
            $dt = now()->subDays($i);
            $this->availableDates[] = [
                'id' => $dt->format('Y-m-d'),
                'name' => $i === 0 ? 'Today (' . $dt->format('M j') . ')' : ($i === 1 ? 'Yesterday (' . $dt->format('M j') . ')' : $dt->format('l, M j')),
            ];
        }

        $this->loadReportForDate($this->date);
    }

    public function updatedDate($newDate)
    {
        $this->loadReportForDate($newDate);
    }

    public function loadReportForDate(string $targetDate)
    {
        $user = auth()->user();
        $this->date = $targetDate;

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

        // Check if report already exists for selected date
        $existingReport = DailyReport::with('entries')
            ->where('user_id', $user->id)
            ->where('date', $this->date)
            ->first();

        $this->entries = [];

        if ($existingReport) {
            $this->privacy_level = $existingReport->privacy_level;
            $this->notes = $existingReport->notes ?? '';

            foreach ($existingReport->entries as $entry) {
                $this->entries[$entry->user_report_item_id] = [
                    'boolean_value' => (bool) $entry->boolean_value,
                    'numeric_value' => $entry->numeric_value,
                    'text_value' => $entry->text_value ?? '',
                ];
            }
        } else {
            $this->privacy_level = 'private';
            $this->notes = '';
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

    public function adjustNumber(int $itemId, int $delta)
    {
        $current = (int) ($this->entries[$itemId]['numeric_value'] ?? 0);
        $newVal = max(0, $current + $delta);
        $this->entries[$itemId]['numeric_value'] = $newVal;
    }

    public function saveReport()
    {
        $this->validate([
            'date' => 'required|date|before_or_equal:today',
            'privacy_level' => 'required|in:private,mentor_only,public',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        $wasAlreadySubmitted = DailyReport::where('user_id', $user->id)
            ->where('date', $this->date)
            ->where('status', 'submitted')
            ->exists();

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
                    'numeric_value' => isset($data['numeric_value']) && $data['numeric_value'] !== '' ? (int) $data['numeric_value'] : null,
                    'text_value' => $data['text_value'] ?? null,
                ]
            );
        }

        // Update streak ONLY if this is a new submission (not updating existing)
        if (!$wasAlreadySubmitted) {
            $streak = UserStreak::firstOrCreate(
                ['user_id' => $user->id],
                ['current_streak' => 0, 'longest_streak' => 0]
            );

            $lastDate = $streak->last_report_date ? Carbon::parse($streak->last_report_date) : null;
            $selectedDate = Carbon::parse($this->date);

            if (!$lastDate || $selectedDate->isAfter($lastDate)) {
                if ($lastDate && $selectedDate->diffInDays($lastDate) === 1) {
                    $streak->increment('current_streak');
                } elseif (!$lastDate) {
                    $streak->current_streak = 1;
                } else {
                    $streak->current_streak = 1;
                }

                if ($streak->current_streak > $streak->longest_streak) {
                    $streak->longest_streak = $streak->current_streak;
                }
                $streak->last_report_date = $this->date;
            }

            $streak->increment('total_reports');
            $streak->save();
        }

        $this->success('Daily report saved successfully!');
        $this->redirect(route('app.daily-reports'), navigate: true);
    }
};
