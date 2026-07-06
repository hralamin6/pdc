<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SimplePushNotification extends Notification
{
    public $title;
    public $body;
    public $url;

    public function __construct($title, $body, $url = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url ?? route('app.dashboard');
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage())
            ->title($this->title)
            ->body($this->body)
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data(['url' => $this->url])
            ->tag('notification-' . time());
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->body,
            'action_url' => $this->url,
            'action_text' => 'View',
            'icon' => 'o-bell',
            'type' => 'info',
            'category' => 'general',
        ];
    }
}

