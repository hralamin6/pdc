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
?>

<div class="max-w-7xl mx-auto py-6">
    <div class="flex items-center gap-4 mb-8">
        <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
        <div>
            <h1 class="text-3xl font-bold text-base-content">{{ __('Admin: Daily Reports') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Monitor community consistency and send reminders.') }}</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-base-100 p-6 rounded-2xl border border-base-content/5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <x-icon name="o-users" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-3xl font-black">{{ $stats['total_users'] }}</p>
                    <p class="text-sm font-semibold text-base-content/50 uppercase">Total Members</p>
                </div>
            </div>
        </div>
        <div class="bg-base-100 p-6 rounded-2xl border border-base-content/5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center text-success">
                    <x-icon name="o-check-circle" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-3xl font-black">{{ $stats['submitted_today'] }}</p>
                    <p class="text-sm font-semibold text-base-content/50 uppercase">Submitted Today</p>
                </div>
            </div>
        </div>
        <div class="bg-base-100 p-6 rounded-2xl border border-base-content/5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-warning/10 flex items-center justify-center text-warning">
                    <x-icon name="o-clock" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-3xl font-black">{{ $stats['missed_today'] }}</p>
                    <p class="text-sm font-semibold text-base-content/50 uppercase">Pending Today</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex gap-2">
            <x-input wire:model.live.debounce.300ms="search" placeholder="Search members..." icon="o-magnifying-glass" class="input-sm w-64" />
            <x-select wire:model.live="filter" :options="[
                ['id' => 'all', 'name' => 'All Members'],
                ['id' => 'missed_today', 'name' => 'Missed Today'],
                ['id' => 'submitted_today', 'name' => 'Submitted Today'],
            ]" class="select-sm w-40" />
        </div>
        
        <x-button label="Remind All Pending" icon="o-bell" wire:click="remindAll" class="btn-primary btn-sm" />
    </div>

    {{-- Users List --}}
    <div class="bg-base-100 rounded-2xl border border-base-content/5 overflow-hidden">
        <table class="table w-full">
            <thead>
                <tr class="bg-base-200/50">
                    <th>Member</th>
                    <th>Today's Status</th>
                    <th>Current Streak</th>
                    <th>Longest Streak</th>
                    <th>Last Report</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="hover:bg-base-200/30">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar">
                                    <div class="w-8 h-8 rounded-full bg-primary/10">
                                        <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" />
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $user->name }}</div>
                                    <div class="text-xs text-base-content/50">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->daily_reports_count > 0)
                                <span class="badge badge-success badge-sm">Submitted</span>
                            @else
                                <span class="badge badge-warning badge-sm">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-1 font-bold">
                                <x-icon name="o-fire" class="w-4 h-4 text-orange-500" />
                                {{ $user->current_streak ?? 0 }}
                            </div>
                        </td>
                        <td>{{ $user->longest_streak ?? 0 }}</td>
                        <td class="text-sm text-base-content/70">
                            {{ $user->last_report_date ? Carbon\Carbon::parse($user->last_report_date)->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            @if($user->daily_reports_count == 0)
                                <x-button icon="o-bell" wire:click="remindUser({{ $user->id }})" class="btn-ghost btn-sm btn-circle text-primary" tooltip="Send Reminder" />
                            @endif
                            {{-- Button to view user's public reports (placeholder for future) --}}
                            <x-button icon="o-eye" class="btn-ghost btn-sm btn-circle" tooltip="View Reports" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="p-4 border-t border-base-content/5">
            {{ $users->links() }}
        </div>
    </div>
</div>
