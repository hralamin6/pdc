<?php

use Livewire\WithPagination;
use App\Models\GalleryAlbum;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Showcase Albums')] #[Layout('layouts.app')] class extends Component {
    use WithPagination, Toast;

    public $search = '';
    public $showCreateModal = false;
    public $title = '';
    public $description = '';
    public $category = '';
    public $is_published = true;

    public function mount()
    {
        Gate::authorize('gallery.manage');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createAlbum()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
        ]);

        $album = GalleryAlbum::create([
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . uniqid(),
            'description' => $this->description,
            'category' => $this->category,
            'is_published' => $this->is_published,
        ]);

        $this->showCreateModal = false;
        $this->reset(['title', 'description', 'category', 'is_published']);
        $this->success('Album created successfully!');
        
        return redirect()->route('app.gallery.edit', $album->slug);
    }

    public function deleteAlbum($id)
    {
        $album = GalleryAlbum::findOrFail($id);
        $album->delete();
        $this->success('Album deleted.');
    }

    public function getAlbumsProperty()
    {
        return GalleryAlbum::where('title', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);
    }
};
