<div class="max-w-6xl mx-auto pb-12">
    {{-- Header / Breadcrumbs --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-base-content/60">
        <a href="{{ route('web.library') }}" class="hover:text-primary transition-colors" wire:navigate>Library</a>
        <x-icon name="o-chevron-right" class="w-3 h-3" />
        <span class="text-base-content">{{ $book->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Left Sidebar: Cover & Actions --}}
        <div class="lg:col-span-3 space-y-6">
            <div class="rounded-xl overflow-hidden shadow-xl aspect-[2/3] bg-base-200 border border-base-content/5 relative">
                @if($book->cover_url)
                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover" />
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-base-content/30">
                        <x-icon name="o-book-open" class="w-16 h-16 mb-2" />
                        <span class="text-xs uppercase font-bold tracking-widest text-center px-4">{{ $book->title }}</span>
                    </div>
                @endif
                
                @if(in_array($book->type, ['ebook', 'both']))
                    <div class="absolute top-2 left-2 badge badge-primary border-none shadow-sm">eBook</div>
                @endif
            </div>

            <div class="flex flex-col gap-2">
                @if(in_array($book->type, ['ebook', 'both']))
                    @if($book->pdf_url)
                        <x-button label="Read Online" icon="o-book-open" class="btn-primary w-full shadow-lg shadow-primary/30" :link="route('app.book-reader', $book->slug)" wire:navigate />
                    @elseif($book->external_link)
                        <x-button icon="o-link" label="External Link" class="btn-primary w-full shadow-lg shadow-primary/30" link="{{ $book->external_link }}" external target="_blank" />
                    @endif
                    
                    @if($book->pdf_url)
                        <x-button icon="o-arrow-down-tray" label="Download PDF" class="btn-outline w-full" link="{{ $book->pdf_url }}" external download />
                    @endif
                @endif

                @if(in_array($book->type, ['physical', 'both']))
                    @if($book->copies->isEmpty())
                        <x-button icon="o-information-circle" label="No Physical Copies" class="btn-secondary w-full" disabled />
                    @else
                        <x-button icon="o-building-library" label="Borrow Physical Copy" class="btn-secondary w-full" disabled tooltip="Check physical availability below" />
                    @endif
                @endif
            </div>

            {{-- Tracking Dropdown --}}
            <div class="bg-base-200/50 rounded-xl p-4 border border-base-content/5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/50 mb-3">Your Progress</h4>
                
                <div class="dropdown w-full mb-4">
                    <div tabindex="0" role="button" class="btn btn-outline w-full justify-between {{ $reading_status === 'completed' ? 'btn-success text-white' : ($reading_status === 'reading' ? 'btn-info text-white' : ($reading_status === 'want_to_read' ? 'btn-warning text-white' : '')) }}">
                        {{ $reading_status ? str_replace('_', ' ', Str::title($reading_status)) : 'Add to List...' }}
                        <x-icon name="o-chevron-down" class="w-4 h-4" />
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-xl bg-base-100 rounded-box w-full mt-1 border border-base-content/10">
                        <li><a wire:click="setStatus('want_to_read')" class="{{ $reading_status === 'want_to_read' ? 'active' : '' }}"><x-icon name="o-bookmark" class="w-4 h-4" /> Want to Read</a></li>
                        <li><a wire:click="setStatus('reading')" class="{{ $reading_status === 'reading' ? 'active' : '' }}"><x-icon name="o-book-open" class="w-4 h-4" /> Currently Reading</a></li>
                        <li><a wire:click="setStatus('completed')" class="{{ $reading_status === 'completed' ? 'active' : '' }}"><x-icon name="o-check-circle" class="w-4 h-4" /> Completed</a></li>
                        @if($reading_status)
                            <div class="divider my-0"></div>
                            <li><a wire:click="setStatus('')" class="text-error"><x-icon name="o-trash" class="w-4 h-4" /> Remove from list</a></li>
                        @endif
                    </ul>
                </div>

                @if($reading_status === 'reading' && $book->pages_count)
                    <div class="mb-1 flex justify-between text-xs">
                        <span>{{ $pages_read }} / {{ $book->pages_count }} pages</span>
                        <span>{{ round(($pages_read / $book->pages_count) * 100) }}%</span>
                    </div>
                    <progress class="progress progress-info w-full" value="{{ $pages_read }}" max="{{ $book->pages_count }}"></progress>
                    <div class="mt-2 flex gap-2">
                        <x-input wire:model="pages_read" type="number" min="0" max="{{ $book->pages_count }}" class="input-sm input-bordered" />
                        <x-button label="Update" class="btn-sm btn-ghost" wire:click="saveInteraction" />
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Side: Details & Reviews --}}
        <div class="lg:col-span-9 space-y-8">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black text-base-content mb-2">{{ $book->title }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-base-content/70">
                    <div class="flex items-center gap-2">
                        @if($book->author)
                            <div class="avatar">
                                <div class="w-6 rounded-full">
                                    <img src="{{ $book->author->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($book->author->name).'&background=random' }}" />
                                </div>
                            </div>
                            <span class="font-medium text-lg">{{ $book->author->name }}</span>
                        @else
                            <span class="font-medium text-lg">Unknown Author</span>
                        @endif
                    </div>
                    
                    @if($avgRating)
                        <div class="flex items-center gap-1 text-warning bg-warning/10 px-2 py-0.5 rounded-full">
                            <x-icon name="s-star" class="w-4 h-4" />
                            <span class="font-bold text-sm text-warning-content">{{ $avgRating }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Metadata Grid --}}
            <div class="flex flex-wrap gap-4 p-4 rounded-2xl bg-base-200/30 border border-base-content/5">
                <div class="flex-1 min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-base-content/40 mb-1">Category</span>
                    <div class="flex items-center gap-1 font-medium">
                        <x-icon :name="$book->category?->icon ?? 'o-tag'" class="w-4 h-4 text-primary" />
                        {{ $book->category?->name ?? 'Uncategorized' }}
                    </div>
                </div>
                
                @if($book->publication)
                <div class="flex-1 min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-base-content/40 mb-1">Publication</span>
                    <span class="font-medium">{{ $book->publication->name }}</span>
                </div>
                @endif

                @if($book->pages_count)
                <div class="flex-1 min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-base-content/40 mb-1">Pages</span>
                    <span class="font-medium">{{ $book->pages_count }}</span>
                </div>
                @endif

                @if($book->isbn)
                <div class="flex-1 min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-base-content/40 mb-1">ISBN</span>
                    <span class="font-medium font-mono text-sm">{{ $book->isbn }}</span>
                </div>
                @endif

                @if($book->publication_year)
                <div class="flex-1 min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-base-content/40 mb-1">Year</span>
                    <span class="font-medium">{{ $book->publication_year }}</span>
                </div>
                @endif
            </div>

            {{-- Description --}}
            @if($book->description)
                <div class="prose prose-sm md:prose-base dark:prose-invert max-w-none text-base-content/80 leading-relaxed">
                    {{ $book->description }}
                </div>
            @endif

            {{-- Physical Copies Availability --}}
            @if(in_array($book->type, ['physical', 'both']))
                <div class="divider"></div>
                <div>
                    <h3 class="font-bold text-xl mb-4">Physical Copies Available ({{ $book->copies->count() }})</h3>
                    @if($book->copies->isEmpty())
                        <div class="bg-base-200/50 rounded-2xl p-6 text-center text-base-content/50 border border-dashed border-base-content/10">
                            No physical copies are currently available for borrowing.
                        </div>
                    @else
                        @if($userOwnsCopy)
                            <div class="alert alert-info shadow-sm mb-4">
                                <x-icon name="o-information-circle" class="w-5 h-5" />
                                <span>You already own a physical copy of this book, so you cannot request to borrow it from others.</span>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($book->copies as $copy)
                                <div class="bg-base-100 rounded-xl p-4 border border-base-content/10 shadow-sm flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        @if($copy->libraryHub)
                                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                                <x-icon name="o-building-library" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm">{{ $copy->libraryHub->name }}</div>
                                                <div class="text-xs text-base-content/50">Community Hub</div>
                                            </div>
                                        @else
                                            <div class="avatar">
                                                <div class="w-10 rounded-full">
                                                    <img src="{{ $copy->owner->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($copy->owner->name) }}" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm">{{ $copy->owner->name }}</div>
                                                <div class="text-xs text-base-content/50">Condition: {{ $copy->condition }}</div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if(auth()->id() != $copy->owner_id && !$userOwnsCopy)
                                        @if(in_array($copy->id, $activeRequestsCopyIds))
                                            <span class="badge badge-warning border-none shadow-sm text-xs">Requested</span>
                                        @else
                                            <x-button label="Request" icon="o-hand-raised" class="btn-primary btn-sm" wire:click="openBorrowModal({{ $copy->id }})" />
                                        @endif
                                    @elseif(auth()->id() != $copy->owner_id && $userOwnsCopy)
                                        {{-- Hide button since they own a copy --}}
                                    @else
                                        <div class="flex flex-col gap-1 items-end">
                                            <span class="badge badge-ghost border-none shadow-sm text-xs font-bold">Your Copy</span>
                                            @if(!$copy->is_borrowable)
                                                <span class="text-[10px] text-error font-bold">Private</span>
                                            @elseif($copy->status !== 'available')
                                                <span class="text-[10px] text-warning font-bold uppercase">{{ $copy->status }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="divider"></div>

            {{-- My Review Section --}}
            <div class="bg-base-200/50 rounded-2xl p-6 border border-base-content/5">
                <h3 class="font-bold text-lg mb-4">Your Review</h3>
                <div class="flex gap-2 mb-4">
                    @for($i = 1; $i <= 5; $i++)
                        <button wire:click="setRating({{ $i }})" class="hover:scale-110 transition-transform">
                            <x-icon :name="$rating >= $i ? 's-star' : 'o-star'" class="w-8 h-8 {{ $rating >= $i ? 'text-warning' : 'text-base-content/20' }}" />
                        </button>
                    @endfor
                </div>
                <x-textarea wire:model="review" placeholder="What did you think of this book?" rows="3" class="textarea-bordered rounded-xl bg-base-100" />
                <div class="mt-3 flex justify-end">
                    <x-button label="Save Review" wire:click="saveInteraction" class="btn-primary" spinner="saveInteraction" />
                </div>
            </div>

            {{-- Community Reviews --}}
            <div>
                <h3 class="font-bold text-xl mb-6">Community Reviews ({{ $reviewsCount }})</h3>
                
                <div class="space-y-4">
                    @forelse($reviews as $rev)
                        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 flex gap-4">
                            <div class="avatar hidden sm:block">
                                <div class="w-10 h-10 rounded-full">
                                    <img src="{{ $rev->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($rev->user->name) }}" />
                                </div>
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold">{{ $rev->user->name }}</span>
                                    <span class="text-xs text-base-content/40">{{ $rev->updated_at->diffForHumans() }}</span>
                                </div>
                                @if($rev->rating)
                                    <div class="flex gap-1 text-warning mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-icon :name="$rev->rating >= $i ? 's-star' : 'o-star'" class="w-3 h-3" />
                                        @endfor
                                    </div>
                                @endif
                                <p class="text-sm text-base-content/80">{{ $rev->review }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-base-content/40 italic">
                            No reviews yet. Be the first to review!
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Borrow Request Modal --}}
    <x-modal wire:model="borrowModal" title="Request to Borrow" class="backdrop-blur-sm">
        <div class="space-y-4">
            <p class="text-sm text-base-content/70">You are requesting to borrow this physical copy. The owner will be notified.</p>
            
            <x-input type="number" wire:model="requested_days" label="Requested Duration (Days) *" min="1" max="30" class="input-bordered rounded-xl" />
            <div class="text-xs text-base-content/50 italic mt-1">Maximum 30 days. The owner may accept or decline your request.</div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('borrowModal', false)" class="btn-ghost" />
            <x-button label="Send Request" icon="o-paper-airplane" wire:click="submitBorrowRequest" class="btn-primary" spinner="submitBorrowRequest" />
        </x-slot:actions>
    </x-modal>
</div>
