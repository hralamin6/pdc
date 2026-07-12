<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\GalleryAlbum;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Title('Edit Album')] #[Layout('layouts.app')] class extends Component {
    use WithFileUploads, Toast;

    public GalleryAlbum $album;
    public $photos = [];

    public function mount($slug)
    {
        Gate::authorize('gallery.manage');
        $this->album = GalleryAlbum::where('slug', $slug)->firstOrFail();
    }

    public function uploadPhotos()
    {
        $this->validate([
            'photos.*' => 'image|max:10240', // 10MB max
        ]);

        foreach ($this->photos as $photo) {
            $this->album->addMedia($photo)->toMediaCollection('gallery_images');
        }

        $this->photos = [];
        $this->success(__('Photos uploaded successfully!'));
    }

    public function deletePhoto($mediaId)
    {
        $media = $this->album->media()->findOrFail($mediaId);
        $media->delete();
        $this->success(__('Photo removed.'));
    }
};
