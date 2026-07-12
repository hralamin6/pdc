<div>
    <x-header title="{{ __('Edit Album: :title', ['title' => $album->title]) }}" subtitle="{{ __('Upload and manage photos for this album.') }}">
        <x-slot:actions>
            <x-button label="{{ __('Back to Albums') }}" icon="o-arrow-left" link="{{ route('app.gallery.admin') }}" class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <x-card title="{{ __('Upload Photos') }}">
                <x-form wire:submit="uploadPhotos">
                    <x-file wire:model="photos" label="{{ __('Select Images') }}" multiple accept="image/*" />

                    <x-slot:actions>
                        <x-button label="{{ __('Upload') }}" type="submit" class="btn-primary btn-block" spinner="uploadPhotos" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
        
        <div class="md:col-span-2">
            <x-card title="{{ __('Gallery Photos') }}">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @forelse($album->getMedia('gallery_images') as $media)
                        <div class="relative group rounded-xl overflow-hidden aspect-square bg-base-200">
                            <img src="{{ $media->getUrl('thumb') }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <x-button icon="o-trash" class="btn-circle btn-error btn-sm" wire:click="deletePhoto({{ $media->id }})" wire:confirm="{{ __('Remove this photo?') }}" />
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-base-content/50">
                            {{ __('No photos uploaded yet.') }}
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
