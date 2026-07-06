<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alertTitle;
    protected $alertMessage;
    protected $alertType; // info, warning, error, success
    protected $actionUrl;
    protected $actionText;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $alertTitle,
        string $alertMessage,
        string $alertType = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null
    ) {
        $this->alertTitle = $alertTitle;
        $this->alertMessage = $alertMessage;
        $this->alertType = $alertType;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText ?? 'View Details';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->getNotificationPreference('system');

        if ($preference->database_enabled) {
            $channels[] = 'database';
        }

        if ($preference->email_enabled && in_array($this->alertType, ['warning', 'error'])) {
            // Only send emails for warnings and errors
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
        $mailMessage = (new MailMessage)
            ->subject($this->alertTitle);

        if ($this->alertType === 'error') {
            $mailMessage->error();
        } elseif ($this->alertType === 'success') {
            $mailMessage->success();
        }

        $mailMessage
            ->greeting('System Alert')
            ->line($this->alertMessage);

        if ($this->actionUrl) {
            $mailMessage->action($this->actionText, $this->actionUrl);
        }

        return $mailMessage->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->alertTitle,
            'message' => $this->alertMessage,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'icon' => $this->getIcon(),
            'type' => $this->alertType,
            'category' => 'system',
        ];
    }

    /**
     * Get the web push representation.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->alertTitle)
            ->body($this->alertMessage)
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => $this->actionUrl ?? route('app.dashboard'),
                'category' => 'system',
                'type' => $this->alertType
            ])
            ->options(['requireInteraction' => in_array($this->alertType, ['warning', 'error'])])
            ->tag('system-alert-' . time());
    }

    /**
     * Get icon based on alert type.
     */
    protected function getIcon(): string
    {
        return match ($this->alertType) {
            'error' => 'o-exclamation-circle',
            'warning' => 'o-exclamation-triangle',
            'success' => 'o-check-circle',
            default => 'o-information-circle',
        };
    }
}

