<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Mary\Traits\Toast;

class GlobalAiImageGenerator extends Component
{
    use Toast;

    public bool $show = false;
    public string $targetId = '';
    public string $targetProperty = '';

    public string $prompt = '';
    public string $size = '1:1';
    public string $provider = 'openai';
    public string $model = 'dall-e-3';

    public bool $isGenerating = false;

    #[On('ai-generator:open')]
    public function open(string $targetId, string $property)
    {
        $this->targetId = $targetId;
        $this->targetProperty = $property;
        $this->show = true;
    }

    public function generate()
    {
        $this->validate([
            'prompt' => 'required|string|min:3',
            'size' => 'required|string',
            'provider' => 'required|string',
            'model' => 'required|string',
        ]);

        $this->isGenerating = true;

        try {
            $pending = Image::of($this->prompt)->size($this->size);

            $response = $pending->generate($this->provider, $this->model);
            $image = $response->firstImage();

            $disk = config('livewire.temporary_file_upload.disk', 'local');
            $filename = 'livewire-tmp/' . Str::random(32) . '.png';
            
            // The image property on GeneratedImage is a base64 encoded string
            Storage::disk($disk)->put($filename, base64_decode($image->image));

            $this->dispatch('ai-image:generated', tmpPath: $filename, property: $this->targetProperty, targetId: $this->targetId);

            $this->show = false;
            $this->prompt = '';
            
            $this->success(__('AI Image generated and added successfully!'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function render()
    {
        return view('livewire.global-ai-image-generator');
    }
}
