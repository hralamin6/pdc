<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
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
                ->take(6)
                ->get(),
            'recentSeries' => HalaqahSeries::withCount('halaqahs')
                ->where('status', 'active')
                ->latest()
                ->take(3)
                ->get(),
            'stats' => [
                'members' => User::count(),
                'sessions' => Halaqah::where('status', 'completed')->count(),
                'courses' => HalaqahSeries::count(),
                'upcoming' => Halaqah::where('scheduled_at', '>=', now())->where('status', 'published')->count(),
            ],
            'mentors' => User::role(['mentor', 'admin', 'super-admin'])->take(4)->get(),
        ];
    }
};
?>

<div class="bg-base-100 dark:bg-base-100">

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white">
        {{-- Geometric pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><path d=%22M30 0L60 30L30 60L0 30Z%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>');"></div>
        {{-- Gradient orbs --}}
        <div class="absolute top-1/4 -left-20 w-72 h-72 bg-primary/30 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/20 rounded-full blur-[120px]"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20 md:pt-36 md:pb-28">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-white/10 border border-white/20 text-white/90">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        Open for enrollment
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] tracking-tight">
                        Where Knowledge
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-300">Meets Brotherhood</span>
                    </h1>
                    <p class="text-lg text-white/70 max-w-xl leading-relaxed">
                        PSTU Dawah Community — a campus-led initiative connecting students through structured Islamic study circles, courses, and mentorship.
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="{{ route('web.halaqahs') }}" wire:navigate class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-8 font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                            Browse Sessions
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline border-white/30 text-white hover:bg-white/10 hover:border-white/50 rounded-xl px-8 font-bold">
                                Join Community
                            </a>
                        @endguest
                    </div>
                </div>

                {{-- Stats cards --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/[0.07] backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:bg-white/[0.12] transition-colors">
                        <x-icon name="o-users" class="w-8 h-8 text-amber-300 mb-3" />
                        <p class="text-3xl font-black">{{ $stats['members'] }}</p>
                        <p class="text-white/50 text-sm font-medium mt-1">Active Members</p>
                    </div>
                    <div class="bg-white/[0.07] backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:bg-white/[0.12] transition-colors">
                        <x-icon name="o-academic-cap" class="w-8 h-8 text-emerald-300 mb-3" />
                        <p class="text-3xl font-black">{{ $stats['sessions'] }}</p>
                        <p class="text-white/50 text-sm font-medium mt-1">Sessions Completed</p>
                    </div>
                    <div class="bg-white/[0.07] backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:bg-white/[0.12] transition-colors">
                        <x-icon name="o-book-open" class="w-8 h-8 text-sky-300 mb-3" />
                        <p class="text-3xl font-black">{{ $stats['courses'] }}</p>
                        <p class="text-white/50 text-sm font-medium mt-1">Active Courses</p>
                    </div>
                    <div class="bg-white/[0.07] backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:bg-white/[0.12] transition-colors">
                        <x-icon name="o-calendar-days" class="w-8 h-8 text-rose-300 mb-3" />
                        <p class="text-3xl font-black">{{ $stats['upcoming'] }}</p>
                        <p class="text-white/50 text-sm font-medium mt-1">Upcoming Sessions</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== UPCOMING SESSIONS ===== --}}
    <section class="py-20 bg-base-200/50 dark:bg-base-200/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Next Up</p>
                    <h2 class="text-3xl font-extrabold text-base-content">Upcoming Study Sessions</h2>
                </div>
                <a href="{{ route('web.halaqahs') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    View all <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>

            @if($upcomingHalaqahs->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingHalaqahs->take(3) as $halaqah)
                <a href="{{ route('web.halaqah.show', $halaqah) }}" wire:navigate class="group block">
                    <div class="bg-base-100 dark:bg-base-100 rounded-2xl border border-base-content/5 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                        {{-- Date ribbon --}}
                        <div class="bg-gradient-to-r from-primary to-secondary text-primary-content px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="text-center leading-none">
                                    <p class="text-2xl font-black">{{ $halaqah->scheduled_at->format('d') }}</p>
                                    <p class="text-xs font-bold uppercase opacity-80">{{ $halaqah->scheduled_at->format('M') }}</p>
                                </div>
                                <div class="border-l border-white/30 pl-3">
                                    <p class="font-bold text-sm">{{ $halaqah->scheduled_at->format('l') }}</p>
                                    <p class="text-xs opacity-80">{{ $halaqah->scheduled_at->format('g:i A') }}</p>
                                </div>
                            </div>
                            @if($halaqah->gender_restriction !== 'none')
                                <span class="badge badge-sm bg-white/20 border-white/30 text-white">{{ str_replace('_', ' ', ucfirst($halaqah->gender_restriction)) }}</span>
                            @endif
                        </div>
                        
                        <div class="p-5 flex-grow flex flex-col">
                            @if($halaqah->series)
                                <p class="text-xs font-bold text-primary/70 uppercase tracking-wider mb-1">{{ $halaqah->series->title }}</p>
                            @endif
                            <h3 class="font-bold text-lg text-base-content mb-2 group-hover:text-primary transition-colors">{{ $halaqah->title }}</h3>
                            <p class="text-sm text-base-content/60 mb-4 line-clamp-2 flex-grow">{{ $halaqah->topic }}</p>
                            
                            <div class="flex items-center justify-between text-xs text-base-content/50 pt-3 border-t border-base-content/5">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="o-map-pin" class="w-3.5 h-3.5" /> {{ $halaqah->location }}
                                </span>
                                @if($halaqah->speaker)
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="o-user" class="w-3.5 h-3.5" /> {{ $halaqah->speaker->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
                <div class="text-center py-16 bg-base-100 rounded-2xl border border-dashed border-base-content/10">
                    <x-icon name="o-calendar" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                    <p class="text-base-content/50 font-medium">No upcoming sessions scheduled yet.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== FEATURES / WHAT WE OFFER ===== --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">What We Offer</p>
                <h2 class="text-3xl font-extrabold text-base-content">Built for Serious Seekers of Knowledge</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $features = [
                        ['icon' => 'o-book-open', 'title' => 'Structured Study Circles', 'desc' => 'Join curated halaqahs organized in multi-session series with progressive learning paths and materials.', 'color' => 'primary'],
                        ['icon' => 'o-clipboard-document-check', 'title' => 'Preparation Tracking', 'desc' => 'Mark your reading completion before each session. Mentors can verify and monitor student engagement.', 'color' => 'secondary'],
                        ['icon' => 'o-chart-bar', 'title' => 'Series Progress', 'desc' => 'Visual progress tracking across multi-session courses so you know exactly where you stand.', 'color' => 'accent'],
                        ['icon' => 'o-document-text', 'title' => 'Session Materials', 'desc' => 'Download PDFs, access reference links, and review resources before and after every session.', 'color' => 'info'],
                        ['icon' => 'o-sparkles', 'title' => 'AI-Powered Planning', 'desc' => 'Mentors can use the built-in AI assistant to generate complete session plans from a quick description.', 'color' => 'warning'],
                        ['icon' => 'o-users', 'title' => 'Community & Chat', 'desc' => 'Connect with brothers and sisters through direct messaging, real-time chat, and push notifications.', 'color' => 'success'],
                    ];
                @endphp

                @foreach($features as $feature)
                    <div class="relative p-6 rounded-2xl bg-base-100 dark:bg-base-100 border border-base-content/5 hover:border-{{ $feature['color'] }}/30 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-xl bg-{{ $feature['color'] }}/10 text-{{ $feature['color'] }} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <x-icon :name="$feature['icon']" class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-base-content mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-base-content/60 leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== COURSES / SERIES ===== --}}
    @if($recentSeries->count())
    <section class="py-20 bg-base-200/50 dark:bg-base-200/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Courses</p>
                    <h2 class="text-3xl font-extrabold text-base-content">Active Study Series</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($recentSeries as $series)
                    <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between mb-4">
                            <span class="badge badge-primary badge-sm">{{ ucfirst($series->target_audience_level) }}</span>
                            <span class="text-xs text-base-content/50 font-medium">{{ $series->halaqahs_count }} sessions</span>
                        </div>
                        <h3 class="text-xl font-bold text-base-content mb-2">{{ $series->title }}</h3>
                        <p class="text-sm text-base-content/60 line-clamp-3 leading-relaxed">{{ $series->description ?: 'A structured learning series for our community.' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== MENTORS ===== --}}
    @if($mentors->count())
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
                <div>
                    <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Our People</p>
                    <h2 class="text-3xl font-extrabold text-base-content">Meet Our Mentors</h2>
                </div>
                <a href="{{ route('web.members') }}" wire:navigate class="text-primary font-semibold text-sm hover:underline flex items-center gap-1 group">
                    All Members <x-icon name="o-arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($mentors as $mentor)
                    <div class="text-center group">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden ring-4 ring-base-content/5 group-hover:ring-primary/30 transition-all">
                            <img src="{{ $mentor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($mentor->name) . '&background=6366f1&color=fff&size=96' }}" alt="{{ $mentor->name }}" class="w-full h-full object-cover">
                        </div>
                        <h4 class="font-bold text-base-content">{{ $mentor->name }}</h4>
                        <p class="text-xs text-base-content/50 font-medium mt-1">
                            @php $roles = $mentor->getRoleNames(); @endphp
                            {{ $roles->first() ? ucfirst($roles->first()) : 'Mentor' }}
                        </p>
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
                <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Ready to Begin Your Journey?</h2>
                <p class="text-lg text-white/60 mb-8 max-w-xl mx-auto">Create a free account and start attending study circles, accessing materials, and connecting with your community.</p>
                <a href="{{ route('register') }}" class="btn bg-white text-slate-900 hover:bg-white/90 border-none rounded-xl px-10 font-bold shadow-lg">
                    Create Free Account
                </a>
            </div>
        </div>
    </section>
    @endguest

</div>
