<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title(__('Community Members'))] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->role) {
            $query->role($this->role);
        }

        return [
            'members' => $query->latest()->paginate(18),
            'totalCount' => User::count(),
            'mentorCount' => User::role(['mentor', 'admin', 'super-admin'])->count(),
            'recentCount' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }
};
?>

<div class="bg-base-100 min-h-screen">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-20 relative overflow-hidden">
        <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-violet-500/20 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <p class="text-violet-400 font-bold text-sm uppercase tracking-widest mb-3">{{ __('Our People') }}</p>
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">{{ __('Community Members') }}</h1>
            <p class="text-white/50 text-lg max-w-xl mb-8">{{ __('Meet the brothers and sisters of PSTU Dawah Community.') }}</p>
            <div class="flex gap-8">
                <div>
                    <p class="text-3xl font-black">{{ $totalCount }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ __('Total Members') }}</p>
                </div>
                <div class="border-l border-white/20 pl-8">
                    <p class="text-3xl font-black">{{ $mentorCount }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ __('Mentors & Admins') }}</p>
                </div>
                <div class="border-l border-white/20 pl-8">
                    <p class="text-3xl font-black">{{ $recentCount }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ __('Joined this Month') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-10 items-start sm:items-center justify-between">
            <div class="flex gap-2 flex-wrap">
                @php $roles = ['' => __('All'), 'mentor' => __('Mentors'), 'admin' => __('Admins'), 'super-admin' => __('Leaders')]; @endphp
                @foreach($roles as $val => $label)
                    <button wire:click="$set('role', '{{ $val }}')"
                        class="btn btn-sm rounded-full transition-all {{ $role === $val ? 'bg-primary text-white shadow-lg shadow-primary/30 border-none' : 'btn-ghost border border-base-content/10' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <x-input wire:model.live.debounce.300ms="search" :placeholder="__('Search members...')" icon="o-magnifying-glass" class="input-sm w-full sm:w-64 rounded-full" clearable />
        </div>

        {{-- Grid --}}
        @if($members->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($members as $member)
                <div class="text-center group">
                    <div class="relative mx-auto mb-3 w-20 h-20">
                        <div class="w-20 h-20 rounded-full overflow-hidden ring-4 ring-base-content/5 group-hover:ring-primary/40 transition-all duration-300 group-hover:scale-105">
                            <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff&size=80' }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        </div>
                        @php $memberRoles = $member->getRoleNames(); @endphp
                        @if($memberRoles->contains('super-admin') || $memberRoles->contains('admin'))
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-amber-400 rounded-full flex items-center justify-center ring-2 ring-base-100">
                                <x-icon name="o-star" class="w-3 h-3 text-amber-900" />
                            </div>
                        @elseif($memberRoles->contains('mentor'))
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-primary rounded-full flex items-center justify-center ring-2 ring-base-100">
                                <x-icon name="o-academic-cap" class="w-3 h-3 text-white" />
                            </div>
                        @endif
                    </div>
                    <h4 class="font-bold text-sm text-base-content truncate">{{ $member->name }}</h4>
                    @if($memberRoles->count())
                        <p class="text-xs text-primary font-semibold mt-0.5">{{ ucfirst($memberRoles->first()) }}</p>
                    @else
                        <p class="text-xs text-base-content/40 mt-0.5">{{ __('Member') }}</p>
                    @endif
                    <p class="text-[10px] text-base-content/30 mt-1">{{ __('Joined') }} {{ $member->created_at->format('M Y') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10">{{ $members->links() }}</div>
        @else
            <div class="text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-users" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">{{ __('No members found') }}</h3>
                <p class="text-base-content/50 text-sm">{{ __('Try a different search term.') }}</p>
            </div>
        @endif
    </div>
</div>
