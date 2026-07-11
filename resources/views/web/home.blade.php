<?php

use App\Models\DonationCampaign;
use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\Post;
use App\Models\User;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\Donation;
use App\Models\Expense;
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
            'latestBooks' => Book::with('author')->latest()->take(3)->get(),
            'activeQuiz' => Quiz::whereIn('status', ['published', 'live'])->latest()->first(),
            'stats' => [
                'members' => User::count(),
                'sessions' => Halaqah::where('status', 'completed')->count(),
                'courses' => HalaqahSeries::count(),
                'campaigns' => DonationCampaign::where('status', 'active')->count(),
                'total_donations' => Donation::sum('amount') ?? 0,
                'total_expenses' => Expense::sum('amount') ?? 0,
            ],
            'mentors' => User::role(['mentor', 'admin', 'super-admin'])->take(6)->get(),
        ];
    }
};
?>

<div class="bg-slate-50 dark:bg-slate-900 font-sans selection:bg-cyan-500/30 selection:text-cyan-900 dark:selection:text-cyan-100 overflow-hidden">

    {{-- ===== HERO (GAMIFIED & VIBRANT) ===== --}}
    <section class="relative bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white min-h-[90vh] flex items-center pt-16">
        <!-- Vibrant Orbs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-fuchsia-400/20 dark:bg-fuchsia-600/30 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-screen animate-pulse duration-1000"></div>
            <div class="absolute top-40 -left-40 w-[500px] h-[500px] bg-cyan-400/20 dark:bg-cyan-600/30 rounded-full blur-[100px] mix-blend-multiply dark:mix-blend-screen"></div>
            <div class="absolute -bottom-40 left-1/2 w-[800px] h-[800px] bg-violet-400/10 dark:bg-violet-600/20 rounded-full blur-[150px] mix-blend-multiply dark:mix-blend-screen"></div>
            
            <!-- Grid pattern -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djI2aC0ydjI0aDJWMEgzNHYzNGgyem0yMCAwdjI2aC0ydjI0aDJWMGgtMnYzNGgyem0tNDAgMHYyNmgtMnYyNGgyVjBIMTR2MzRoMnptMjAtMTRoMjZ2LTJoLTI2di0yejI2IDM0aC0yNnYyaDI2di0yeiIgLz48L2c+PC9nPjwvc3ZnPg==')] opacity-[0.03] dark:opacity-30 mix-blend-difference dark:mix-blend-normal"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="text-center max-w-4xl mx-auto space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-white/90 shadow-md dark:shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                    <span class="w-2.5 h-2.5 bg-cyan-500 dark:bg-cyan-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(6,182,212,0.5)] dark:shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
                    {{ __('Ready for Enrollment') }}
                </div>
                
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black leading-[1.1] tracking-tight text-slate-900 dark:text-white">
                    {{ __('Level Up Your') }}
                    <span class="block mt-2 text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-fuchsia-600 to-amber-500 dark:from-cyan-400 dark:via-fuchsia-400 dark:to-amber-400 drop-shadow-sm">{{ __('Deen & Brotherhood') }}</span>
                </h1>
                
                <p class="text-xl text-slate-600 dark:text-white/70 max-w-2xl mx-auto leading-relaxed font-medium">
                    {{ __('PSTU Dawah Community — Gamifying your Islamic journey. Join circles, track your habits, earn XP, and grow together.') }}
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6">
                    <a href="{{ route('web.halaqahs') }}" wire:navigate class="btn bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-600 hover:to-blue-700 border-none rounded-2xl px-10 h-14 font-black shadow-lg dark:shadow-[0_0_30px_rgba(6,182,212,0.4)] hover:-translate-y-1 transition-transform text-lg">
                        {{ __('Explore Quests') }}
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn bg-white dark:bg-white/10 border border-slate-200 dark:border-white/20 text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 rounded-2xl px-10 h-14 font-black shadow-sm dark:shadow-none hover:-translate-y-1 transition-transform text-lg">
                            {{ __('Create Avatar') }}
                        </a>
                    @endguest
                </div>

                <!-- Stats Badges -->
                <div class="flex flex-wrap justify-center gap-6 sm:gap-12 pt-12">
                    @php $statCards = [
                        ['val' => $stats['members'], 'label' => __('Active Players'), 'clr' => 'cyan'],
                        ['val' => $stats['sessions'], 'label' => __('Sessions Complete'), 'clr' => 'fuchsia'],
                        ['val' => $stats['courses'], 'label' => __('Active Series'), 'clr' => 'amber'],
                    ]; @endphp
                    @foreach($statCards as $sc)
                        <div class="flex flex-col items-center">
                            <p class="text-3xl md:text-5xl font-black text-{{ $sc['clr'] }}-600 dark:text-{{ $sc['clr'] }}-400 dark:drop-shadow-[0_0_15px_rgba(var(--tw-colors-{{ $sc['clr'] }}-400),0.5)]">{{ $sc['val'] }}</p>
                            <p class="text-slate-500 dark:text-white/60 text-xs font-bold uppercase tracking-widest mt-2">{{ $sc['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===== PRAYER TIMES BAR (BARISHAL FALLBACK) ===== --}}
    <section class="relative -mt-10 z-20 max-w-6xl mx-auto px-4"
        x-data="{
            times: {}, nextName: '---', nextTime: '--:--',
            async boot() {
                // Fallback to Barishal immediately
                await this.fetchTimes(22.7010, 90.3535);
                
                // Try GPS in background, update if allowed
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => this.fetchTimes(pos.coords.latitude, pos.coords.longitude),
                        () => {} // silent fail, keep Barishal
                    );
                }
            },
            async fetchTimes(lat, lng) {
                try {
                    const res = await fetch(`https://api.aladhan.com/v1/timings?latitude=${lat}&longitude=${lng}&method=1&school=1`);
                    const d = (await res.json()).data.timings;
                    this.times = {Fajr: d.Fajr, Dhuhr: d.Dhuhr, Asr: d.Asr, Maghrib: d.Maghrib, Isha: d.Isha};
                    this.findNext();
                } catch {}
            },
            findNext() {
                const now = new Date(); const hm = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
                for (const [n,t] of Object.entries(this.times)) { if (t > hm) { this.nextName=n; this.nextTime=t; return; } }
                const first = Object.entries(this.times)[0]; if(first) { this.nextName=first[0]; this.nextTime=first[1]; }
            }
        }" x-init="boot()">
        <div class="bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 p-5 md:p-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 bg-white/5 rounded-[1.5rem] p-3 pr-8 border border-white/5 w-full md:w-auto shrink-0">
                    <div class="w-14 h-14 bg-cyan-500 rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(6,182,212,0.6)] shrink-0">
                        <x-icon name="o-clock" class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-400">{{ __('Next Objective') }}</p>
                        <p class="text-2xl font-black text-white leading-none mt-1 whitespace-nowrap" x-text="nextName + ' — ' + nextTime"></p>
                    </div>
                </div>
                
                <div class="flex gap-2 md:gap-6 w-full overflow-x-auto pb-2 md:pb-0 hide-scrollbar justify-start md:justify-end">
                    <template x-for="[name, time] in Object.entries(times)" :key="name">
                        <div class="text-center px-4 md:px-5 py-3 rounded-2xl transition-all min-w-[80px]" 
                             :class="name === nextName ? 'bg-cyan-500/20 border border-cyan-500/50 shadow-[0_0_20px_rgba(6,182,212,0.2)]' : 'border border-transparent opacity-70'">
                            <p class="text-[10px] font-black uppercase tracking-widest text-white mb-1" x-text="name"></p>
                            <p class="text-lg md:text-xl font-bold" :class="name === nextName ? 'text-cyan-400' : 'text-white'" x-text="time"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SHOWCASE TEASER ===== --}}
    @php
        $featuredAlbums = \App\Models\GalleryAlbum::where('is_published', true)->with('media')->latest()->take(3)->get();
    @endphp
    @if($featuredAlbums->count() > 0)
    <section class="py-12 relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-800 dark:text-white">{{ __('Community Memories') }}</h2>
                <p class="text-slate-500 mt-1 text-sm font-semibold">{{ __('Glimpses of our brotherhood and impact') }}</p>
            </div>
            <a href="{{ route('web.showcase') }}" wire:navigate class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-fuchsia-600 dark:text-fuchsia-400 hover:text-fuchsia-500 transition-colors">
                {{ __('View Full Gallery') }} <x-icon name="o-arrow-right" class="w-4 h-4" />
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredAlbums as $album)
                <a href="{{ route('web.showcase') }}" wire:navigate class="group relative h-64 md:h-72 rounded-[2rem] overflow-hidden shadow-lg border border-slate-200 dark:border-slate-800 block">
                    @if($album->getFirstMediaUrl('gallery_images'))
                        <img src="{{ $album->getFirstMediaUrl('gallery_images') }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                    @else
                        <div class="absolute inset-0 bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                            <x-icon name="o-photo" class="w-12 h-12 text-slate-400 opacity-50" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <span class="inline-block px-2 py-1 mb-2 text-[10px] font-black uppercase tracking-wider rounded-md bg-white/20 text-white backdrop-blur-md border border-white/20">{{ $album->category }}</span>
                        <h3 class="text-xl font-bold text-white">{{ $album->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6 text-center sm:hidden">
            <a href="{{ route('web.showcase') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-fuchsia-600 dark:text-fuchsia-400">
                {{ __('View Full Gallery') }} <x-icon name="o-arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </section>
    @endif

    {{-- ===== BENTO BOX (TREASURY, QUIZ, BOOKS, ROUTINE) ===== --}}
    <section class="py-16 md:py-24 relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            <!-- TREASURY: Col span 12 or 7 -->
            <div class="md:col-span-7 bg-gradient-to-br from-indigo-900 to-violet-900 rounded-[2.5rem] p-8 md:p-10 text-white relative overflow-hidden shadow-2xl shadow-indigo-900/20 group">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-fuchsia-500/30 rounded-full blur-[80px] group-hover:bg-fuchsia-500/40 transition-colors duration-700 pointer-events-none"></div>
                
                <div class="flex justify-between items-start mb-10 relative z-10">
                    <div>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-white/10 shadow-lg">
                            <x-icon name="o-banknotes" class="w-4 h-4 text-emerald-400" />
                            {{ __('Treasury Transparency') }}
                        </div>
                        <h3 class="text-3xl md:text-4xl font-black tracking-tight">{{ __('Community Fund') }}</h3>
                        <p class="text-white/70 text-sm mt-2 max-w-sm font-medium">{{ __('Open records of all donations and expenses to ensure trust.') }}</p>
                    </div>
                    <a href="{{ route('app.donations') }}" class="btn btn-circle btn-ghost bg-white/10 hover:bg-white/20 border border-white/10 shrink-0">
                        <x-icon name="o-arrow-right" class="w-5 h-5 text-white"/>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 relative z-10">
                    <div class="bg-black/20 backdrop-blur-md rounded-3xl p-6 border border-white/5">
                        <p class="text-white/50 text-[10px] font-black uppercase tracking-widest mb-2">{{ __('Total Raised XP') }}</p>
                        <p class="text-3xl md:text-4xl font-black text-emerald-400 drop-shadow-[0_0_10px_rgba(52,211,153,0.3)]">৳{{ number_format($stats['total_donations']) }}</p>
                    </div>
                    <div class="bg-black/20 backdrop-blur-md rounded-3xl p-6 border border-white/5">
                        <p class="text-white/50 text-[10px] font-black uppercase tracking-widest mb-2">{{ __('Total Spent XP') }}</p>
                        <p class="text-3xl md:text-4xl font-black text-rose-400 drop-shadow-[0_0_10px_rgba(251,113,133,0.3)]">৳{{ number_format($stats['total_expenses']) }}</p>
                    </div>
                </div>
            </div>

            <!-- QUIZ: Col span 5 -->
            @if($activeQuiz)
            <div class="md:col-span-5 bg-gradient-to-br from-amber-500 to-orange-600 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl shadow-orange-500/20 flex flex-col justify-between group overflow-hidden relative">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/20 rounded-full blur-[50px] group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-black/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-white/20 shadow-lg">
                        <x-icon name="o-trophy" class="w-4 h-4 text-yellow-200" />
                        {{ __('Live Quest') }}
                    </div>
                    <h3 class="text-3xl font-black mb-3 leading-tight drop-shadow-md">{{ Str::limit($activeQuiz->title, 40) }}</h3>
                    <div class="flex gap-4 items-center">
                        <span class="bg-black/20 px-3 py-1 rounded-lg text-xs font-bold">{{ $activeQuiz->questions_count ?? 10 }} Qs</span>
                        <span class="bg-black/20 px-3 py-1 rounded-lg text-xs font-bold text-yellow-200">{{ $activeQuiz->total_marks ?? 10 }} XP</span>
                    </div>
                </div>
                
                <div class="mt-10 relative z-10">
                    <a href="{{ route('app.quiz.take', $activeQuiz) }}" class="btn bg-white text-orange-600 hover:bg-orange-50 hover:scale-105 border-none rounded-2xl w-full font-black text-lg shadow-[0_10px_20px_rgba(0,0,0,0.2)] transition-transform h-14">
                        {{ __('Play & Earn XP') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- BOOKS: Col span 6 -->
            <div class="md:col-span-6 bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 border-2 border-slate-100 dark:border-slate-700/50 shadow-xl shadow-slate-200/50 dark:shadow-none flex flex-col justify-between">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center">
                            <x-icon name="o-book-open" class="w-5 h-5" />
                        </div>
                        {{ __('Grimoires (Library)') }}
                    </h3>
                    <a href="{{ route('app.books') }}" class="text-indigo-500 dark:text-indigo-400 text-sm font-bold hover:underline">{{ __('All Books') }}</a>
                </div>
                
                <div class="space-y-4">
                    @forelse($latestBooks as $book)
                    <div class="flex items-center gap-5 group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/30 p-2 -mx-2 rounded-2xl transition-colors">
                        <div class="w-14 h-20 bg-slate-200 dark:bg-slate-700 rounded-xl overflow-hidden shrink-0 shadow-md group-hover:-translate-y-1 group-hover:shadow-lg group-hover:shadow-indigo-500/20 transition-all">
                            @if($book->cover_url)
                                <img src="{{ $book->cover_url }}" class="w-full h-full object-cover" alt="Cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500"><x-icon name="o-book-open" class="w-6 h-6"/></div>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 dark:text-white text-base line-clamp-1 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors">{{ $book->title }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-bold">{{ $book->author?->name ?? __('Unknown Scholar') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 font-bold py-4">{{ __('The library is currently being stocked.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- DAILY ROUTINE: Col span 6 -->
            <div class="md:col-span-6 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-[2.5rem] p-8 md:p-10 text-white shadow-xl shadow-teal-500/20 relative overflow-hidden group">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc2MCcgaGVpZ2h0PSc2MCc+PHBhdGggZD0nTTU0LjYyNyAwdjYwaC02VjB6bS0xMiAwdjYwaC02VjB6bS0xMiAwdjYwaC02VjB6bS0xMiAwdjYwaC02VjB6TTEyIDB2NjBINlYwTTAgMHY2MGg2VjB6JyBmaWxsPSd3aGl0ZScgZmlsbC1vcGFjaXR5PScwLjA1JyBmaWxsLXJ1bGU9J2V2ZW5vZGQnLz48L3N2Zz4=')] opacity-50"></div>
                
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-black/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-white/20 shadow-lg">
                            <x-icon name="o-check-badge" class="w-4 h-4 text-white" />
                            {{ __('Daily Grinding') }}
                        </div>
                        <h3 class="text-3xl font-black mb-3 tracking-tight">{{ __('Islamic Routine') }}</h3>
                        <p class="text-white/80 text-sm font-medium leading-relaxed max-w-sm">{{ __('Track your Salah, Quran, and Habits daily to build consistency and level up.') }}</p>
                    </div>
                    
                    <div class="mt-8 flex items-center justify-between bg-black/10 backdrop-blur-sm p-4 rounded-3xl border border-white/10">
                        <div class="flex items-center gap-4">
                            <div class="flex -space-x-3">
                                @foreach($mentors->take(3) as $mentor)
                                    <img class="w-12 h-12 rounded-full border-2 border-teal-500 shadow-lg object-cover" src="{{ $mentor->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($mentor->name).'&background=fff&color=14b8a6' }}" alt="Mentor">
                                @endforeach
                            </div>
                            <div class="text-xs font-bold leading-tight">
                                <p>{{ __('Join') }} {{ $stats['members'] }}+</p>
                                <p class="text-teal-100">{{ __('Active Players') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('app.daily-reports') }}" class="btn btn-circle bg-white text-teal-600 hover:bg-teal-50 border-none shadow-lg hover:scale-110 transition-transform">
                            <x-icon name="o-play" class="w-6 h-6 ml-1"/>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    {{-- ===== UPCOMING SESSIONS (QUESTS) ===== --}}
    <section class="py-24 bg-slate-100 dark:bg-slate-800/30 relative border-y-2 border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-16">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                        <x-icon name="o-map" class="w-4 h-4" /> {{ __('Available Quests') }}
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Upcoming Sessions') }}</h2>
                </div>
                <a href="{{ route('web.halaqahs') }}" wire:navigate class="btn btn-outline border-slate-300 dark:border-slate-700 dark:text-white rounded-2xl font-black shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700">
                    {{ __('View All Quests') }}
                </a>
            </div>
            
            @if($upcomingHalaqahs->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($upcomingHalaqahs as $h)
                <a href="{{ route('web.halaqah.show', $h) }}" wire:navigate class="group block h-full">
                    <div class="bg-white dark:bg-slate-800 rounded-[2rem] border-2 border-slate-200 dark:border-slate-700/50 overflow-hidden hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-[0_20px_40px_rgba(99,102,241,0.15)] hover:-translate-y-2 transition-all duration-300 h-full flex flex-col relative">
                        <!-- Date Badge -->
                        <div class="absolute top-5 right-5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl p-3 min-w-[70px] text-center border-2 border-slate-100 dark:border-slate-700 shadow-xl z-10 group-hover:border-indigo-500 transition-colors">
                            <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase leading-none mb-1">{{ $h->scheduled_at->format('M') }}</p>
                            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 leading-none">{{ $h->scheduled_at->format('d') }}</p>
                        </div>
                        
                        <div class="h-40 p-6 flex flex-col justify-end bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 relative overflow-hidden">
                            <!-- Background accent -->
                            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl"></div>
                            
                            @if($h->series)
                                <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-1 relative z-10">{{ $h->series->title }}</p>
                            @else
                                <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1 relative z-10">{{ __('Single Event') }}</p>
                            @endif
                        </div>
                        
                        <div class="p-8 flex-grow flex flex-col">
                            <h3 class="font-black text-2xl text-slate-900 dark:text-white mb-3 leading-tight">{{ $h->title }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-8 line-clamp-2 flex-grow font-medium leading-relaxed">{{ $h->topic }}</p>
                            
                            <div class="flex items-center justify-between pt-5 border-t-2 border-dashed border-slate-200 dark:border-slate-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-500 border border-indigo-200 dark:border-indigo-800">
                                        <x-icon name="o-user" class="w-5 h-5"/>
                                    </div>
                                    <p class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $h->speaker->name ?? __('TBA') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ $h->scheduled_at->format('l') }}</p>
                                    <p class="text-sm font-black text-slate-800 dark:text-white">{{ $h->scheduled_at->format('g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
                <div class="text-center py-20 bg-white dark:bg-slate-800/50 rounded-[3rem] border-2 border-dashed border-slate-300 dark:border-slate-700">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-calendar" class="w-10 h-10 text-slate-400 dark:text-slate-500" />
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-black text-xl">{{ __('No active quests currently.') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== ACTIVE CAMPAIGNS (GUILD SUPPORT) ===== --}}
    @if($activeCampaigns->count())
    <section class="py-24 relative bg-slate-50 dark:bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-16">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                        <x-icon name="o-heart" class="w-4 h-4" /> {{ __('Support the Guild') }}
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Active Campaigns') }}</h2>
                </div>
                <a href="{{ route('web.campaigns') }}" wire:navigate class="btn btn-outline border-slate-300 dark:border-slate-700 dark:text-white rounded-2xl font-black shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700">
                    {{ __('View All') }}
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($activeCampaigns as $campaign)
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border-2 border-slate-200 dark:border-slate-700/50 overflow-hidden hover:border-rose-500 dark:hover:border-rose-400 hover:shadow-[0_20px_40px_rgba(244,63,94,0.15)] hover:-translate-y-2 transition-all duration-300 group flex flex-col">
                    <div class="h-56 relative overflow-hidden bg-rose-50 dark:bg-rose-900/20">
                        @if($campaign->cover_url)
                            <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $campaign->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center"><x-icon name="o-sparkles" class="w-16 h-16 text-rose-300 dark:text-rose-700/50" /></div>
                        @endif
                        
                        <!-- Progress Badge -->
                        <div class="absolute top-5 right-5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md text-rose-600 dark:text-rose-400 font-black px-4 py-2 rounded-xl text-sm shadow-xl border border-slate-200 dark:border-slate-700">
                            {{ $campaign->progress_percentage }}% {{ __('Funded') }}
                        </div>
                    </div>
                    
                    <div class="p-8 flex-grow flex flex-col">
                        <h3 class="font-black text-2xl text-slate-900 dark:text-white mb-4 leading-tight group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">{{ $campaign->title }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-8 flex-grow font-medium leading-relaxed">{{ Str::limit($campaign->description, 90) }}</p>
                        
                        <div class="space-y-3 mb-8 bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl border-2 border-slate-100 dark:border-slate-700/50">
                            <div class="flex justify-between text-sm font-black">
                                <span class="text-emerald-500 dark:text-emerald-400">৳{{ number_format($campaign->collected_amount) }}</span>
                                <span class="text-slate-400">/ ৳{{ number_format($campaign->goal_amount) }}</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-1000 relative" style="width: {{ $campaign->progress_percentage }}%">
                                    <div class="absolute inset-0 bg-white/20 w-full animate-[pulse_2s_infinite]"></div>
                                </div>
                            </div>
                        </div>
                        
                        <a href="{{ route('app.donations') }}" wire:navigate class="btn bg-rose-500 hover:bg-rose-600 text-white btn-block rounded-xl font-black text-lg shadow-[0_10px_20px_rgba(244,63,94,0.3)] h-14">
                            {{ __('Contribute Now') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== KNOWLEDGE BASE (BLOG) ===== --}}
    @if($featuredPosts->count())
    <section class="py-24 bg-slate-100 dark:bg-slate-800/30 relative border-t-2 border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-16">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                        <x-icon name="o-academic-cap" class="w-4 h-4" /> {{ __('Knowledge Hub') }}
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Lore & Insights') }}</h2>
                </div>
                <a href="{{ route('web.posts') }}" wire:navigate class="btn btn-outline border-slate-300 dark:border-slate-700 dark:text-white rounded-2xl font-black shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700">
                    {{ __('Read All Archives') }}
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Large Featured Post (First) -->
                @if($mainPost = $featuredPosts->first())
                <div class="lg:col-span-7">
                    <a href="{{ route('web.post', $mainPost->slug) }}" wire:navigate class="group block h-full">
                        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-200 dark:border-slate-700/50 overflow-hidden hover:border-amber-400 hover:shadow-[0_20px_40px_rgba(251,191,36,0.15)] hover:-translate-y-2 transition-all duration-300 h-full flex flex-col relative">
                            <div class="h-72 sm:h-96 relative overflow-hidden bg-gradient-to-br from-amber-400 to-orange-500">
                                @if($mainPost->getFirstMediaUrl('featured_image'))
                                    <img src="{{ $mainPost->getFirstMediaUrl('featured_image') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="{{ $mainPost->title }}">
                                @endif
                                @if($mainPost->category)
                                    <div class="absolute top-6 left-6">
                                        <span class="px-5 py-2.5 text-xs font-black text-slate-900 bg-amber-300 rounded-xl shadow-xl uppercase tracking-widest">{{ $mainPost->category->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-8 md:p-10 flex-grow flex flex-col justify-center">
                                <h3 class="font-black text-3xl md:text-4xl text-slate-900 dark:text-white mb-5 group-hover:text-amber-500 transition-colors leading-tight">{{ $mainPost->title }}</h3>
                                <p class="text-base text-slate-600 dark:text-slate-400 line-clamp-3 mb-8 font-medium leading-relaxed">{{ $mainPost->excerpt }}</p>
                                
                                <div class="flex items-center gap-4 mt-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-black text-slate-700 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-600">
                                        {{ substr($mainPost->user->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-sm text-slate-900 dark:text-white">{{ $mainPost->user->name ?? __('Archivist') }}</p>
                                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mt-0.5">{{ $mainPost->published_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                
                <!-- Smaller Posts (Rest) -->
                <div class="lg:col-span-5 flex flex-col gap-8">
                    @foreach($featuredPosts->skip(1) as $post)
                    <a href="{{ route('web.post', $post->slug) }}" wire:navigate class="group block h-full">
                        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border-2 border-slate-200 dark:border-slate-700/50 overflow-hidden hover:border-amber-400 hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 h-full flex flex-col sm:flex-row">
                            <div class="w-full sm:w-2/5 h-56 sm:h-auto relative bg-amber-100 dark:bg-amber-900/20 overflow-hidden shrink-0">
                                @if($post->getFirstMediaUrl('featured_image'))
                                    <img src="{{ $post->getFirstMediaUrl('featured_image') }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $post->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center"><x-icon name="o-document-text" class="w-12 h-12 text-amber-500/50" /></div>
                                @endif
                            </div>
                            <div class="p-6 md:p-8 flex flex-col justify-center flex-grow">
                                @if($post->category)
                                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-3">{{ $post->category->name }}</p>
                                @endif
                                <h3 class="font-black text-xl text-slate-900 dark:text-white mb-3 group-hover:text-amber-500 transition-colors line-clamp-2 leading-snug">{{ $post->title }}</h3>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-auto">{{ $post->published_at?->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ===== MENTORS (GUILD LEADERS) ===== --}}
    @if($mentors->count())
    <section class="py-24 relative bg-slate-50 dark:bg-slate-900 border-t-2 border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-3">{{ __('Guild Leaders') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 font-medium mb-16 max-w-2xl mx-auto">{{ __('Learn from experienced mentors guiding the community quests.') }}</p>
            
            <div class="flex flex-wrap justify-center gap-10 md:gap-16">
                @foreach($mentors as $mentor)
                    <div class="text-center group cursor-pointer">
                        <div class="w-28 h-28 mx-auto mb-5 rounded-[2rem] rotate-3 overflow-hidden border-4 border-white dark:border-slate-800 shadow-xl group-hover:rotate-0 group-hover:scale-110 group-hover:border-cyan-400 group-hover:shadow-[0_15px_30px_rgba(6,182,212,0.3)] transition-all duration-300">
                            <img src="{{ $mentor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($mentor->name) . '&background=06b6d4&color=fff' }}" alt="{{ $mentor->name }}" class="w-full h-full object-cover">
                        </div>
                        <h4 class="font-black text-slate-900 dark:text-white text-base">{{ $mentor->name }}</h4>
                        <p class="text-[10px] font-black uppercase tracking-widest text-cyan-500 mt-1">{{ ucfirst($mentor->getRoleNames()->first() ?? __('Mentor')) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== CTA ===== --}}
    @guest
    <section class="py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-900 pb-32">
        <div class="max-w-5xl mx-auto text-center bg-gradient-to-br from-indigo-600 via-purple-600 to-fuchsia-600 rounded-[3rem] p-12 md:p-24 text-white relative overflow-hidden shadow-[0_30px_60px_rgba(147,51,234,0.3)]">
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-[80px]"></div>
            <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-black/20 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10">
                <x-icon name="o-rocket-launch" class="w-20 h-20 mx-auto mb-8 text-white/90 drop-shadow-lg" />
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black mb-6 tracking-tight leading-tight">{{ __('Ready to Start Your Journey?') }}</h2>
                <p class="text-lg md:text-xl text-white/80 mb-12 max-w-2xl mx-auto font-medium leading-relaxed">{{ __('Join the PSTU Dawah Community today. Track habits, attend sessions, and earn your place on the leaderboards.') }}</p>
                
                <a href="{{ route('register') }}" class="btn bg-white text-purple-700 hover:bg-slate-50 hover:scale-105 border-none rounded-2xl px-12 h-16 font-black text-xl shadow-[0_15px_30px_rgba(255,255,255,0.2)] transition-transform">
                    {{ __('Create Free Account') }}
                </a>
            </div>
        </div>
    </section>
    @endguest

</div>
