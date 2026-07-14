<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BookNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $type; // request, accepted, given, received

    public string $senderName;

    public string $bookTitle;

    public string $url;

    public ?int $requestedDays;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, string $senderName, string $bookTitle, string $url, ?int $requestedDays = null)
    {
        $this->type = $type;
        $this->senderName = $senderName;
        $this->bookTitle = $bookTitle;
        $this->url = $url;
        $this->requestedDays = $requestedDays;
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
            $channels[] = 'broadcast'; // Real-time notification broadcast via Reverb
        }

        if ($preference->email_enabled) {
            // $channels[] = 'mail';
        }

        if ($preference->push_enabled) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = new MailMessage;

        switch ($this->type) {
            case 'request':
                return $mail
                    ->subject('New Book Borrow Request')
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line($this->senderName.' wants to borrow your book: "'.$this->bookTitle.'" for '.($this->requestedDays ?? 7).' days.')
                    ->action('View Request', $this->url)
                    ->line('Click the button above to manage this request.');

            case 'accepted':
                return $mail
                    ->subject('Borrow Request Accepted')
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('Great news! '.$this->senderName.' has accepted your request to borrow "'.$this->bookTitle.'".')
                    ->line('Please coordinate with them to collect the physical book.')
                    ->action('View Requests', $this->url)
                    ->line('Click the button above to view and track your borrow requests.');

            case 'given':
                return $mail
                    ->subject('Book Handed Over')
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line($this->senderName.' has marked the book "'.$this->bookTitle.'" as handed over to you.')
                    ->line('Please confirm receipt once you have received the book.')
                    ->action('Confirm Receipt', $this->url)
                    ->line('Click the button above to manage and confirm your borrow requests.');

            case 'received':
                return $mail
                    ->subject('Book Receipt Confirmed')
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line($this->senderName.' has confirmed receipt of the book "'.$this->bookTitle.'".')
                    ->line('The borrowing period has now officially started.')
                    ->action('View Shelf', $this->url)
                    ->line('Click the button above to manage your bookshelf.');

            case 'reminder':
                return $mail
                    ->subject('Return Reminder: '.$this->bookTitle)
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('This is a friendly reminder from '.$this->senderName.' to return the book "'.$this->bookTitle.'".')
                    ->action('View Requests', $this->url)
                    ->line('Please return the physical book to the owner.');

            case 'reminder_tomorrow':
                return $mail
                    ->subject('Due Tomorrow: '.$this->bookTitle)
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('Friendly reminder: Your borrow period for "'.$this->bookTitle.'" expires tomorrow.')
                    ->action('View Requests', $this->url)
                    ->line('Please arrange to return the book to '.$this->senderName.'.');

            case 'reminder_today':
                return $mail
                    ->subject('Due Today: '.$this->bookTitle)
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('Friendly reminder: Your borrow period for "'.$this->bookTitle.'" is due today.')
                    ->action('View Requests', $this->url)
                    ->line('Please return the book to '.$this->senderName.'.');

            case 'reminder_overdue':
                return $mail
                    ->subject('Overdue: '.$this->bookTitle)
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('Urgent reminder: Your borrow period for "'.$this->bookTitle.'" was due yesterday.')
                    ->action('View Requests', $this->url)
                    ->line('Please return the book to '.$this->senderName.' immediately.');

            default:
                return $mail
                    ->subject('Library Notification')
                    ->greeting('Hi '.$notifiable->name.'!')
                    ->line('You have a new library notification regarding "'.$this->bookTitle.'".')
                    ->action('View Library', $this->url);
        }
    }

    /**
     * Get the array representation for database.
     */
    public function toArray(object $notifiable): array
    {
        $title = 'Library Notification';
        $message = '';
        $icon = 'o-bell';
        $style = 'info';

        switch ($this->type) {
            case 'request':
                $title = 'New Borrow Request';
                $message = $this->senderName.' requested to borrow "'.$this->bookTitle.'"';
                $icon = 'o-book-open';
                $style = 'info';
                break;
            case 'accepted':
                $title = 'Borrow Request Accepted';
                $message = $this->senderName.' accepted your request to borrow "'.$this->bookTitle.'"';
                $icon = 'o-check-circle';
                $style = 'success';
                break;
            case 'given':
                $title = 'Book Handed Over';
                $message = $this->senderName.' marked "'.$this->bookTitle.'" as handed over. Please confirm receipt.';
                $icon = 'o-hand-raised';
                $style = 'info';
                break;
            case 'received':
                $title = 'Book Receipt Confirmed';
                $message = $this->senderName.' confirmed receipt of "'.$this->bookTitle.'"';
                $icon = 'o-check';
                $style = 'success';
                break;
            case 'reminder':
                $title = 'Return Reminder';
                $message = $this->senderName.' sent a reminder to return "'.$this->bookTitle.'"';
                $icon = 'o-bell';
                $style = 'warning';
                break;
            case 'reminder_tomorrow':
                $title = 'Return Reminder (Due Tomorrow)';
                $message = '"'.$this->bookTitle.'" is due tomorrow. Please return it to '.$this->senderName.'.';
                $icon = 'o-clock';
                $style = 'info';
                break;
            case 'reminder_today':
                $title = 'Return Reminder (Due Today)';
                $message = '"'.$this->bookTitle.'" is due today. Please return it to '.$this->senderName.'.';
                $icon = 'o-exclamation-triangle';
                $style = 'warning';
                break;
            case 'reminder_overdue':
                $title = 'Overdue Reminder';
                $message = '"'.$this->bookTitle.'" is overdue. Please return it to '.$this->senderName.' immediately.';
                $icon = 'o-no-symbol';
                $style = 'error';
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'action_url' => $this->url,
            'action_text' => 'View Details',
            'icon' => $icon,
            'type' => $style,
            'category' => 'mentions',
            'data' => [
                'type' => $this->type,
                'sender_name' => $this->senderName,
                'book_title' => $this->bookTitle,
                'requested_days' => $this->requestedDays,
            ],
        ];
    }

    /**
     * Get the web push representation.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $title = 'Library Notification';
        $body = '';

        switch ($this->type) {
            case 'request':
                $title = 'New Borrow Request';
                $body = $this->senderName.' requested to borrow "'.$this->bookTitle.'"';
                break;
            case 'accepted':
                $title = 'Borrow Request Accepted';
                $body = $this->senderName.' accepted your request to borrow "'.$this->bookTitle.'"';
                break;
            case 'given':
                $title = 'Book Handed Over';
                $body = $this->senderName.' marked "'.$this->bookTitle.'" as handed over';
                break;
            case 'received':
                $title = 'Book Receipt Confirmed';
                $body = $this->senderName.' confirmed receipt of "'.$this->bookTitle.'"';
                break;
            case 'reminder':
                $title = 'Return Reminder';
                $body = $this->senderName.' requested you return "'.$this->bookTitle.'"';
                break;
            case 'reminder_tomorrow':
                $title = 'Due Tomorrow: '.$this->bookTitle;
                $body = '"'.$this->bookTitle.'" is due tomorrow. Please return to '.$this->senderName;
                break;
            case 'reminder_today':
                $title = 'Due Today: '.$this->bookTitle;
                $body = '"'.$this->bookTitle.'" is due today. Please return to '.$this->senderName;
                break;
            case 'reminder_overdue':
                $title = 'Overdue: '.$this->bookTitle;
                $body = '"'.$this->bookTitle.'" is overdue. Return to '.$this->senderName.' immediately';
                break;
        }

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => $this->url,
                'category' => 'mentions',
            ])
            ->action('View', 'view', asset('logo.png'))
            ->action('Dismiss', 'dismiss', asset('logo.png'))
            ->tag('book-notification-'.$this->type.'-'.time());
    }
}
