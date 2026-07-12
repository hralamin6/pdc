<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Halaqahs & Study Circles')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'upcoming'; // upcoming | series | past

    #[Url]
    public string $search = '';

    #[Url]
    public string $series_filter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->search = '';
        $this->series_filter = '';
    }

    public function with(): array
    {
        $query = Halaqah::query()
            ->with(['speaker', 'series'])
            ->withCount(['attendances as rsvp_count' => fn ($q) => $q->where('status_new', 'rsvp')]);

        if ($this->activeTab === 'upcoming') {
            $query->whereIn('status', ['published'])->where('scheduled_at', '>=', now())->orderBy('scheduled_at', 'asc');
        } elseif ($this->activeTab === 'past') {
            $query->whereIn('status', ['published', 'completed'])->where('scheduled_at', '<', now())->orderBy('scheduled_at', 'desc');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('topic', 'like', "%{$this->search}%")
                  ->orWhere('location', 'like', "%{$this->search}%");
            });
        }

        if ($this->series_filter) {
            $query->where('series_id', $this->series_filter);
        }

        $seriesQuery = HalaqahSeries::withCount(['halaqahs', 'quizzes'])
            ->where('status', 'active')
            ->orderBy('title');

        return [
            'halaqahs'       => ($this->activeTab !== 'series') ? $query->paginate(9) : Halaqah::query()->whereRaw('1=0')->paginate(1),
            'allSeries'      => HalaqahSeries::where('status', 'active')->orderBy('title')->get(),
            'series'         => $seriesQuery->paginate(9, ['*'], 'seriesPage'),
            'totalUpcoming'  => Halaqah::where('scheduled_at', '>=', now())->where('status', 'published')->count(),
            'totalCompleted' => Halaqah::where('status', 'completed')->count(),
            'totalSeries'    => HalaqahSeries::where('status', 'active')->count(),
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- ═══════════════════════════ HERO ═══════════════════════════ --}}
    <div class="relative bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white overflow-hidden">
        {{-- Background blobs --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/15 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-10 w-72 h-72 bg-secondary/10 rounded-full blur-[100px] translate-y-1/2 pointer-events-none"></div>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/20 border border-primary/30 text-primary text-xs font-black uppercase tracking-widest mb-5">
                    <x-icon name="o-academic-cap" class="w-3.5 h-3.5" /> {{ __('Study Programs') }}
                </span>
                <h1 class="text-4xl md:text-6xl font-black mb-4 tracking-tight leading-none">
                    {{ __('Halaqahs') }} &<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">{{ __('Study Circles') }}</span>
                </h1>
                <p class="text-white/50 text-lg max-w-xl mb-10 leading-relaxed">
                    {{ __('Discover, join and engage with our campus Islamic study sessions, live courses, and knowledge programs.') }}
                </p>

                {{-- Stats --}}
                <div class="flex gap-8 flex-wrap">
                    <div>
                        <p class="text-4xl font-black text-white">{{ $totalUpcoming }}</p>
                        <p class="text-xs text-white/40 uppercase tracking-wider mt-1 font-bold">{{ __('Upcoming') }}</p>
                    </div>
                    <div class="border-l border-white/10 pl-8">
                        <p class="text-4xl font-black text-white">{{ $totalCompleted }}</p>
                        <p class="text-xs text-white/40 uppercase tracking-wider mt-1 font-bold">{{ __('Completed') }}</p>
                    </div>
                    <div class="border-l border-white/10 pl-8">
                        <p class="text-4xl font-black text-white">{{ $totalSeries }}</p>
                        <p class="text-xs text-white/40 uppercase tracking-wider mt-1 font-bold">{{ __('Active Series') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab navigation bar at bottom of hero --}}
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-end gap-1">
                @foreach(['upcoming' => ['label' => __('Upcoming Sessions'), 'icon' => 'o-calendar'], 'series' => ['label' => __('Courses & Series'), 'icon' => 'o-academic-cap'], 'past' => ['label' => __('Past Sessions'), 'icon' => 'o-clock']] as $tab => $meta)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                        class="flex items-center gap-2 px-5 py-3 text-sm font-bold rounded-t-2xl transition-all border border-b-0
                        {{ $activeTab === $tab
                            ? 'bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white border-slate-200/20 dark:border-slate-800/50'
                            : 'text-white/50 hover:text-white/80 border-transparent hover:bg-white/5' }}">
                        <x-icon name="{{ $meta['icon'] }}" class="w-4 h-4" />
                        {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Filter bar (not shown for series tab) --}}
        @if($activeTab !== 'series')
        <div class="flex flex-col md:flex-row gap-4 mb-8 items-start md:items-center justify-between">
            <div></div>
            <div class="flex gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-72">
                    <x-icon name="o-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search sessions...') }}"
                        class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/40 focus:outline-none transition text-slate-700 dark:text-slate-300" />
                </div>
                @if($allSeries->count())
                    <select wire:model.live="series_filter"
                        class="px-3 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/40 focus:outline-none transition text-slate-700 dark:text-slate-300">
                        <option value="">{{ __('All Series') }}</option>
                        @foreach($allSeries as $s)
                            <option value="{{ $s->id }}">{{ $s->title }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
        @endif

        {{-- ══════════════ UPCOMING TAB ══════════════ --}}
        @if($activeTab === 'upcoming')
            @if($halaqahs->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($halaqahs as $halaqah)
                <a href="{{ route('web.halaqah.show', $halaqah) }}" wire:navigate wire:key="upcoming-{{ $halaqah->id }}" class="group block">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 overflow-hidden hover:shadow-2xl hover:shadow-primary/10 hover:-translate-y-1.5 transition-all duration-300 h-full flex flex-col">

                        {{-- Date header --}}
                        <div class="bg-gradient-to-r from-primary to-secondary text-primary-content px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="text-center leading-none">
                                    <p class="text-3xl font-black">{{ $halaqah->scheduled_at->format('d') }}</p>
                                    <p class="text-xs font-bold uppercase opacity-80">{{ $halaqah->scheduled_at->format('M Y') }}</p>
                                </div>
                                <div class="border-l border-white/30 pl-3">
                                    <p class="font-bold text-sm">{{ $halaqah->scheduled_at->format('l') }}</p>
                                    <p class="text-xs opacity-80">{{ $halaqah->scheduled_at->format('g:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                @if($halaqah->gender_restriction !== 'none')
                                    <span class="badge badge-sm bg-white/20 border-white/30 text-white text-[10px] font-black">
                                        {{ $halaqah->gender_restriction === 'brothers_only' ? '♂ ' . __('Brothers') : '♀ ' . __('Sisters') }}
                                    </span>
                                @endif
                                @if($halaqah->quizzes()->exists())
                                    <span class="badge badge-sm bg-amber-400/20 border-amber-300/30 text-amber-100 text-[10px] font-black">📝 {{ __('Quiz') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 flex-grow flex flex-col">
                            @if($halaqah->series)
                                <a href="{{ route('web.course.show', $halaqah->series) }}" wire:navigate class="text-xs font-black text-primary/70 hover:text-primary uppercase tracking-widest mb-1 transition-colors">{{ $halaqah->series->title }}</a>
                            @endif
                            <h3 class="font-black text-lg text-slate-800 dark:text-slate-100 mb-1 group-hover:text-primary transition-colors leading-tight">{{ $halaqah->title }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 flex-grow line-clamp-2">{{ $halaqah->topic }}</p>

                            {{-- Countdown --}}
                            <div class="bg-primary/5 dark:bg-primary/10 border border-primary/10 rounded-2xl px-3 py-2 mb-4 text-center"
                                x-data="{
                                    display: '{{ $halaqah->scheduled_at->diffForHumans() }}',
                                    target: new Date('{{ $halaqah->scheduled_at->toIso8601String() }}'),
                                    intervalId: null,
                                    init() {
                                        this.update();
                                        this.intervalId = setInterval(() => this.update(), 1000);
                                    },
                                    update() {
                                        const diff = this.target - new Date();
                                        if (diff <= 0) { this.display = '{{ __('Starting now') }}'; clearInterval(this.intervalId); return; }
                                        const d = Math.floor(diff / 86400000);
                                        const h = Math.floor((diff % 86400000) / 3600000);
                                        const m = Math.floor((diff % 3600000) / 60000);
                                        const s = Math.floor((diff % 60000) / 1000);
                                        if (d > 0) this.display = `In ${d}d ${h}h ${m}m`;
                                        else if (h > 0) this.display = `In ${h}h ${m}m ${s}s`;
                                        else this.display = `In ${m}m ${s}s`;
                                    }
                                }">
                                <p class="text-xs font-black text-primary" x-text="display">{{ $halaqah->scheduled_at->diffForHumans() }}</p>
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-400 dark:text-slate-500 border-t border-slate-100 dark:border-slate-800 pt-3">
                                <span class="flex items-center gap-1.5 font-semibold">
                                    <x-icon name="o-map-pin" class="w-3.5 h-3.5" /> {{ Str::limit($halaqah->location, 25) }}
                                </span>
                                @if($halaqah->rsvp_count > 0 || $halaqah->max_capacity)
                                    <span class="flex items-center gap-1 font-semibold {{ $halaqah->available_seats === 0 ? 'text-warning' : 'text-success' }}">
                                        <x-icon name="o-users" class="w-3.5 h-3.5" />
                                        @if($halaqah->max_capacity)
                                            {{ $halaqah->rsvp_count }}/{{ $halaqah->max_capacity }}
                                        @else
                                            {{ $halaqah->rsvp_count }} {{ __('going') }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $halaqahs->links() }}</div>
            @else
                <div class="text-center py-24 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                    <x-icon name="o-calendar-days" class="w-14 h-14 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                    <h3 class="text-lg font-black text-slate-500 dark:text-slate-400 mb-1">{{ __('No Upcoming Sessions') }}</h3>
                    <p class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Check back later for newly scheduled study circles.') }}</p>
                </div>
            @endif

        {{-- ══════════════ SERIES TAB ══════════════ --}}
        @elseif($activeTab === 'series')
            @if($series->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($series as $course)
                @php
                    $totalSessions = $course->halaqahs_count;
                    $completedSessions = $course->halaqahs()->where('status', 'completed')->count();
                    $progressPct = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;
                @endphp
                <div wire:key="series-{{ $course->id }}" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 overflow-hidden hover:shadow-2xl hover:shadow-secondary/10 hover:-translate-y-1 transition-all duration-300 flex flex-col">
                    {{-- Header gradient --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-6 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                                <x-icon name="o-academic-cap" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="badge badge-sm bg-white/20 border-white/30 text-white text-[10px] font-black uppercase">{{ $course->target_audience_level }}</span>
                                @if($course->quizzes_count > 0)
                                    <span class="badge badge-sm bg-amber-400/20 border-amber-300/30 text-amber-100 text-[10px] font-black">📝 {{ $course->quizzes_count }} {{ __('Quizzes') }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('web.course.show', $course) }}" wire:navigate class="hover:text-indigo-300 transition-colors">
                            <h3 class="text-xl font-black mt-3 leading-tight">{{ $course->title }}</h3>
                        </a>
                    </div>

                    <div class="p-5 flex-grow flex flex-col">
                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3 mb-4 flex-grow">{{ $course->description ?: __('A structured course program.') }}</p>

                        {{-- Progress --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">
                                <span>{{ $completedSessions }} / {{ $totalSessions }} {{ __('sessions') }}</span>
                                <span class="text-indigo-600 dark:text-indigo-400">{{ $progressPct }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full transition-all duration-700" style="width: {{ $progressPct }}%"></div>
                            </div>
                        </div>

                        <a href="{{ route('web.course.show', $course) }}" wire:navigate
                            class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-black text-sm rounded-2xl hover:bg-indigo-100 dark:hover:bg-indigo-950/60 transition-colors border border-indigo-100 dark:border-indigo-900/50">
                            <x-icon name="o-play-circle" class="w-4 h-4" />
                            {{ __('View Course') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-10">{{ $series->links() }}</div>
            @else
                <div class="text-center py-24 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                    <x-icon name="o-academic-cap" class="w-14 h-14 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                    <h3 class="text-lg font-black text-slate-500 dark:text-slate-400 mb-1">{{ __('No Active Series') }}</h3>
                    <p class="text-slate-400 dark:text-slate-500 text-sm">{{ __('There are no structured courses running at the moment.') }}</p>
                </div>
            @endif

        {{-- ══════════════ PAST TAB ══════════════ --}}
        @elseif($activeTab === 'past')
            @if($halaqahs->count())
            <div class="space-y-3">
                @foreach($halaqahs as $halaqah)
                <a href="{{ route('web.halaqah.show', $halaqah) }}" wire:navigate wire:key="past-{{ $halaqah->id }}"
                    class="group flex items-center gap-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 px-5 py-4 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all">
                    {{-- Date block --}}
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex flex-col items-center justify-center shrink-0 text-center">
                        <p class="text-lg font-black text-slate-700 dark:text-slate-200 leading-none">{{ $halaqah->scheduled_at->format('d') }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $halaqah->scheduled_at->format('M') }}</p>
                    </div>
                    {{-- Info --}}
                    <div class="flex-grow min-w-0">
                        <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                            <h4 class="font-black text-slate-800 dark:text-slate-100 group-hover:text-primary transition-colors">{{ $halaqah->title }}</h4>
                            @if($halaqah->series)
                                <a href="{{ route('web.course.show', $halaqah->series) }}" wire:navigate class="badge badge-xs badge-ghost hover:bg-base-300 transition-colors">{{ $halaqah->series->title }}</a>
                            @endif
                            @if($halaqah->materials_path || !empty($halaqah->resources))
                                <span class="badge badge-xs badge-success badge-outline">{{ __('Materials') }}</span>
                            @endif
                            @if($halaqah->quizzes()->exists())
                                <span class="badge badge-xs badge-warning badge-outline">{{ __('Quiz') }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-3 flex-wrap">
                            <span class="flex items-center gap-1"><x-icon name="o-tag" class="w-3 h-3" /> {{ $halaqah->topic }}</span>
                            @if($halaqah->speaker)
                                <span class="flex items-center gap-1"><x-icon name="o-user" class="w-3 h-3" /> {{ $halaqah->speaker->name }}</span>
                            @endif
                            <span class="flex items-center gap-1"><x-icon name="o-map-pin" class="w-3 h-3" /> {{ $halaqah->location }}</span>
                        </p>
                    </div>
                    <x-icon name="o-arrow-right" class="w-4 h-4 text-slate-300 group-hover:text-primary group-hover:translate-x-1 transition-all shrink-0" />
                </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $halaqahs->links() }}</div>
            @else
                <div class="text-center py-24 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                    <x-icon name="o-folder-open" class="w-14 h-14 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                    <h3 class="text-lg font-black text-slate-500 dark:text-slate-400 mb-1">{{ __('No Past Sessions') }}</h3>
                    <p class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Try adjusting your filters.') }}</p>
                </div>
            @endif
        @endif

    </div>

    <div class="pb-16"></div>
</div>


