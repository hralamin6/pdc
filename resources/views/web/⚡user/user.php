<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.web')]
class extends Component
{
    use WithPagination;

    public User $user;

    public string $activeTab = 'posts'; // posts, activity, stats

    /**
     * Mount the component
     */
    public function mount(string $id): void
    {
        $this->user = User::with(['roles', 'media', 'detail'])
            ->findOrFail($id);
    }

    /**
     * Get user's social links
     */
    #[Computed]
    public function socialLinks()
    {
        $detail = $this->user->detail;
        if (! $detail) {
            return collect([]);
        }

        return collect([
            'website' => $detail->website,
            'facebook' => $detail->facebook,
            'twitter' => $detail->twitter,
            'instagram' => $detail->instagram,
            'linkedin' => $detail->linkedin,
            'youtube' => $detail->youtube,
            'github' => $detail->github,
        ])->filter();
    }

    /**
     * Get page title
     */
    public function title(): string
    {
        return $this->user->name.' - Profile';
    }

    /**
     * Get user's posts
     */
    #[Computed]
    public function posts()
    {
        return $this->user->posts()
            ->with(['category', 'user'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(6);
    }

    /**
     * Get user statistics
     */
    #[Computed]
    public function stats()
    {
        $totalPosts = $this->user->posts()
            ->whereNotNull('published_at')
            ->count();

        $totalViews = $this->user->posts()
            ->whereNotNull('published_at')
            ->sum('views_count');

        $totalComments = DB::table('comments')
            ->whereIn('post_id', $this->user->posts()->pluck('id'))
            ->count();

        $avgViews = $totalPosts > 0 ? round($totalViews / $totalPosts) : 0;

        return [
            'total_posts' => $totalPosts,
            'total_views' => $totalViews,
            'total_comments' => $totalComments,
            'avg_views_per_post' => $avgViews,
            'member_since_days' => $this->user->created_at->diffInDays(now()),
        ];
    }

    /**
     * Get post analytics by category
     */
    #[Computed]
    public function postsByCategory()
    {
        return $this->user->posts()
            ->whereNotNull('published_at')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($posts) {
                $category = $posts->first()->category;

                return [
                    'name' => $category ? $category->name : 'Uncategorized',
                    'count' => $posts->count(),
                    'views' => $posts->sum('views_count'),
                ];
            })
            ->sortByDesc('count')
            ->take(6)
            ->values();
    }

    /**
     * Get monthly post activity (last 6 months)
     */
    #[Computed]
    public function monthlyActivity()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $posts = $this->user->posts()
                ->whereYear('published_at', $date->year)
                ->whereMonth('published_at', $date->month)
                ->whereNotNull('published_at')
                ->count();

            $months[] = [
                'month' => $date->format('M'),
                'posts' => $posts,
            ];
        }

        return collect($months);
    }

    /**
     * Get recent activities
     */
    #[Computed]
    public function recentActivities()
    {
        // Get user's recent posts as activities
        $posts = $this->user->posts()
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(10)
            ->get()
            ->map(function ($post) {
                return [
                    'type' => 'post',
                    'title' => 'Published a post',
                    'description' => $post->title,
                    'date' => $post->published_at,
                    'icon' => '📝',
                    'link' => route('web.post', $post->slug),
                ];
            });

        return $posts->sortByDesc('date')->take(10);
    }

    /**
     * Get top performing posts
     */
    #[Computed]
    public function topPosts()
    {
        return $this->user->posts()
            ->whereNotNull('published_at')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Switch tab
     */
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
};
