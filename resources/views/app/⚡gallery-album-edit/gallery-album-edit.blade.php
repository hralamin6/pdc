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
                        <div class="flex flex-col gap-2 w-full">
                            <x-button 
                                label="{{ __('Generate with AI') }}" 
                                icon="s-sparkles" 
                                class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 border-none text-white btn-block hover:scale-[1.02] transition-transform" 
                                wire:click="$dispatch('ai-generator:open', { targetId: '{{ $this->getId() }}', property: 'photos', contextTitle: '{{ addslashes($album->title) }}', contextPrompt: 'A beautiful, highly detailed cover image for the Islamic album titled: {{ addslashes($album->title) }}. High quality, cinematic lighting.' })" 
                            />
                            <x-button label="{{ __('Upload Selected') }}" type="submit" class="btn-primary btn-block" spinner="uploadPhotos" />
                        </div>
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
