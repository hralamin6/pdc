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
?>

<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center gap-4 mb-8">
        <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
        <div>
            <h1 class="text-3xl font-bold text-base-content">{{ __('Submit Daily Report') }}</h1>
            <p class="text-base-content/70 mt-1">{{ Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
        </div>
    </div>

    <form wire:submit="saveReport" class="space-y-6">
        
        {{-- Report Items --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
            <div class="space-y-6">
                @foreach($trackingItems as $item)
                    @php
                        $title = $item->custom_title ?? $item->template->title;
                        $type = $item->type;
                    @endphp
                    <div class="p-4 rounded-xl bg-base-200/30 border border-base-content/5">
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            
                            {{-- Checkbox for boolean or mixed --}}
                            @if(in_array($type, ['boolean', 'mixed']))
                                <div class="flex items-center gap-3 w-full sm:w-1/3">
                                    <x-checkbox wire:model="entries.{{ $item->id }}.boolean_value" class="checkbox-primary" />
                                    <span class="font-medium text-base-content">{{ $title }}</span>
                                </div>
                            @else
                                <div class="w-full sm:w-1/3">
                                    <span class="font-medium text-base-content">{{ $title }}</span>
                                </div>
                            @endif

                            {{-- Input for number --}}
                            @if($type === 'number')
                                <div class="w-full sm:w-2/3">
                                    <x-input type="number" wire:model="entries.{{ $item->id }}.numeric_value" placeholder="Amount / Quantity" class="input-sm" />
                                </div>
                            @endif

                            {{-- Input for text or mixed --}}
                            @if(in_array($type, ['text', 'mixed']))
                                <div class="w-full sm:w-2/3">
                                    <x-input type="text" wire:model="entries.{{ $item->id }}.text_value" placeholder="Notes / Details..." class="input-sm" />
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Meta & Privacy --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
            <h2 class="text-lg font-bold mb-4">General Reflection</h2>
            <div class="space-y-4">
                <x-textarea wire:model="notes" placeholder="Any overall thoughts, struggles, or wins for today?" rows="3" />
                
                <x-select label="Privacy Level" wire:model="privacy_level" :options="[
                    ['id' => 'private', 'name' => 'Private (Only me)'],
                    ['id' => 'mentor_only', 'name' => 'Mentor Only (Me & My Mentor)'],
                    ['id' => 'public', 'name' => 'Public (Anonymous leaderboard)'],
                ]" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <x-button label="Cancel" :link="route('app.daily-reports')" wire:navigate class="btn-ghost" />
            <x-button label="Submit Report" type="submit" class="btn-primary" icon="o-check" spinner="saveReport" />
        </div>
    </form>
</div>
