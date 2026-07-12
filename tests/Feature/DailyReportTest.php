<?php

use App\Models\DailyReport;
use App\Models\DailyReportTemplate;
use App\Models\User;
use App\Models\UserReportItem;
use App\Models\UserStreak;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    DailyReportTemplate::create([
        'title' => 'Fajr Prayer',
        'type' => 'boolean',
        'category' => 'Ibadah',
        'sort_order' => 10,
    ]);

    DailyReportTemplate::create([
        'title' => 'Quran Reading',
        'type' => 'number',
        'category' => 'Quran & Adhkar',
        'sort_order' => 20,
    ]);
});

it('auto-initializes default tracking items for a new user on dashboard view', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('daily-reports.view');

    $this->actingAs($user);

    Livewire::test('app::daily-reports')
        ->assertStatus(200);

    expect(UserReportItem::where('user_id', $user->id)->count())->toBeGreaterThan(0);
});

it('allows submitting a daily report and updates streak correctly', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('daily-reports.view');

    $this->actingAs($user);

    Livewire::test('app::daily-reports-form')
        ->assertStatus(200)
        ->set('entries.'.UserReportItem::where('user_id', $user->id)->first()->id.'.boolean_value', true)
        ->set('notes', 'Great day of reflection!')
        ->call('saveReport');

    expect(DailyReport::where('user_id', $user->id)->count())->toBe(1);

    $streak = UserStreak::where('user_id', $user->id)->first();
    expect($streak->current_streak)->toBe(1);
});

it('does not double-increment streak when re-submitting report on the same day', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('daily-reports.view');

    $this->actingAs($user);

    $component = Livewire::test('app::daily-reports-form');
    $component->call('saveReport');

    $streak1 = UserStreak::where('user_id', $user->id)->first()->current_streak;

    // Save report again on same day
    $component->call('saveReport');

    $streak2 = UserStreak::where('user_id', $user->id)->first()->current_streak;

    expect($streak1)->toBe($streak2);
});

it('allows admin to send reminders to pending users', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $member = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test('app::daily-reports-admin')
        ->assertStatus(200)
        ->call('remindUser', $member->id);

    Notification::assertSentTo(
        $member,
        \App\Notifications\DailyReportReminderNotification::class
    );
});
