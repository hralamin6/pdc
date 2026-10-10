<?php

namespace App\Livewire\Nexus;

use App\Models\NexusDocument;
use App\Nexus\Jobs\IndexNexusDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class DocumentManager extends Component
{
    use WithFileUploads, Toast;

    public ?int $workspaceId = null;
    public $file = null;
    public string $docName = '';

    #[On('workspace-changed')]
    public function setWorkspace(int $id): void
    {
        $this->workspaceId = $id;
    }

    public function uploadDocument(): void
    {
        $this->validate([
            'file'    => 'required|file|max:20480|mimes:pdf,txt,md,csv',
            'docName' => 'nullable|string|max:100',
        ]);

        $path = $this->file->store('nexus/documents', 'local');

        $doc = NexusDocument::create([
            'user_id'            => auth()->id(),
            'nexus_workspace_id' => $this->workspaceId,
            'name'               => $this->docName ?: $this->file->getClientOriginalName(),
            'original_filename'  => $this->file->getClientOriginalName(),
            'mime_type'          => $this->file->getMimeType(),
            'file_path'          => $path,
            'file_size'          => $this->file->getSize(),
            'status'             => 'pending',
        ]);

        IndexNexusDocument::dispatch($doc->id);

        $this->reset(['file', 'docName']);
        $this->success('Document uploaded! Indexing in background...');
    }

    public function deleteDocument(int $id): void
    {
        $doc = NexusDocument::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$doc) return;

        Storage::disk('local')->delete($doc->file_path);
        $doc->delete();
        $this->success('Document deleted.');
    }

    public function reindex(int $id): void
    {
        $doc = NexusDocument::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$doc) return;

        $doc->update(['status' => 'pending']);
        IndexNexusDocument::dispatch($doc->id);
        $this->success('Re-indexing started...');
    }

    public function getDocumentsProperty()
    {
        return NexusDocument::where('user_id', auth()->id())
            ->when($this->workspaceId, fn($q) => $q->where('nexus_workspace_id', $this->workspaceId))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('nexus.livewire.document-manager', [
            'documents' => $this->documents,
        ]);
    }
}
