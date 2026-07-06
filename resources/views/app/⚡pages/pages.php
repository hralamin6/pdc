<?php

use App\Models\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Pages')] #[Layout('layouts.app')] class  extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public Collection $library;

    // Table/List Properties
    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'order';

    public $orderDirection = 'asc';

    public $search = '';

    public $searchBy = 'title';

    public $itemStatus = null;

    // Form Properties
    public $title = '';

    public $slug = '';

    public $content = '';

    public $meta_title = '';

    public $meta_description = '';

    public $meta_keywords = '';

    public $status = 'draft';

    public $published_at = null;

    public $order = 0;

    // File uploads
    public $photo;

    public $image_url = '';

    // Current model being edited
    public $page = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('pages.view');
    }
    #[Computed]
    public function items()
    {
        return $this->data;
    }

    public function getDataProperty()
    {
        return Page::where($this->searchBy, 'like', '%'.$this->search.'%')
            ->orderBy($this->orderBy, $this->orderDirection)
            ->when($this->itemStatus, function ($query) {
                return $query->where('status', $this->itemStatus);
            })
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:pages,slug,'.$this->page?->id,
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'order' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
        ];
    }

    public function updatedTitle(): void
    {
        $this->slug = \Str::slug($this->title);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedItemPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedItemStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSelectPageRows($value): void
    {
        if ($value) {
            $this->selectedRows = $this->data->pluck('id')->map(function ($id) {
                return (string) $id;
            })->toArray();
        } else {
            $this->reset('selectedRows', 'selectPageRows');
        }
    }

    public function orderByDirection($field): void
    {
        if ($this->orderBy == $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $field;
            $this->orderDirection = 'asc';
        }
    }

    public function saveData(): void
    {
        $this->authorize('pages.create');

        // Pre-process data
        $this->meta_title = $this->meta_title ?: $this->title;
        $this->meta_description = $this->meta_description ?: substr(strip_tags($this->content), 0, 155);

        // Validate
        $data = $this->validate();

        // Create
        $model = Page::create($data);

        // Handle media
        $this->handleMediaUpload($model);

        // Notify
        $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
        $this->goToPage($this->getDataProperty()->lastPage());
        $this->success(__('Page created successfully!'));

        // Reset
        $this->resetData();
    }

    public function loadData(Page $page): void
    {
        //      $page = Page::findOrFail($id);
        $this->resetData();

        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content;
        $this->meta_title = $page->meta_title ?? '';
        $this->meta_description = $page->meta_description ?? '';
        $this->meta_keywords = $page->meta_keywords ?? '';
        $this->status = $page->status;
        $this->published_at = $page->published_at?->format('Y-m-d\TH:i');
        $this->order = $page->order;

        $this->page = $page;
    }

    public function editData(): void
    {
        $this->authorize('pages.edit');

        // Pre-process data
        $this->meta_title = $this->meta_title ?: $this->title;
        $this->meta_description = $this->meta_description ?: substr(strip_tags($this->content), 0, 155);

        $data = $this->validate();
        $this->page->update($data);

        $this->handleMediaUpload($this->page);

        $this->dispatch('dataAdded', dataId: "item-id-{$this->page->id}");
        $this->success(__('Page updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset([
            'title', 'slug', 'content', 'meta_title', 'meta_description',
            'meta_keywords', 'status', 'published_at', 'order',
            'image_url', 'photo', 'page',
        ]);

        // Re-initialize defaults
        $this->status = 'draft';
        $this->order = 0;
    }

    public function deleteSingle(Page $page): void
    {
        $this->authorize('pages.delete');
        $page->delete();
        $this->success(__('Page deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('pages.delete');

        Page::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Pages deleted successfully'));
    }

    public function changeStatus(Page $page): void
    {
        $this->authorize('pages.edit');

        $page->status == 'draft'
          ? $page->update(['status' => 'published'])
          : $page->update(['status' => 'draft']);

        $this->success(__('Status updated successfully'));
    }

    public function deleteMedia(Page $page, $key): void
    {
        $this->authorize('pages.edit');

        $media = $page->getMedia('featured_image');
        $media[$key]->delete();
        $this->success(__('Image deleted successfully'));
    }

    protected function handleMediaUpload($model): void
    {
        // URL upload (priority)
        if ($this->image_url && checkImageUrl($this->image_url)) {
            $extension = pathinfo(parse_url($this->image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $media = $model->addMediaFromUrl($this->image_url)->usingFileName($model->id.'.'.$extension)->toMediaCollection('featured_image');
            $path = storage_path('app/public/Page/'.$model->id.'/'.$media->file_name);
            if (file_exists($path)) {
                unlink($path);
            }
        } // File upload (fallback)
        elseif ($this->photo) {
            $media = $model->addMedia($this->photo->getRealPath())
                ->usingFileName($model->id.'.'.$this->photo->extension())
                ->toMediaCollection('featured_image');

            $path = storage_path('app/public/Page/'.$model->id.'/'.$media->file_name);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
};
