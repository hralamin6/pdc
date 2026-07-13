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
    public ?string $contextTitle = null;

    public function mount()
    {
        $providers = array_keys(config('ai.providers', []));
        if (!empty($providers)) {
            $this->provider = $providers[0];
            $this->fetchModelsForProvider();
        }
    }

    #[On('ai-generator:open')]
    public function open(string $targetId, string $property, ?string $contextTitle = null, ?string $contextPrompt = null)
    {
        $this->targetId = $targetId;
        $this->targetProperty = $property;
        $this->contextTitle = $contextTitle;
        
        if ($contextPrompt && empty($this->prompt)) {
            $this->prompt = $contextPrompt;
        }
        
        $this->fetchModelsForProvider();
        
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
                $response = \Illuminate\Support\Facades\Http::withToken($config['key'] ?? '')->timeout(60)->get($url);
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
        
        // Always append Custom option
        $models[] = ['id' => 'custom', 'name' => 'Custom Model...'];

        return $models;
    }

    public ?string $generatedImageUrl = null;
    public ?string $generatedImagePath = null;

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
            $pending = Image::of($this->prompt)->size($this->size)->timeout(60);

            $response = $pending->generate($this->provider, $finalModel);
            $image = $response->firstImage();

            $filename = 'ai-tmp/' . Str::random(32) . '.png';
            
            Storage::disk('public')->put($filename, base64_decode($image->image));

            $this->generatedImageUrl = Storage::disk('public')->url($filename);
            $this->generatedImagePath = Storage::disk('public')->path($filename);

            $this->success(__('AI Image generated successfully!'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function useImage()
    {
        if ($this->generatedImagePath) {
            $this->dispatch('ai-image:generated', 
                path: $this->generatedImagePath, 
                property: $this->targetProperty, 
                targetId: $this->targetId
            );
        }

        $this->resetImage();
        $this->show = false;
        $this->prompt = '';
    }

    public function discardImage()
    {
        if ($this->generatedImagePath && file_exists($this->generatedImagePath)) {
            @unlink($this->generatedImagePath);
        }
        $this->resetImage();
    }

    public function resetImage()
    {
        $this->generatedImageUrl = null;
        $this->generatedImagePath = null;
    }

    public function render()
    {
        return view('livewire.global-ai-image-generator');
    }
}
