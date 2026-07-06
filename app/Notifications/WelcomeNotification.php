<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $userName)
    {
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // Check user preferences
        $preference = $notifiable->getNotificationPreference('welcome');

        if ($preference->database_enabled) {
//            $channels[] = 'database';
        }

        if ($preference->email_enabled) {
            $channels[] = 'mail';
        }

        if ($preference->push_enabled && $notifiable->pushSubscriptions()->exists()) {
//            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting('Hello ' . $this->userName . '!')
            ->line('Welcome to ' . config('app.name') . '. We\'re excited to have you on board!')
            ->line('Here are some things you can do to get started:')
            ->line('• Complete your profile')
            ->line('• Explore the dashboard')
            ->line('• Enable push notifications for real-time updates')
            ->action('Go to Dashboard', route('app.dashboard'))
            ->line('Thank you for joining us!');
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome to ' . config('app.name') . '!',
            'message' => 'We\'re excited to have you, ' . $this->userName . '!',
            'action_url' => route('app.dashboard'),
            'action_text' => 'Get Started',
            'icon' => 'o-check-circle',
            'type' => 'success',
            'category' => 'welcome',
        ];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Welcome to ' . config('app.name') . '!')
            ->body('We\'re excited to have you, ' . $this->userName . '!')
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => route('app.dashboard'),
                'category' => 'welcome'
            ])
            ->tag('welcome-notification');
    }
}
