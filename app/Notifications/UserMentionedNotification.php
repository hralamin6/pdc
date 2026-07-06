<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class UserMentionedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $mentionedBy;
    protected $content;
    protected $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $mentionedBy, string $content, string $url)
    {
        $this->mentionedBy = $mentionedBy;
        $this->content = $content;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->getNotificationPreference('mentions');

        if ($preference->database_enabled) {
            $channels[] = 'database';
        }

        if ($preference->email_enabled) {
            $channels[] = 'mail';
        }

        if ($preference->push_enabled && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mentionedBy . ' mentioned you')
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line($this->mentionedBy . ' mentioned you in a post:')
            ->line('"' . \Str::limit($this->content, 150) . '"')
            ->action('View Post', $this->url)
            ->line('Click the button above to view the full post.');
    }

    /**
     * Get the array representation for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->mentionedBy . ' mentioned you',
            'message' => \Str::limit($this->content, 100),
            'action_url' => $this->url,
            'action_text' => 'View Post',
            'icon' => 'o-at-symbol',
            'type' => 'info',
            'category' => 'mentions',
            'data' => [
                'mentioned_by' => $this->mentionedBy,
                'content' => $this->content,
            ],
        ];
    }

    /**
     * Get the web push representation.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->mentionedBy . ' mentioned you')
            ->body(\Str::limit($this->content, 100))
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => $this->url,
                'category' => 'mentions'
            ])
            ->action('View', 'view', asset('logo.png'))
            ->action('Dismiss', 'dismiss', asset('logo.png'))
            ->tag('mention-' . time());
    }
}

