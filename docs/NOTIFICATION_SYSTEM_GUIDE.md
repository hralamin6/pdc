# Complete Notification System Documentation

## Overview

This comprehensive notification system supports **3 channels**:
- 🔔 **Push Notifications** - Browser push notifications with VAPID
- 📧 **Email Notifications** - HTML email messages
- 💾 **Database Notifications** - In-app notification center

## Features

### ✅ Implemented Features
- Multi-channel notification delivery (Push, Email, Database)
- User notification preferences per category
- Notification Center with unread tracking
- Queueable notifications for performance
- Multiple notification types (Welcome, Mentions, System Alerts)
- Example implementations and API endpoints
- CLI commands for testing and sending
- Automatic preference management

## Quick Start

### 1. Run Migrations
```bash
php artisan migrate
```

This creates:
- `notifications` table - stores database notifications
- `notification_preferences` table - stores user preferences per category

### 2. Set Up Mail Configuration

In your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Configure Queue (Recommended)

Notifications are queued for better performance:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## Notification Types

### 1. Welcome Notification
Sent to new users on registration.

**Channels:** Database + Email + Push

**Usage:**
```php
use App\Notifications\WelcomeNotification;

$user->notify(new WelcomeNotification($user->name));
```

### 2. User Mention Notification
Sent when a user is mentioned.

**Channels:** Database + Email + Push

**Usage:**
```php
use App\Notifications\UserMentionedNotification;

$user->notify(new UserMentionedNotification(
    mentionedBy: 'John Doe',
    content: 'Hey @username, check this out!',
    url: route('posts.show', $post->id)
));
```

### 3. System Alert Notification
System-wide alerts and announcements.

**Channels:** Database + Email (warnings/errors only) + Push

**Usage:**
```php
use App\Notifications\SystemAlertNotification;

$user->notify(new SystemAlertNotification(
    alertTitle: 'Maintenance Scheduled',
    alertMessage: 'System will be down tonight at 2 AM',
    alertType: 'warning', // info, warning, error, success
    actionUrl: route('app.dashboard'),
    actionText: 'Learn More'
));
```

### 4. Generic Web Push Notification
Simple push + database notification.

**Usage:**
```php
use App\Notifications\WebPushNotification;

$user->notify(new WebPushNotification(
    title: 'New Message',
    body: 'You have a new message!',
    icon: asset('logo.png'),
    badge: asset('logo.png'),
    data: ['url' => route('messages.show', $id)],
    actions: [
        ['title' => 'View', 'action' => 'view', 'icon' => '/logo.png'],
        ['title' => 'Dismiss', 'action' => 'dismiss', 'icon' => '/logo.png'],
    ],
    tag: 'message-' . $id,
    requireInteraction: true
));
```

## User Interface

### Pages Available

1. **Push Notifications** - `/app/notifications`
   - Subscribe/unsubscribe to push notifications
   - Send test notifications
   - View subscription status

2. **Notification Center** - `/app/notification-center`
   - View all notifications (database)
   - Mark as read/unread
   - Delete notifications
   - Filter by read status

3. **Notification Preferences** - `/app/notification-preferences`
   - Configure notification channels per category
   - Enable/disable push, email, or database per type
   - Bulk enable/disable all notifications

## CLI Commands

### Test Notifications
```bash
# Send all test notifications to user ID 1
php artisan notification:test 1

# Send only welcome notification
php artisan notification:test 1 --type=welcome

# Send only mention notification
php artisan notification:test 1 --type=mention

# Send only system alerts
php artisan notification:test 1 --type=system
```

### Send Push Notification
```bash
# Send to all subscribed users
php artisan push:send "Hello" "This is a message"

# Send to specific users
php artisan push:send "Alert" "Important message" --user=1 --user=2

# With URL and require interaction
php artisan push:send "Update" "New feature available" \
  --url=https://example.com/feature \
  --require-interaction \
  --tag=feature-update
```

## API Endpoints

### Push Subscription
```http
POST /api/push/subscribe
POST /api/push/unsubscribe
GET  /api/push/status
GET  /api/push/vapid-key
```

### Notification Management
```http
GET  /api/notifications/unread-count
POST /api/notifications/{id}/mark-read
POST /api/notifications/mark-all-read
```

### Send Notifications (Examples)
```http
POST /api/notifications/send-welcome
POST /api/notifications/send-mention
POST /api/notifications/send-system-alert
```

## Usage Examples

### Example 1: Send Welcome Email on Registration

```php
// In your registration controller or event listener
use App\Notifications\WelcomeNotification;

public function registered(User $user)
{
    $user->notify(new WelcomeNotification($user->name));
}
```

### Example 2: Notify User When Mentioned

```php
// In your comment/post creation logic
use App\Notifications\UserMentionedNotification;

public function createComment($postId, $content)
{
    // Extract mentioned users from content
    preg_match_all('/@(\w+)/', $content, $mentions);
    
    foreach ($mentions[1] as $username) {
        $user = User::where('username', $username)->first();
        
        if ($user) {
            $user->notify(new UserMentionedNotification(
                auth()->user()->name,
                $content,
                route('posts.show', $postId)
            ));
        }
    }
}
```

### Example 3: Send System Alert to All Users

```php
use App\Notifications\SystemAlertNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

$users = User::all();

Notification::send($users, new SystemAlertNotification(
    'Scheduled Maintenance',
    'The system will undergo maintenance tonight from 2-4 AM.',
    'warning',
    route('maintenance.info'),
    'View Details'
));
```

### Example 4: Using the PushNotificationService

```php
use App\Services\PushNotificationService;

$service = app(PushNotificationService::class);

// Send to one user
$service->sendToUser(
    $user,
    'New Feature!',
    'Check out our new feature in the dashboard',
    ['data' => ['url' => route('features.new')]]
);

// Send to all users
$service->sendToAll(
    'System Update',
    'We have released a new version with exciting features!'
);

// Send to specific role
$service->sendToRole(
    'admin',
    'Admin Alert',
    'New admin features available'
);

// Get statistics
$stats = $service->getStats();
// Returns: ['total_users', 'subscribed_users', 'subscription_rate', 'total_subscriptions']
```

## Notification Preferences System

### How It Works

Users can control which channels they receive notifications on for each category:

**Categories:**
- `general` - General notifications
- `welcome` - Welcome messages
- `mentions` - User mentions
- `system` - System alerts
- `messages` - Direct messages
- `updates` - Product updates

**Channels:**
- `push_enabled` - Browser push notifications
- `email_enabled` - Email notifications
- `database_enabled` - In-app notifications

### Programmatic Access

```php
// Get user's preference for a category
$preference = $user->getNotificationPreference('mentions');

// Check if push is enabled
if ($user->isPushEnabled('mentions')) {
    // Send push notification
}

// Update preferences
$user->notificationPreferences()->updateOrCreate(
    ['category' => 'mentions'],
    [
        'push_enabled' => true,
        'email_enabled' => false,
        'database_enabled' => true,
    ]
);
```

### Implementing Preferences in Custom Notifications

```php
public function via(object $notifiable): array
{
    $channels = [];
    $preference = $notifiable->getNotificationPreference('your_category');

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
```

## Creating Custom Notifications

### Step 1: Generate Notification Class

```bash
php artisan make:notification CustomNotification
```

### Step 2: Implement All Channels

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->getNotificationPreference('custom');

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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Subject')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your custom message')
            ->action('Action Button', url('/'))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Custom Notification',
            'message' => 'Your message here',
            'action_url' => url('/'),
            'action_text' => 'View',
            'icon' => 'o-bell',
            'type' => 'info',
            'category' => 'custom',
            'data' => $this->data,
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Custom Notification')
            ->body('Your message here')
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data(['url' => url('/')]);
    }
}
```

### Step 3: Add Category to Preferences

Update `NotificationPreferences` component to include your new category.

## Testing

### Manual Testing

1. **Test Email Notifications:**
```bash
php artisan tinker
```
```php
$user = User::find(1);
$user->notify(new \App\Notifications\WelcomeNotification($user->name));
```
Check your email inbox.

2. **Test Database Notifications:**
Visit `/app/notification-center` after sending a notification.

3. **Test Push Notifications:**
   - Visit `/app/notifications`
   - Click "Subscribe"
   - Send a test notification
   - Check browser for push notification

### Automated Testing

```bash
# Send all test notifications
php artisan notification:test 1
```

## Troubleshooting

### Emails Not Sending
- Check `.env` mail configuration
- Verify queue is running: `php artisan queue:work`
- Check logs: `storage/logs/laravel.log`
- Test mail config: `php artisan tinker` then `Mail::raw('Test', function($m) { $m->to('test@example.com'); });`

### Database Notifications Not Showing
- Run migrations: `php artisan migrate`
- Clear cache: `php artisan config:clear`
- Check notifications table exists

### Push Notifications Not Working
- Ensure HTTPS (required except localhost)
- Check VAPID keys are set
- Verify user is subscribed
- Check browser console for errors

## Best Practices

1. **Always Queue Notifications**
   - Implements `ShouldQueue` for better performance
   - Don't block user requests

2. **Respect User Preferences**
   - Always check preferences before sending
   - Provide granular control

3. **Use Appropriate Channels**
   - Email for important, detailed messages
   - Push for instant, brief alerts
   - Database for in-app history

4. **Provide Action URLs**
   - Link to relevant content
   - Make notifications actionable

5. **Use Descriptive Icons**
   - Match icon to notification type
   - Helps users quickly identify importance

## Performance Considerations

- Notifications are queued automatically (ShouldQueue)
- Use database queue for better reliability
- Consider using Redis for high-traffic apps
- Batch notifications when possible
- Clean up old notifications periodically

## Security

- VAPID keys must be kept secure
- Never expose private key to client
- Validate notification permissions
- Rate limit notification endpoints
- Sanitize user input in notifications

## Next Steps

- Implement notification scheduling
- Add notification analytics/tracking
- Create notification templates
- Add rich media support (images, videos)
- Implement notification grouping
- Add notification sounds
- Create notification digest emails

