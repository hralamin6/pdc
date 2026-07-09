<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Http;

Route::get('/benchmark', function () {
    Benchmark::dd([
        // 'home' => fn () => Http::get(route('web.home')),
        'posts' => fn () => Http::get(route('web.posts')),
    ]);
});

Route::livewire('/', 'web::home')->name('web.home');
Route::livewire('/blog', 'web::posts')->name('web.posts');
Route::livewire('/blog/{slug}', 'web::post')->name('web.post');
Route::livewire('/campaigns', 'web::campaigns')->name('web.campaigns');
Route::livewire('/members', 'web::members')->name('web.members');
Route::livewire('/halaqahs', 'web::halaqahs')->name('web.halaqahs');
Route::livewire('/halaqahs/{halaqah}', 'web::halaqah-show')->name('web.halaqah.show');

// Backward compat redirects
Route::redirect('/posts', '/blog');
Route::redirect('/posts/{slug}', '/blog/{slug}');

Route::middleware('auth')->group(function () {
    Route::livewire('/app/', 'app::dashboard')->name('app.dashboard');

    Route::livewire('/app/profile/', 'app::profile')->name('app.profile');
    Route::livewire('/app/settings/', 'app::settings')->name('app.settings');
    Route::livewire('/app/roles/', 'app::roles')->name('app.roles');
    Route::livewire('/app/users/', 'app::users')->name('app.users');
    Route::livewire('/app/backups/', 'app::backups')->name('app.backups');
    Route::livewire('/app/translate/', 'app::translate')->name('app.translate');
    Route::livewire('/app/pages/', 'app::pages')->name('app.pages');
    Route::livewire('/app/categories/', 'app::categories')->name('app.categories');
    Route::livewire('/app/posts/', 'app::posts')->name('app.posts');

    // Halaqahs Routes
    Route::livewire('/app/halaqahs', 'app::halaqahs')->name('app.halaqahs');
    Route::livewire('/app/halaqahs/manage', 'app::halaqahs-manage')->name('app.halaqahs.manage');
    Route::livewire('/app/halaqahs/{halaqah}/attendance', 'app::halaqahs-attendance')->name('app.halaqahs.attendance');
    Route::livewire('/app/halaqahs/{halaqah}', 'app::halaqahs-show')->name('app.halaqahs.show');

    // Donations Routes
    Route::livewire('/app/donations', 'app::donations')->name('app.donations');
    Route::livewire('/app/donations/admin', 'app::donations-admin')->name('app.donations.admin');

    // Daily Reports Routes
    Route::livewire('/app/daily-reports', 'app::daily-reports')->name('app.daily-reports');
    Route::livewire('/app/daily-reports/form', 'app::daily-reports-form')->name('app.daily-reports.form');
    Route::livewire('/app/daily-reports/settings', 'app::daily-reports-settings')->name('app.daily-reports.settings');
    Route::livewire('/app/daily-reports/admin', 'app::daily-reports-admin')->name('app.daily-reports.admin');

    Route::livewire('/app/notifications/', 'app::notifications')->name('app.notifications');

    Route::livewire('/app/activities/feed/', 'app::activity-feed')->name('app.activity.feed');
    Route::livewire('/app/activities/my/', 'app::my-activities')->name('app.activity.my');

    // Chat routes
    Route::livewire('/app/chat/{conversation?}', 'app::chat')->name('app.chat');
    Route::livewire('/app/ai-chat/{conversation?}', 'app::ai-chat')->name('app.ai-chat');
});

// Push notification API routes (now accessible to both guests and authenticated users)
Route::post('api/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('api/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
Route::get('api/push/status', [\App\Http\Controllers\PushSubscriptionController::class, 'status'])->name('push.status');

// Public VAPID key endpoint (must be accessible without authentication)
Route::get('api/push/vapid-key', [\App\Http\Controllers\PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid-key');

Route::livewire('{slug}', 'web::page')->name('web.page');
