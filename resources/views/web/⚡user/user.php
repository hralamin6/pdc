<?php

use App\Models\Conversation;
use App\Models\BookUserInteraction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Layout('layouts.web')]
class extends Component
{
    use WithPagination, Toast;

    public User $user;
    public string $activeTab = 'overview'; // overview, posts, bookshelf, quizzes, reports

    public function mount(string $slug): void
    {
        $this->user = User::with(['roles', 'media', 'detail'])
            ->findOrFail($slug);
    }

    public function title(): string
    {
        return $this->user->name . ' — Member Profile';
    }

    /** XP level */
    #[Computed]
    public function level(): int
    {
        $xp = $this->user->gamification_points ?? 0;
        return max(1, (int) floor(log(max($xp, 1), 1.5)));
    }

    #[Computed]
    public function levelTitle(): string
    {
        return match (true) {
            $this->level >= 25 => 'Grand Scholar',
            $this->level >= 20 => 'Senior Scholar',
            $this->level >= 15 => 'Mentor',
            $this->level >= 10 => 'Practitioner',
            $this->level >= 5  => 'Seeker',
            default            => 'Novice',
        };
    }

    #[Computed]
    public function xpProgress(): array
    {
        $xp = $this->user->gamification_points ?? 0;
        $level = $this->level;
        $cur = (int) round(1.5 ** $level);
        $next = (int) round(1.5 ** ($level + 1));
        $pct = $next > $cur ? min(100, round((($xp - $cur) / ($next - $cur)) * 100)) : 100;
        return ['current' => $xp, 'next' => $next, 'percent' => $pct];
    }

    #[Computed]
    public function socialLinks()
    {
        $d = $this->user->detail;
        if (!$d) return collect([]);
        return collect([
            'website'   => ['url' => $d->website,   'icon' => 'o-globe-alt',    'label' => 'Website',   'color' => 'text-slate-600'],
            'facebook'  => ['url' => $d->facebook,  'icon' => 'o-link',         'label' => 'Facebook',  'color' => 'text-blue-600'],
            'twitter'   => ['url' => $d->twitter,   'icon' => 'o-at-symbol',    'label' => 'Twitter',   'color' => 'text-sky-500'],
            'instagram' => ['url' => $d->instagram, 'icon' => 'o-camera',       'label' => 'Instagram', 'color' => 'text-pink-600'],
            'linkedin'  => ['url' => $d->linkedin,  'icon' => 'o-briefcase',    'label' => 'LinkedIn',  'color' => 'text-blue-700'],
            'youtube'   => ['url' => $d->youtube,   'icon' => 'o-play-circle',  'label' => 'YouTube',   'color' => 'text-red-600'],
            'github'    => ['url' => $d->github,    'icon' => 'o-code-bracket', 'label' => 'GitHub',    'color' => 'text-slate-800'],
        ])->filter(fn($s) => !empty($s['url']));
    }

    #[Computed]
    public function stats(): array
    {
        $totalPosts = $this->user->posts()->whereNotNull('published_at')->count();
        $totalViews = $this->user->posts()->whereNotNull('published_at')->sum('views_count');
        $totalComments = DB::table('comments')->whereIn('post_id', $this->user->posts()->pluck('id'))->count();
        $totalReports = $this->user->dailyReports()->count();
        $streak = $this->user->userStreak()->first();
        $currentStreak = $streak?->current_streak ?? 0;
        $longestStreak = $streak?->longest_streak ?? 0;
        $quizCount = DB::table('quiz_attempts')->where('user_id', $this->user->id)->where('status', 'completed')->count();
        $totalDonated = $this->user->donations()->where('status', 'confirmed')->where('is_anonymous', false)->sum('amount');
        $booksRead = BookUserInteraction::where('user_id', $this->user->id)->where('reading_status', 'completed')->count();
        $booksReading = BookUserInteraction::where('user_id', $this->user->id)->where('reading_status', 'reading')->count();

        return compact('totalPosts', 'totalViews', 'totalComments', 'totalReports', 'currentStreak', 'longestStreak', 'quizCount', 'totalDonated', 'booksRead', 'booksReading');
    }

    #[Computed]
    public function posts()
    {
        return $this->user->posts()
            ->with(['category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(6, pageName: 'postsPage');
    }

    #[Computed]
    public function dailyReports()
    {
        return $this->user->dailyReports()
            ->latest()
            ->paginate(10, pageName: 'reportsPage');
    }

    #[Computed]
    public function quizAttempts()
    {
        return DB::table('quiz_attempts')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->where('quiz_attempts.user_id', $this->user->id)
            ->where('quiz_attempts.status', 'completed')
            ->select('quiz_attempts.id', 'quiz_attempts.score_percentage', 'quiz_attempts.score_raw', 'quiz_attempts.passed', 'quiz_attempts.created_at', 'quizzes.title as quiz_title')
            ->latest('quiz_attempts.created_at')
            ->paginate(8, pageName: 'quizPage');
    }

    /** Book shelf grouped by reading status */
    #[Computed]
    public function bookshelf(): array
    {
        $interactions = BookUserInteraction::where('user_id', $this->user->id)
            ->with(['book.author', 'book.category'])
            ->whereNotNull('reading_status')
            ->latest()
            ->get();

        $ownedCopies = \App\Models\BookCopy::where('owner_id', $this->user->id)
            ->with(['book.author', 'book.category', 'libraryHub'])
            ->latest()
            ->get();

        $uploadedBooks = \App\Models\Book::where('uploaded_by', $this->user->id)
            ->with(['author', 'category'])
            ->latest()
            ->get();

        return [
            'reading'      => $interactions->where('reading_status', 'reading'),
            'completed'    => $interactions->where('reading_status', 'completed'),
            'want_to_read' => $interactions->where('reading_status', 'want_to_read'),
            'owned'        => $ownedCopies,
            'uploaded'     => $uploadedBooks,
        ];
    }

    /** Recent donations (non-anonymous only) */
    #[Computed]
    public function donations()
    {
        return $this->user->donations()
            ->where('status', 'confirmed')
            ->where('is_anonymous', false)
            ->with('campaign')
            ->latest('donated_at')
            ->take(5)
            ->get();
    }

    /** Daily report activity last 7 days for mini heatmap */
    #[Computed]
    public function reportHeatmap(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[$date] = $this->user->dailyReports()
                ->whereDate('created_at', $date)
                ->exists();
        }
        return $days;
    }

    #[Computed]
    public function achievements(): array
    {
        $s = $this->stats;
        $badges = [];
        if ($s['totalPosts'] >= 1)   $badges[] = ['icon' => '✍️', 'title' => 'First Post',      'desc' => 'Published first article'];
        if ($s['totalPosts'] >= 10)  $badges[] = ['icon' => '📚', 'title' => 'Writer',           'desc' => '10+ posts'];
        if ($s['totalPosts'] >= 50)  $badges[] = ['icon' => '🏆', 'title' => 'Content Master',   'desc' => '50+ posts'];
        if ($s['currentStreak'] >= 7) $badges[] = ['icon' => '🔥', 'title' => 'Week Streak',     'desc' => '7-day streak'];
        if ($s['currentStreak'] >= 30)$badges[] = ['icon' => '⚡', 'title' => 'Monthly Streak',  'desc' => '30-day streak'];
        if ($s['quizCount'] >= 1)    $badges[] = ['icon' => '🎯', 'title' => 'Quiz Taker',       'desc' => 'First quiz done'];
        if ($s['quizCount'] >= 10)   $badges[] = ['icon' => '🧠', 'title' => 'Quiz Master',      'desc' => '10+ quizzes'];
        if ($s['booksRead'] >= 1)    $badges[] = ['icon' => '📖', 'title' => 'Reader',           'desc' => 'First book finished'];
        if ($s['booksRead'] >= 5)    $badges[] = ['icon' => '🔖', 'title' => 'Bookworm',         'desc' => '5+ books read'];
        if ($s['totalDonated'] > 0)  $badges[] = ['icon' => '💚', 'title' => 'Donor',            'desc' => 'Contributed to causes'];
        if ($this->user->email_verified_at) $badges[] = ['icon' => '✅', 'title' => 'Verified',  'desc' => 'Email verified'];
        if ($this->user->hasRole('mentor')) $badges[] = ['icon' => '🕌', 'title' => 'Mentor',    'desc' => 'Community guide'];
        if ($this->user->hasRole('admin'))  $badges[] = ['icon' => '🛡️', 'title' => 'Guardian', 'desc' => 'Administrator'];
        return $badges;
    }

    /** Get or create conversation with this user for chat redirect */
    public function startChat(): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }
        if (auth()->id() === $this->user->id) {
            return;
        }
        $conversation = Conversation::findOrCreateBetween(auth()->id(), $this->user->id);
        $this->redirectRoute('web.chat', ['conversation' => $conversation->id]);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage('postsPage');
        $this->resetPage('reportsPage');
        $this->resetPage('quizPage');
    }
};
