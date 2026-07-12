<?php

namespace App\Support;

use App\Models\HalaqahSeries;
use App\Models\Page;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Spotlight
{
    /**
     * Static navigation links grouped by category.
     * Each item: [name, description, link, icon (Heroicon outline key)]
     */
    private function staticLinks(): array
    {
        return [
            // ─── Dashboard ───────────────────────────────────────────────
            ['name' => 'Dashboard',            'description' => 'Main admin overview',          'link' => route('app.dashboard'),              'icon' => 'o-home'],

            // ─── Public / Web ─────────────────────────────────────────────
            ['name' => 'Home',                 'description' => 'Public homepage',               'link' => route('web.home'),                   'icon' => 'o-globe-alt'],
            ['name' => 'Blog / Posts',         'description' => 'Articles & reflections',        'link' => route('web.posts'),                  'icon' => 'o-newspaper'],
            ['name' => 'Campaigns',            'description' => 'Donation campaigns',            'link' => route('web.campaigns'),              'icon' => 'o-heart'],
            ['name' => 'Members',              'description' => 'Community directory',           'link' => route('web.members'),                'icon' => 'o-users'],
            ['name' => 'Library',              'description' => 'Books & Islamic resources',     'link' => route('web.library'),                'icon' => 'o-book-open'],
            ['name' => 'Showcase',             'description' => 'Community gallery',             'link' => route('web.showcase'),               'icon' => 'o-photo'],
            ['name' => 'Finances',             'description' => 'Treasury & transparency',       'link' => route('web.finances'),               'icon' => 'o-banknotes'],
            ['name' => 'Halaqahs',             'description' => 'Study circles & sessions',      'link' => route('web.halaqahs'),               'icon' => 'o-academic-cap'],
            ['name' => 'Quizzes',              'description' => 'Islamic knowledge competitions','link' => route('web.quizzes'),                'icon' => 'o-question-mark-circle'],

            // ─── My Account ───────────────────────────────────────────────
            ['name' => 'My Profile',           'description' => 'View & edit your profile',     'link' => route('web.profile'),                'icon' => 'o-user'],
            ['name' => 'My Books',             'description' => 'Books I have borrowed',         'link' => route('web.my-books'),               'icon' => 'o-book-open'],
            ['name' => 'My Donations',         'description' => 'My donation history',           'link' => route('web.my-donations'),           'icon' => 'o-heart'],
            ['name' => 'My Quizzes',           'description' => 'Quiz history & scores',         'link' => route('web.my-quizzes'),             'icon' => 'o-clipboard-document-check'],
            ['name' => 'My Daily Report',      'description' => 'Today\'s daily report',         'link' => route('web.my-report'),              'icon' => 'o-chart-bar-square'],
            ['name' => 'Fill Daily Report',    'description' => 'Submit today\'s report',        'link' => route('web.my-report.fill'),         'icon' => 'o-pencil-square'],
            ['name' => 'Report History',       'description' => 'Past daily reports',            'link' => route('web.my-report.history'),      'icon' => 'o-clock'],
            ['name' => 'Report Stats',         'description' => 'My report statistics',          'link' => route('web.my-report.stats'),        'icon' => 'o-chart-bar'],
            ['name' => 'Notifications',        'description' => 'My notifications',              'link' => route('web.notifications'),          'icon' => 'o-bell'],
            ['name' => 'Messages / Chat',      'description' => 'Community chat',                'link' => route('web.chat'),                   'icon' => 'o-chat-bubble-left-right'],
            ['name' => 'Quiz Leaderboard',     'description' => 'Top quiz performers',           'link' => route('web.quizzes.leaderboard'),    'icon' => 'o-trophy'],
            ['name' => 'Quiz History',         'description' => 'All past quiz attempts',        'link' => route('web.quizzes.history'),        'icon' => 'o-list-bullet'],

            // ─── Daily Reports (Admin) ─────────────────────────────────────
            ['name' => 'Report Admin',         'description' => 'Supervision portal',            'link' => route('web.my-report.admin'),        'icon' => 'o-shield-check'],
            ['name' => 'Report Analytics',     'description' => 'Community analytics',           'link' => route('web.my-report.analytics'),    'icon' => 'o-chart-pie'],
            ['name' => 'Report Leaderboard',   'description' => 'Report leaderboard',            'link' => route('web.my-report.leaderboard'),  'icon' => 'o-trophy'],
            ['name' => 'Report Templates',     'description' => 'Manage report templates',       'link' => route('web.my-report.templates'),    'icon' => 'o-document-duplicate'],

            // ─── Halaqah Admin ─────────────────────────────────────────────
            ['name' => 'Halaqah Series',       'description' => 'Manage series',                 'link' => route('app.halaqah-series'),         'icon' => 'o-academic-cap'],
            ['name' => 'Session Scheduler',    'description' => 'Schedule halaqah sessions',     'link' => route('app.halaqahs.schedule'),      'icon' => 'o-calendar-days'],

            // ─── Donations Admin ───────────────────────────────────────────
            ['name' => 'Campaigns Admin',      'description' => 'Manage campaigns',              'link' => route('app.donations.campaigns'),    'icon' => 'o-megaphone'],
            ['name' => 'Pledges Admin',        'description' => 'Manage recurring pledges',      'link' => route('app.donations.pledges'),      'icon' => 'o-arrow-path'],
            ['name' => 'Verify Payments',      'description' => 'Verify donations',              'link' => route('app.donations.verify'),       'icon' => 'o-check-circle'],
            ['name' => 'Transactions',         'description' => 'All transactions',              'link' => route('app.donations.transactions'), 'icon' => 'o-banknotes'],

            // ─── Treasury ──────────────────────────────────────────────────
            ['name' => 'Expenses',             'description' => 'Manage expenses',               'link' => route('app.expenses.admin'),         'icon' => 'o-receipt-percent'],
            ['name' => 'Fund Transfers',       'description' => 'Account transfers',             'link' => route('app.fund-transfers'),         'icon' => 'o-arrows-right-left'],
            ['name' => 'Bank Accounts',        'description' => 'Manage bank accounts',          'link' => route('app.bank-accounts'),          'icon' => 'o-credit-card'],
            ['name' => 'Expense Categories',   'description' => 'Manage categories',             'link' => route('app.expense-categories'),     'icon' => 'o-tag'],
            ['name' => 'Monthly Reports',      'description' => 'Treasury monthly reports',      'link' => route('app.treasury-report'),        'icon' => 'o-document-chart-bar'],

            // ─── Library Admin ─────────────────────────────────────────────
            ['name' => 'Library Hubs',         'description' => 'Manage community hubs',         'link' => route('app.library-hubs'),           'icon' => 'o-building-library'],
            ['name' => 'Manage Catalog',       'description' => 'Manage books catalog',          'link' => route('app.books.admin'),            'icon' => 'o-cog'],
            ['name' => 'Authors & Metadata',   'description' => 'Manage book metadata',          'link' => route('app.books.metadata'),         'icon' => 'o-tag'],

            // ─── Quiz Admin ────────────────────────────────────────────────
            ['name' => 'Manage Quizzes',       'description' => 'Create & edit quizzes',         'link' => route('app.quiz.manage'),            'icon' => 'o-cog-6-tooth'],
            ['name' => 'Grade Answers',        'description' => 'Grade quiz submissions',        'link' => route('app.quiz.grade'),             'icon' => 'o-check-badge'],

            // ─── Settings & Admin ──────────────────────────────────────────
            ['name' => 'App Settings',         'description' => 'System settings',               'link' => route('app.settings'),               'icon' => 'o-adjustments-horizontal'],
            ['name' => 'Users',                'description' => 'Manage users',                  'link' => route('app.users'),                  'icon' => 'o-users'],
            ['name' => 'Roles',                'description' => 'Manage roles & permissions',    'link' => route('app.roles'),                  'icon' => 'o-shield-check'],
            ['name' => 'Pages',                'description' => 'Manage static pages',           'link' => route('app.pages'),                  'icon' => 'o-document-text'],
            ['name' => 'Blog Admin',           'description' => 'Manage blog posts',             'link' => route('app.posts'),                  'icon' => 'o-newspaper'],
            ['name' => 'Translations',         'description' => 'Manage translations',           'link' => route('app.translate'),              'icon' => 'o-language'],
            ['name' => 'Backups',              'description' => 'System backups',                'link' => route('app.backups'),                'icon' => 'o-cloud'],
            ['name' => 'Anonymous Nasiha',     'description' => 'View anonymous feedback',       'link' => route('app.feedback.admin'),         'icon' => 'o-inbox'],
            ['name' => 'Showcase Gallery',     'description' => 'Manage gallery albums',         'link' => route('app.gallery.admin'),          'icon' => 'o-photo'],
            ['name' => 'Activity Feed',        'description' => 'All system activities',         'link' => route('app.activity.feed'),          'icon' => 'o-list-bullet'],
            ['name' => 'AI Chat',              'description' => 'AI chat assistant',             'link' => route('app.ai-chat'),                'icon' => 'o-sparkles'],
        ];
    }

    /**
     * Search and return the results.
     */
    public function search(Request $request): Collection
    {
        $search = strtolower(trim($request->search ?? ''));

        // 1. Filter static navigation links
        $navLinks = collect($this->staticLinks())
            ->filter(fn ($item) =>
                $search === '' ||
                str_contains(strtolower($item['name']), $search) ||
                str_contains(strtolower($item['description']), $search)
            )
            ->take($search === '' ? 10 : 8)
            ->values();

        // Skip DB queries for empty search — nav links are enough
        if ($search === '') {
            return $navLinks;
        }

        // 2. Dynamic: Users
        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->take(4)
            ->get()
            ->map(fn (User $user) => [
                'avatar'      => $user->getMedia('avatar')->first()?->getUrl()
                                 ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                'name'        => $user->name,
                'description' => $user->email,
                'link'        => route('web.user', $user->getRouteKey()),
                'icon'        => 'o-user',
            ]);

        // 3. Dynamic: Published Pages
        $pages = Page::published()
            ->where('title', 'like', "%{$search}%")
            ->take(3)
            ->get()
            ->map(fn (Page $page) => [
                'name'        => $page->title,
                'description' => __('Page'),
                'link'        => route('web.page', $page->slug),
                'icon'        => 'o-document-text',
            ]);

        // 4. Dynamic: Blog Posts
        $posts = Post::published()
            ->where('title', 'like', "%{$search}%")
            ->take(3)
            ->get()
            ->map(fn (Post $post) => [
                'name'        => $post->title,
                'description' => __('Blog Post'),
                'link'        => route('web.post', $post->slug),
                'icon'        => 'o-newspaper',
            ]);

        // 5. Dynamic: Quizzes
        $quizzes = Quiz::published()
            ->where('title', 'like', "%{$search}%")
            ->take(3)
            ->get()
            ->map(fn (Quiz $quiz) => [
                'name'        => $quiz->title,
                'description' => __('Quiz'),
                'link'        => route('web.quizzes.show', $quiz->id),
                'icon'        => 'o-question-mark-circle',
            ]);

        // 6. Dynamic: Halaqah Series
        $series = HalaqahSeries::where('title', 'like', "%{$search}%")
            ->take(3)
            ->get()
            ->map(fn (HalaqahSeries $s) => [
                'name'        => $s->title,
                'description' => __('Halaqah Series'),
                'link'        => route('app.halaqah-series.show', $s->id),
                'icon'        => 'o-academic-cap',
            ]);

        return $navLinks
            ->merge($users)
            ->merge($pages)
            ->merge($posts)
            ->merge($quizzes)
            ->merge($series)
            ->filter(fn ($item) => !empty($item['name']))
            ->values();
    }
}
