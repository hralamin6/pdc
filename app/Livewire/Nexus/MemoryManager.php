<?php

namespace App\Livewire\Nexus;

use App\Models\NexusMemory;
use App\Nexus\Services\NexusMemoryService;
use Livewire\Component;
use Mary\Traits\Toast;

class MemoryManager extends Component
{
    use Toast;

    public string $newKey   = '';
    public string $newValue = '';
    public string $newCat   = 'general';
    public bool   $showAdd  = false;

    public function addMemory(NexusMemoryService $svc): void
    {
        $this->validate([
            'newKey'   => 'required|string|max:80',
            'newValue' => 'required|string|max:500',
        ]);

        $svc->remember(auth()->id(), $this->newKey, $this->newValue, $this->newCat);
        $this->reset(['newKey', 'newValue', 'newCat', 'showAdd']);
        $this->success('Memory saved!');
    }

    public function deleteMemory(int $id): void
    {
        NexusMemory::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function getMemoriesProperty()
    {
        return NexusMemory::where('user_id', auth()->id())
            ->orderBy('category')
            ->orderBy('key')
            ->get();
    }

    public function render()
    {
        return view('nexus.livewire.memory-manager', [
            'memories' => $this->memories,
        ]);
    }
}
