<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;
    protected $sendPush;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message, bool $sendPush = false)
    {
        $this->message = $message;
        $this->sendPush = $sendPush;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database']; // Always send database notification

        // Send push notification if:
        // 1. User is online (has active session)
        // 2. User has enabled push for chat
        if ($this->sendPush && $notifiable->isPushEnabledFor('chat')) {
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
            ->subject('New message from ' . $this->message->user->name)
            ->line($this->message->user->name . ' sent you a message.')
            ->line('"' . \Illuminate\Support\Str::limit($this->message->body, 100) . '"')
            ->action('View Message', route('app.chat', ['conversation' => $this->message->conversation_id]))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        $sender = $this->message->user;
        $hasAttachment = $this->message->hasAttachments();

        // Determine body text
        if ($hasAttachment && !$this->message->body) {
            $bodyText = '📎 Sent an attachment';
        } elseif ($hasAttachment && $this->message->body) {
            $bodyText = $this->message->body . ' 📎';
        } else {
            $bodyText = $this->message->body;
        }

        return (new WebPushMessage)
            ->title('💬 ' . $sender->name)
            ->icon($sender->avatar_url)
            ->body(\Illuminate\Support\Str::limit($bodyText, 100))
            ->badge('/notification-badge.png')
            ->tag('chat-' . $this->message->conversation_id) // Group notifications by conversation
            ->renotify(true) // Vibrate/sound even if notification is replaced
            ->requireInteraction(false) // Auto-dismiss after timeout
            ->action('Reply', 'reply')
            ->action('View', 'view')
            ->action('Mark as Read', 'mark_read')
            ->data([
                'url' => route('app.chat', ['conversation' => $this->message->conversation_id]),
                'conversation_id' => $this->message->conversation_id,
                'message_id' => $this->message->id,
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'sender_avatar' => $sender->avatar_url,
                'timestamp' => $this->message->created_at->toISOString(),
                'has_attachment' => $hasAttachment,
            ])
            ->options([
                'TTL' => 3600, // Notification expires after 1 hour
                'urgency' => 'high', // High priority
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->user_id,
            'sender_name' => $this->message->user->name,
            'sender_avatar' => $this->message->user->avatar_url,
            'body' => \Illuminate\Support\Str::limit($this->message->body ?? 'Sent an attachment', 100),
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
