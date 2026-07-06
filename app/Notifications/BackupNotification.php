<?php

namespace App\Notifications;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BackupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $backup;
    protected $status;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Backup $backup, string $status, ?string $errorMessage = null)
    {
        $this->backup = $backup;
        $this->status = $status;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {

        $channels = [];

        // Check user preferences
        $preference = $notifiable->getNotificationPreference('welcome');

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
        $mailMessage = (new MailMessage);

        if ($this->status === 'success') {
          return (new MailMessage)->subject('Backup Completed Successfully')
                ->success()
                ->greeting('Backup Completed!')
                ->line("Your backup '{$this->backup->name}' has been completed successfully.")
                ->line("Size: {$this->backup->formatted_file_size}")
                ->line("Type: " . ucfirst($this->backup->type))
                ->action('View Backups', route('app.backups'))
            ->line('Thank you for using our application!');
        } else {
          return (new MailMessage)->subject('Backup Failed')
                ->error()
                ->greeting('Backup Failed!')
                ->line("Your backup '{$this->backup->name}' has failed.")
                ->line("Error: " . ($this->errorMessage ?? 'Unknown error'))
                ->action('View Backups', route('app.backups'))
            ->line('Thank you for using our application!');
        }

    }

    /**
     * Get the array representation for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->status === 'success'
                ? 'Backup Completed Successfully'
                : 'Backup Failed',
            'message' => $this->status === 'success'
                ? "Backup '{$this->backup->name}' completed successfully. Size: {$this->backup->formatted_file_size}"
                : "Backup '{$this->backup->name}' failed. Error: " . ($this->errorMessage ?? 'Unknown error'),
            'action_url' => route('app.backups'),
            'action_text' => 'View Backups',
            'icon' => $this->status === 'success' ? 'o-check-circle' : 'o-x-circle',
            'type' => $this->status === 'success' ? 'success' : 'error',
            'category' => 'backup',
            'backup_id' => $this->backup->id,
            'backup_status' => $this->status,
        ];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $title = $this->status === 'success'
            ? 'Backup Completed'
            : 'Backup Failed';

        $body = $this->status === 'success'
            ? "'{$this->backup->name}' completed successfully"
            : "'{$this->backup->name}' failed";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/logo.png')
            ->body($body)
            ->data([
                'url' => route('app.backups'),
                'backup_id' => $this->backup->id,
            ])
            ->tag('backup-' . $this->backup->id);
    }
}

