<div>
    <x-header title="My Shelf" subtitle="Manage your physical books and lending settings" separator>
        <x-slot:actions>
            <x-button icon="o-plus" label="Add Book to Shelf" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30" wire:click="openAddModal()" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($this->myCopies as $copy)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden flex flex-col relative" wire:key="copy-{{ $copy->id }}">
                {{-- Cover --}}
                <div class="h-48 bg-base-200 relative">
                    @if($copy->book->cover_url)
                        <img src="{{ $copy->book->cover_url }}" class="w-full h-full object-cover" />
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-base-content/30">
                            <x-icon name="o-book-open" class="w-12 h-12 mb-2" />
                        </div>
                    @endif

                    <div class="absolute top-2 right-2 flex flex-col gap-1">
                        @if($copy->status === 'available')
                            <span class="badge badge-success border-none text-white shadow-sm">Available</span>
                        @elseif($copy->status === 'borrowed')
                            <span class="badge badge-warning border-none shadow-sm">Borrowed Out</span>
                        @endif
                    </div>
                </div>

                {{-- Details --}}
                <div class="p-5 flex-grow flex flex-col">
                    <h3 class="font-bold text-lg leading-tight line-clamp-2 mb-1">{{ $copy->book->title }}</h3>
                    <p class="text-sm text-base-content/60 mb-3">{{ $copy->book->author?->name ?? 'Unknown Author' }}</p>

                    <div class="flex items-center justify-between text-xs text-base-content/50 mb-4 bg-base-200/50 p-2 rounded-lg">
                        <span>Cond: <span class="font-semibold text-base-content">{{ $copy->condition }}</span></span>
                        <span>
                            @if($copy->is_borrowable)
                                <span class="text-success flex items-center gap-1"><x-icon name="o-check-circle" class="w-3 h-3" /> Lendable</span>
                            @else
                                <span class="text-error flex items-center gap-1"><x-icon name="o-x-circle" class="w-3 h-3" /> Private</span>
                            @endif
                        </span>
                    </div>

                    <div class="mt-auto border-t border-base-content/5 pt-3 flex justify-between items-center">
                        <x-button class="btn-xs {{ $copy->is_borrowable ? 'btn-outline text-error' : 'btn-outline text-success' }}" 
                            label="{{ $copy->is_borrowable ? 'Make Private' : 'Make Lendable' }}" 
                            wire:click="toggleBorrowable({{ $copy->id }})" />
                            
                        <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="deleteCopy({{ $copy->id }})" wire:confirm="Remove this book from your shelf?" />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-bookmark-square" class="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-base-content/70 mb-2">Your shelf is empty</h3>
                <p class="text-base-content/50 mb-4">Add physical books you own to let others in the community borrow them.</p>
                <x-button icon="o-plus" label="Add Book" class="btn-primary" wire:click="openAddModal()" />
            </div>
        @endforelse
    </div>

    {{-- Add Book Modal --}}
    <x-modal wire:model="addModal" title="Add Physical Book" class="backdrop-blur-sm">
        <div class="space-y-4">
            <x-input wire:model.live.debounce="search" icon="o-magnifying-glass" placeholder="Search book title..." label="Search Global Catalog" class="input-bordered rounded-xl" />
            
            <div class="max-h-60 overflow-y-auto rounded-xl border border-base-content/10 bg-base-200/30 p-2 space-y-1">
                @forelse($this->availableBooks as $b)
                    <button type="button" wire:click="selectBook({{ $b->id }})" class="w-full text-left p-3 rounded-lg flex items-center gap-3 transition-colors {{ $selectedBookId === $b->id ? 'bg-primary text-primary-content' : 'hover:bg-base-300' }}">
                        @if($b->cover_url)
                            <img src="{{ $b->cover_url }}" class="w-8 h-12 object-cover rounded shadow-sm" />
                        @else
                            <div class="w-8 h-12 bg-base-100 flex items-center justify-center rounded shadow-sm"><x-icon name="o-book-open" class="w-4 h-4 opacity-50" /></div>
                        @endif
                        <div>
                            <div class="font-bold text-sm">{{ $b->title }}</div>
                            <div class="text-xs opacity-70">{{ $b->author?->name }}</div>
                        </div>
                    </button>
                @empty
                    <div class="p-4 text-center text-sm text-base-content/50 italic">
                        @if($search)
                            No exact match found in the global catalog.
                        @else
                            Type to search for books...
                        @endif
                    </div>
                @endforelse
            </div>

            @if(!$isCustom)
                <div class="text-center mt-2">
                    <x-button label="Can't find your book? Create a new one." class="btn-link btn-sm text-primary text-xs" wire:click="toggleCustom" />
                </div>
            @endif

            @if($isCustom)
                <div class="bg-base-200/50 p-4 rounded-xl border border-base-content/10 space-y-3 mt-4">
                    <h3 class="font-bold text-sm">Add Custom Book</h3>
                    <x-input wire:model="customTitle" label="Book Title *" class="input-bordered rounded-xl" />
                    <x-input wire:model="customAuthor" label="Author Name" class="input-bordered rounded-xl" />
                    <x-select wire:model="customCategoryId" label="Category" :options="$this->categories" option-value="id" option-label="name" placeholder="Select Category" class="select-bordered rounded-xl" />
                </div>
            @endif

            @if($selectedBookId || $isCustom)
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <x-select wire:model="condition" label="Condition *" :options="[['id'=>'New','name'=>'New'],['id'=>'Good','name'=>'Good'],['id'=>'Fair','name'=>'Fair'],['id'=>'Worn','name'=>'Worn']]" class="select-bordered rounded-xl" />
                    <x-select wire:model="is_borrowable" label="Lending Status *" :options="[['id'=>1,'name'=>'Available to Lend'],['id'=>0,'name'=>'Keep Private']]" class="select-bordered rounded-xl" />
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('addModal', false)" class="btn-ghost" />
            <x-button label="Add to Shelf" icon="o-plus" wire:click="addCopy" class="btn-primary" spinner="addCopy" :disabled="!$selectedBookId && !$isCustom" />
        </x-slot:actions>
    </x-modal>
</div>
