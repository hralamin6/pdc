<div>
    <x-header title="Showcase Albums" subtitle="Manage your gamified gallery hub">
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce.300ms="search" />
            <x-button label="Create Album" icon="o-plus" class="btn-primary" wire:click="$set('showCreateModal', true)" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title / Category</th>
                        <th>Status</th>
                        <th>Photos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->albums as $album)
                        <tr>
                            <td>
                                @if($album->cover_url)
                                    <img src="{{ $album->cover_url }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-base-200 flex items-center justify-center"><x-icon name="o-photo" class="w-5 h-5" /></div>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold">{{ $album->title }}</div>
                                <div class="text-xs text-base-content/70">{{ $album->category ?? 'No Category' }}</div>
                            </td>
                            <td>
                                @if($album->is_published)
                                    <div class="badge badge-success badge-sm">Published</div>
                                @else
                                    <div class="badge badge-ghost badge-sm">Draft</div>
                                @endif
                            </td>
                            <td>{{ $album->media()->count() }} photos</td>
                            <td>
                                <x-button icon="o-pencil" class="btn-sm btn-ghost" link="{{ route('app.gallery.edit', $album->slug) }}" />
                                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="deleteAlbum({{ $album->id }})" wire:confirm="Are you sure?" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No albums found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $this->albums->links() }}</div>
    </x-card>

    <x-modal wire:model="showCreateModal" title="Create New Album">
        <x-form wire:submit="createAlbum">
            <x-input label="Title" wire:model="title" required />
            <x-input label="Category (Optional)" wire:model="category" />
            <x-textarea label="Description (Optional)" wire:model="description" rows="3" />
            <x-toggle label="Publish immediately" wire:model="is_published" />
            
            <x-slot:actions>
                <x-button label="Cancel" wire:click="$set('showCreateModal', false)" />
                <x-button label="Create" type="submit" class="btn-primary" spinner="createAlbum" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
