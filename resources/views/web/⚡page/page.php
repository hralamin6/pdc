<?php

use App\Models\Page as PageModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.auth')]
class extends Component
{
    public ?PageModel $page = null;

    public ?string $slug = null;

    public function mount($slug = null)
    {
        if ($slug) {
            $this->slug = $slug;
            $this->page = PageModel::where('slug', $slug)
                ->published()
                ->firstOrFail();
        }
    }
};
