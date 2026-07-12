<div>
    <x-header :title="__('Community Hubs Management')" :subtitle="__('Manage your hubs, inventory, and borrow requests')" separator>
        <x-slot:actions>
            @if(auth()->user()->hasRole(['super-admin', 'admin']))
                <x-button label="{{ __('Create Hub') }}" icon="o-plus" class="btn-primary" wire:click="openHubModal" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- Tabs --}}
    <x-tabs wire:model="activeTab" class="mb-6">
        <x-tab name="hubs" label="{{ __('Hubs & Inventory') }}" icon="o-building-library">
            <div class="grid grid-cols-1 gap-6 mt-6">
                @forelse($this->hubs as $hub)
                    <div class="bg-base-100 border border-base-content/10 shadow-sm rounded-2xl overflow-hidden" wire:key="hub-{{ $hub->id }}">
                        <div class="bg-primary/5 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-base-content/10">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-primary text-primary-content rounded-xl flex items-center justify-center shrink-0">
                                    <x-icon name="o-building-library" class="w-8 h-8" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-base-content">{{ $hub->name }}</h2>
                                    <p class="text-sm text-base-content/60 flex items-center gap-2 mt-1">
                                        <x-icon name="o-map-pin" class="w-4 h-4" /> {{ $hub->location ?? __('Unknown Location') }}
                                        <span class="opacity-50">|</span>
                                        <x-icon name="o-user" class="w-4 h-4" /> {{ $hub->manager->name ?? __('No Manager') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(auth()->user()->hasRole(['super-admin', 'admin']))
                                    <x-button icon="o-pencil" class="btn-ghost btn-sm btn-circle" wire:click="openHubModal({{ $hub->id }})" tooltip="{{ __('Edit Hub') }}" />
                                    <x-button icon="o-power" class="btn-ghost btn-sm btn-circle {{ $hub->is_active ? 'text-success' : 'text-error' }}" wire:click="toggleHubStatus({{ $hub->id }})" tooltip="{{ __('Toggle Status') }}" />
                                @endif
                                <span class="badge {{ $hub->is_active ? 'badge-success text-success-content' : 'badge-error text-error-content' }}">
                                    {{ $hub->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4 border-b border-base-content/5 pb-2">
                                <h3 class="font-bold text-sm uppercase tracking-wider text-base-content/50">{{ __('Inventory') }} ({{ $hub->book_copies_count }})</h3>
                                <x-button label="{{ __('Add Books') }}" icon="o-plus" class="btn-primary btn-xs" wire:click="openAddBooksModal({{ $hub->id }})" />
                            </div>
                            
                            @if($hub->bookCopies->isEmpty())
                                <p class="text-sm text-base-content/50 italic">{{ __('No books in this hub yet.') }}</p>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($hub->bookCopies as $copy)
                                        <div class="flex items-start gap-3 p-3 rounded-xl border border-base-content/5 bg-base-200/30">
                                            @if($copy->book->cover_url)
                                                <img src="{{ $copy->book->cover_url }}" class="w-12 h-16 object-cover rounded shadow-sm" />
                                            @else
                                                <div class="w-12 h-16 bg-base-300 rounded shadow-sm flex items-center justify-center">
                                                    <x-icon name="o-book-open" class="w-6 h-6 text-base-content/30" />
                                                </div>
                                            @endif
                                            <div class="flex-grow min-w-0">
                                                <h4 class="font-bold text-sm truncate">{{ $copy->book->title }}</h4>
                                                <p class="text-xs text-base-content/60 truncate">{{ $copy->book->author->name ?? '' }}</p>
                                                <div class="mt-2 flex gap-1">
                                                    <span class="badge badge-outline text-[10px]">{{ $copy->condition }}</span>
                                                    <span class="badge {{ $copy->status === 'available' ? 'badge-success text-success-content' : 'badge-warning text-warning-content' }} text-[10px]">
                                                        {{ strtoupper($copy->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-base-content/50 bg-base-100 rounded-2xl border border-base-content/5">
                        <x-icon name="o-building-library" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                        <p>{{ __('No hubs available or you do not have permission to manage any hubs.') }}</p>
                    </div>
                @endforelse
            </div>
        </x-tab>

        <x-tab name="requests" label="{{ __('Borrow Requests') }}" icon="o-inbox">
            <div class="mt-6">
                <div class="join mb-6 flex justify-center">
                    <button wire:click="$set('requestsSubTab', 'incoming')" class="btn btn-sm join-item {{ $requestsSubTab === 'incoming' ? 'btn-primary' : '' }}">{{ __('Active Requests') }}</button>
                    <button wire:click="$set('requestsSubTab', 'history')" class="btn btn-sm join-item {{ $requestsSubTab === 'history' ? 'btn-primary' : '' }}">{{ __('History') }}</button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @forelse($this->borrowRequests as $req)
                        <div class="bg-base-100 border border-base-content/10 shadow-sm rounded-2xl p-4 flex gap-4" wire:key="req-{{ $req->id }}">
                            @if($req->bookCopy->book->cover_url)
                                <img src="{{ $req->bookCopy->book->cover_url }}" class="w-16 h-24 object-cover rounded-lg shadow-sm" />
                            @else
                                <div class="w-16 h-24 bg-base-200 flex items-center justify-center rounded-lg shadow-sm text-base-content/30 shrink-0">
                                    <x-icon name="o-book-open" class="w-8 h-8" />
                                </div>
                            @endif
                            <div class="flex-grow min-w-0">
                                <h3 class="font-bold text-sm leading-tight mb-1 truncate">{{ $req->bookCopy->book->title }}</h3>
                                <p class="text-xs text-base-content/60 mb-2">
                                    <span class="font-bold">{{ __('From Hub:') }}</span> {{ $req->bookCopy->libraryHub->name ?? 'Unknown' }}
                                </p>
                                <div class="bg-base-200/50 rounded-lg p-2 text-xs mb-3">
                                    <p><span class="font-bold text-base-content/60">{{ __('Borrower:') }}</span> {{ $req->borrower->name ?? 'Unknown' }}</p>
                                    <p><span class="font-bold text-base-content/60">{{ __('Requested Duration:') }}</span> {{ $req->requested_days }} {{ __('days') }}</p>
                                    @if($req->status === 'active' || $req->status === 'given')
                                        <p class="{{ $req->due_date && $req->due_date->isPast() ? 'text-error font-bold' : 'text-success' }}">
                                            <span class="font-bold">{{ __('Due:') }}</span> 
                                            {{ $req->due_date ? $req->due_date->diffForHumans() : 'Not set' }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="badge badge-sm uppercase tracking-wide
                                        {{ $req->status === 'pending' ? 'badge-warning text-warning-content' : '' }}
                                        {{ $req->status === 'accepted' ? 'badge-info text-info-content' : '' }}
                                        {{ $req->status === 'given' ? 'badge-primary text-primary-content' : '' }}
                                        {{ $req->status === 'active' ? 'badge-success text-success-content' : '' }}
                                        {{ in_array($req->status, ['returned','rejected','cancelled']) ? 'badge-ghost' : '' }}">
                                        {{ $req->status }}
                                    </span>
                                    
                                    <div class="flex gap-2">
                                        @if($req->status === 'pending')
                                            <x-button icon="o-x-mark" class="btn-error btn-xs btn-outline" wire:click="rejectRequest({{ $req->id }})" spinner tooltip="{{ __('Reject') }}" />
                                            <x-button icon="o-check" class="btn-success btn-xs" wire:click="acceptRequest({{ $req->id }})" spinner tooltip="{{ __('Accept') }}" />
                                        @elseif($req->status === 'accepted')
                                            <x-button label="{{ __('Mark as Given') }}" icon="o-hand-raised" class="btn-primary btn-xs" wire:click="markGiven({{ $req->id }})" spinner />
                                        @elseif(in_array($req->status, ['active', 'given']))
                                            @if($req->status === 'active')
                                                <x-button label="{{ __('Remind') }}" icon="o-bell" class="btn-warning btn-xs" wire:click="sendReminder({{ $req->id }})" spinner />
                                            @endif
                                            <x-button label="{{ __('Confirm Returned') }}" icon="o-arrow-uturn-left" class="btn-success btn-xs" wire:click="confirmReturned({{ $req->id }})" spinner />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-2 py-12 text-center text-base-content/50 bg-base-100 rounded-2xl border border-base-content/5">
                            <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                            <p>{{ __('No requests found for the selected filter.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </x-tab>
    </x-tabs>

    {{-- Hub Modal --}}
    <x-modal wire:model="hubModal" title="{{ $editingHubId ? __('Edit Hub') : __('Create Hub') }}" box-class="max-w-md">
        <div class="space-y-4">
            <x-input label="{{ __('Hub Name') }}" wire:model="hubName" placeholder="e.g. PSTU Central Library" icon="o-building-library" />
            <x-input label="{{ __('Location / Description') }}" wire:model="hubLocation" placeholder="e.g. Main Campus, 2nd Floor" icon="o-map-pin" />
            <x-select label="{{ __('Manager') }}" wire:model="hubManagerId" :options="$this->potentialManagers" option-value="id" option-label="name" placeholder="Select a manager" icon="o-user" />
            <x-toggle label="{{ __('Active') }}" wire:model="hubIsActive" right />
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('hubModal', false)" class="btn-ghost" />
            <x-button label="{{ __('Save Hub') }}" class="btn-primary" wire:click="saveHub" spinner="saveHub" />
        </x-slot:actions>
    </x-modal>

    {{-- Add Books Modal --}}
    <x-modal wire:model="addBooksModal" title="{{ __('Add Physical Books to Hub') }}" box-class="max-w-2xl">
        <div class="space-y-4">
            <x-input wire:model.live.debounce.300ms="bookSearch" placeholder="{{ __('Search catalog by title or author...') }}" icon="o-magnifying-glass" clearable />

            @if(strlen($bookSearch) > 1)
                <div class="bg-base-200/50 rounded-xl max-h-64 overflow-y-auto border border-base-300">
                    @forelse($this->catalogBooks as $b)
                        <div class="p-3 border-b border-base-300 last:border-0 flex items-center gap-3 cursor-pointer hover:bg-base-300/50 transition-colors" wire:click="toggleBookSelection({{ $b->id }})">
                            <div class="pt-1">
                                <x-checkbox wire:model.live="selectedBookIds" value="{{ $b->id }}" />
                            </div>
                            @if($b->cover_url)
                                <img src="{{ $b->cover_url }}" class="w-10 h-14 object-cover rounded shadow-sm shrink-0" />
                            @else
                                <div class="w-10 h-14 bg-base-300 flex items-center justify-center rounded shadow-sm shrink-0"><x-icon name="o-book-open" class="w-4 h-4 opacity-50" /></div>
                            @endif
                            <div class="flex-grow min-w-0">
                                <div class="font-bold text-sm truncate leading-tight">{{ $b->title }}</div>
                                <div class="text-xs opacity-70 truncate">{{ $b->author?->name ?? __('Unknown Author') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-sm opacity-60">
                            {{ __('No matches found. Ensure the book is added to the global catalog first.') }}
                            <br>
                            <a href="{{ route('app.books.admin') }}" target="_blank" class="text-primary hover:underline font-bold text-xs mt-2 inline-block">
                                {{ __('Go to Manage Catalog') }} <x-icon name="o-arrow-top-right-on-square" class="w-3 h-3" />
                            </a>
                        </div>
                    @endforelse
                </div>
            @endif

            <div class="bg-primary/5 p-4 rounded-xl border border-primary/20">
                <div class="flex items-center gap-2 mb-3">
                    <div class="badge badge-primary">{{ count($selectedBookIds) }}</div>
                    <span class="font-bold text-sm">{{ __('Book(s) Selected') }}</span>
                </div>
                
                @if(count($selectedBookIds) === 1)
                    <x-select label="{{ __('Physical Condition') }}" wire:model="singleCondition" :options="[['id' => 'New', 'name' => 'New'], ['id' => 'Good', 'name' => 'Good'], ['id' => 'Fair', 'name' => 'Fair'], ['id' => 'Worn', 'name' => 'Worn']]" option-value="id" option-label="name" />
                @elseif(count($selectedBookIds) > 1)
                    <div class="alert alert-info text-xs py-2">
                        <x-icon name="o-information-circle" />
                        <span>{{ __('Bulk adding multiple books will set their condition to "Good" by default. You can edit this later.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('addBooksModal', false)" class="btn-ghost" />
            <x-button label="{{ count($selectedBookIds) > 0 ? __('Add ' . count($selectedBookIds) . ' Book(s)') : __('Select Books to Add') }}" class="btn-primary" wire:click="saveBooksToHub" spinner="saveBooksToHub" :disabled="count($selectedBookIds) === 0" />
        </x-slot:actions>
    </x-modal>
</div>
