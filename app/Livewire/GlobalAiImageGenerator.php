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
    public string $provider = '';
    public string $model = 'dall-e-3';
    public string $customModel = '';

    public bool $isGenerating = false;

    public array $fetchedModels = [];

    public function mount()
    {
        $providers = array_keys(config('ai.providers', []));
        if (!empty($providers)) {
            $this->provider = $providers[0];
            $this->fetchModelsForProvider();
        }
    }

    #[On('ai-generator:open')]
    public function open(string $targetId, string $property)
    {
        $this->targetId = $targetId;
        $this->targetProperty = $property;
        $this->show = true;
    }

    public function getProvidersProperty()
    {
        $providers = collect(array_keys(config('ai.providers', [])))->map(function ($key) {
            return ['id' => $key, 'name' => Str::title($key)];
        })->toArray();

        if (empty($providers)) {
            return [['id' => 'openai', 'name' => 'OpenAI']];
        }

        return $providers;
    }

    public function fetchModelsForProvider()
    {
        $this->fetchedModels = [];
        
        $config = config('ai.providers.' . $this->provider);
        if ($config && isset($config['url'])) {
            try {
                $url = rtrim($config['url'], '/') . '/models';
                $response = \Illuminate\Support\Facades\Http::withToken($config['key'] ?? '')->timeout(3)->get($url);
                if ($response->successful() && isset($response['data'])) {
                    foreach ($response['data'] as $model) {
                        if (isset($model['id'])) {
                            $this->fetchedModels[] = [
                                'id' => $model['id'],
                                'name' => $model['name'] ?? $model['id']
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore API errors, fallback to manual list
            }
        }
    }

    public function updatedProvider()
    {
        $this->fetchModelsForProvider();
        
        $available = $this->availableModels;
        $this->model = $available[0]['id'] ?? 'custom';
    }

    public function getAvailableModelsProperty()
    {
        $models = $this->fetchedModels;
        
        // If API didn't return models, fallback to hardcoded lists
        if (empty($models)) {
            switch ($this->provider) {
                case 'openai':
                    $models = [
                        ['id' => 'dall-e-3', 'name' => 'DALL-E 3'],
                        ['id' => 'dall-e-2', 'name' => 'DALL-E 2']
                    ];
                    break;
                case 'replicate':
                    $models = [
                        ['id' => 'flux-pro', 'name' => 'Flux Pro'],
                        ['id' => 'flux-schnell', 'name' => 'Flux Schnell'],
                        ['id' => 'stable-diffusion-xl', 'name' => 'SDXL']
                    ];
                    break;
                case 'pollinations':
                    $models = [
                        ['id' => 'flux', 'name' => 'Flux'],
                        ['id' => 'turbo', 'name' => 'SD Turbo']
                    ];
                    break;
                case 'midjourney':
                    $models = [
                        ['id' => 'v6', 'name' => 'Midjourney V6'],
                        ['id' => 'v5.2', 'name' => 'Midjourney V5.2']
                    ];
                    break;
                default:
                    $models = [
                        ['id' => 'default', 'name' => 'Default Model']
                    ];
                    break;
            }
        }

        // Always append Custom option
        $models[] = ['id' => 'custom', 'name' => 'Custom Model...'];

        return $models;
    }

    public function generate()
    {
        $this->validate([
            'prompt' => 'required|string|min:3',
            'size' => 'required|string',
            'provider' => 'required|string',
        ]);

        $this->isGenerating = true;

        $finalModel = $this->model === 'custom' ? $this->customModel : $this->model;

        try {
            $pending = Image::of($this->prompt)->size($this->size);

            $response = $pending->generate($this->provider, $finalModel);
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
