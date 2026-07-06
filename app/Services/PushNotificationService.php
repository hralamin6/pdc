<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class PushNotificationService
{
    /**
     * Send a push notification to a single user
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $options = []
    ): bool {
        if (!$user->pushSubscriptions()->exists()) {
            return false;
        }

        $notification = $this->createNotification($title, $body, $options);
        $user->notify($notification);

        return true;
    }

    /**
     * Send a push notification to multiple users
     */
    public function sendToUsers(
        Collection $users,
        string $title,
        string $body,
        array $options = []
    ): int {
        $usersWithSubscriptions = $users->filter(function ($user) {
            return $user->pushSubscriptions()->exists();
        });

        if ($usersWithSubscriptions->isEmpty()) {
            return 0;
        }

        $notification = $this->createNotification($title, $body, $options);
        Notification::send($usersWithSubscriptions, $notification);

        return $usersWithSubscriptions->count();
    }

    /**
     * Send a push notification to all subscribed users
     */
    public function sendToAll(string $title, string $body, array $options = []): int
    {
        $users = User::whereHas('pushSubscriptions')->get();
        return $this->sendToUsers($users, $title, $body, $options);
    }

    /**
     * Send a push notification based on user role
     */
    public function sendToRole(
        string $role,
        string $title,
        string $body,
        array $options = []
    ): int {
        $users = User::role($role)->whereHas('pushSubscriptions')->get();
        return $this->sendToUsers($users, $title, $body, $options);
    }

    /**
     * Send a welcome notification to a new user
     */
    public function sendWelcomeNotification(User $user): bool
    {
        return $this->sendToUser(
            $user,
            'Welcome to ' . config('app.name') . '!',
            'Thank you for subscribing to our notifications. Stay tuned for updates!',
            [
                'icon' => asset('logo.png'),
                'badge' => asset('logo.png'),
                'data' => ['url' => route('app.dashboard')],
                'tag' => 'welcome',
            ]
        );
    }

    /**
     * Create a notification instance
     */
    protected function createNotification(
        string $title,
        string $body,
        array $options = []
    ): WebPushNotification {
        return new WebPushNotification(
            title: $title,
            body: $body,
            icon: $options['icon'] ?? asset('logo.png'),
            badge: $options['badge'] ?? asset('logo.png'),
            data: $options['data'] ?? [],
            actions: $options['actions'] ?? [],
            tag: $options['tag'] ?? null,
            requireInteraction: $options['requireInteraction'] ?? false
        );
    }

    /**
     * Get subscription statistics
     */
    public function getStats(): array
    {
        $totalUsers = User::count();
        $subscribedUsers = User::whereHas('pushSubscriptions')->count();
        $subscriptionRate = $totalUsers > 0 ? ($subscribedUsers / $totalUsers) * 100 : 0;

        return [
            'total_users' => $totalUsers,
            'subscribed_users' => $subscribedUsers,
            'subscription_rate' => round($subscriptionRate, 2),
            'total_subscriptions' => \NotificationChannels\WebPush\PushSubscription::count(),
        ];
    }

    /**
     * Clean up expired or invalid subscriptions
     */
    public function cleanupSubscriptions(): int
    {
        // This would require implementing logic to test each subscription
        // and remove those that return errors
        $deleted = 0;

        // Example: Remove subscriptions older than 1 year without activity
        $deleted = \NotificationChannels\WebPush\PushSubscription::where(
            'updated_at',
            '<',
            now()->subYear()
        )->delete();

        return $deleted;
    }
}

