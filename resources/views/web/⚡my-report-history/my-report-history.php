<?php

use App\Models\DailyReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Daily Report History')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    public ?int $selectedReportId = null;
    public ?array $selectedReportDetails = null;

    public function mount(): void
    {
        $this->authorize('daily-reports.view');
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

    public function with(): array
    {
        $reports = DailyReport::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->paginate(15);

        return [
            'reports' => $reports,
        ];
    }
};
