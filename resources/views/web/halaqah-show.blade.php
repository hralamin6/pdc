<?php

use App\Models\Halaqah;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.web')] class extends Component
{
    public Halaqah $halaqah;

    public function mount(Halaqah $halaqah): void
    {
        $this->halaqah = $halaqah->load(['speaker', 'series.halaqahs']);
    }

    public function getSeriesProgressProperty(): int
    {
        if (!$this->halaqah->series) {
            return 0;
        }
        $total = $this->halaqah->series->halaqahs->count();
        $completed = $this->halaqah->series->halaqahs->where('status', 'completed')->count();
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    #[Title]
    public function title(): string
    {
        return $this->halaqah->title . ' | PSTU Dawah';
    }
};
?>

<div class="bg-base-100 dark:bg-base-100 min-h-screen">

    {{-- Hero banner --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white pt-16 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('web.halaqahs') }}" wire:navigate class="inline-flex items-center gap-1 text-white/50 hover:text-white text-sm font-medium mb-6 transition-colors">
                <x-icon name="o-arrow-left" class="w-4 h-4" /> Back to all sessions
            </a>

            @if($halaqah->series)
                <p class="text-sm font-bold text-amber-300 uppercase tracking-widest mb-2">{{ $halaqah->series->title }}</p>
            @endif
            <h1 class="text-3xl md:text-4xl font-extrabold mb-4">{{ $halaqah->title }}</h1>

            <div class="flex flex-wrap gap-3 items-center text-white/60 text-sm">
                <span class="flex items-center gap-1.5"><x-icon name="o-calendar" class="w-4 h-4" /> {{ $halaqah->scheduled_at->format('l, F j, Y ・ g:i A') }}</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span class="flex items-center gap-1.5"><x-icon name="o-map-pin" class="w-4 h-4" /> {{ $halaqah->location }}</span>
                @if($halaqah->gender_restriction !== 'none')
                    <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                    <span class="badge badge-sm bg-white/10 border-white/20 text-white">{{ str_replace('_', ' ', ucfirst($halaqah->gender_restriction)) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="grid lg:grid-cols-3 gap-8">

            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- About --}}
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-base-content mb-1">Topic</h2>
                    <p class="text-primary font-semibold mb-4">{{ $halaqah->topic }}</p>

                    @if($halaqah->description)
                        <h3 class="text-sm font-bold text-base-content/70 uppercase tracking-wider mb-2">Description</h3>
                        <div class="prose prose-sm max-w-none text-base-content/70">{!! nl2br(e($halaqah->description)) !!}</div>
                    @endif
                </div>

                {{-- Materials --}}
                @if($halaqah->materials_path || ($halaqah->resources && count($halaqah->resources)))
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-base-content mb-4">Session Materials</h2>
                    
                    @if($halaqah->materials_path)
                        <a href="{{ Storage::disk('public')->url($halaqah->materials_path) }}" target="_blank" class="flex items-center gap-3 p-3 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors mb-3 group">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                <x-icon name="o-document-arrow-down" class="w-5 h-5" />
                            </div>
                            <div class="flex-grow">
                                <p class="font-bold text-sm text-base-content">Download Material</p>
                                <p class="text-xs text-base-content/50">PDF / Document</p>
                            </div>
                            <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4 text-base-content/30 group-hover:text-primary transition-colors" />
                        </a>
                    @endif

                    @if($halaqah->resources && count($halaqah->resources))
                        <div class="space-y-2">
                            @foreach($halaqah->resources as $link)
                                <a href="{{ $link }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 rounded-xl bg-base-200/50 hover:bg-base-200 transition-colors group">
                                    <div class="w-10 h-10 rounded-lg bg-info/10 text-info flex items-center justify-center">
                                        <x-icon name="o-link" class="w-5 h-5" />
                                    </div>
                                    <span class="text-sm text-base-content/70 truncate flex-grow group-hover:text-primary transition-colors">{{ $link }}</span>
                                    <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4 text-base-content/30 group-hover:text-primary transition-colors shrink-0" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- Series Progress --}}
                @if($halaqah->series)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-base-content mb-4">Series Progress — {{ $halaqah->series->title }}</h2>
                    <div class="flex items-center gap-4 mb-4">
                        <progress class="progress progress-primary flex-grow" value="{{ $this->seriesProgress }}" max="100"></progress>
                        <span class="text-sm font-bold text-primary">{{ $this->seriesProgress }}%</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($halaqah->series->halaqahs->sortBy('scheduled_at') as $session)
                            <div class="flex items-center gap-3 p-2 rounded-lg {{ $session->id === $halaqah->id ? 'bg-primary/10 border border-primary/20' : '' }}">
                                @if($session->status === 'completed')
                                    <div class="w-6 h-6 rounded-full bg-success/20 text-success flex items-center justify-center shrink-0">
                                        <x-icon name="o-check" class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-6 h-6 rounded-full border-2 border-base-content/20 shrink-0"></div>
                                @endif
                                <span class="text-sm {{ $session->id === $halaqah->id ? 'font-bold text-primary' : 'text-base-content/70' }}">{{ $session->title }}</span>
                                <span class="text-xs text-base-content/40 ml-auto shrink-0">{{ $session->scheduled_at->format('M d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Quick Info Card --}}
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h3 class="font-bold text-base-content mb-4">Session Details</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <x-icon name="o-calendar" class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                            <div>
                                <p class="font-semibold text-base-content">{{ $halaqah->scheduled_at->format('l, F j, Y') }}</p>
                                <p class="text-base-content/50">{{ $halaqah->scheduled_at->format('g:i A') }} • {{ $halaqah->scheduled_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-icon name="o-map-pin" class="w-5 h-5 text-secondary shrink-0 mt-0.5" />
                            <p class="text-base-content/70">{{ $halaqah->location }}</p>
                        </div>
                        @if($halaqah->speaker)
                        <div class="flex items-start gap-3">
                            <x-icon name="o-user" class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <p class="text-base-content/70">{{ $halaqah->speaker->name }}</p>
                        </div>
                        @endif
                        @if($halaqah->meeting_link)
                        <div class="flex items-start gap-3">
                            <x-icon name="o-link" class="w-5 h-5 text-info shrink-0 mt-0.5" />
                            <a href="{{ $halaqah->meeting_link }}" target="_blank" class="text-info hover:underline truncate">Join Online</a>
                        </div>
                        @endif
                        @if($halaqah->max_capacity)
                        <div class="flex items-start gap-3">
                            <x-icon name="o-user-group" class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                            <p class="text-base-content/70">Max {{ $halaqah->max_capacity }} attendees</p>
                        </div>
                        @endif
                    </div>

                    @auth
                        <div class="mt-6 pt-4 border-t border-base-content/5">
                            <a href="{{ route('app.halaqahs.show', $halaqah) }}" wire:navigate class="btn btn-primary btn-block rounded-xl font-bold">
                                RSVP / View in Dashboard
                            </a>
                        </div>
                    @else
                        <div class="mt-6 pt-4 border-t border-base-content/5">
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-block rounded-xl font-bold">
                                Login to RSVP
                            </a>
                        </div>
                    @endauth
                </div>

                {{-- Series card --}}
                @if($halaqah->series)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h3 class="font-bold text-base-content mb-2">Part of Series</h3>
                    <p class="text-primary font-semibold">{{ $halaqah->series->title }}</p>
                    <p class="text-xs text-base-content/50 mt-1">Level: {{ ucfirst($halaqah->series->target_audience_level) }}</p>
                    @if($halaqah->series->description)
                        <p class="text-sm text-base-content/60 mt-3 line-clamp-4">{{ $halaqah->series->description }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="pb-16"></div>
</div>
