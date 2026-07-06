# PWA and Web Push Notification Implementation Guide

## Overview

This guide covers the advanced PWA (Progressive Web App) and Web Push Notification implementation in your Laravel application using:
- [erag/laravel-pwa](https://github.com/eramitgupta/laravel-pwa) - PWA functionality
- [laravel-notification-channels/webpush](https://github.com/laravel-notification-channels/webpush) - Web Push notifications

## Features Implemented

### 🚀 PWA Features
- ✅ Service Worker with advanced caching strategies
- ✅ Offline support with custom offline page
- ✅ App installation prompt
- ✅ Dynamic manifest configuration
- ✅ Background sync capability
- ✅ Runtime caching for better performance
- ✅ Automatic service worker updates

### 🔔 Web Push Notification Features
- ✅ Push subscription management
- ✅ VAPID authentication
- ✅ Custom notification icons and badges
- ✅ Notification actions (interactive buttons)
- ✅ Vibration patterns
- ✅ Notification click handling
- ✅ Background notification handling
- ✅ Subscription persistence
- ✅ Permission management
- ✅ Test notification sending

## Setup Instructions

### 1. Generate VAPID Keys

First, generate your VAPID keys for web push notifications:

```bash
php artisan webpush:vapid
```

This will output something like:
```
VAPID_PUBLIC_KEY=BG...
VAPID_PRIVATE_KEY=...
```

Add these to your `.env` file:

```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:your-email@example.com
```

### 2. Build Assets

Compile the JavaScript assets to include the push notification manager:

```bash
npm install
npm run build
```

Or for development:

```bash
npm run dev
```

### 3. Clear Cache

Clear the application cache to ensure all changes take effect:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Update PWA Manifest

Update the PWA manifest with your app details:

```bash
php artisan erag:update-manifest
```

## Usage

### Accessing the Notifications Page

Navigate to `/app/notifications` to access the push notifications management page where you can:
- Subscribe/Unsubscribe from push notifications
- Send test notifications
- View subscription status
- Check service worker status

### Sending Notifications Programmatically

#### Basic Notification

```php
use App\Notifications\WebPushNotification;

$user = auth()->user();

$notification = new WebPushNotification(
    title: 'Hello!',
    body: 'This is a test notification',
);

$user->notify($notification);
```

#### Advanced Notification with Actions

```php
use App\Notifications\WebPushNotification;

$user = auth()->user();

$notification = new WebPushNotification(
    title: 'New Message',
    body: 'You have received a new message',
    icon: asset('logo.png'),
    badge: asset('logo.png'),
    data: ['url' => route('messages.show', $messageId)],
    actions: [
        ['title' => 'View', 'action' => 'view', 'icon' => '/logo.png'],
        ['title' => 'Dismiss', 'action' => 'dismiss', 'icon' => '/logo.png'],
    ],
    tag: 'message-' . $messageId,
    requireInteraction: true
);

$user->notify($notification);
```

### Sending to Multiple Users

```php
use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Support\Facades\Notification;

$users = User::whereHas('pushSubscriptions')->get();

$notification = new WebPushNotification(
    title: 'System Update',
    body: 'The system will be updated in 10 minutes',
    requireInteraction: true
);

Notification::send($users, $notification);
```

## JavaScript API

### PushNotificationManager Class

The `PushNotificationManager` class is automatically initialized and available globally as `window.pushManager`.

#### Methods

##### Initialize
```javascript
await window.pushManager.init();
```

##### Subscribe to Push Notifications
```javascript
await window.pushManager.subscribe();
```

##### Unsubscribe
```javascript
await window.pushManager.unsubscribe();
```

##### Check Subscription Status
```javascript
const subscription = await window.pushManager.checkSubscription();
console.log(subscription);
```

##### Request Permission
```javascript
const permission = await window.pushManager.requestPermission();
```

##### Show Test Notification
```javascript
await window.pushManager.showTestNotification('Test', {
    body: 'This is a test',
    icon: '/logo.png'
});
```

### Global Helper Functions

```javascript
// Subscribe
await subscribeToPush();

// Unsubscribe
await unsubscribeFromPush();

// Request permission
await requestNotificationPermission();

// Check subscription
await checkPushSubscription();
```

## Service Worker Features

### Caching Strategies

The service worker implements multiple caching strategies:

1. **Static Cache** - Essential files cached on install
2. **Runtime Cache** - Dynamic content cached on first access
3. **Network First** - Navigation requests with offline fallback

### Push Event Handling

The service worker automatically handles push events and displays notifications with:
- Custom icons and badges
- Vibration patterns
- Click actions
- Background processing

### Notification Actions

Users can interact with notifications through action buttons. Handle these in your Laravel routes:

```javascript
// In service worker (already implemented)
self.addEventListener('notificationclick', (event) => {
    // Opens the specified URL or focuses existing window
});
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/push/subscribe` | Subscribe to push notifications |
| POST | `/api/push/unsubscribe` | Unsubscribe from push notifications |
| GET | `/api/push/status` | Get subscription status |
| GET | `/api/push/vapid-key` | Get VAPID public key |

## Configuration

### PWA Configuration

Edit `config/pwa.php`:

```php
return [
    'install-button' => true,
    'manifest' => [
        'name' => 'Your App Name',
        'short_name' => 'App',
        'background_color' => '#6777ef',
        'display' => 'fullscreen',
        'description' => 'Your app description',
        'theme_color' => '#6777ef',
        'icons' => [
            [
                'src' => 'logo.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],
];
```

### WebPush Configuration

Edit `config/webpush.php`:

```php
return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
];
```

## Permissions Required

The User model must use the `HasPushSubscriptions` trait:

```php
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasPushSubscriptions;
}
```

## Testing

### Test Notification via UI
1. Navigate to `/app/notifications`
2. Click "Subscribe" button
3. Grant notification permission when prompted
4. Fill in the test notification form
5. Click "Send Test Notification"

### Test Notification via Tinker

```bash
php artisan tinker
```

```php
$user = User::find(1);

$notification = new \App\Notifications\WebPushNotification(
    'Test Title',
    'Test Body',
    asset('logo.png')
);

$user->notify($notification);
```

## Troubleshooting

### Notifications Not Working

1. **Check VAPID keys are set**
   ```bash
   php artisan config:clear
   ```

2. **Verify subscription**
   ```javascript
   await checkPushSubscription();
   ```

3. **Check browser console for errors**

4. **Ensure HTTPS** (required for push notifications, except localhost)

### Service Worker Not Updating

1. Unregister old service workers:
   ```javascript
   navigator.serviceWorker.getRegistrations().then(registrations => {
       registrations.forEach(r => r.unregister());
   });
   ```

2. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)

### Permission Denied

- Clear site data and try again
- Check browser notification settings
- Ensure user hasn't blocked notifications for your site

## Browser Support

- ✅ Chrome 42+
- ✅ Firefox 44+
- ✅ Edge 17+
- ✅ Opera 37+
- ✅ Safari 16+ (macOS 13+, iOS 16.4+)

## Security Considerations

1. **VAPID Keys**: Keep your private key secure, never expose it to the client
2. **HTTPS Required**: Push notifications only work on HTTPS (except localhost)
3. **Permission Management**: Always request permission gracefully
4. **Rate Limiting**: Consider rate limiting notification subscriptions

## Advanced Features

### Background Sync

The service worker supports background sync for offline actions:

```javascript
// Register a sync
navigator.serviceWorker.ready.then(registration => {
    return registration.sync.register('sync-notifications');
});
```

### Custom Notification Sounds

```php
$notification = new WebPushNotification(
    title: 'New Message',
    body: 'You have a new message',
    data: ['sound' => '/sounds/notification.mp3']
);
```

### Notification Grouping

```php
$notification = new WebPushNotification(
    title: 'New Messages',
    body: '5 new messages',
    tag: 'messages' // Same tag replaces previous notification
);
```

## Performance Optimization

1. **Cache Strategy**: The service worker uses intelligent caching
2. **Lazy Loading**: Push notification manager loads asynchronously
3. **Minimal Payload**: Notifications are optimized for size
4. **Background Processing**: Service worker handles notifications in background

## Next Steps

- Implement notification preferences per user
- Add notification history
- Create notification scheduling
- Implement notification categories
- Add rich media support (images, videos)
- Create notification analytics

## Resources

- [MDN Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Web App Manifest](https://developer.mozilla.org/en-US/docs/Web/Manifest)

