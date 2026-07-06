# User Profile Web Component - Documentation

## Overview
A comprehensive, professional user profile page for the public-facing website. This component displays detailed user information, activity analytics, posts, and engagement metrics with modern design and interactive features.

## Files Created
1. `/resources/views/web/⚡user/user.php` - Livewire component logic
2. `/resources/views/web/⚡user/user.blade.php` - Component view
3. Route added to `/routes/web.php` as `web.user`

## Features

### 🎨 Visual Design
- **Hero Banner Section**: Large banner with user avatar overlay
- **Professional Profile Card**: Elevated card with avatar, name, email, roles, and stats
- **Gradient Accents**: Modern gradient backgrounds matching the site theme
- **Responsive Layout**: Optimized for mobile, tablet, and desktop devices
- **Dark Mode Support**: Full dark mode compatibility throughout
- **Smooth Animations**: Hover effects and transitions on all interactive elements

### 👤 Profile Information
- **Large Avatar Display**: Circular avatar with online status indicator
- **Banner Background**: Customizable banner or gradient fallback
- **User Details**: Name, email, and verification status
- **Role Badges**: All assigned roles displayed as styled badges
- **Member Since**: Join date and account age
- **Quick Actions**: Edit profile (for own profile) or Follow button

### 📊 Statistics Dashboard
Four prominent stat cards displaying:
1. **Total Posts**: Number of published posts
2. **Total Views**: Cumulative views across all posts
3. **Total Comments**: Comments received on all posts
4. **Average Views per Post**: Engagement metric

### 📑 Tabbed Content System
Three main tabs with full content:

#### 1. Posts Tab
- **Grid Layout**: Responsive post cards (3 columns on desktop)
- **Post Cards**: Each showing:
  - Featured image or emoji fallback
  - Category badge
  - Post title and excerpt
  - Publication date
  - View count
- **Pagination**: Built-in pagination for large post collections
- **Empty State**: Helpful message when no posts exist
- **Click to Read**: Entire card is clickable to view post

#### 2. Activity Tab
- **Timeline View**: Chronological list of user activities
- **Activity Items**: Each showing:
  - Activity icon (emoji)
  - Action title
  - Description/detail
  - Time since activity
  - Link to related content
- **Recent Activities**: Last 10 activities displayed
- **Empty State**: Message when no activity exists

#### 3. Analytics Tab
Comprehensive analytics dashboard with:

**a) Posts by Category**
- Visual progress bars showing post distribution
- Category names with post counts
- Total views per category
- Percentage-based width calculation
- Top 6 categories displayed

**b) Monthly Activity Chart**
- 6-month activity visualization
- Bar chart showing posts per month
- Hover effects on bars
- Responsive height based on max value
- Month labels at the bottom

**c) Top Performing Posts**
- Ranked list of top 5 posts by views
- Shows: rank number, post title, category, view count
- Clickable to view full post
- Visual ranking indicators

### 🔍 Key Computed Properties

#### `stats()`
Returns comprehensive statistics:
```php
[
    'total_posts' => int,
    'total_views' => int,
    'total_comments' => int,
    'avg_views_per_post' => int,
    'member_since_days' => int,
]
```

#### `postsByCategory()`
Analyzes post distribution:
```php
[
    ['name' => 'Category Name', 'count' => 5, 'views' => 1234],
    ...
]
```

#### `monthlyActivity()`
Last 6 months of posting activity:
```php
[
    ['month' => 'Jan', 'posts' => 3],
    ...
]
```

#### `recentActivities()`
Timeline of recent user actions:
```php
[
    [
        'type' => 'post',
        'title' => 'Published a post',
        'description' => 'Post Title',
        'date' => Carbon instance,
        'icon' => '📝',
        'link' => 'url',
    ],
    ...
]
```

## Usage

### Accessing User Profiles
- URL: `/users/{id}` (route name: `web.user`)
- Example: `/users/1` for user with ID 1
- Clickable from users list page

### Navigation Flow
```
Users List (/users) → Click User Card → User Profile (/users/1)
```

### Tab Switching
Users can switch between tabs:
- Posts (default): `wire:click="switchTab('posts')"`
- Activity: `wire:click="switchTab('activity')"`
- Analytics: `wire:click="switchTab('stats')"`

### State Management
- Active tab state managed via `$activeTab` property
- URL doesn't change when switching tabs
- Smooth transitions between tab content

## Technical Details

### Livewire Attributes
- `#[Layout('layouts.auth')]` - Uses auth layout
- `#[Computed]` - Cached computed properties for performance
- `WithPagination` - Pagination support for posts

### Database Queries
Optimized with:
- **Eager Loading**: `with(['roles', 'media'])` on user
- **Conditional Loading**: Only loads data for active tab
- **Pagination**: Posts are paginated (6 per page)
- **Sorting**: Latest posts first by default

### Performance Optimizations
1. **Computed Properties**: Stats cached until data changes
2. **Lazy Loading**: Tab content only loads when activated
3. **Eager Loading**: Prevents N+1 queries
4. **Limited Results**: Top contributors, top posts limited to prevent overload

## Analytics Details

### Post Analytics
- **Category Distribution**: Shows which topics user writes about most
- **Monthly Trends**: Identifies posting patterns and consistency
- **Top Performers**: Highlights most successful content

### Comment Analytics
- Aggregates comments across all user posts
- Shows engagement level
- Useful for identifying influential members

### View Metrics
- Total views shows reach
- Average views per post shows consistent quality
- Helps identify what content performs best

## Customization

### Changing Post Grid
In `user.blade.php`, modify grid classes:
```blade
<!-- Default: 3 columns on desktop -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

<!-- Change to 4 columns -->
<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
```

### Adding More Tabs
1. Add button to tab navigation:
```blade
<button wire:click="switchTab('new_tab')">New Tab</button>
```

2. Add content section:
```blade
@if($activeTab === 'new_tab')
  <div>
    <!-- Your content -->
  </div>
@endif
```

### Changing Statistics
Modify the `stats()` method in `user.php`:
```php
public function stats()
{
    return [
        'total_posts' => $this->user->posts()->count(),
        'new_metric' => // your calculation
    ];
}
```

### Custom Analytics
Add new computed properties:
```php
#[Computed]
public function customAnalytic()
{
    return $this->user->posts()
        // your query
        ->get();
}
```

## Integration Points

### User Model
Requires these relationships and attributes:
- `roles` - Spatie Permission relationship
- `media` - Spatie Media Library relationship
- `posts` - HasMany relationship to Post model
- `avatar_url` - Computed attribute
- `banner_url` - Computed attribute
- `created_at` - Timestamp
- `email_verified_at` - Timestamp (optional)

### Post Model
Requires:
- `category` - BelongsTo relationship
- `user` - BelongsTo relationship
- `views_count` - Integer field
- `published_at` - Timestamp
- `slug` - String for routing

## Security Considerations
- Public profile (no authentication required)
- Only shows published posts
- Email is visible (consider privacy settings)
- Edit profile only shown to profile owner
- No sensitive data exposed

## Future Enhancements

### Potential Features
1. **Social Integration**
   - Follow/Unfollow functionality
   - Follower/Following counts
   - Social media links

2. **Enhanced Analytics**
   - Engagement rate calculation
   - Growth charts
   - Comparative analytics

3. **User Bio/About**
   - Rich text bio section
   - Skills/interests tags
   - Location information

4. **Activity Feed**
   - Comments made by user
   - Likes/reactions given
   - Shares and bookmarks

5. **Achievements/Badges**
   - Milestone badges
   - Contribution awards
   - Special recognitions

6. **Privacy Controls**
   - Hide email option
   - Private profile setting
   - Activity visibility controls

7. **Export Options**
   - Export user data
   - Download analytics reports
   - Generate PDF profile

8. **Interactive Charts**
   - Use Chart.js or similar
   - Interactive tooltips
   - Zoom and filter options

## Testing Recommendations

### Manual Testing
- [ ] Profile loads with valid user ID
- [ ] 404 for invalid user ID
- [ ] All tabs switch correctly
- [ ] Posts display and paginate
- [ ] Activity timeline shows correctly
- [ ] Analytics charts render properly
- [ ] Empty states display when no data
- [ ] Edit profile link works for own profile
- [ ] Follow button works for other profiles
- [ ] Responsive design on mobile
- [ ] Dark mode displays correctly

### Automated Tests
```php
it('displays user profile', function () {
    $user = User::factory()->create();
    
    $this->get(route('web.user', $user->id))
        ->assertSeeLivewire('web::user')
        ->assertSee($user->name)
        ->assertSee($user->email);
});

it('shows user posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create([
        'published_at' => now(),
    ]);
    
    Livewire::test('web::user', ['id' => $user->id])
        ->assertSee($post->title);
});

it('calculates stats correctly', function () {
    $user = User::factory()->create();
    Post::factory()->count(5)->for($user)->create([
        'published_at' => now(),
        'views_count' => 100,
    ]);
    
    $component = Livewire::test('web::user', ['id' => $user->id]);
    
    expect($component->stats['total_posts'])->toBe(5);
    expect($component->stats['total_views'])->toBe(500);
});

it('switches tabs', function () {
    $user = User::factory()->create();
    
    Livewire::test('web::user', ['id' => $user->id])
        ->assertSet('activeTab', 'posts')
        ->call('switchTab', 'activity')
        ->assertSet('activeTab', 'activity');
});
```

## Common Issues

### Issue: No posts showing
**Solution**: Check that posts have `published_at` set and it's not in the future

### Issue: Analytics not calculating
**Solution**: Ensure relationships are properly defined on User model

### Issue: Images not loading
**Solution**: Verify Spatie Media Library is configured and media exists

### Issue: Slow loading
**Solution**: 
- Add database indexes on commonly queried fields
- Implement caching for expensive queries
- Reduce number of computed properties loaded at once

## Related Files
- `/app/Models/User.php` - User model
- `/app/Models/Post.php` - Post model
- `/resources/views/web/⚡users/users.php` - Users list component
- `/resources/views/layouts/auth.blade.php` - Layout file
- `/config/media-library.php` - Media configuration

## Conclusion
This user profile component provides a comprehensive, analytics-rich view of user activity and contributions. It follows modern web design principles, includes extensive analytics, and offers an engaging user experience while maintaining performance through optimized queries and caching.
