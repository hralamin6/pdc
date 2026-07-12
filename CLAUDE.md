# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TallKit is a modern Laravel 12 application built with the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire). It's a comprehensive platform with features including:
- User management and authentication
- Book library and borrowing system
- Financial tracking (expenses, donations, fund transfers)
- AI-powered features and chat
- Daily reports and activity logging
- Media management and galleries
- Quiz system with AI integration
- Real-time notifications and messaging

## Architecture Overview

### Backend (Laravel 12)

**Core Structure:**
- **app/Models/** - Eloquent models with relationships and casts
- **app/Http/Controllers/** - RESTful controllers for web routes
- **app/Http/Middleware/** - Global and route middleware (registered in bootstrap/app.php)
- **app/Services/** - Business logic services (ActivityLogger, AI services, TranslationService, etc.)
- **app/Notifications/** - Notification classes for various events
- **app/Events/** - Event classes for domain events
- **app/Listeners/** - Event listeners
- **app/Jobs/** - Queueable jobs
- **app/Providers/** - Service providers (bootstrapped via bootstrap/providers.php)
- **app/Ai/** - AI-specific logic and services
- **app/BotBook/** - Bot-related functionality
- **app/Livewire/** - Livewire components (organized by feature)

**Key Models:**
- `User` - Core user model with permissions (spatie/laravel-permission)
- `Book`, `BookCopy`, `BorrowRequest` - Library system
- `Post`, `Comment` - Content management
- `Expense`, `Donation`, `FundTransfer` - Financial tracking
- `Halaqah`, `Quiz`, `Activity` - Various domain entities
- `Message`, `Conversation` - Messaging system

**Services:**
- `ActivityLogger` - Tracks user activities across the system
- `PushNotificationService` - Handles web push notifications
- `TranslationService` - Manages multi-language content
- `QuizAiService`, `QuizPointsService` - Quiz system services
- AI-related services in `app/Services/AI/` and `app/Services/BotBook/`

### Frontend

**TALL Stack:**
- **Tailwind CSS v4** - Utility-first CSS framework
- **Alpine.js** - Minimal JavaScript framework for interactivity
- **Livewire v4** - Full-stack framework for Laravel (Blade-based)
- **Livewire Blaze** - Optional Livewire companion

**Build System:**
- **Vite** - Frontend build tool with Laravel plugin
- **PostCSS** - CSS processing (via Tailwind v4)
- **Concurrently** - Running multiple dev servers simultaneously

**Asset Structure:**
- **resources/css/app.css** - Main CSS entry point (Tailwind directives)
- **resources/js/app.js** - Main JavaScript entry point
- **resources/views/** - Blade templates organized hierarchically:
  - `layouts/` - Base layouts (app.blade.php, base.blade.php)
  - `components/` - Reusable Blade components
  - `app/` - Application-specific views (organized by feature with ⚡ prefix)
  - `web/` - Web-specific views
  - `livewire/` - Livewire component views
  - `vendor/` - Third-party package views

### Routing

**Web Routes:**
- Defined in `routes/web.php`
- Uses Laravel's routing system with named routes
- Mix of traditional controller routes and Livewire components

**API Routes:**
- Not explicitly separated; follows RESTful conventions within web routes

**Console Routes:**
- Defined in `routes/console.php`
- Uses Laravel 12's streamlined console configuration

### Configuration

**Key Config Files:**
- `config/app.php` - Application settings
- `config/services.php` - Third-party service credentials
- `config/permission.php` - Spatie permissions configuration
- `config/media-library.php` - Media library settings
- `config/pwa.php` - PWA configuration
- `config/webpush.php` - Web push notifications
- `config/broadcasting.php` - Reverb (WebSocket) configuration
- `config/pulse.php` - Laravel Pulse monitoring
- `config/queue.php` - Queue configuration

### Database & Storage

**Migrations:**
- Standard Laravel migrations in `database/migrations/`
- Follows Laravel 12 conventions

**Models:**
- Use Eloquent ORM with proper relationships
- Casts defined in `casts()` method where applicable
- Factories in `database/factories/`
- Seeders in `database/seeders/`

**Storage:**
- Uses Laravel's storage system with `spatie/laravel-medialibrary` for file handling
- Media conversions and responsive images configured

### Authentication & Authorization

- **Laravel Sanctum** - For API authentication
- **spatie/laravel-permission** - Role-based permissions
- **laravel/socialite** - OAuth providers
- Policies and gates defined in `app/Policies/`

### Real-time Features

- **Laravel Reverb** - WebSocket server for real-time communication
- **Pusher PHP SDK** - Pusher integration
- **Laravel Echo** - Frontend real-time library
- Events broadcast via Laravel's event system

### Testing

**Testing Framework:**
- **Pest PHP v4** - Primary testing framework
- **PHPUnit** - Backend testing foundation
- **Laravel Pest Plugin** - Laravel-specific testing utilities

**Test Structure:**
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests
- `tests/Browser/` - Browser tests (Pest v4)

**Test Commands:**
- `php artisan test` - Run all tests
- `php artisan test --filter=TestName` - Run specific test
- `php artisan test tests/Feature/ExampleTest.php` - Run specific file

### Queues

- Uses Laravel's queue system for background jobs
- Jobs implement `ShouldQueue` interface for async processing
- Queue workers should be running in production

### DevOps & Deployment

**Development Tools:**
- **Laravel Sail** - Docker-based local development
- **Laravel Pail** - Log streaming for debugging
- **Laravel Boost** - MCP server for Laravel-specific tools

**Deployment:**
- `deploy.sh` - Basic deployment script
- Uses standard Laravel deployment practices
- Environment variables configured in `.env`

## Development Commands

### Setup & Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build

# Or for development
npm run dev

# Alternative dev command (runs multiple servers)
composer run dev
```

### Running the Application

```bash
# Serve the application
php artisan serve

# Run queue worker (for background jobs)
php artisan queue:work

# Or listen for jobs with retry
php artisan queue:listen --tries=1

# Run the development server with queue and logs
composer run dev
```

### Code Quality & Formatting

```bash
# Run Pint (code formatter)
vendor/bin/pint

# Check formatting without fixing
vendor/bin/pint --test

# Run tests
php artisan test

# Run specific test
php artisan test --filter=testName

# Run all tests in a file
php artisan test tests/Feature/ExampleTest.php

# Run browser tests
php artisan test tests/Browser/
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Refresh database (migrate:fresh + seed)
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m

# Create model with migration and factory
php artisan make:model ModelName -mf

# Create seeder
php artisan make:seeder SeederName

# Run seeders
php artisan db:seed
php artisan db:seed --class=SpecificSeeder
```

### Cache Operations

```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan optimize:clear
```

### Asset Management

```bash
# Build production assets
npm run build

# Start development server
npm run dev

# Watch for changes
npm run dev

# Build and watch (alternative)
npm run watch
```

### Livewire Operations

```bash
# Create Livewire component
php artisan make:livewire ComponentName

# Create Livewire component in a namespace
php artisan make:livewire Posts/CreatePost

# Test Livewire component
php artisan test --filter=ComponentTest
```

### Model Operations

```bash
# Create model
php artisan make:model ModelName

# Create model with migration
php artisan make:model ModelName -m

# Create model with migration, factory, and seeder
php artisan make:model ModelName -mfs

# Create factory
php artisan make:factory ModelNameFactory --model=ModelName

# Create seeder
php artisan make:seeder ModelNameSeeder

# Create policy
php artisan make:policy ModelNamePolicy --model=ModelName
```

### Testing Specific Scenarios

```bash
# Run tests with coverage
php artisan test --coverage

# Run tests with parallel processing
php artisan test --parallel

# Run tests in a specific directory
php artisan test tests/Feature/

# Run browser tests
php artisan test tests/Browser/

# Run pest tests
./vendor/bin/pest
```

### Queue Operations

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with timeout
php artisan queue:work --timeout=60

# Listen for jobs (auto-restart)
php artisan queue:listen

# Process failed jobs
php artisan queue:retry all
php artisan queue:forget <job-id>
```

### Maintenance Mode

```bash
# Enable maintenance mode
php artisan down

# Enable maintenance mode with message
php artisan down --message="We'll be back soon!"

# Disable maintenance mode
php artisan up
```

### IDE Helper

```bash
# Generate IDE helper files
composer run post-autoload-dump

# Generate Laravel IDE helper
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models
```

### Package Discovery

```bash
# Discover packages
php artisan package:discover
```

## Development Workflow

### Creating New Features

1. **Identify the domain area** (e.g., books, expenses, users)
2. **Create necessary models** with relationships:
   ```bash
   php artisan make:model Book -mf
   ```
3. **Create controllers** for RESTful endpoints:
   ```bash
   php artisan make:controller BookController --resource
   ```
4. **Create Livewire components** for interactive UI:
   ```bash
   php artisan make:livewire Books/BookList
   php artisan make:livewire Books/BookForm
   ```
5. **Create policies** for authorization:
   ```bash
   php artisan make:policy BookPolicy --model=Book
   ```
6. **Create form requests** for validation:
   ```bash
   php artisan make:request StoreBookRequest
   ```
7. **Create tests** for the feature:
   ```bash
   php artisan make:test Feature/BookTest --pest
   ```
8. **Create views** in the appropriate directory
9. **Update routes** in `routes/web.php`
10. **Run Pint** to format code:
    ```bash
    vendor/bin/pint
    ```
11. **Run tests** to ensure everything works:
    ```bash
    php artisan test
    ```

### Working with Livewire

1. **Component Structure:**
   - PHP class in `app/Livewire/`
   - Blade view in `resources/views/livewire/`
   - Follows Laravel Livewire v4 conventions

2. **Best Practices:**
   - Use `wire:key` in loops
   - Implement lifecycle hooks (`mount()`, `updatedFoo()`)
   - Validate input in component methods
   - Use proper authorization checks
   - Keep business logic in services when complex

3. **Testing Livewire:**
   ```php
   Livewire::test(BookList::class)
       ->assertSee('Books')
       ->assertCount('books', 5);
   ```

### Working with AI Services

The application has extensive AI integration:

- **app/Services/AI/** - Core AI services
- **app/Services/BotBook/** - Bot-related AI functionality
- **app/Ai/** - AI-specific logic

Key services:
- `QuizAiService` - Generates quiz questions and content
- AI-powered chat and conversation systems
- Content generation and summarization

**Important:** Always check existing AI documentation files:
- `AGENTS.md` - AI agent system
- `BOTBOOK_FINAL.md` - BotBook system
- `POST_GENERATION.md` - Post generation with AI
- `CHAT_SYSTEM_GUIDE.md` - Chat system documentation

### Working with Permissions

The application uses `spatie/laravel-permission`:

1. **Assign roles to users:**
   ```php
   $user->assignRole('admin');
   ```

2. **Check permissions:**
   ```php
   $user->can('edit books');
   auth()->user()->hasRole('admin');
   ```

3. **Create permissions:**
   ```bash
   # Permissions are typically created via seeders or during setup
   ```

4. **Middleware:**
   - Use `can:` middleware for route protection
   - Example: `Route::get('/admin', fn() => ...)->middleware('can:access admin');`

### Working with Media Library

Uses `spatie/laravel-medialibrary`:

```php
// Add media to a model
$user->addMediaFromRequest('avatar')->toMediaCollection('avatars');

// Get media URLs
$user->getFirstMediaUrl('avatars');

// Responsive images
$media->getAvailableUrl(['responsive']);
```

### Working with Notifications

```php
# Create notification
php artisan make:notification BookBorrowedNotification

# Send notification
$user->notify(new BookBorrowedNotification($book));

# Mark as read
notification()->markAsRead();
```

### Working with Events & Listeners

```bash
# Create event
php artisan make:event BookBorrowed

# Create listener
php artisan make:listener SendBookBorrowedNotification --event=BookBorrowed

# Dispatch event
event(new BookBorrowed($book, $user));
```

## Common Development Patterns

### Form Request Validation

Always use form requests for validation:

```bash
php artisan make:request StoreBookRequest
```

Example:
```php
// app/Http/Requests/StoreBookRequest.php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'isbn' => 'nullable|string|unique:books',
    ];
}
```

### API Resources

For JSON responses, use Eloquent API Resources:

```bash
php artisan make:resource BookResource
```

### Service Classes

For complex business logic, create service classes:

```bash
php artisan make:class ActivityLogger
```

Example:
```php
// app/Services/ActivityLogger.php
class ActivityLogger
{
    public function logBookBorrowed(User $user, Book $book)
    {
        Activity::create([
            'user_id' => $user->id,
            'action' => 'book_borrowed',
            'model_type' => Book::class,
            'model_id' => $book->id,
        ]);
    }
}
```

### Repository Pattern (if needed)

For complex data access patterns, create repositories:

```bash
php artisan make:class BookRepository
```

### Testing Patterns

**Feature Tests:**
```php
it('can borrow a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $this->actingAs($user)
        ->post(route('books.borrow', $book))
        ->assertRedirect()
        ->assertSessionHas('success');
});
```

**Unit Tests:**
```php
it('calculates total correctly', function () {
    $expense = Expense::factory()->create(['amount' => 100]);
    
    expect($expense->total)->toBe(100);
});
```

**Browser Tests:**
```php
it('can login', function () {
    $user = User::factory()->create();
    
    $page = visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Login')
        ->assertPath('/dashboard');
});
```

## Environment Configuration

### Key Environment Variables

```env
# Application
APP_NAME=TallKit
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tallkit
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file

# Queue
QUEUE_CONNECTION=database

# Broadcasting (Reverb)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# PWA
PWA_NAME="TallKit"
PWA_SHORT_NAME="TallKit"
PWA_COLOR="#ffffff"
PWA_ICON=/images/logo.png

# Web Push
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=

# AI Services
OPENAI_API_KEY=
ANTHROPIC_API_KEY=

# Socialite (OAuth)
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

### Database Setup

```bash
# Create database (MySQL example)
CREATE DATABASE tallkit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Run migrations
php artisan migrate

# Seed database (if needed)
php artisan db:seed
```

## Troubleshooting

### Vite/Asset Issues

**Problem:** Styles or JavaScript not loading

**Solution:**
```bash
# Rebuild assets
npm run build

# Or for development
npm run dev
```

### Queue Worker Issues

**Problem:** Jobs not processing

**Solution:**
```bash
# Check queue status
php artisan queue:work --once

# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed
```

### Database Connection Issues

**Problem:** Cannot connect to database

**Solution:**
```bash
# Check .env configuration
# Verify database server is running
# Test connection
mysql -u root -p
```

### Permission Issues

**Problem:** Storage or cache permissions

**Solution:**
```bash
# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Or use Laravel's built-in command
php artisan storage:link
```

### Livewire Issues

**Problem:** Livewire component not working

**Solution:**
```bash
# Check component registration
# Verify view file exists
# Check browser console for errors
# Ensure proper wire:key usage
```

### Real-time Issues

**Problem:** WebSocket events not received

**Solution:**
```bash
# Check Reverb is running
php artisan reverb:start

# Verify broadcasting is enabled
# Check .env BROADCAST_DRIVER=reverb
# Verify Echo configuration in frontend
```

## Code Style & Standards

### PHP Standards

- Follow Laravel coding standards
- Use PHP 8.4 features (constructor property promotion, match expressions, etc.)
- Use explicit return types
- Use type hints for parameters
- Use PHPDoc blocks for complex methods
- Follow PSR-12 coding standards

### JavaScript Standards

- Follow ESLint/Prettier rules
- Use modern JavaScript (ES6+)
- Keep Alpine.js components simple and focused
- Use proper event handling

### CSS Standards

- Use Tailwind CSS utility classes
- Follow existing design patterns
- Support dark mode where applicable (use `dark:` prefix)
- Use gap utilities for spacing in flex/grid layouts

### Blade Templates

- Use component-based architecture
- Keep logic minimal in views (push to controllers/services)
- Use `@include`, `@component`, and `@slot` for reusable parts
- Follow existing naming conventions

### Git Conventions

- Use descriptive commit messages
- Follow conventional commits where applicable
- Create feature branches for new features
- Use pull requests for code review
- Keep commits focused and atomic

## Additional Documentation

Comprehensive documentation exists in the `docs/` directory:

- `AGENTS.md` - AI agent system
- `BOTBOOK_FINAL.md` - BotBook system
- `CHAT_SYSTEM_GUIDE.md` - Chat system
- `POST_GENERATION.md` - Post generation
- `ACTIVITY_SYSTEM_GUIDE.md` - Activity logging
- `PWA_WEBPUSH_GUIDE.md` - PWA and WebPush setup
- `USER_PROFILE_WEB_COMPONENT.md` - User profile component
- `USER_LIST_WEB_COMPONENT.md` - User list component
- `GLOBAL_ACTIVITY_LOGGING.md` - Global activity system
- `WEB_POSTS.md` - Web posts system
- `CATEGORY_GENERATION.md` - Category generation
- `IMAGE_VISION_GUIDE.md` - Image vision AI
- `QUIZ_SYSTEM.md` - Quiz system
- `POLLINATIONS_INTEGRATION.md` - Pollinations AI integration
- `CEREBRAS_INTEGRATION.md` - Cerebras integration

## Important Notes

1. **Always check existing patterns** before creating new components or services
2. **Use existing services** rather than duplicating logic
3. **Follow the TALL stack conventions** (Tailwind, Alpine, Laravel, Livewire)
4. **Use Pest for testing** - all new tests should use Pest syntax
5. **Run Pint before committing** - maintain consistent code style
6. **Test thoroughly** - both unit and feature tests
7. **Check Copilot instructions** in `.github/copilot-instructions.md`
8. **Respect the architecture** - don't bypass Eloquent or Laravel conventions
9. **Use environment variables** for configuration - never hardcode sensitive data
10. **Document complex logic** with PHPDoc blocks and comments where necessary

## Getting Help

- Use Laravel documentation and Boost tools for Laravel-specific questions
- Search the codebase for existing patterns
- Check the comprehensive documentation in `docs/` directory
- Use `search-docs` tool for Laravel ecosystem documentation
- Refer to `.github/copilot-instructions.md` for Laravel Boost guidelines
