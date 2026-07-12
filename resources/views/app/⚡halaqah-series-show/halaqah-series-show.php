<?php

use App\Models\HalaqahSeries;
use App\Models\Halaqah;
use App\Models\User;
use App\Notifications\SimplePushNotification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Halaqah Program Console')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination, WithFileUploads;

    public HalaqahSeries $series;

    // Tabs
    public string $activeTab = 'sessions'; // sessions, roster, analytics

    // Announcement Modal
    public bool $showAnnouncementModal = false;
    public string $announcementTitle = '';
    public string $announcementMessage = '';

    // Quick Add Session Modal
    public bool $showSessionModal = false;
    public string $sessionTitle = '';
    public string $sessionTopic = '';
    public ?string $sessionDescription = null;
    public ?int $sessionSpeakerId = null;
    public string $sessionScheduledAt = '';
    public string $sessionLocation = '';
    public ?string $sessionMeetingLink = null;
    public string $sessionGenderRestriction = 'none';
    public ?int $sessionMaxCapacity = null;
    public bool $sessionIsRegistrationOpen = true;
    public string $sessionStatus = 'draft';
    public $sessionMaterialsFile;

    // QR Code Modal
    public bool $showQrModal = false;
    public ?string $qrCodeUrl = null;
    public ?string $qrHalaqahTitle = null;

    public function mount(HalaqahSeries $series)
    {
        $this->authorize('halaqahs.view');
        $this->series = $series;
    }

    public function openSessionModal()
    {
        $this->reset(['sessionTitle', 'sessionTopic', 'sessionDescription', 'sessionSpeakerId', 'sessionLocation', 'sessionMeetingLink', 'sessionGenderRestriction', 'sessionMaxCapacity', 'sessionMaterialsFile']);
        $this->sessionSpeakerId = auth()->id();
        $this->sessionScheduledAt = now()->addDays(1)->format('Y-m-d\T18:00');
        $this->sessionIsRegistrationOpen = true;
        $this->sessionStatus = 'draft';
        $this->showSessionModal = true;
    }

    public function saveSession()
    {
        $this->validate([
            'sessionTitle' => 'required|string|max:255',
            'sessionTopic' => 'required|string|max:255',
            'sessionDescription' => 'nullable|string',
            'sessionSpeakerId' => 'nullable|exists:users,id',
            'sessionScheduledAt' => 'required|date',
            'sessionLocation' => 'required|string|max:255',
            'sessionMeetingLink' => 'nullable|url',
            'sessionGenderRestriction' => 'required|in:none,brothers_only,sisters_only',
            'sessionMaxCapacity' => 'nullable|integer|min:1',
            'sessionIsRegistrationOpen' => 'required|boolean',
            'sessionStatus' => 'required|in:draft,published,completed,cancelled',
            'sessionMaterialsFile' => 'nullable|file|max:10240', // 10MB
        ]);

        $data = [
            'series_id' => $this->series->id,
            'title' => $this->sessionTitle,
            'topic' => $this->sessionTopic,
            'description' => $this->sessionDescription,
            'speaker_id' => $this->sessionSpeakerId,
            'scheduled_at' => $this->sessionScheduledAt,
            'location' => $this->sessionLocation,
            'meeting_link' => $this->sessionMeetingLink,
            'gender_restriction' => $this->sessionGenderRestriction,
            'max_capacity' => $this->sessionMaxCapacity,
            'is_registration_open' => $this->sessionIsRegistrationOpen,
            'status' => $this->sessionStatus,
        ];

        if ($this->sessionMaterialsFile) {
            $data['materials_path'] = $this->sessionMaterialsFile->store('halaqah-materials', 'public');
        }

        Halaqah::create($data);

        $this->success(__('New session added directly to this program series!'));
        $this->showSessionModal = false;
    }

    public function showQrCode(int $id)
    {
        $halaqah = Halaqah::findOrFail($id);
        $this->qrHalaqahTitle = $halaqah->title;
        $scanUrl = route('web.halaqah.show', ['halaqah' => $halaqah->id]) . '?qr=' . $halaqah->qr_token;
        $this->qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($scanUrl);
        $this->showQrModal = true;
    }

    public function openAnnouncementModal()
    {
        $this->announcementTitle = "Announcement: {$this->series->title}";
        $this->announcementMessage = '';
        $this->showAnnouncementModal = true;
    }

    public function sendAnnouncement()
    {
        $this->validate([
            'announcementTitle' => 'required|string|max:255',
            'announcementMessage' => 'required|string|max:1000',
        ]);

        // Find all users who have RSVP'd or attended any session in this series
        $users = User::whereHas('halaqahAttendances.halaqah', function ($query) {
            $query->where('series_id', $this->series->id);
        })->get();

        if ($users->isEmpty()) {
            $this->error(__('No enrolled members found to notify in this series.'));
            return;
        }

        // Send push notification to all
        $firstSession = $this->series->halaqahs()->first();
        $redirectUrl = $firstSession ? route('web.halaqah.show', ['halaqah' => $firstSession->id]) : route('app.dashboard');

        foreach ($users as $user) {
            $user->notify(new SimplePushNotification(
                $this->announcementTitle,
                $this->announcementMessage,
                $redirectUrl
            ));
        }

        $this->success(__('Announcement successfully sent to ') . $users->count() . __(' enrolled members!'));
        $this->showAnnouncementModal = false;
    }

    public function updateStatus(string $status)
    {
        $this->series->update(['status' => $status]);
        $this->success(__('Program status updated to ') . ucfirst($status));
    }

    public function with(): array
    {
        $seriesId = $this->series->id;

        // Fetch Sessions
        $sessions = Halaqah::with('speaker')
            ->where('series_id', $seriesId)
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Fetch Enrolled Roster with individual stats
        $enrolledUsers = User::query()
            ->whereHas('halaqahAttendances.halaqah', function ($q) use ($seriesId) {
                $q->where('series_id', $seriesId);
            })
            ->get()
            ->map(function ($user) use ($seriesId) {
                // Total RSVPs
                $rsvps = DB::table('halaqah_attendances')
                    ->join('halaqahs', 'halaqahs.id', '=', 'halaqah_attendances.halaqah_id')
                    ->where('halaqahs.series_id', $seriesId)
                    ->where('halaqah_attendances.user_id', $user->id)
                    ->count();

                // Total Attended
                $attended = DB::table('halaqah_attendances')
                    ->join('halaqahs', 'halaqahs.id', '=', 'halaqah_attendances.halaqah_id')
                    ->where('halaqahs.series_id', $seriesId)
                    ->where('halaqah_attendances.user_id', $user->id)
                    ->where('halaqah_attendances.attended', true)
                    ->count();

                // Total Completed Sessions in Series
                $totalCompleted = Halaqah::where('series_id', $seriesId)
                    ->where('status', 'completed')
                    ->count();

                $rate = $totalCompleted > 0 ? round(($attended / $totalCompleted) * 100) : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'rsvps' => $rsvps,
                    'attended' => $attended,
                    'rate' => $rate,
                ];
            });

        // Calculate Analytics stats
        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'completed')->count();
        $totalRsvps = DB::table('halaqah_attendances')
            ->join('halaqahs', 'halaqahs.id', '=', 'halaqah_attendances.halaqah_id')
            ->where('halaqahs.series_id', $seriesId)
            ->count();
        
        $totalAttendance = DB::table('halaqah_attendances')
            ->join('halaqahs', 'halaqahs.id', '=', 'halaqah_attendances.halaqah_id')
            ->where('halaqahs.series_id', $seriesId)
            ->where('halaqah_attendances.attended', true)
            ->count();

        $avgAttendanceRate = $completedSessions > 0 && $enrolledUsers->count() > 0 
            ? round(($totalAttendance / ($completedSessions * $enrolledUsers->count())) * 100)
            : 0;

        return [
            'sessions' => $sessions,
            'roster' => $enrolledUsers,
            'speakers' => User::role(['mentor', 'admin', 'super-admin'])->get(),
            'analytics' => [
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'total_enrolled' => $enrolledUsers->count(),
                'total_rsvps' => $totalRsvps,
                'avg_attendance_rate' => $avgAttendanceRate,
            ],
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
