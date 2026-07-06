<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:send
                            {title : The notification title}
                            {body : The notification body}
                            {--user=* : User IDs to send to (optional, sends to all subscribed users if not specified)}
                            {--icon= : Custom icon URL}
                            {--badge= : Custom badge URL}
                            {--url= : URL to open when notification is clicked}
                            {--tag= : Notification tag for grouping}
                            {--require-interaction : Notification stays until user dismisses it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a push notification to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $title = $this->argument('title');
        $body = $this->argument('body');
        $userIds = $this->option('user');
        $icon = $this->option('icon');
        $badge = $this->option('badge');
        $url = $this->option('url');
        $tag = $this->option('tag');
        $requireInteraction = $this->option('require-interaction');

        // Get users to send to
        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)
                ->whereHas('pushSubscriptions')
                ->get();
        } else {
            $users = User::whereHas('pushSubscriptions')->get();
        }

        if ($users->isEmpty()) {
            $this->error('No users with push subscriptions found.');
            return self::FAILURE;
        }

        $this->info("Sending notification to {$users->count()} user(s)...");

        // Create notification data
        $data = [];
        if ($url) {
            $data['url'] = $url;
        }

        $notification = new WebPushNotification(
            title: $title,
            body: $body,
            icon: $icon,
            badge: $badge,
            data: $data,
            tag: $tag,
            requireInteraction: $requireInteraction
        );

        // Send notification
        Notification::send($users, $notification);

        $this->info('✓ Notification sent successfully!');

        return self::SUCCESS;
    }
}

