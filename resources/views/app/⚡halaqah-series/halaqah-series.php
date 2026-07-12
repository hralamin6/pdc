<?php

use App\Models\HalaqahSeries;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Halaqah Programs & Series')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination, WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    // Form Fields
    public string $title = '';
    public ?string $description = null;
    public ?int $mentor_id = null;
    public string $status = 'draft';
    public string $target_audience_level = 'beginner';
    public $banner_file;
    public ?string $existing_banner_path = null;

    public string $search = '';

    public function mount()
    {
        $this->authorize('halaqahs.create');
    }

    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $series = HalaqahSeries::findOrFail($id);
            $this->title = $series->title;
            $this->description = $series->description;
            $this->mentor_id = $series->mentor_id;
            $this->status = $series->status;
            $this->target_audience_level = $series->target_audience_level ?? 'beginner';
            $this->existing_banner_path = $series->banner_path;
            $this->banner_file = null;
        } else {
            $this->title = '';
            $this->description = '';
            $this->mentor_id = auth()->id();
            $this->status = 'draft';
            $this->target_audience_level = 'beginner';
            $this->existing_banner_path = null;
            $this->banner_file = null;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mentor_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,active,completed,cancelled',
            'target_audience_level' => 'required|string',
            'banner_file' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'mentor_id' => $this->mentor_id,
            'status' => $this->status,
            'target_audience_level' => $this->target_audience_level,
        ];

        if ($this->banner_file) {
            $data['banner_path'] = $this->banner_file->store('halaqah-series', 'public');
        }

        if ($this->editingId) {
            $series = HalaqahSeries::findOrFail($this->editingId);
            $series->update($data);
            $this->success(__('Halaqah series updated successfully!'));
        } else {
            HalaqahSeries::create($data);
            $this->success(__('Halaqah series created successfully!'));
        }

        $this->showModal = false;
    }

    public function deleteSeries(int $id)
    {
        $series = HalaqahSeries::findOrFail($id);
        $series->delete();
        $this->success(__('Halaqah series deleted successfully!'));
    }

    public function with(): array
    {
        $query = HalaqahSeries::with('mentor')->withCount('halaqahs');

        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        return [
            'series' => $query->orderBy('created_at', 'desc')->paginate(10),
            'mentors' => User::role(['mentor', 'admin', 'super-admin'])->get(),
            'statusOptions' => [
                ['id' => 'draft', 'name' => __('Draft')],
                ['id' => 'active', 'name' => __('Active')],
                ['id' => 'completed', 'name' => __('Completed')],
                ['id' => 'cancelled', 'name' => __('Cancelled')],
            ],
            'levelOptions' => [
                ['id' => 'beginner', 'name' => __('Beginner')],
                ['id' => 'intermediate', 'name' => __('Intermediate')],
                ['id' => 'advanced', 'name' => __('Advanced')],
            ]
        ];
    }
};
