<?php

namespace App\Notifications;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ActivityNotification extends Notification
{
    use Queueable;

    protected Activity $activity;
    protected string $customMessage;

    public function __construct(Activity $activity, ?string $customMessage = null)
    {
        $this->activity = $activity;
        $this->customMessage = $customMessage ?? $this->activity->description;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Activity Alert')
            ->body($this->customMessage)
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => route('app.activity.feed'),
                'activity_id' => $this->activity->id,
            ])
            ->action('View Activity', 'view-activity');
    }

    public function toArray($notifiable): array
    {
        return [
            'activity_id' => $this->activity->id,
            'description' => $this->activity->description,
            'log_name' => $this->activity->log_name,
            'event' => $this->activity->event,
            'created_at' => $this->activity->created_at,
        ];
    }
}

