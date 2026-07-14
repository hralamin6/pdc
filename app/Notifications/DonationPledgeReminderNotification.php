<?php

namespace App\Notifications;

use App\Models\DonationPledge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationPledgeReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public DonationPledge $pledge;

    /**
     * Create a new notification instance.
     */
    public function __construct(DonationPledge $pledge)
    {
        $this->pledge = $pledge;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->getNotificationPreference('donations');

        if ($preference->database_enabled) {
            $channels[] = 'database';
                        $channels[] = 'broadcast';

        }

        if ($preference->email_enabled) {
            // $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Pledge Payment Reminder'))
            ->greeting(__('Assalamu Alaikum, :name!', ['name' => $notifiable->name]))
            ->line(__('This is a gentle reminder that your recurring donation pledge of BDT :amount is currently overdue.', [
                'amount' => number_format($this->pledge->amount, 2),
            ]))
            ->line(__('Frequency: :frequency', ['frequency' => __($this->pledge->frequency)]))
            ->line(__('Thank you for your ongoing support. May Allah reward your generosity!'))
            ->action(__('Process Payment'), route('web.my-donations'))
            ->line(__('If you have already paid this cycle, please ignore this email or update your transaction details.'));
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Pledge Payment Overdue!'),
            'message' => __('Your recurring donation pledge of BDT :amount (:frequency) is due.', [
                'amount' => number_format($this->pledge->amount),
                'frequency' => __($this->pledge->frequency)
            ]),
            'action_url' => route('web.my-donations'),
            'action_text' => __('Pay Now'),
            'icon' => 'o-clock',
            'type' => 'warning',
            'category' => 'donations',
        ];
    }
}
