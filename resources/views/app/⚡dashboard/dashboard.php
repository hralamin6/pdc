<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\User;
use App\Models\DailyReport;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Halaqah;
use App\Models\HalaqahAttendance;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Activity;
use App\Models\UserStreak;
use App\Models\Book;
use App\Models\BorrowRequest;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\Post;
use App\Models\Comment;
use App\Notifications\DailyReportReminderNotification;
use Mary\Traits\Toast;

new #[Title('Admin Dashboard')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public function mount(): void
    {
        $this->authorize('dashboard.view');
    }

    public function remindAll()
    {
        $today = now()->format('Y-m-d');
        $pendingUsers = User::whereDoesntHave('dailyReports', function ($q) use ($today) {
            $q->where('date', $today)->where('status', 'submitted');
        })->get();

        foreach ($pendingUsers as $user) {
            $user->notify(new DailyReportReminderNotification());
        }

        $this->success(__('Reminder notifications sent to :count pending members!', ['count' => $pendingUsers->count()]));
    }

    public function with(): array
    {
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth();

        // 1. General totals (KPIs)
        $totalUsers = User::count();
        $submittedToday = DailyReport::where('date', $today)->where('status', 'submitted')->count();
        $reportSubmissionRate = $totalUsers > 0 ? (int) round(($submittedToday / $totalUsers) * 100) : 0;

        $totalQuizzes = Quiz::count();
        $totalQuizAttempts = QuizAttempt::count();
        
        $totalHalaqahs = Halaqah::count();
        $averageAttendance = 0;
        $completedHalaqahs = Halaqah::where('scheduled_at', '<', now())->get();
        if ($completedHalaqahs->isNotEmpty()) {
            $totalAttendees = HalaqahAttendance::whereIn('halaqah_id', $completedHalaqahs->pluck('id'))
                ->where('attended', true)
                ->count();
            $averageAttendance = (int) round($totalAttendees / $completedHalaqahs->count());
        }

        // 2. Daily Report Submission Rate Trend (Last 7 Days)
        $reportTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $submitted = DailyReport::where('date', $dateStr)->where('status', 'submitted')->count();
            $rate = $totalUsers > 0 ? round(($submitted / $totalUsers) * 100) : 0;
            $reportTrend[] = [
                'day' => $date->format('D'),
                'date' => $date->format('M d'),
                'rate' => $rate,
                'count' => $submitted
            ];
        }

        // 3. Recent Activity (Latest 5 logs)
        $recentActivities = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Pending items requiring action
        $pendingGrading = QuizAnswer::whereHas('question', function ($q) {
            $q->where('type', 'short_text');
        })->whereNull('admin_grade')->count();

        $pendingDonationsCount = Donation::where('status', 'pending')->count();
        $pendingFeedbackCount = Feedback::where('is_read', false)->count();
        $pendingBorrowRequests = BorrowRequest::where('status', 'pending')->count();

        // 5. Streaks Leaderboard (Engagement Focus)
        $topStreaks = UserStreak::with('user')
            ->where('current_streak', '>', 0)
            ->orderByDesc('current_streak')
            ->limit(5)
            ->get();

        // 6. Treasury Ledger (Financial Snapshot)
        $donationsThisMonth = (float) Donation::where('status', 'confirmed')
            ->where('donated_at', '>=', $startOfMonth)
            ->sum('amount');

        $expensesThisMonth = (float) Expense::where('status', 'confirmed')
            ->where('expense_date', '>=', $startOfMonth)
            ->sum('amount');

        $activeCampaignsCount = DonationCampaign::where('status', 'active')->count();

        // 7. Library Hub Summary
        $totalBooks = Book::where('status', 'approved')->count();
        $activeBorrows = BorrowRequest::whereIn('status', ['given', 'active'])->count();
        $overdueBorrows = BorrowRequest::whereIn('status', ['given', 'active'])
            ->where('due_date', '<', now())
            ->count();

        // 8. Content Overview
        $totalBlogPosts = Post::published()->count();
        $totalComments = Comment::count();

        // 9. Upcoming Halaqahs & Top Quizzes
        $upcomingHalaqahs = Halaqah::with('speaker')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit(3)
            ->get();

        $topQuizzes = Quiz::withCount('attempts')
            ->orderBy('attempts_count', 'desc')
            ->limit(3)
            ->get();

        return [
            'metrics' => [
                'total_users' => $totalUsers,
                'submitted_today' => $submittedToday,
                'report_rate' => $reportSubmissionRate,
                'total_quizzes' => $totalQuizzes,
                'quiz_attempts' => $totalQuizAttempts,
                'total_halaqahs' => $totalHalaqahs,
                'avg_attendance' => $averageAttendance,
                'pending_grading' => $pendingGrading,
                'pending_donations' => $pendingDonationsCount,
                'pending_feedback' => $pendingFeedbackCount,
                'pending_borrows' => $pendingBorrowRequests,
            ],
            'treasury' => [
                'income' => $donationsThisMonth,
                'expenses' => $expensesThisMonth,
                'net' => $donationsThisMonth - $expensesThisMonth,
                'active_campaigns' => $activeCampaignsCount,
            ],
            'library' => [
                'total_books' => $totalBooks,
                'active_borrows' => $activeBorrows,
                'overdue' => $overdueBorrows,
            ],
            'content' => [
                'blog_posts' => $totalBlogPosts,
                'comments' => $totalComments,
            ],
            'topStreaks' => $topStreaks,
            'reportTrend' => $reportTrend,
            'recentActivities' => $recentActivities,
            'topQuizzes' => $topQuizzes,
            'upcomingHalaqahs' => $upcomingHalaqahs,
        ];
    }
};