<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Advanced Halaqah Management')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination, \Livewire\WithFileUploads;

    public bool $sessionModal = false;
    public bool $seriesModal = false;
    public bool $aiModal = false;
    public string $activeTab = 'sessions';
    public string $aiPrompt = '';
    
    // Session Form
    public $session_id;
    public $title;
    public $topic;
    public $description;
    public $speaker_id;
    public $scheduled_at;
    public $location;
    public $meeting_link;
    public $series_id;
    public $gender_restriction = 'none';
    public $max_capacity;
    public $is_registration_open = true;
    public $status = 'draft';
    public $materials_file;
    public $resources;

    // Series Form
    public $series_title;
    public $series_description;
    public $series_level = 'beginner';

    public function mount(): void
    {
        $this->authorize('halaqahs.create');
    }

    public function with(): array
    {
        return [
            'halaqahs' => Halaqah::with(['speaker', 'series'])
                ->withCount(['attendances as rsvp_count' => function ($query) {
                    $query->where('status_new', 'rsvp');
                }])
                ->orderBy('scheduled_at', 'desc')
                ->paginate(10, ['*'], 'sessionPage'),
                
            'seriesList' => HalaqahSeries::withCount('halaqahs')->orderBy('created_at', 'desc')->paginate(10, ['*'], 'seriesPage'),
            
            'speakers' => User::role(['mentor', 'admin', 'super-admin'])->get(),
            'allSeries' => HalaqahSeries::all(),
            
            'genderOptions' => [
                ['id' => 'none', 'name' => 'Open to All'],
                ['id' => 'brothers_only', 'name' => 'Brothers Only'],
                ['id' => 'sisters_only', 'name' => 'Sisters Only'],
            ],
            
            'statusOptions' => [
                ['id' => 'draft', 'name' => 'Draft'],
                ['id' => 'published', 'name' => 'Published'],
                ['id' => 'completed', 'name' => 'Completed'],
                ['id' => 'cancelled', 'name' => 'Cancelled'],
            ]
        ];
    }

    public function editSession(Halaqah $halaqah)
    {
        $this->session_id = $halaqah->id;
        $this->title = $halaqah->title;
        $this->topic = $halaqah->topic;
        $this->description = $halaqah->description;
        $this->speaker_id = $halaqah->speaker_id;
        $this->scheduled_at = $halaqah->scheduled_at?->format('Y-m-d\TH:i');
        $this->location = $halaqah->location;
        $this->meeting_link = $halaqah->meeting_link;
        $this->series_id = $halaqah->series_id;
        $this->gender_restriction = $halaqah->gender_restriction;
        $this->max_capacity = $halaqah->max_capacity;
        $this->is_registration_open = $halaqah->is_registration_open;
        $this->status = $halaqah->status;
        $this->resources = is_array($halaqah->resources) ? implode("\n", $halaqah->resources) : '';
        $this->materials_file = null;
        
        $this->sessionModal = true;
    }

    public function createSession()
    {
        $this->reset(['session_id', 'title', 'topic', 'description', 'speaker_id', 'scheduled_at', 'location', 'meeting_link', 'series_id', 'gender_restriction', 'max_capacity', 'status', 'resources', 'materials_file']);
        $this->is_registration_open = true;
        $this->status = 'draft';
        $this->gender_restriction = 'none';
        
        $this->sessionModal = true;
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
                if (isset($data['scheduled_at'])) $this->scheduled_at = $data['scheduled_at'];
                if (isset($data['location'])) $this->location = $data['location'];
                if (array_key_exists('meeting_link', $data)) $this->meeting_link = $data['meeting_link'];
                if (isset($data['status']) && in_array($data['status'], ['draft', 'published', 'completed', 'cancelled'])) {
                    $this->status = $data['status'];
                }
                if (isset($data['is_registration_open'])) $this->is_registration_open = (bool) $data['is_registration_open'];
                if (isset($data['resources'])) $this->resources = $data['resources'];
                
                $this->aiModal = false;
                $this->aiPrompt = '';
                $this->success('Session details generated by AI!');
            } else {
                $this->error('Failed to parse AI response.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to generate from AI: ' . $e->getMessage());
        }
    }

    public function saveSession()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'location' => 'required|string|max:255',
            'gender_restriction' => 'required|in:none,brothers_only,sisters_only',
            'status' => 'required|in:draft,published,completed,cancelled',
            'max_capacity' => 'nullable|integer|min:1',
            'materials_file' => 'nullable|file|max:10240', // 10MB
            'resources' => 'nullable|string',
        ]);

        $resourcesArray = [];
        if ($this->resources) {
            $resourcesArray = array_filter(array_map('trim', explode("\n", $this->resources)));
        }

        $data = [
            'title' => $this->title,
            'topic' => $this->topic,
            'description' => $this->description,
            'speaker_id' => $this->speaker_id ?: null,
            'scheduled_at' => $this->scheduled_at,
            'location' => $this->location,
            'meeting_link' => $this->meeting_link,
            'series_id' => $this->series_id ?: null,
            'gender_restriction' => $this->gender_restriction,
            'max_capacity' => $this->max_capacity ?: null,
            'is_registration_open' => (bool)$this->is_registration_open,
            'status' => $this->status,
            'resources' => $resourcesArray,
        ];

        if ($this->materials_file) {
            $data['materials_path'] = $this->materials_file->store('halaqahs', 'public');
        }

        if ($this->session_id) {
            Halaqah::where('id', $this->session_id)->update($data);
            $this->success('Session updated successfully.');
        } else {
            Halaqah::create($data);
            $this->success('Session created successfully.');
        }

        $this->sessionModal = false;
    }

    public function saveSeries()
    {
        $this->validate([
            'series_title' => 'required|string|max:255',
            'series_level' => 'required|in:beginner,intermediate,advanced',
        ]);

        HalaqahSeries::create([
            'mentor_id' => auth()->id(),
            'title' => $this->series_title,
            'description' => $this->series_description,
            'target_audience_level' => $this->series_level,
            'status' => 'active'
        ]);

        $this->success('Series created successfully.');
        $this->seriesModal = false;
        $this->reset(['series_title', 'series_description', 'series_level']);
    }
};
