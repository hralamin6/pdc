<?php

use App\Models\GalleryAlbum;
use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.web')]
class extends Component
{
    public GalleryAlbum $album;

    public function mount(string $slug): void
    {
        $this->album = GalleryAlbum::where('slug', $slug)
            ->where('is_published', true)
            ->with('media')
            ->firstOrFail();
    }

    public function title(): string
    {
        return $this->album->title . ' — Showcase';
    }
};
