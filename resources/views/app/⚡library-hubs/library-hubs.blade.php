<div>
    <x-header title="Community Hubs" subtitle="Physical books managed by the community library" separator />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($hubs as $hub)
            <div class="bg-base-100 border border-base-content/10 shadow-sm rounded-2xl overflow-hidden" wire:key="hub-{{ $hub->id }}">
                <div class="bg-primary/10 p-5 flex items-center gap-4">
                    <div class="w-14 h-14 bg-primary text-white rounded-xl flex items-center justify-center shrink-0">
                        <x-icon name="o-building-library" class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-base-content">{{ $hub->name }}</h2>
                        <p class="text-sm text-base-content/60 flex items-center gap-1">
                            <x-icon name="o-map-pin" class="w-3 h-3" /> {{ $hub->location ?? 'Unknown Location' }}
                        </p>
                    </div>
                </div>
                
                <div class="p-5">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-base-content/50 mb-4">Books Available ({{ $hub->bookCopies->count() }})</h3>
                    
                    <div class="space-y-3">
                        @forelse($hub->bookCopies as $copy)
                            <a href="{{ route('app.book-details', $copy->book->slug) }}" class="flex items-center gap-3 p-3 rounded-xl border border-base-content/5 hover:border-primary/30 hover:bg-base-200/50 transition-colors" wire:navigate>
                                @if($copy->book->cover_url)
                                    <img src="{{ $copy->book->cover_url }}" class="w-10 h-14 object-cover rounded shadow-sm" />
                                @else
                                    <div class="w-10 h-14 bg-base-200 flex items-center justify-center rounded"><x-icon name="o-book-open" class="w-5 h-5 opacity-50" /></div>
                                @endif
                                
                                <div class="flex-grow">
                                    <div class="font-bold text-sm line-clamp-1">{{ $copy->book->title }}</div>
                                    <div class="text-xs text-base-content/50">Cond: {{ $copy->condition }}</div>
                                </div>
                                
                                <div>
                                    @if($copy->status === 'available')
                                        <span class="badge badge-success badge-sm text-white border-none shadow-sm">Available</span>
                                    @else
                                        <span class="badge badge-warning badge-sm border-none shadow-sm">Borrowed</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-6 text-base-content/40 italic text-sm">
                                No books donated to this hub yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-building-library" class="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-base-content/70 mb-2">No Community Hubs</h3>
                <p class="text-base-content/50">Admins have not created any Master Book Houses yet.</p>
            </div>
        @endforelse
    </div>
</div>
