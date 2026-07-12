<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Halaqah Session Scheduler')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination, WithFileUploads;

    public bool $showModal = false;
    public bool $showQrModal = false;
    public bool $showAiModal = false;
    public ?int $editingId = null;
    public ?int $qrHalaqahId = null;
    public ?string $qrCodeUrl = null;
    public ?string $qrHalaqahTitle = null;
    public string $aiPrompt = '';

    // Form Fields
    public string $title = '';
    public string $topic = '';
    public ?string $description = null;
    public ?int $series_id = null;
    public ?int $speaker_id = null;
    public string $scheduled_at = '';
    public string $location = '';
    public ?string $meeting_link = null;
    public string $gender_restriction = 'none';
    public ?int $max_capacity = null;
    public bool $is_registration_open = true;
    public string $status = 'draft';
    public $materials_file;
    public ?string $existing_materials_path = null;

    public string $search = '';
    public string $filterSeries = 'all';

    public function mount()
    {
        $this->authorize('halaqahs.create');
    }

    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $halaqah = Halaqah::findOrFail($id);
            $this->title = $halaqah->title;
            $this->topic = $halaqah->topic;
            $this->description = $halaqah->description;
            $this->series_id = $halaqah->series_id;
            $this->speaker_id = $halaqah->speaker_id;
            $this->scheduled_at = $halaqah->scheduled_at ? $halaqah->scheduled_at->format('Y-m-d\TH:i') : '';
            $this->location = $halaqah->location;
            $this->meeting_link = $halaqah->meeting_link;
            $this->gender_restriction = $halaqah->gender_restriction;
            $this->max_capacity = $halaqah->max_capacity;
            $this->is_registration_open = $halaqah->is_registration_open;
            $this->status = $halaqah->status;
            $this->existing_materials_path = $halaqah->materials_path;
            $this->materials_file = null;
        } else {
            $this->title = '';
            $this->topic = '';
            $this->description = '';
            $this->series_id = null;
            $this->speaker_id = auth()->id();
            $this->scheduled_at = now()->addDays(1)->format('Y-m-d\T18:00');
            $this->location = '';
            $this->meeting_link = '';
            $this->gender_restriction = 'none';
            $this->max_capacity = null;
            $this->is_registration_open = true;
            $this->status = 'draft';
            $this->existing_materials_path = null;
            $this->materials_file = null;
        }

        $this->showModal = true;
    }

    public function showQrCode(int $id)
    {
        $halaqah = Halaqah::findOrFail($id);
        $this->qrHalaqahId = $halaqah->id;
        $this->qrHalaqahTitle = $halaqah->title;
        
        // Scan target route: redirects to checkin page
        $scanUrl = route('web.halaqah.show', ['halaqah' => $halaqah->id]) . '?qr=' . $halaqah->qr_token;
        $this->qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($scanUrl);
        $this->showQrModal = true;
    }

    public function generateFromAi()
    {
        $this->validate(['aiPrompt' => 'required|string']);
        
        $seriesInfo = '';
        if ($this->series_id) {
            $series = HalaqahSeries::find($this->series_id);
            if ($series) {
                $seriesInfo = " The session belongs to a series called '{$series->title}' which is about: {$series->description}.";
            }
        }
        
        $prompt = "Details: " . $this->aiPrompt . $seriesInfo;
        
        try {
            $response = (string) \App\Ai\Agents\SessionGenerator::make()->prompt($prompt);
            $response = str_replace(['```json', '```'], '', $response);
            $data = json_decode(trim($response), true);
            
            if ($data && is_array($data)) {
                if (isset($data['title'])) $this->title = $data['title'];
                if (isset($data['topic'])) $this->topic = $data['topic'];
                if (isset($data['description'])) $this->description = $data['description'];
                if (isset($data['gender_restriction']) && in_array($data['gender_restriction'], ['none', 'brothers_only', 'sisters_only'])) {
                    $this->gender_restriction = $data['gender_restriction'];
                }
                if (array_key_exists('max_capacity', $data)) $this->max_capacity = $data['max_capacity'];
                if (isset($data['scheduled_at'])) $this->scheduled_at = \Carbon\Carbon::parse($data['scheduled_at'])->format('Y-m-d\TH:i');
                if (isset($data['location'])) $this->location = $data['location'];
                if (array_key_exists('meeting_link', $data)) $this->meeting_link = $data['meeting_link'];
                if (isset($data['status']) && in_array($data['status'], ['draft', 'published', 'completed', 'cancelled'])) {
                    $this->status = $data['status'];
                }
                if (isset($data['is_registration_open'])) $this->is_registration_open = (bool) $data['is_registration_open'];
                
                $this->showAiModal = false;
                $this->aiPrompt = '';
                $this->success(__('Session details generated by AI! Please review the form before saving.'));
                $this->showModal = true;
            } else {
                $this->error(__('Failed to parse AI response.'));
            }
        } catch (\Exception $e) {
            $this->error(__('Failed to generate from AI: ') . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'series_id' => 'nullable|exists:halaqah_series,id',
            'speaker_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date',
            'location' => 'required|string|max:255',
            'meeting_link' => 'nullable|url',
            'gender_restriction' => 'required|in:none,brothers_only,sisters_only',
            'max_capacity' => 'nullable|integer|min:1',
            'is_registration_open' => 'required|boolean',
            'status' => 'required|in:draft,published,completed,cancelled',
            'materials_file' => 'nullable|file|max:10240', // 10MB Max
        ]);

        $data = [
            'title' => $this->title,
            'topic' => $this->topic,
            'description' => $this->description,
            'series_id' => $this->series_id,
            'speaker_id' => $this->speaker_id,
            'scheduled_at' => $this->scheduled_at,
            'location' => $this->location,
            'meeting_link' => $this->meeting_link,
            'gender_restriction' => $this->gender_restriction,
            'max_capacity' => $this->max_capacity,
            'is_registration_open' => $this->is_registration_open,
            'status' => $this->status,
        ];

        if ($this->materials_file) {
            $data['materials_path'] = $this->materials_file->store('halaqah-materials', 'public');
        }

        if ($this->editingId) {
            $halaqah = Halaqah::findOrFail($this->editingId);
            $halaqah->update($data);
            $this->success(__('Session updated successfully!'));
        } else {
            Halaqah::create($data);
            $this->success(__('Session scheduled successfully!'));
        }

        $this->showModal = false;
    }

    public function deleteSession(int $id)
    {
        $halaqah = Halaqah::findOrFail($id);
        $halaqah->delete();
        $this->success(__('Session cancelled/deleted successfully!'));
    }

    public function with(): array
    {
        $query = Halaqah::with(['series', 'speaker'])->withCount('attendances');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('topic', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterSeries !== 'all') {
            if ($this->filterSeries === 'independent') {
                $query->whereNull('series_id');
            } else {
                $query->where('series_id', $this->filterSeries);
            }
        }

        return [
            'sessions' => $query->orderBy('scheduled_at', 'desc')->paginate(10),
            'allSeries' => HalaqahSeries::all()->map(fn($s) => ['id' => $s->id, 'name' => $s->title])->toArray(),
            'speakers' => User::role(['mentor', 'admin', 'super-admin'])->get(),
            'genderOptions' => [
                ['id' => 'none', 'name' => __('Open to All')],
                ['id' => 'brothers_only', 'name' => __('Brothers Only')],
                ['id' => 'sisters_only', 'name' => __('Sisters Only')],
            ],
            'statusOptions' => [
                ['id' => 'draft', 'name' => __('Draft')],
                ['id' => 'published', 'name' => __('Published')],
                ['id' => 'completed', 'name' => __('Completed')],
                ['id' => 'cancelled', 'name' => __('Cancelled')],
            ],
        ];
    }
};
