<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Illuminate\Notifications\Messages\MailMessage;

class WebPushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $icon;
    protected $badge;
    protected $data;
    protected $actions;
    protected $tag;
    protected $requireInteraction;

    public function __construct(
        string $title,
        string $body,
        ?string $icon = null,
        ?string $badge = null,
        array $data = [],
        array $actions = [],
        ?string $tag = null,
        bool $requireInteraction = false
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->icon = $icon ?? asset('logo.png');
        $this->badge = $badge ?? asset('logo.png');
        $this->data = $data;
        $this->actions = $actions;
        $this->tag = $tag;
        $this->requireInteraction = $requireInteraction;
    }

    public function via($notifiable): array
    {
        $channels = [];

        // Always include web push
        $channels[] = WebPushChannel::class;

        // Add database channel
        $channels[] = 'database';

        return $channels;
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $message = (new WebPushMessage())
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon)
            ->badge($this->badge)
            ->data($this->data)
            ->vibrate([200, 100, 200]);

        if ($this->tag) {
            $message->tag($this->tag);
        }

        if ($this->requireInteraction) {
            $message->options(['requireInteraction' => true]);
        }

        foreach ($this->actions as $action) {
            $message->action(
                $action['title'],
                $action['action'],
                $action['icon'] ?? null
            );
        }

        return $message;
    }

    /**
     * Get the array representation for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->body,
            'action_url' => $this->data['url'] ?? null,
            'action_text' => 'View',
            'icon' => 'o-bell',
            'type' => 'info',
            'category' => 'general',
            'data' => $this->data,
        ];
    }
}
