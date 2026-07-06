<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('Notifications')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast, WithPagination;

    // Active tab/section
    public $activeTab = 'center'; // center, preferences

    // Notification Center
    public $selectedFilter = 'all'; // all, unread, read
    public $unreadCount = 0;

    // Notification Preferences
    public array $preferences = [];
    public array $categories = [
        'chat' => [
            'name' => 'Chat Messages',
            'description' => 'New messages in conversations',
        ],
        'general' => [
            'name' => 'General',
            'description' => 'General notifications and updates',
        ],
        'welcome' => [
            'name' => 'Welcome',
            'description' => 'Welcome messages and onboarding',
        ],
        'mentions' => [
            'name' => 'Mentions',
            'description' => 'When someone mentions you',
        ],
        'system' => [
            'name' => 'System Alerts',
            'description' => 'System updates and important alerts',
        ],
        'messages' => [
            'name' => 'Messages',
            'description' => 'Direct messages and conversations',
        ],
        'updates' => [
            'name' => 'Updates',
            'description' => 'Product updates and announcements',
        ],
    ];

    public function mount(): void
    {
        $this->updateUnreadCount();
        $this->loadPreferences();
    }

    // ========== Notification Center Methods ==========

    public function updateUnreadCount(): void
    {
        $this->unreadCount = Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead($notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->updateUnreadCount();
            $this->success('Notification marked as read');
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->updateUnreadCount();
        $this->success('All notifications marked as read');
    }

    public function deleteNotification($notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->updateUnreadCount();
            $this->success('Notification deleted');
        }
    }

    public function deleteAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->updateUnreadCount();
        $this->success('All notifications deleted');
    }

    public function getNotifications()
    {
        $query = Auth::user()->notifications();

        if ($this->selectedFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->selectedFilter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->paginate(10);
    }

    // ========== Notification Preferences Methods ==========

    public function loadPreferences(): void
    {
        $user = Auth::user();

        foreach ($this->categories as $category => $details) {
            $preference = $user->notificationPreferences()
                ->where('category', $category)
                ->first();

            if (!$preference) {
                $preference = $user->notificationPreferences()->create([
                    'category' => $category,
                ]);
            }

            $this->preferences[$category] = [
                'push_enabled' => $preference->push_enabled,
                'email_enabled' => $preference->email_enabled,
                'database_enabled' => $preference->database_enabled,
            ];
        }
    }

    public function savePreferences(): void
    {
        $user = Auth::user();

        foreach ($this->preferences as $category => $settings) {
            $user->notificationPreferences()->updateOrCreate(
                ['category' => $category],
                [
                    'push_enabled' => $settings['push_enabled'] ?? true,
                    'email_enabled' => $settings['email_enabled'] ?? true,
                    'database_enabled' => $settings['database_enabled'] ?? true,
                ]
            );
        }

        $this->success('Notification preferences saved successfully!');
    }

    public function enableAll(): void
    {
        foreach ($this->preferences as $category => $settings) {
            $this->preferences[$category] = [
                'push_enabled' => true,
                'email_enabled' => true,
                'database_enabled' => true,
            ];
        }

        $this->savePreferences();
    }

    public function disableAll(): void
    {
        foreach ($this->preferences as $category => $settings) {
            $this->preferences[$category] = [
                'push_enabled' => false,
                'email_enabled' => false,
                'database_enabled' => false,
            ];
        }

        $this->savePreferences();
    }
    #[Computed]
    public function notifications()
    {
        return $this->activeTab === 'center' ? $this->getNotifications() : collect();
    }
};

