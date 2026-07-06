# Activity Management System

This guide explains how to use the comprehensive Activity Management System in your Laravel application.

## Features

- ✅ Automatic activity logging for model events (created, updated, deleted)
- ✅ Authentication activity tracking (login, logout, failed attempts)
- ✅ Custom activity logging
- ✅ Admin dashboard with statistics and charts
- ✅ User-specific activity timeline
- ✅ Activity feed with advanced filtering
- ✅ Web push notifications for important activities
- ✅ Automatic cleanup of old activities
- ✅ IP address and user agent tracking

## Components

### 1. Models & Migrations

- **Activity Model**: Core model for storing all activities
- **Migration**: Creates the `activities` table with all necessary fields

### 2. Traits

**LogsActivity Trait**: Add to any model to automatically log its changes

```php
use App\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;
    
    // Optional: Customize what to log
    protected $activityLogAttributes = ['title', 'content', 'status'];
    protected $activityLogName = 'posts';
}
```

### 3. Services

**ActivityLogger Service**: For manual activity logging

```php
use App\Services\ActivityLogger;

// Log custom activity
ActivityLogger::log('User performed action', $model, ['key' => 'value']);

// Pre-built methods
ActivityLogger::logLogin($user);
ActivityLogger::logLogout($user);
ActivityLogger::logPasswordChange($user);
ActivityLogger::logProfileUpdate($user, $changes);
ActivityLogger::logSystem('System maintenance started');
```

### 4. Livewire Components

- **ActivityDashboard**: Admin dashboard with statistics (`/app/activities/`)
- **ActivityFeed**: Complete activity feed with filters (`/app/activities/feed/`)
- **MyActivities**: User's personal activity log (`/app/activities/my/`)

### 5. Automatic Logging

The system automatically logs:
- ✅ User login/logout
- ✅ Failed login attempts
- ✅ Email verification
- ✅ Profile updates
- ✅ Any model with `LogsActivity` trait

### 6. Notifications

Send web push notifications for important activities:

```php
use App\Notifications\ActivityNotification;

$activity = ActivityLogger::log('Important event', $model);
$user->notify(new ActivityNotification($activity, 'Custom message'));
```

### 7. Filtering & Search

The Activity Feed supports:
- Search by description
- Filter by log type (authentication, profile, system, etc.)
- Filter by event (created, updated, deleted, etc.)
- Filter by date range
- Filter by user

### 8. Cleanup Command

Clean old activities automatically:

```bash
# Delete activities older than 90 days (default)
php artisan activities:clean

# Delete activities older than 30 days
php artisan activities:clean --days=30
```

Schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('activities:clean --days=90')->weekly();
}
```

## Usage Examples

### Logging Model Activities

```php
// Automatically logged when using the trait
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;
}

// Now all creates, updates, deletes are logged
$product = Product::create(['name' => 'New Product']);
```

### Manual Logging

```php
use App\Services\ActivityLogger;

// Log custom activity
ActivityLogger::log(
    'User downloaded report',
    $user,
    ['report_type' => 'sales', 'format' => 'pdf'],
    'reports',
    'download'
);
```

### Query Activities

```php
use App\Models\Activity;

// Get all activities by a user
$activities = Activity::causedBy($user)->get();

// Get activities for a specific model
$activities = Activity::forSubject($post)->get();

// Get recent activities
$activities = Activity::recent(10)->get();

// Filter by log name
$activities = Activity::inLog('authentication')->get();

// Filter by event
$activities = Activity::forEvent('created')->get();
```

### Access Activity Properties

```php
$activity = Activity::find(1);

// Get what changed
$changes = $activity->changes; // new values
$old = $activity->old; // old values

// Access relationships
$user = $activity->causer; // who did it
$subject = $activity->subject; // what was affected

// Metadata
$activity->ip_address;
$activity->user_agent;
$activity->created_at;
```

## Routes

- `/app/activities/` - Activity Dashboard (statistics)
- `/app/activities/feed/` - Complete Activity Feed
- `/app/activities/my/` - My Activities (personal timeline)

## Database Structure

```sql
activities
├── id
├── log_name (authentication, profile, system, etc.)
├── description (human-readable description)
├── subject_type (polymorphic - what was affected)
├── subject_id
├── causer_type (polymorphic - who did it)
├── causer_id
├── properties (JSON - changes, metadata)
├── event (created, updated, deleted, etc.)
├── ip_address
├── user_agent
└── timestamps
```

## Best Practices

1. **Use descriptive log names**: Group related activities together
2. **Clean old data regularly**: Use the cleanup command to prevent database bloat
3. **Customize logged attributes**: Only log what you need for better performance
4. **Add custom descriptions**: Override `getActivityDescription()` for better readability
5. **Use notifications wisely**: Only notify for critical activities to avoid spam

## Performance Tips

1. Add indexes for frequently queried fields (already included)
2. Use eager loading: `Activity::with(['causer', 'subject'])`
3. Paginate large result sets
4. Schedule regular cleanup of old activities
5. Consider archiving very old activities to separate storage

## Customization

### Custom Activity Description

```php
class Post extends Model
{
    use LogsActivity;
    
    protected function getActivityDescription(string $event): string
    {
        return "Post '{$this->title}' was {$event}";
    }
}
```

### Custom Log Name

```php
class Post extends Model
{
    use LogsActivity;
    
    protected $activityLogName = 'blog_posts';
}
```

### Selective Attribute Logging

```php
class User extends Model
{
    use LogsActivity;
    
    // Only log these attributes
    protected $activityLogAttributes = ['name', 'email'];
    
    // Or exclude sensitive data
    protected $activityLogAttributes = ['*'];
    protected $hidden = ['password', 'remember_token'];
}
```

## Integration with Existing System

The activity system integrates seamlessly with your existing:
- ✅ Notification system (Web Push)
- ✅ User authentication
- ✅ Role-based permissions
- ✅ Livewire components

Enjoy comprehensive activity tracking! 🎉

