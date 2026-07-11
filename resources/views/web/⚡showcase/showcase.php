<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GalleryAlbum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Title('Showcase & Gallery')] #[Layout('layouts.web')] class extends Component {
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getAlbumsProperty()
    {
        return GalleryAlbum::where('is_published', true)
            ->where('title', 'like', '%' . $this->search . '%')
            ->with('media')
            ->latest()
            ->paginate(12);
    }
};
