<?php

use App\Models\DonationCampaign;
use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\Post;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Welcome | PSTU Dawah Community')] #[Layout('layouts.web')] class extends Component
{
    public function with(): array
    {
        return [
            'upcomingHalaqahs' => Halaqah::with(['speaker', 'series'])
                ->where('scheduled_at', '>=', now())
                ->whereIn('status', ['published'])
                ->orderBy('scheduled_at', 'asc')
                ->take(3)
                ->get(),
            'recentSeries' => HalaqahSeries::withCount('halaqahs')
                ->where('status', 'active')
                ->latest()
                ->take(3)
                ->get(),
            'featuredPosts' => Post::with(['user', 'category'])
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->take(3)
                ->get(),
            'activeCampaigns' => DonationCampaign::where('status', 'active')
                ->latest()
                ->take(3)
                ->get(),
            'stats' => [
                'members' => User::count(),
                'sessions' => Halaqah::where('status', 'completed')->count(),
                'courses' => HalaqahSeries::count(),
                'campaigns' => DonationCampaign::where('status', 'active')->count(),
            ],
            'mentors' => User::role(['mentor', 'admin', 'super-admin'])->take(6)->get(),
        ];
    }
};
?>

<div class="bg-base-100">

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white min-h-[85vh] flex items-center">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><path d=%22M30 0L60 30L30 60L0 30Z%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>');"></div>
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-primary/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-secondary/20 rounded-full blur-[150px]"></div>
        <div class="absolute top-10 right-1/4 w-40 h-40 bg-amber-400/20 rounded-full blur-[80px]"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-white/10 border border-white/20 text-white/90">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                        Open for enrollment
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.08] tracking-tight">
                        Where Knowledge
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-300">Meets Brotherhood</span>
                    </h1>
                    <p class="text-lg text-white/60 max-w-xl leading-relaxed">
                        PSTU Dawah Community — a campus-led initiative connecting students through structured Islamic study circles, courses, and mentorship.
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="{{ route('web.halaqahs') }}" wire:navigate class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-8 font-bold shadow-xl shadow-white/10 hover:-translate-y-0.5 hover:shadow-2xl transition-all">
                            Browse Sessions
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline border-white/30 text-white hover:bg-white/10 hover:border-white/50 rounded-xl px-8 font-bold">
                                Join Community
                            </a>
                        @endguest
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @php $statCards = [
                        ['icon' => 'o-users', 'val' => $stats['members'], 'label' => 'Active Members', 'clr' => 'amber'],
                        ['icon' => 'o-academic-cap', 'val' => $stats['sessions'], 'label' => 'Sessions Done', 'clr' => 'emerald'],
                        ['icon' => 'o-book-open', 'val' => $stats['courses'], 'label' => 'Active Courses', 'clr' => 'sky'],
                        ['icon' => 'o-heart', 'val' => $stats['campaigns'], 'label' => 'Active Campaigns', 'clr' => 'rose'],
                    ]; @endphp
                    @foreach($statCards as $sc)
                        <div class="bg-white/[0.07] backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:bg-white/[0.12] transition-all duration-300 hover:-translate-y-1">
                            <x-icon :name="$sc['icon']" class="w-8 h-8 text-{{ $sc['clr'] }}-300 mb-3" />
                            <p class="text-3xl font-black">{{ $sc['val'] }}</p>
                            <p class="text-white/50 text-sm font-medium mt-1">{{ $sc['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===== PRAYER TIMES BANNER ===== --}}
    <section class="relative -mt-8 z-20 max-w-5xl mx-auto px-4"
        x-data="{
            times: {}, nextName: '---', nextTime: '--:--', isLoading: true,
            async boot() {
                try {
                    const pos = await new Promise((r, e) => navigator.geolocation ? navigator.geolocation.getCurrentPosition(r, () => e(), {timeout: 5000}) : e());
                    const res = await fetch(`https://api.aladhan.com/v1/timings?latitude=${pos.coords.latitude}&longitude=${pos.coords.longitude}&method=1&school=1`);
                    const d = (await res.json()).data.timings;
                    this.times = {Fajr: d.Fajr, Dhuhr: d.Dhuhr, Asr: d.Asr, Maghrib: d.Maghrib, Isha: d.Isha};
                } catch { this.times = {Fajr:'--:--',Dhuhr:'--:--',Asr:'--:--',Maghrib:'--:--',Isha:'--:--'}; }
                this.findNext(); this.isLoading = false;
            },
            findNext() {
                const now = new Date(); const hm = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
                for (const [n,t] of Object.entries(this.times)) { if (t > hm) { this.nextName=n; this.nextTime=t; return; } }
                const first = Object.entries(this.times)[0]; if(first) { this.nextName=first[0]; this.nextTime=first[1]; }
            }
        }" x-init="boot()">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl shadow-black/10 dark:shadow-black/30 border border-slate-100 dark:border-white/5 p-6 md:p-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <x-icon name="o-clock" class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Next Prayer</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white" x-text="nextName + ' — ' + nextTime"></p>
                    </div>
                </div>
                <div class="flex gap-4 md:gap-6 flex-wrap justify-center">
                    <template x-for="[name, time] in Object.entries(times)" :key="name">
                        <div class="text-center min-w-[60px]" :class="name === nextName ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'">
                            <p class="text-[10px] font-bold uppercase tracking-wider" x-text="name"></p>
                            <p class="text-sm font-bold" x-text="time"></p>
                        </div>
                    </template>
                </div>
                @auth
                    <a href="{{ route('app.dashboard') }}" wire:navigate class="btn btn-sm btn-ghost text-primary font-bold rounded-xl shrink-0">
                        Full Schedule →
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ===== UPCOMING SESSIONS ===== --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Next Up</p>
                    <h2 class="text-3xl font-black text-base-content tracking-tight">Upcoming Study Sessions</h2>
                </div>
                <a href="{{ route('web.halaqahs') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    View all <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>
            @if($upcomingHalaqahs->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingHalaqahs as $h)
                <a href="{{ route('web.halaqah.show', $h) }}" wire:navigate class="group block">
                    <div class="bg-base-100 rounded-2xl border border-base-content/5 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                        <div class="bg-gradient-to-r from-primary to-secondary text-primary-content px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="text-center leading-none">
                                    <p class="text-2xl font-black">{{ $h->scheduled_at->format('d') }}</p>
                                    <p class="text-xs font-bold uppercase opacity-80">{{ $h->scheduled_at->format('M') }}</p>
                                </div>
                                <div class="border-l border-white/30 pl-3">
                                    <p class="font-bold text-sm">{{ $h->scheduled_at->format('l') }}</p>
                                    <p class="text-xs opacity-80">{{ $h->scheduled_at->format('g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 flex-grow flex flex-col">
                            @if($h->series)
                                <p class="text-xs font-bold text-primary/70 uppercase tracking-wider mb-1">{{ $h->series->title }}</p>
                            @endif
                            <h3 class="font-bold text-lg text-base-content mb-2 group-hover:text-primary transition-colors">{{ $h->title }}</h3>
                            <p class="text-sm text-base-content/60 mb-4 line-clamp-2 flex-grow">{{ $h->topic }}</p>
                            <div class="flex items-center justify-between text-xs text-base-content/50 pt-3 border-t border-base-content/5">
                                <span class="flex items-center gap-1.5"><x-icon name="o-map-pin" class="w-3.5 h-3.5" /> {{ $h->location }}</span>
                                @if($h->speaker)
                                    <span class="flex items-center gap-1.5"><x-icon name="o-user" class="w-3.5 h-3.5" /> {{ $h->speaker->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
                <div class="text-center py-16 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                    <x-icon name="o-calendar" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                    <p class="text-base-content/50 font-medium">No upcoming sessions scheduled yet.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== FEATURED POSTS ===== --}}
    @if($featuredPosts->count())
    <section class="py-20 bg-base-200/40 dark:bg-base-200/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-amber-600 dark:text-amber-400 font-bold text-sm uppercase tracking-widest mb-2">Knowledge Hub</p>
                    <h2 class="text-3xl font-black text-base-content tracking-tight">Latest from the Blog</h2>
                </div>
                <a href="{{ route('web.posts') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    Visit Blog <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredPosts as $post)
                <a href="{{ route('web.post', $post->slug) }}" wire:navigate class="group block">
                    <div class="bg-base-100 rounded-2xl overflow-hidden border border-base-content/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                        <div class="relative h-48 bg-gradient-to-br from-amber-400/20 to-orange-500/20 overflow-hidden">
                            @if($post->getFirstMediaUrl('featured_image'))
                                <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center"><x-icon name="o-newspaper" class="w-16 h-16 text-amber-500/30" /></div>
                            @endif
                            @if($post->category)
                                <div class="absolute top-3 left-3">
                                    <span class="px-3 py-1 text-xs font-bold text-white bg-black/40 backdrop-blur-sm rounded-full">{{ $post->category->name }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-5 flex-grow flex flex-col">
                            <h3 class="font-bold text-lg text-base-content mb-2 line-clamp-2 group-hover:text-primary transition-colors">{{ $post->title }}</h3>
                            <p class="text-sm text-base-content/60 line-clamp-2 mb-4 flex-grow">{{ $post->excerpt }}</p>
                            <div class="flex items-center gap-3 pt-3 border-t border-base-content/5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ substr($post->user->name ?? 'A', 0, 1) }}
                                </div>
                                <div class="text-xs">
                                    <p class="font-semibold text-base-content">{{ $post->user->name ?? 'Anonymous' }}</p>
                                    <p class="text-base-content/50">{{ $post->published_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== ACTIVE CAMPAIGNS ===== --}}
    @if($activeCampaigns->count())
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-rose-600 dark:text-rose-400 font-bold text-sm uppercase tracking-widest mb-2">Give Back</p>
                    <h2 class="text-3xl font-black text-base-content tracking-tight">Active Campaigns</h2>
                </div>
                <a href="{{ route('web.campaigns') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    See all campaigns <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($activeCampaigns as $campaign)
                <div class="bg-base-100 rounded-2xl overflow-hidden border border-base-content/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                    <div class="h-44 relative overflow-hidden">
                        @if($campaign->cover_url)
                            <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $campaign->title }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-tr from-rose-400/20 to-pink-500/20 flex items-center justify-center"><x-icon name="o-heart" class="w-16 h-16 text-rose-500/30" /></div>
                        @endif
                        <div class="absolute top-3 right-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm text-rose-600 font-black px-3 py-1 rounded-full text-xs shadow">
                            {{ $campaign->progress_percentage }}%
                        </div>
                    </div>
                    <div class="p-5 flex-grow flex flex-col">
                        <h3 class="font-bold text-lg text-base-content mb-2 group-hover:text-primary transition-colors">{{ $campaign->title }}</h3>
                        <p class="text-sm text-base-content/60 line-clamp-2 mb-4 flex-grow">{{ Str::limit($campaign->description, 90) }}</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm font-semibold">
                                <span class="text-emerald-600 dark:text-emerald-400">৳{{ number_format($campaign->collected_amount) }}</span>
                                <span class="opacity-50">of ৳{{ number_format($campaign->goal_amount) }}</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-1000" style="width: {{ $campaign->progress_percentage }}%"></div>
                            </div>
                        </div>
                        @auth
                            <a href="{{ route('app.donations') }}" wire:navigate class="btn btn-primary btn-block btn-sm rounded-xl font-bold shadow-lg shadow-primary/20">Donate Now</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-block btn-sm rounded-xl font-bold shadow-lg shadow-primary/20">Sign in to Donate</a>
                        @endauth
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== DAILY ROUTINE TEASER ===== --}}
    <section class="py-20 bg-base-200/40 dark:bg-base-200/20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 md:p-14 text-center">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-primary/20 rounded-full blur-[120px]"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-clipboard-document-check" class="w-8 h-8 text-amber-300" />
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-white mb-3">Track Your Daily Islamic Routine</h2>
                    <p class="text-white/50 max-w-xl mx-auto mb-8 leading-relaxed">Quran recitation, hadith reading, prayer completion, fasting — track your progress and build consistency with our gamified daily report system.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        @auth
                            <a href="{{ route('app.daily-reports') }}" wire:navigate class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-8 font-bold shadow-xl">
                                Open My Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-8 font-bold shadow-xl">
                                Join Free to Unlock
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== MENTORS ===== --}}
    @if($mentors->count())
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Our People</p>
                    <h2 class="text-3xl font-black text-base-content tracking-tight">Meet Our Mentors</h2>
                </div>
                <a href="{{ route('web.members') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    All Members <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($mentors as $mentor)
                    <div class="text-center group">
                        <div class="w-20 h-20 mx-auto mb-3 rounded-full overflow-hidden ring-4 ring-base-content/5 group-hover:ring-primary/40 transition-all duration-300 group-hover:scale-105">
                            <img src="{{ $mentor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($mentor->name) . '&background=6366f1&color=fff&size=80' }}" alt="{{ $mentor->name }}" class="w-full h-full object-cover">
                        </div>
                        <h4 class="font-bold text-sm text-base-content truncate">{{ $mentor->name }}</h4>
                        <p class="text-xs text-primary font-semibold mt-0.5">{{ ucfirst($mentor->getRoleNames()->first() ?? 'Mentor') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== CTA ===== --}}
    @guest
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-12 md:p-16 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/20 rounded-full blur-[80px]"></div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-black mb-4">Ready to Begin Your Journey?</h2>
                <p class="text-lg text-white/50 mb-8 max-w-xl mx-auto">Create a free account and start attending study circles, accessing materials, and connecting with your community.</p>
                <a href="{{ route('register') }}" class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-10 font-bold shadow-xl">
                    Create Free Account
                </a>
            </div>
        </div>
    </section>
    @endguest

</div>
