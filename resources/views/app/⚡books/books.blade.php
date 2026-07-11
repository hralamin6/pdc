<div>
    <x-header title="Library" subtitle="Explore Islamic literature, study materials, and books" separator>
        <x-slot:actions>
            <x-input wire:model.live.debounce="search" icon="o-magnifying-glass" placeholder="Search title or author..." class="input-bordered rounded-xl w-64" />
        </x-slot:actions>
    </x-header>

    {{-- Category Filters --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <button wire:click="$set('category_id', null)" class="btn btn-sm rounded-full {{ is_null($category_id) ? 'btn-primary' : 'btn-ghost border-base-content/20' }}">All Books</button>
        @foreach($categories as $cat)
            <button wire:click="$set('category_id', {{ $cat->id }})" class="btn btn-sm rounded-full {{ $category_id === $cat->id ? 'btn-primary' : 'btn-ghost border-base-content/20' }}">
                <x-icon :name="$cat->icon" class="w-4 h-4 mr-1" />
                {{ $cat->name }}
            </button>
        @endforeach
    </div>

    {{-- Book Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse($books as $book)
            <a href="{{ route('app.book-details', $book->slug) }}" class="group block relative" wire:navigate>
                <div class="bg-base-200 rounded-lg shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden aspect-[2/3] relative">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-base-300 text-base-content/40 group-hover:bg-primary/5 transition-colors">
                            <x-icon name="o-book-open" class="w-16 h-16 mb-2 opacity-50" />
                            <span class="text-xs font-bold uppercase tracking-widest text-center px-4 line-clamp-3">{{ $book->title }}</span>
                        </div>
                    @endif
                    
                    {{-- Badges --}}
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        @if($book->type === 'ebook')
                            <span class="badge badge-primary border-none shadow-sm text-xs">eBook</span>
                        @elseif($book->type === 'both')
                            <span class="badge badge-accent border-none shadow-sm text-white text-xs">eBook & Physical</span>
                        @else
                            <span class="badge badge-secondary border-none shadow-sm text-xs">Physical</span>
                        @endif
                        
                        @if($book->created_at->diffInDays() < 14)
                            <span class="badge badge-success border-none text-white shadow-sm text-xs">New</span>
                        @endif
                    </div>
                    
                    {{-- Hover Overlay --}}
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 text-white">
                        <p class="text-xs font-bold text-primary-400 mb-1 line-clamp-1">{{ $book->category?->name }}</p>
                        <p class="text-xs line-clamp-3 opacity-80">{{ $book->description }}</p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h3 class="font-bold text-base-content leading-tight line-clamp-1 group-hover:text-primary transition-colors">{{ $book->title }}</h3>
                    <p class="text-xs text-base-content/60 font-medium mt-1 truncate">{{ $book->author?->name ?? 'Unknown Author' }}</p>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-book-open" class="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-base-content/70 mb-2">No books found</h3>
                <p class="text-base-content/50">There are currently no approved books matching your filters.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-8">{{ $books->links() }}</div>
</div>
