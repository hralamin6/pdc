<div>
    <div class="flex items-center justify-between mb-3">
        <div class="text-xs font-medium text-[--nx-text-muted] uppercase tracking-wider">🧠 AI Memory</div>
        <button wire:click="$toggle('showAdd')" class="nx-btn nx-btn-ghost text-xs">{{ $showAdd ? '✕' : '+ Add' }}</button>
    </div>

    @if($showAdd)
    <div class="bg-[--nx-surface2] border border-[--nx-border] rounded-lg p-2.5 mb-3 space-y-2">
        <input wire:model="newKey" placeholder="Key (e.g. user_name)" class="nx-input text-xs">
        <input wire:model="newValue" placeholder="Value" class="nx-input text-xs">
        <select wire:model="newCat" class="nx-input text-xs">
            <option value="general">General</option>
            <option value="personal">Personal</option>
            <option value="preference">Preference</option>
            <option value="fact">Fact</option>
        </select>
        <button wire:click="addMemory" class="nx-btn nx-btn-accent w-full text-xs justify-center">Save Memory</button>
    </div>
    @endif

    <div class="space-y-1.5">
        @forelse($memories->groupBy('category') as $cat => $group)
        <div class="mb-2">
            <div class="text-xs text-[--nx-text-dim] uppercase tracking-wider mb-1">{{ $cat }}</div>
            @foreach($group as $mem)
            <div class="flex items-start gap-2 bg-[--nx-surface2] border border-[--nx-border] rounded-lg p-2 text-xs group">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-[--nx-text]">{{ $mem->key }}</div>
                    <div class="text-[--nx-text-muted] truncate">{{ $mem->value }}</div>
                </div>
                <button wire:click="deleteMemory({{ $mem->id }})" class="text-[--nx-text-dim] hover:text-[--nx-red] hidden group-hover:block flex-shrink-0">✕</button>
            </div>
            @endforeach
        </div>
        @empty
        <div class="text-center py-4 text-[--nx-text-dim] text-xs">No memories yet.<br>AI learns from your conversations.</div>
        @endforelse
    </div>
</div>
