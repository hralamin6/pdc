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
Route::livewire('/user/{slug}', 'web::user')->name('web.user');

Route::livewire('/campaigns', 'web::campaigns')->name('web.campaigns');
Route::livewire('/campaigns/{slug}', 'web::campaign')->name('web.campaign');
Route::livewire('/members', 'web::members')->name('web.members');
Route::livewire('/library', 'web::library')->name('web.library');
Route::livewire('/library/user/{id}', 'web::library-user')->name('web.library.user');
Route::livewire('/library/hub/{id}', 'web::library-hub')->name('web.library.hub');
Route::livewire('/library/{slug}', 'web::book')->name('web.book');
Route::livewire('/showcase', 'web::showcase')->name('web.showcase');
Route::livewire('/halaqahs', 'web::halaqahs')->name('web.halaqahs');
Route::livewire('/halaqahs/{halaqah}', 'web::halaqah-show')->name('web.halaqah.show');

Route::livewire('/quizzes', 'web::quizzes')->name('web.quizzes');
Route::livewire('/courses/{series}', 'web::course-show')->name('web.course.show');

// Web Quiz Routes — accessible without app panel (requires auth)
Route::middleware('auth')->group(function () {
    Route::livewire('/my-quizzes', 'web::my-quizzes')->name('web.my-quizzes');
    
    Route::livewire('/quizzes/{quiz}/take', 'web::quiz-take')->name('web.quiz.take');
    Route::livewire('/quizzes/{quiz}/live', 'web::quiz-live')->name('web.quiz.live');

    Route::livewire('/quizzes/leaderboard', 'web::quizzes-leaderboard')->name('web.quizzes.leaderboard');
    Route::livewire('/quizzes/history', 'web::quizzes-history')->name('web.quizzes.history');
    Route::livewire('/quizzes/{quiz}/review/{attempt}', 'web::quiz-review')->name('web.quizzes.review');
});

Route::livewire('/quizzes/{quiz}', 'web::quiz-show')->name('web.quizzes.show');

// Backward compat redirects
Route::redirect('/posts', '/blog');
Route::redirect('/posts/{slug}', '/blog/{slug}');

Route::middleware('auth')->group(function () {
    Route::livewire('/profile', 'web::profile')->name('web.profile');
    Route::livewire('/my-books', 'web::my-books')->name('web.my-books');
    Route::livewire('/my-donations', 'web::my-donations')->name('web.my-donations');
    Route::livewire('/notifications', 'web::notifications')->name('web.notifications');
    Route::livewire('/chat/{conversation?}', 'web::chat')->name('web.chat');
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

    Route::livewire('/app/donations/campaigns', 'app::donations-campaigns')->name('app.donations.campaigns');
    Route::livewire('/app/donations/campaigns/{campaign}', 'app::donations-campaign-details')->name('app.donations.campaign-details');
    Route::livewire('/app/donations/verify', 'app::donations-verify')->name('app.donations.verify');
    Route::livewire('/app/donations/pledges', 'app::donations-pledges')->name('app.donations.pledges');
    Route::livewire('/app/donations/pledges/{pledge}', 'app::donations-pledge-details')->name('app.donations.pledge-details');
    Route::livewire('/app/donations/transactions', 'app::donations-transactions')->name('app.donations.transactions');

    // Treasury / Expense Routes
    Route::livewire('/app/expenses', 'app::expenses')->name('app.expenses');
    Route::livewire('/app/expenses/admin', 'app::expenses-admin')->name('app.expenses.admin');
    Route::livewire('/app/expenses/categories', 'app::expense-categories')->name('app.expense-categories');
    Route::livewire('/app/expenses/bank-accounts', 'app::bank-accounts')->name('app.bank-accounts');
    Route::livewire('/app/expenses/transfers', 'app::fund-transfers')->name('app.fund-transfers');
    Route::livewire('/app/expenses/treasury-report', 'app::treasury-report')->name('app.treasury-report');

    // Library / Books Routes

    Route::livewire('/app/library/hubs', 'app::library-hubs')->name('app.library-hubs');
    Route::livewire('/app/library/{slug}', 'app::book-details')->name('app.book-details');
    Route::livewire('/app/library/{slug}/reader', 'app::book-reader')->name('app.book-reader');
    Route::livewire('/app/library-admin', 'app::books-admin')->name('app.books.admin');
    Route::livewire('/app/library-admin/metadata', 'app::books-metadata')->name('app.books.metadata');

    // Daily Reports Routes
    Route::livewire('/app/daily-reports', 'app::daily-reports')->name('app.daily-reports');
    Route::livewire('/app/daily-reports/form', 'app::daily-reports-form')->name('app.daily-reports.form');
    Route::livewire('/app/daily-reports/settings', 'app::daily-reports-settings')->name('app.daily-reports.settings');
    Route::livewire('/app/daily-reports/admin', 'app::daily-reports-admin')->name('app.daily-reports.admin');

    // Quiz Routes
    Route::livewire('/app/quizzes', 'app::quiz-manage')->name('app.quiz.manage');
    Route::livewire('/app/quizzes/{quiz}/live/host', 'app::quiz-live-host')->name('app.quiz.live.host');
    Route::livewire('/app/quizzes/grade', 'app::quiz-grade')->name('app.quiz.grade');

    // Feedback Route
    Route::livewire('/app/feedback', 'app::feedback-admin')->name('app.feedback.admin');

    // Gallery / Showcase Routes
    Route::livewire('/app/gallery', 'app::gallery-admin')->name('app.gallery.admin');
    Route::livewire('/app/gallery/{slug}', 'app::gallery-album-edit')->name('app.gallery.edit');

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
