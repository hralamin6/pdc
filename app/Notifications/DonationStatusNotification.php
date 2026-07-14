<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Donation $donation;
    public string $status; // 'confirmed' or 'rejected'
    public ?string $note; // Rejection note

    /**
     * Create a new notification instance.
     */
    public function __construct(Donation $donation, string $status, ?string $note = null)
    {
        $this->donation = $donation;
        $this->status = $status;
        $this->note = $note;
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
        $mailMessage = (new MailMessage)
            ->subject($this->status === 'confirmed' ? __('Donation Confirmed!') : __('Donation Payment Unverified'));

        if ($this->status === 'confirmed') {
            $mailMessage->success()
                ->greeting(__('Assalamu Alaikum, :name!', ['name' => $notifiable->name]))
                ->line(__('We have successfully verified your donation of BDT :amount.', ['amount' => number_format($this->donation->amount, 2)]))
                ->line(__('Your transaction reference is: :txid', ['txid' => $this->donation->transaction_id ?: __('N/A')]))
                ->line(__('Thank you for supporting the community. May Allah accept your contribution!'))
                ->action(__('View Donation History'), route('web.my-donations'));
        } else {
            $mailMessage->error()
                ->greeting(__('Assalamu Alaikum, :name!', ['name' => $notifiable->name]))
                ->line(__('We were unable to verify your donation of BDT :amount.', ['amount' => number_format($this->donation->amount, 2)]));

            if ($this->note) {
                $mailMessage->line(__('Reason for rejection: :note', ['note' => $this->note]));
            }

            $mailMessage->line(__('Please review your transaction details or retry the payment verification.'))
                ->action(__('Verify Donation Again'), route('web.my-donations'));
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $isConfirmed = $this->status === 'confirmed';

        return [
            'title' => $isConfirmed ? __('Donation Confirmed!') : __('Donation Rejection Notice'),
            'message' => $isConfirmed 
                ? __('Your donation of BDT :amount was verified.', ['amount' => number_format($this->donation->amount)])
                : __('Your donation of BDT :amount could not be verified. Reason: :note', ['amount' => number_format($this->donation->amount), 'note' => $this->note ?: __('N/A')]),
            'action_url' => route('web.my-donations'),
            'action_text' => __('View History'),
            'icon' => $isConfirmed ? 'o-check-circle' : 'o-x-circle',
            'type' => $isConfirmed ? 'success' : 'error',
            'category' => 'donations',
        ];
    }
}
