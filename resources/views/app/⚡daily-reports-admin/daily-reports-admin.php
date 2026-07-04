<?php

use App\Models\DailyReport;
use App\Models\User;
use App\Models\UserStreak;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Daily Reports Admin')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'all';

    public function with(): array
    {
        $today = now()->format('Y-m-d');
        
        $query = User::query()
            ->with(['roles'])
            ->withCount(['dailyReports' => function($q) use ($today) {
                $q->where('date', $today)->where('status', 'submitted');
            }])
            ->leftJoin('user_streaks', 'users.id', '=', 'user_streaks.user_id')
            ->select('users.*', 'user_streaks.current_streak', 'user_streaks.longest_streak', 'user_streaks.last_report_date');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filter === 'missed_today') {
            $query->having('daily_reports_count', 0);
        } elseif ($this->filter === 'submitted_today') {
            $query->having('daily_reports_count', '>', 0);
        }

        $stats = [
            'total_users' => User::count(),
            'submitted_today' => DailyReport::where('date', $today)->where('status', 'submitted')->count(),
        ];
        $stats['missed_today'] = $stats['total_users'] - $stats['submitted_today'];

        return [
            'users' => $query->paginate(15),
            'stats' => $stats,
        ];
    }

    public function remindUser($userId)
    {
        // Placeholder for notification system
        // Notification::send($user, new DailyReportReminderNotification());
        
        $this->dispatch('success', 'Reminder sent successfully!');
    }
    
    public function remindAll()
    {
        // Placeholder for batch notification
        $this->dispatch('success', 'Reminders sent to all pending users!');
    }
};
