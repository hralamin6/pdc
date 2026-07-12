<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $groups = [
            'dashboard' => ['dashboard.view'],
            'users' => [
                'users.view', 'users.create', 'users.update', 'users.delete', 'users.assign-roles',
            ],
            'roles' => [
                'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign-permissions',
            ],
            'settings' => [
                'settings.view', 'settings.update', 'settings.run-commands',
            ],
            'media' => [
                'media.view', 'media.upload', 'media.delete',
            ],
            'profile' => [
                'profile.update',
            ],
            'activity' => [
                'activity.dashboard', 'activity.feed', 'activity.delete', 'activity.my',
            ],
            'backup' => [
                'backups.view', 'backups.create', 'backups.download', 'backups.delete', 'backups.manage-schedules', 'backups.cleanup',
            ],
            'translate' => [
                'translations.view', 'translations.create', 'translations.update', 'translations.delete', 'translations.scan', 'translations.import', 'translations.export', 'translations.ai-translate', 'translations.manage',
            ],
            'pages' => [
                'pages.view', 'pages.create', 'pages.edit', 'pages.delete',
            ],
            'categories' => [
                'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            ],
            'posts' => [
                'posts.view', 'posts.view-all', 'posts.view-own', 'posts.create', 'posts.update', 'posts.update-own', 'posts.delete', 'posts.delete-own', 'posts.publish', 'posts.feature',
            ],
            'halaqahs' => [
                'halaqahs.view', 'halaqahs.create', 'halaqahs.update', 'halaqahs.delete', 'halaqahs.manage-attendance',
            ],
            'quiz' => [
                'quiz.view', 'quiz.manage', 'quiz.create', 'quiz.grade', 'quiz.attempt', 'quiz.live.host',
            ],
            'expenses' => [
                'expenses.view',                  // member: see published monthly summary
                'expenses.manage',                // accountant: full CRUD expenses
                'expenses.categories.manage',     // admin: manage expense categories
                'expenses.bank-accounts.manage',  // admin: manage bank accounts
                'expenses.transfers.manage',      // accountant: record fund transfers
                'expenses.reports.manage',        // admin: generate & publish monthly reports
                'expenses.reports.view',          // member: view published reports
            ],
            'feedback' => [
                'feedback.manage', 'feedback.view',
            ],
            'gallery' => [
                'gallery.manage', 'gallery.view',
            ],
            'donations' => [
                'donations.campaigns.manage', 'donations.verify', 'donations.pledges.manage', 'donations.transactions.manage',
            ],
            'library' => [
                'library.view', 'library.manage', 'library.hubs.manage', 'library.hubs.create',
            ],
            'daily-reports' => [
                'daily-reports.view', 'daily-reports.manage',
            ],
        ];

        // Create permissions
        foreach ($groups as $items) {
            foreach ($items as $name) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        // Create roles
        $super = Role::firstOrCreate(['name' => 'super-admin',  'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'admin',         'guard_name' => $guard]);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => $guard]);
        $mentor = Role::firstOrCreate(['name' => 'mentor',        'guard_name' => $guard]);
        $user = Role::firstOrCreate(['name' => 'user',          'guard_name' => $guard]);
        $bot = Role::firstOrCreate(['name' => 'bot',           'guard_name' => $guard]);
        $librarian = Role::firstOrCreate(['name' => 'librarian',     'guard_name' => $guard]);

        // Assign permissions
        $allPerms = Permission::where('guard_name', $guard)->get();
        $super->syncPermissions($allPerms);

        $adminPerms = Permission::whereIn('name', [
            'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.assign-roles',
            'roles.view', 'roles.create', 'roles.update', 'roles.assign-permissions',
            'settings.view', 'settings.update',
            'media.view', 'media.upload',
            'profile.update',
            'activity.dashboard', 'activity.feed', 'activity.delete',
            'pages.view', 'pages.create', 'pages.edit',
            'categories.view', 'categories.create', 'categories.update',
            'posts.view', 'posts.view-all', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish', 'posts.feature',
            'halaqahs.view', 'halaqahs.create', 'halaqahs.update', 'halaqahs.delete', 'halaqahs.manage-attendance',
            'translations.view', 'translations.create', 'translations.update', 'translations.delete', 'translations.scan', 'translations.import', 'translations.export', 'translations.ai-translate',
            'expenses.manage', 'expenses.categories.manage', 'expenses.bank-accounts.manage', 'expenses.transfers.manage', 'expenses.reports.manage', 'expenses.reports.view',
            'feedback.manage', 'feedback.view',
            'gallery.manage', 'gallery.view',
            'quiz.view', 'quiz.manage', 'quiz.create', 'quiz.grade', 'quiz.attempt', 'quiz.live.host',
            'donations.campaigns.manage', 'donations.verify', 'donations.pledges.manage', 'donations.transactions.manage',
            'library.view', 'library.manage', 'library.hubs.manage', 'library.hubs.create',
            'daily-reports.view', 'daily-reports.manage',
        ])->get();
        $admin->syncPermissions($adminPerms);

        // Accountant role: can manage expenses & transfers but not categories/accounts config
        $accountantPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
            'expenses.view', 'expenses.manage',
            'expenses.transfers.manage',
            'expenses.reports.view',
            'donations.campaigns.manage', 'donations.verify', 'donations.pledges.manage', 'donations.transactions.manage',
            'library.view',
            'daily-reports.view',
        ])->get();
        $accountant->syncPermissions($accountantPerms);

        $userPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
            'posts.view',
            'posts.view-own',
            'posts.create',
            'posts.update-own',
            'posts.delete-own',
            'expenses.view',          // members see financial summary
            'expenses.reports.view',  // members see published reports
            'quiz.view', 'quiz.attempt',
            'library.view',
            'daily-reports.view',
        ])->get();
        $user->syncPermissions($userPerms);

        $mentorPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
            'halaqahs.view', 'halaqahs.create', 'halaqahs.update', 'halaqahs.delete', 'halaqahs.manage-attendance',
            'expenses.view', 'expenses.reports.view',
            'quiz.view', 'quiz.attempt', 'quiz.live.host',
            'library.view', 'library.hubs.manage',
            'daily-reports.view', 'daily-reports.manage',
        ])->get();
        $mentor->syncPermissions($mentorPerms);

        $botPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
            'posts.view',
            'posts.view-own',
            'posts.create',
            'posts.update-own',
            'posts.delete-own',
            'library.view',
            'daily-reports.view',
        ])->get();
        $bot->syncPermissions($botPerms);

        $librarianPerms = Permission::whereIn('name', [
            'dashboard.view',
            'profile.update',
            'activity.my',
            'library.view', 'library.hubs.manage',
            'daily-reports.view',
        ])->get();
        $librarian->syncPermissions($librarianPerms);
    }
}
