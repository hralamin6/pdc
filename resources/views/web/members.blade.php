<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Community Members')] #[Layout('layouts.web')] class extends Component
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
            'members' => $query->latest()->paginate(12),
            'totalCount' => User::count(),
            'mentorCount' => User::role(['mentor', 'admin', 'super-admin'])->count(),
        ];
    }
};
?>

<div class="bg-base-100 dark:bg-base-100 min-h-screen">

    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Community Members</h1>
            <p class="text-white/60 text-lg">Meet the brothers and sisters of PSTU Dawah Community.</p>
            <div class="flex gap-6 mt-6">
                <div>
                    <p class="text-2xl font-black">{{ $totalCount }}</p>
                    <p class="text-xs text-white/50 uppercase tracking-wider">Total Members</p>
                </div>
                <div class="border-l border-white/20 pl-6">
                    <p class="text-2xl font-black">{{ $mentorCount }}</p>
                    <p class="text-xs text-white/50 uppercase tracking-wider">Mentors</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-10 items-start sm:items-center justify-between">
            <div class="flex gap-2 flex-wrap">
                <button wire:click="$set('role', '')" class="btn btn-sm rounded-full {{ !$role ? 'btn-primary' : 'btn-ghost border border-base-content/10' }}">All</button>
                <button wire:click="$set('role', 'mentor')" class="btn btn-sm rounded-full {{ $role === 'mentor' ? 'btn-primary' : 'btn-ghost border border-base-content/10' }}">Mentors</button>
                <button wire:click="$set('role', 'admin')" class="btn btn-sm rounded-full {{ $role === 'admin' ? 'btn-primary' : 'btn-ghost border border-base-content/10' }}">Admins</button>
            </div>
            <x-input wire:model.live.debounce.300ms="search" placeholder="Search members..." icon="o-magnifying-glass" class="input-sm w-full sm:w-64 rounded-full" clearable />
        </div>

        {{-- Members Grid --}}
        @if($members->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($members as $member)
                <div class="text-center group">
                    <div class="w-20 h-20 mx-auto mb-3 rounded-full overflow-hidden ring-4 ring-base-content/5 group-hover:ring-primary/30 transition-all">
                        <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff&size=80' }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                    </div>
                    <h4 class="font-bold text-sm text-base-content truncate">{{ $member->name }}</h4>
                    @php $roles = $member->getRoleNames(); @endphp
                    @if($roles->count())
                        <p class="text-xs text-primary font-medium mt-0.5">{{ ucfirst($roles->first()) }}</p>
                    @else
                        <p class="text-xs text-base-content/40 mt-0.5">Member</p>
                    @endif
                    <p class="text-[10px] text-base-content/30 mt-1">Joined {{ $member->created_at->format('M Y') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $members->links() }}
        </div>
        @else
            <div class="text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-users" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No members found</h3>
                <p class="text-base-content/50 text-sm">Try a different search term.</p>
            </div>
        @endif
    </div>
</div>
