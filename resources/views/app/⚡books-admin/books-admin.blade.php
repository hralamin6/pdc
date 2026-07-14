<div>
    <x-header :title="__('Manage Books')" :subtitle="__('Library catalog and community suggestions')" separator>
        <x-slot:actions>
            <x-input wire:model.live.debounce="search" icon="o-magnifying-glass" placeholder="{{ __('Search books...') }}" class="input-bordered rounded-xl w-64" />
            <select wire:model.live="statusFilter" class="select select-bordered rounded-xl">
                <option value="all">{{ __('All Statuses') }}</option>
                <option value="approved">{{ __('Approved') }}</option>
                <option value="pending">{{ __('Pending') }}</option>
                <option value="rejected">{{ __('Rejected') }}</option>
            </select>
            <x-button icon="o-plus" label="{{ __('Add Book') }}" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($books as $book)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden flex flex-col group relative {{ $book->status === 'pending' ? 'ring-2 ring-warning ring-offset-2 ring-offset-base-100' : '' }}" wire:key="book-{{ $book->id }}">
                
                {{-- Cover Image --}}
                <div class="h-48 bg-base-200 relative">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" class="w-full h-full object-cover" />
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center opacity-30">
                            <x-icon name="o-book-open" class="w-12 h-12 mb-2" />
                            <span class="text-xs font-bold uppercase">{{ __('No Cover') }}</span>
                        </div>
                    @endif
                    
                    {{-- Badges --}}
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        @if($book->type === 'ebook')
                            <span class="badge badge-primary border-none shadow-sm">{{ __('eBook') }}</span>
                        @elseif($book->type === 'both')
                            <span class="badge badge-accent border-none shadow-sm text-white">{{ __('eBook & Physical') }}</span>
                        @else
                            <span class="badge badge-secondary border-none shadow-sm">{{ __('Physical') }}</span>
                        @endif
                        
                        @if($book->status === 'pending')
                            <span class="badge badge-warning border-none shadow-sm">{{ __('Pending Approval') }}</span>
                        @endif
                    </div>
                </div>

                <div class="p-5 flex-grow flex flex-col">
                    <div class="flex gap-2 items-start justify-between mb-1">
                        <h3 class="font-bold text-lg leading-tight line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h3>
                    </div>
                    <p class="text-sm font-medium text-base-content/70 mb-3">{{ $book->author?->name ?? __('Unknown Author') }}</p>
                    
                    <div class="flex items-center gap-2 text-xs text-base-content/50 mb-4">
                        <div class="flex items-center gap-1"><x-icon name="o-tag" class="w-3 h-3" /> {{ $book->category?->name ?? __('Uncategorized') }}</div>
                        @if($book->pages_count)
                            <span>&bull;</span>
                            <div class="flex items-center gap-1"><x-icon name="o-document-text" class="w-3 h-3" /> {{ $book->pages_count }} p</div>
                        @endif
                    </div>

                    <div class="mt-auto pt-4 border-t border-base-content/5 flex items-center justify-between">
                        <span class="text-[10px] text-base-content/40">{{ __('By:') }} {{ $book->uploader?->name ?? __('System') }}</span>
                        <div class="flex gap-1">
                            @if($book->status === 'pending')
                                <x-button icon="o-check" class="btn-ghost btn-sm text-success" wire:click="approve({{ $book->id }})" tooltip="{{ __('Approve') }}" />
                            @endif
                            <x-button icon="o-pencil" class="btn-ghost btn-sm" wire:click="openModal({{ $book->id }})" tooltip="{{ __('Edit') }}" />
                            <x-button icon="o-trash" class="btn-ghost btn-sm text-error" wire:click="delete({{ $book->id }})" wire:confirm="{{ __('Delete this book?') }}" tooltip="{{ __('Delete') }}" />
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-book-open" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">{{ __('No books found') }}</h3>
                <p class="text-base-content/50 text-sm mb-4">{{ __('Try adjusting your filters or add a new book to the library.') }}</p>
                <x-button icon="o-plus" label="{{ __('Add Book') }}" class="btn-primary" wire:click="openModal()" />
            </div>
        @endforelse
    </div>
    
    <div class="mt-8">{{ $books->links() }}</div>

    {{-- Form Modal --}}
    <x-modal wire:model="modal" title="{{ $editingId ? __('Edit Book') : __('Add New Book') }}" class="backdrop-blur-sm" box-class="w-full max-w-3xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input wire:model="title" label="{{ __('Book Title *') }}" class="input-bordered rounded-xl" />
            <x-select wire:model.live="type" label="{{ __('Type *') }}" :options="[['id'=>'ebook','name'=>__('eBook (Digital)')],['id'=>'physical','name'=>__('Physical Book')],['id'=>'both','name'=>__('Both (eBook & Physical)')]]" class="select-bordered rounded-xl" />
            
            <x-select wire:model="book_category_id" label="{{ __('Category *') }}" :options="$categories" class="select-bordered rounded-xl" placeholder="{{ __('Select a category...') }}" />
            <x-select wire:model="author_id" label="{{ __('Author') }}" :options="$authors" class="select-bordered rounded-xl" placeholder="{{ __('Select an author...') }}" />
            
            <x-select wire:model="publication_id" label="{{ __('Publication') }}" :options="$publications" class="select-bordered rounded-xl" placeholder="{{ __('Select publication...') }}" />
            <x-select wire:model="status" label="{{ __('Status *') }}" :options="[['id'=>'approved','name'=>__('Approved')],['id'=>'pending','name'=>__('Pending')],['id'=>'rejected','name'=>__('Rejected')]]" class="select-bordered rounded-xl" />
        </div>

        <x-textarea wire:model="description" label="{{ __('Description / Synopsis') }}" rows="3" class="textarea-bordered rounded-xl mb-4" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <x-input wire:model="pages_count" type="number" label="{{ __('Pages Count') }}" class="input-bordered rounded-xl" />
            <x-input wire:model="isbn" label="{{ __('ISBN') }}" class="input-bordered rounded-xl" />
            <x-input wire:model="publication_year" label="{{ __('Year') }}" class="input-bordered rounded-xl" />
        </div>

        <div class="p-4 bg-base-200/50 rounded-xl border border-base-content/10 space-y-4 mb-4">
            <h4 class="font-bold text-sm uppercase text-base-content/50">{{ __('Files & Links') }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-file wire:model="cover_file" label="{{ __('Cover Image') }}" accept="image/*" class="file-input-bordered w-full" />
                    
                    @if ($cover_file)
                        <div class="mt-2 p-2 bg-base-100 rounded-lg border border-base-content/10 flex items-center gap-3">
                            <img src="{{ $cover_file->temporaryUrl() }}" class="w-12 h-16 object-cover rounded-md shadow-sm" />
                            <div>
                                <p class="text-xs font-bold text-base-content/75 truncate max-w-[150px]">{{ $cover_file->getClientOriginalName() }}</p>
                                <p class="text-[10px] text-base-content/50">{{ number_format($cover_file->getSize() / 1024, 1) }} KB</p>
                            </div>
                        </div>
                    @elseif ($editingId && ($existingBook = \App\Models\Book::find($editingId)) && $existingBook->cover_url)
                        <div class="mt-2 p-2 bg-base-100 rounded-lg border border-base-content/10 flex items-center gap-3">
                            <img src="{{ $existingBook->cover_url }}" class="w-12 h-16 object-cover rounded-md shadow-sm" />
                            <div>
                                <p class="text-xs font-bold text-base-content/75">{{ __('Current Cover') }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <x-file wire:model="pdf_file" label="{{ __('PDF File (Max 50MB)') }}" accept="application/pdf" class="file-input-bordered w-full" />
                    
                    @if ($pdf_file)
                        <div class="mt-2 p-2 bg-base-100 rounded-lg border border-base-content/10 flex items-center gap-3">
                            <div class="w-12 h-16 bg-rose-500/10 rounded-md flex flex-col items-center justify-center text-rose-500 shadow-sm border border-rose-500/20">
                                <x-icon name="o-document-text" class="w-6 h-6" />
                                <span class="text-[8px] font-black uppercase">PDF</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-base-content/75 truncate max-w-[150px]">{{ $pdf_file->getClientOriginalName() }}</p>
                                <p class="text-[10px] text-base-content/50">{{ number_format($pdf_file->getSize() / (1024 * 1024), 2) }} MB</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <x-input wire:model="external_link" label="{{ __('Or External Download Link (e.g. Google Drive)') }}" class="input-bordered rounded-xl" />
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('modal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? __('Update Book') : __('Save Book') }}" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-modal>
</div>
