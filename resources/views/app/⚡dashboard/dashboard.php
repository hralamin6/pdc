<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;



new #[Title('Dashboard')] #[Layout('layouts.app')] class extends Component

{
    public function mount(): void
    {
        $this->authorize('dashboard.view');
    }
};