<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DailyReportReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $customMessage = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Daily Report Reminder 📝',
            'message' => $this->customMessage ?? 'Don\'t forget to log your daily reflection and ibadah activities today!',
            'action_url' => route('web.my-report.fill'),
            'type' => 'daily_report_reminder',
            'icon' => 'o-pencil-square',
        ];
    }
}
