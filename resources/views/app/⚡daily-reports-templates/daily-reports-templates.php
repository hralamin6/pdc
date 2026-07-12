<?php

use App\Models\DailyReportTemplate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Daily Report Templates')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $title = '';
    public string $category = 'Ibadah';
    public string $type = 'boolean';
    public int $sort_order = 0;

    public function mount()
    {
        $this->authorize('daily-reports.manage');
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'type' => 'required|in:boolean,number,text,mixed',
            'sort_order' => 'required|integer',
        ];
    }

    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $template = DailyReportTemplate::findOrFail($id);
            $this->title = $template->title;
            $this->category = $template->category;
            $this->type = $template->type;
            $this->sort_order = $template->sort_order;
        } else {
            $this->title = '';
            $this->category = 'Ibadah';
            $this->type = 'boolean';
            $this->sort_order = (DailyReportTemplate::max('sort_order') ?? 0) + 10;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $template = DailyReportTemplate::findOrFail($this->editingId);
            $template->update([
                'title' => $this->title,
                'category' => $this->category,
                'type' => $this->type,
                'sort_order' => $this->sort_order,
            ]);
            $this->success(__('Template updated successfully!'));
        } else {
            DailyReportTemplate::create([
                'title' => $this->title,
                'category' => $this->category,
                'type' => $this->type,
                'sort_order' => $this->sort_order,
            ]);
            $this->success(__('Template created successfully!'));
        }

        $this->showModal = false;
    }

    public function deleteTemplate(int $id)
    {
        $template = DailyReportTemplate::findOrFail($id);
        $template->delete();
        $this->success(__('Template deleted successfully!'));
    }

    public function with(): array
    {
        return [
            'templates' => DailyReportTemplate::orderBy('sort_order')->get()->groupBy('category'),
        ];
    }
};
