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
    public string $filter = 'upcoming';

    #[Url]
    public string $search = '';

    #[Url]
    public string $series_filter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Halaqah::query()
            ->with(['speaker', 'series'])
            ->where('status', 'published');

        if ($this->filter === 'upcoming') {
            $query->where('scheduled_at', '>=', now())->orderBy('scheduled_at', 'asc');
        } elseif ($this->filter === 'past') {
            $query->where('scheduled_at', '<', now())->orderBy('scheduled_at', 'desc');
        } else {
            $query->orderBy('scheduled_at', 'desc');
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

        return [
            'halaqahs' => $query->paginate(9),
            'allSeries' => HalaqahSeries::where('status', 'active')->orderBy('title')->get(),
        ];
    }
};
?>

<div class="bg-base-100 dark:bg-base-100 min-h-screen">

    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Halaqahs & Study Circles</h1>
            <p class="text-white/60 text-lg">Explore and join our upcoming sessions across campus.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Filters bar --}}
        <div class="flex flex-col md:flex-row gap-4 mb-10 items-start md:items-center justify-between">
            <div class="flex gap-2 flex-wrap">
                @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $val => $label)
                    <button wire:click="$set('filter', '{{ $val }}')" class="btn btn-sm rounded-full {{ $filter === $val ? 'btn-primary' : 'btn-ghost border border-base-content/10' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex gap-3 w-full md:w-auto">
                <x-input wire:model.live.debounce.300ms="search" placeholder="Search sessions..." icon="o-magnifying-glass" class="input-sm w-full md:w-64 rounded-full" clearable />
                @if($allSeries->count())
                    <select wire:model.live="series_filter" class="select select-sm select-bordered rounded-full">
                        <option value="">All Series</option>
                        @foreach($allSeries as $s)
                            <option value="{{ $s->id }}">{{ $s->title }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        {{-- Session Grid --}}
        @if($halaqahs->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($halaqahs as $halaqah)
                <a href="{{ route('web.halaqah.show', $halaqah) }}" wire:navigate class="group block">
                    <div class="bg-base-100 rounded-2xl border border-base-content/5 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
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

                            @if($halaqah->scheduled_at->isPast())
                                <div class="mt-3">
                                    <span class="badge badge-ghost badge-sm">Completed</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $halaqahs->links() }}
        </div>
        @else
            <div class="text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-magnifying-glass" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No sessions found</h3>
                <p class="text-base-content/50 text-sm">Try adjusting your filters or search terms.</p>
            </div>
        @endif
    </div>
</div>
