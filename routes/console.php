<?php

use App\Jobs\ScheduledBackupJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('donation:process-recurring')->daily();

// log a message every minute
// Artisan::command('log:message', function () {
//     \Log::info('This is a log message from the log:message command.');
// })->purpose('Log a message every minute')->everyMinute();

// Scheduled backup commands
// Artisan::command('backup:schedule-daily', function () {
//     ScheduledBackupJob::dispatch('daily');
//     $this->info('Daily backup job dispatched successfully.');
// })->purpose('Run daily scheduled backup')->daily();

// Artisan::command('backup:schedule-weekly', function () {
//     ScheduledBackupJob::dispatch('weekly');
//     $this->info('Weekly backup job dispatched successfully.');
// })->purpose('Run weekly scheduled backup')->weekly();

// Artisan::command('backup:schedule-monthly', function () {
//     ScheduledBackupJob::dispatch('monthly');
//     $this->info('Monthly backup job dispatched successfully.');
// })->purpose('Run monthly scheduled backup')->monthly();

// // Cleanup command for old backups
// Artisan::command('backup:cleanup-old', function () {
//     $this->info('Cleaning up old backups...');

//     // Cleanup logic handled by the ScheduledBackupJob
//     // This command can also be run manually if needed
//     $job = new ScheduledBackupJob();
//     $reflection = new \ReflectionClass($job);
//     $method = $reflection->getMethod('cleanupOldBackups');
//     $method->setAccessible(true);
//     $method->invoke($job);

//     $this->info('Old backups cleanup completed.');
// })->purpose('Cleanup old backup files')->daily();

Schedule::call(function () {
    // 1. Due tomorrow (before one day reminder)
    $dueTomorrow = \App\Models\BorrowRequest::with(['borrower', 'bookCopy.owner', 'bookCopy.book'])
        ->where('status', 'active')
        ->whereDate('due_date', \Carbon\Carbon::tomorrow())
        ->get();

    foreach ($dueTomorrow as $req) {
        if ($req->borrower && $req->bookCopy && $req->bookCopy->owner) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'reminder_tomorrow',
                $req->bookCopy->owner->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }
    }

    // 2. Due today (same day reminder)
    $dueToday = \App\Models\BorrowRequest::with(['borrower', 'bookCopy.owner', 'bookCopy.book'])
        ->where('status', 'active')
        ->whereDate('due_date', \Carbon\Carbon::today())
        ->get();

    foreach ($dueToday as $req) {
        if ($req->borrower && $req->bookCopy && $req->bookCopy->owner) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'reminder_today',
                $req->bookCopy->owner->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }
    }

    // 3. Due yesterday (next day/overdue reminder)
    $dueYesterday = \App\Models\BorrowRequest::with(['borrower', 'bookCopy.owner', 'bookCopy.book'])
        ->where('status', 'active')
        ->whereDate('due_date', \Carbon\Carbon::yesterday())
        ->get();

    foreach ($dueYesterday as $req) {
        if ($req->borrower && $req->bookCopy && $req->bookCopy->owner) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'reminder_overdue',
                $req->bookCopy->owner->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }
    }
})->daily();
