<?php

use App\Models\User;
use App\Notifications\SimplePushNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Daily Reports Leaderboard')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public string $sortBy = 'current_streak'; // current_streak, longest_streak, total_reports

    // Recognition Modal properties
    public bool $showModal = false;
    public ?int $selectedUserId = null;
    public ?string $selectedUserName = null;
    public string $notificationMessage = '';

    public function mount()
    {
        $this->authorize('daily-reports.manage');
    }

    public function openRecognitionModal(int $userId, string $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->notificationMessage = __("Congratulations :name! Keep up the amazing consistency in your daily reports!", ['name' => $userName]);
        $this->showModal = true;
    }

    public function sendRecognition()
    {
        $this->validate([
            'notificationMessage' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $user->notify(new SimplePushNotification(
            __('🌟 Habit Recognition!'),
            $this->notificationMessage,
            route('web.my-report')
        ));

        $this->success(__('Congratulations message sent to :name!', ['name' => $user->name]));
        $this->showModal = false;
        $this->selectedUserId = null;
        $this->selectedUserName = null;
    }

    public function with(): array
    {
        $query = User::query()
            ->join('user_streaks', 'users.id', '=', 'user_streaks.user_id')
            ->select('users.*', 'user_streaks.current_streak', 'user_streaks.longest_streak', 'user_streaks.total_reports', 'user_streaks.last_report_date');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->search . '%')
                    ->orWhere('users.email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->sortBy === 'longest_streak') {
            $query->orderBy('user_streaks.longest_streak', 'desc');
        } elseif ($this->sortBy === 'total_reports') {
            $query->orderBy('user_streaks.total_reports', 'desc');
        } else {
            $query->orderBy('user_streaks.current_streak', 'desc');
        }

        return [
            'leaders' => $query->paginate(15),
        ];
    }
};
