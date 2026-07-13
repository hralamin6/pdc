<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

trait InteractsWithAiImages
{
    #[On('ai-image:generated')]
    public function handleAiImageGenerated(string $tmpPath, string $property, string $targetId)
    {
        // Only process if this specific component instance requested the image
        if ($this->getId() !== $targetId) {
            return;
        }

        // Verify the file exists in the temporary disk
        $disk = config('livewire.temporary_file_upload.disk', 'local');
        if (!Storage::disk($disk)->exists($tmpPath)) {
            return;
        }

        // Create the Livewire TemporaryUploadedFile instance
        $file = TemporaryUploadedFile::createFromLivewire($tmpPath);

        // Assign to the property (append if array, replace if single)
        if (is_array($this->$property)) {
            $photos = $this->$property;
            $photos[] = $file;
            $this->$property = $photos;
        } else {
            $this->$property = $file;
        }
    }
}
