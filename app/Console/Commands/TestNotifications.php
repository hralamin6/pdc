<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Notifications\UserMentionedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    protected $signature = 'notification:test
                            {user : User ID to send test notifications to}
                            {--type=all : Type of notification to test (all, welcome, mention, system)}';

    protected $description = 'Send test notifications to a user';

    public function handle(): int
    {
        $user = User::find($this->argument('user'));

        if (!$user) {
            $this->error('User not found!');
            return self::FAILURE;
        }

        $type = $this->option('type');

        $this->info("Sending test notifications to {$user->name} ({$user->email})...");

        if ($type === 'all' || $type === 'welcome') {
            $this->info('📧 Sending Welcome Notification...');
            $user->notify(new WelcomeNotification($user->name));
            $this->line('   ✓ Sent via: Database, Email, Push');
        }

        if ($type === 'all' || $type === 'mention') {
            $this->info('📧 Sending Mention Notification...');
            $user->notify(new UserMentionedNotification(
                'John Doe',
                'Hey @' . $user->name . ', check out this amazing feature we just built!',
                route('app.dashboard')
            ));
            $this->line('   ✓ Sent via: Database, Email, Push');
        }

        if ($type === 'all' || $type === 'system') {
            $this->info('📧 Sending System Alert Notifications...');

            // Info alert
            $user->notify(new SystemAlertNotification(
                'System Maintenance',
                'Scheduled maintenance will occur tonight at 2 AM.',
                'info',
                route('app.dashboard'),
                'Learn More'
            ));

            // Success alert
            $user->notify(new SystemAlertNotification(
                'Backups Completed',
                'Your data backup has been completed successfully.',
                'success'
            ));

            // Warning alert
            $user->notify(new SystemAlertNotification(
                'Storage Warning',
                'You are using 85% of your storage quota. Consider upgrading your plan.',
                'warning',
                route('app.settings')
            ));

            $this->line('   ✓ Sent 3 system alerts');
        }

        $this->newLine();
        $this->info('✅ All test notifications sent successfully!');
        $this->line('');
        $this->line('Check:');
        $this->line('  • Email inbox for email notifications');
        $this->line('  • Browser for push notifications');
        $this->line('  • /app/notification-center for database notifications');

        return self::SUCCESS;
    }
}

