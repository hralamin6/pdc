<?php

use App\Models\LibraryHub;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Community Library Hubs')] #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'hubs' => LibraryHub::with(['manager', 'bookCopies.book'])->get(),
        ];
    }
};
