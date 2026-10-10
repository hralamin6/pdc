<div>
    <div class="mb-3 text-xs font-medium text-[--nx-text-muted] uppercase tracking-wider">📄 Knowledge Base</div>

    {{-- Upload --}}
    <div class="border border-dashed border-[--nx-border2] rounded-lg p-3 mb-3 hover:border-[--nx-accent] transition-colors">
        @if($file)
            <div class="text-xs text-[--nx-green] mb-2 truncate">📎 {{ $file->getClientOriginalName() }}</div>
            <input wire:model="docName" placeholder="Document name (optional)" class="nx-input text-xs mb-2">
            <div class="flex gap-1">
                <button wire:click="uploadDocument" class="nx-btn nx-btn-accent flex-1 text-xs justify-center">Upload & Index</button>
                <button wire:click="$set('file', null)" class="nx-btn nx-btn-ghost text-xs">✕</button>
            </div>
        @else
            <label class="flex flex-col items-center gap-1 cursor-pointer text-[--nx-text-dim] hover:text-[--nx-text] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span class="text-xs">Upload PDF, TXT, CSV, MD</span>
                <input type="file" wire:model="file" class="hidden" accept=".pdf,.txt,.md,.csv">
            </label>
        @endif
    </div>

    {{-- Documents list --}}
    <div class="space-y-1.5">
        @forelse($documents as $doc)
        <div class="bg-[--nx-surface2] border border-[--nx-border] rounded-lg p-2.5 text-xs">
            <div class="flex items-start justify-between gap-1">
                <span class="font-medium text-[--nx-text] truncate">{{ $doc->name }}</span>
                <div class="flex gap-1 flex-shrink-0">
                    @if($doc->isFailed())
                    <button wire:click="reindex({{ $doc->id }})" class="text-[--nx-yellow] hover:underline">Retry</button>
                    @endif
                    <button wire:click="deleteDocument({{ $doc->id }})" class="text-[--nx-red] hover:underline">✕</button>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-1 text-[--nx-text-dim]">
                <span class="status-dot status-{{ $doc->status }}"></span>
                <span>{{ ucfirst($doc->status) }}</span>
                @if($doc->isIndexed())<span>· {{ $doc->chunk_count }} chunks</span>@endif
                <span class="ml-auto">{{ $doc->file_size_human }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-[--nx-text-dim] text-xs">No documents yet</div>
        @endforelse
    </div>
</div>
