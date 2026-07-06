<?php

use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\DailyReportReminderNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Daily Reports Admin Portal')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public string $filter = 'all';
    public ?int $inspectingUserId = null;
    public ?array $inspectedUser = null;

    public function remindUser(int $userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->notify(new DailyReportReminderNotification());
            $this->success("Reminder sent to {$user->name}!");
        }
    }

    public function remindAll()
    {
        $today = now()->format('Y-m-d');
        $pendingUsers = User::whereDoesntHave('dailyReports', function ($q) use ($today) {
            $q->where('date', $today)->where('status', 'submitted');
        })->get();

        foreach ($pendingUsers as $user) {
            $user->notify(new DailyReportReminderNotification());
        }

        $this->success("Reminder notifications sent to {$pendingUsers->count()} pending members!");
    }

    public function inspectUser(int $userId)
    {
        $user = User::with(['userStreak', 'dailyReports' => function ($q) {
            $q->with('entries.item.template')->orderBy('date', 'desc')->take(7);
        }])->find($userId);

        if (!$user) {
            return;
        }

        $this->inspectingUserId = $user->id;
        $this->inspectedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'current_streak' => $user->userStreak?->current_streak ?? 0,
            'longest_streak' => $user->userStreak?->longest_streak ?? 0,
            'total_reports' => $user->userStreak?->total_reports ?? 0,
            'last_report_date' => $user->userStreak?->last_report_date?->format('M j, Y') ?? 'Never',
            'recent_reports' => $user->dailyReports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'date' => $report->date->format('l, M j, Y'),
                    'status' => $report->status,
                    'privacy_level' => $report->privacy_level,
                    'completed_count' => $report->completed_count,
                    'total_items_count' => $report->total_items_count,
                    'completion_percentage' => $report->completion_percentage,
                    'notes' => $report->notes,
                ];
            })->toArray(),
        ];
    }

    public function closeInspectModal()
    {
        $this->inspectingUserId = null;
        $this->inspectedUser = null;
    }

    public function with(): array
    {
        $today = now()->format('Y-m-d');

        $query = User::query()
            ->with(['roles', 'userStreak'])
            ->withCount(['dailyReports' => function ($q) use ($today) {
                $q->where('date', $today)->where('status', 'submitted');
            }]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'missed_today') {
            $query->whereDoesntHave('dailyReports', function ($q) use ($today) {
                $q->where('date', $today)->where('status', 'submitted');
            });
        } elseif ($this->filter === 'submitted_today') {
            $query->whereHas('dailyReports', function ($q) use ($today) {
                $q->where('date', $today)->where('status', 'submitted');
            });
        }

        $totalUsers = User::count();
        $submittedToday = DailyReport::where('date', $today)->where('status', 'submitted')->count();

        $stats = [
            'total_users' => $totalUsers,
            'submitted_today' => $submittedToday,
            'missed_today' => max(0, $totalUsers - $submittedToday),
            'submission_rate' => $totalUsers > 0 ? (int) round(($submittedToday / $totalUsers) * 100) : 0,
        ];

        return [
            'users' => $query->orderBy('name')->paginate(15),
            'stats' => $stats,
        ];
    }
};
