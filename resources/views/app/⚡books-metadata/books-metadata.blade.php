<div>
    <x-header title="Library Metadata" subtitle="Manage authors, publications, and categories" separator>
        <x-slot:actions>
            <x-button icon="o-plus" label="Add New" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    {{-- Tabs --}}
    <div class="tabs tabs-boxed bg-base-200/50 mb-6 p-2 rounded-2xl border border-base-content/5 inline-flex">
        <button wire:click="switchTab('authors')" class="tab font-bold {{ $activeTab === 'authors' ? 'tab-active bg-primary text-white rounded-xl' : '' }}">Authors ({{ $authors->count() }})</button>
        <button wire:click="switchTab('publications')" class="tab font-bold {{ $activeTab === 'publications' ? 'tab-active bg-primary text-white rounded-xl' : '' }}">Publications ({{ $publications->count() }})</button>
        <button wire:click="switchTab('categories')" class="tab font-bold {{ $activeTab === 'categories' ? 'tab-active bg-primary text-white rounded-xl' : '' }}">Categories ({{ $categories->count() }})</button>
    </div>

    {{-- Grids --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @if($activeTab === 'authors')
            @forelse($authors as $author)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm hover:shadow-md transition-all flex flex-col text-center group" wire:key="author-{{ $author->id }}">
                    <div class="avatar mx-auto mb-3">
                        <div class="w-20 rounded-full ring ring-primary/20 ring-offset-base-100 ring-offset-2">
                            <img src="{{ $author->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($author->name).'&background=random' }}" />
                        </div>
                    </div>
                    <h3 class="font-black text-lg text-base-content">{{ $author->name }}</h3>
                    <p class="text-xs text-base-content/50 mb-3">{{ $author->books_count }} books in library</p>
                    
                    <div class="mt-auto pt-4 flex gap-2 justify-center opacity-50 group-hover:opacity-100 transition-opacity border-t border-base-content/5">
                        <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="openModal({{ $author->id }})" tooltip="Edit" />
                        <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="delete({{ $author->id }})" wire:confirm="Delete this author?" tooltip="Delete" />
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-base-content/40">
                    <x-icon name="o-users" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p class="font-medium">No authors recorded yet.</p>
                </div>
            @endforelse
        
        @elseif($activeTab === 'publications')
            @forelse($publications as $pub)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm hover:shadow-md transition-all flex flex-col group" wire:key="pub-{{ $pub->id }}">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="avatar">
                            <div class="w-12 h-12 rounded-lg bg-base-200">
                                @if($pub->logo_url)
                                    <img src="{{ $pub->logo_url }}" class="object-cover" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center font-bold text-base-content/30">{{ substr($pub->name, 0, 1) }}</div>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3 class="font-black text-base-content">{{ $pub->name }}</h3>
                            <p class="text-xs text-base-content/50">{{ $pub->books_count }} books</p>
                        </div>
                    </div>
                    
                    <div class="mt-auto pt-3 flex justify-end opacity-50 group-hover:opacity-100 transition-opacity border-t border-base-content/5">
                        <x-button icon="o-pencil" class="btn-ghost btn-sm" wire:click="openModal({{ $pub->id }})" tooltip="Edit" />
                        <x-button icon="o-trash" class="btn-ghost btn-sm text-error" wire:click="delete({{ $pub->id }})" wire:confirm="Delete this publication?" tooltip="Delete" />
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-base-content/40">
                    <x-icon name="o-building-library" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p class="font-medium">No publications recorded yet.</p>
                </div>
            @endforelse

        @elseif($activeTab === 'categories')
            @forelse($categories as $cat)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group" wire:key="cat-{{ $cat->id }}">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <x-icon :name="$cat->icon" class="w-6 h-6" />
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-bold text-base-content">{{ $cat->name }}</h3>
                        <p class="text-xs text-base-content/50">{{ $cat->books_count }} books</p>
                    </div>
                    <div class="flex flex-col gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                        <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="openModal({{ $cat->id }})" />
                        <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="delete({{ $cat->id }})" wire:confirm="Delete this category?" />
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-base-content/40">
                    <x-icon name="o-tag" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p class="font-medium">No categories recorded yet.</p>
                </div>
            @endforelse
        @endif
    </div>

    {{-- Dynamic Modal --}}
    <x-modal wire:model="modal" title="{{ $editingId ? 'Edit' : 'Add New' }} {{ Str::title(Str::singular($activeTab)) }}" class="backdrop-blur-sm">
        <div class="space-y-4">
            @if($activeTab === 'authors')
                <x-input wire:model="authorName" label="Author Name *" placeholder="e.g. Ibn Kathir" class="input-bordered rounded-xl" />
                <x-textarea wire:model="authorBio" label="Biography" rows="3" class="textarea-bordered rounded-xl" />
                <x-file wire:model="authorPhoto" label="Photo (Optional)" accept="image/*" class="file-input-bordered" />

            @elseif($activeTab === 'publications')
                <x-input wire:model="pubName" label="Publication Name *" placeholder="e.g. Darussalam" class="input-bordered rounded-xl" />
                <x-textarea wire:model="pubDesc" label="Description" rows="2" class="textarea-bordered rounded-xl" />
                <x-file wire:model="pubLogo" label="Logo (Optional)" accept="image/*" class="file-input-bordered" />

            @elseif($activeTab === 'categories')
                <x-input wire:model="catName" label="Category Name *" placeholder="e.g. Fiqh" class="input-bordered rounded-xl" />
                <div>
                    <label class="label"><span class="label-text font-semibold">Icon *</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($iconOptions as $icon => $label)
                            <button type="button" wire:click="$set('catIcon', '{{ $icon }}')" class="flex flex-col items-center p-2 rounded-xl border-2 {{ $catIcon === $icon ? 'border-primary bg-primary/10 text-primary' : 'border-base-content/10' }}">
                                <x-icon :name="$icon" class="w-6 h-6" />
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('modal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? 'Update' : 'Save' }}" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-modal>
</div>
