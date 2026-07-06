<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\CommonMarkConverter;

class NvidiaService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://integrate.api.nvidia.com/v1';
    protected string $defaultModel = 'openai/gpt-oss-120b';

    public function __construct()
    {
        $this->apiKey = config('services.nvidia.api_key');
        $this->baseUrl = config('services.nvidia.base_url', 'https://integrate.api.nvidia.com/v1');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;
        $images = $options['images'] ?? [];

        // Add images to messages if vision model
        if (!empty($images) && $this->isVisionModel($model)) {
            $messages = $this->addImagesToMessages($messages, $images);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(1200)
            ->connectTimeout(300)
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? $response->body();

                Log::error('NVIDIA API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error' => $errorMessage,
                ]);

                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            $markdown = data_get($data, 'choices.0.message.content', '');
            
            if ($markdown === '') {
                throw new \RuntimeException('Empty response from AI');
            }

            $converter = new CommonMarkConverter;
            $html = $converter->convert($markdown);
            $reply = (string) $html;
            
            return [
                'raw' => $markdown,
                'content' => $reply,
                'tokens' => $data['usage']['total_tokens'] ?? 0,
                'model' => $data['model'] ?? $model,
            ];
        } catch (\Exception $e) {
            Log::error('NVIDIA Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function streamChat(array $messages, array $options = []): \Generator
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream' => true,
            ]);

            $stream = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                $buffer .= $chunk;

                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || $line === 'data: [DONE]') {
                        continue;
                    }

                    if (str_starts_with($line, 'data: ')) {
                        $json = substr($line, 6);
                        $data = json_decode($json, true);

                        if (isset($data['choices'][0]['delta']['content'])) {
                            yield $data['choices'][0]['delta']['content'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('NVIDIA Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? 'stabilityai/stable-diffusion-3-medium';
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;
        $steps = $options['steps'] ?? 30;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(120)
            ->post($this->baseUrl . '/images/generations', [
                'model' => $model,
                'prompt' => $prompt,
                'n' => 1,
                'size' => "{$width}x{$height}",
                'response_format' => 'b64_json',
            ]);

            if ($response->failed()) {
                Log::error('NVIDIA Image Generation Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to generate image: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['data'][0]['b64_json'])) {
                throw new \Exception('No image data in response');
            }

            $imageData = base64_decode($data['data'][0]['b64_json']);
            $filename = 'nvidia_' . time() . '_' . uniqid() . '.png';
            $path = 'ai-images/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            return storage_path('app/public/' . $path);

        } catch (\Exception $e) {
            Log::error('NVIDIA Image Generation Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getAvailableModels(): array
    {
        return [
            // Meta Llama Models (High Quality, Free Tier)
            'openai/gpt-oss-120b' => 'openai/gpt-oss-120b',
            'meta/llama-3.3-70b-instruct' => 'Llama 3.3 70B Instruct',
            'meta/llama-3.1-70b-instruct' => 'Llama 3.1 70B Instruct',
            'meta/llama-3.1-8b-instruct' => 'Llama 3.1 8B Instruct',
            'meta/llama-3.2-3b-instruct' => 'Llama 3.2 3B Instruct',
            'meta/llama-3.2-1b-instruct' => 'Llama 3.2 1B Instruct',
            'meta/llama3-70b' => 'Llama3 70B',
            'meta/llama3-8b' => 'Llama3 8B',
            
            // NVIDIA Nemotron Models (Optimized, Free Tier)
            'nvidia/llama-3.3-nemotron-super-49b-v1.5' => 'Llama 3.3 Nemotron Super 49B V1.5',
            'nvidia/llama-3.3-nemotron-super-49b-v1' => 'Llama 3.3 Nemotron Super 49B V1',
            'nvidia/llama-3.1-nemotron-nano-8b-v1' => 'Llama 3.1 Nemotron Nano 8B V1',
            'nvidia/llama-3.1-nemotron-nano-4b-v1.1' => 'Llama 3.1 Nemotron Nano 4B V1.1',
            'nvidia/nemotron-mini-4b-instruct' => 'Nemotron Mini 4B Instruct',
            
            // Mistral Models (High Quality, Free Tier)
            'mistralai/mistral-7b-instruct-v03' => 'Mistral 7B Instruct V0.3',
            'mistralai/mistral-7b-instruct-v2' => 'Mistral 7B Instruct V0.2',
            'mistralai/mixtral-8x7b-instruct' => 'Mixtral 8x7B Instruct',
            'mistralai/mixtral-8x22b-instruct' => 'Mixtral 8x22B Instruct',
            
            // Microsoft Phi Models (Efficient, Free Tier)
            'microsoft/phi-4-mini-instruct' => 'Phi-4 Mini Instruct',
            'microsoft/phi-3.5-mini' => 'Phi-3.5 Mini',
            'microsoft/phi-3-mini' => 'Phi-3 Mini',
            'microsoft/phi-3-small-128k-instruct' => 'Phi-3 Small 128K Instruct',
            'microsoft/phi-3-medium-128k-instruct' => 'Phi-3 Medium 128K Instruct',
            
            // Google Gemma Models (Free Tier)
            'google/gemma-2-27b-it' => 'Gemma 2 27B IT',
            'google/gemma-2-9b-it' => 'Gemma 2 9B IT',
            'google/gemma-2-2b-it' => 'Gemma 2 2B IT',
            'google/gemma-7b' => 'Gemma 7B',
            
            // DeepSeek Models (High Quality, Free Tier)
            'deepseek-ai/deepseek-r1-distill-llama-8b' => 'DeepSeek R1 Distill Llama 8B',
            'deepseek-ai/deepseek-r1-distill-qwen-32b' => 'DeepSeek R1 Distill Qwen 32B',
            'deepseek-ai/deepseek-r1-distill-qwen-14b' => 'DeepSeek R1 Distill Qwen 14B',
            'deepseek-ai/deepseek-r1-distill-qwen-7b' => 'DeepSeek R1 Distill Qwen 7B',
            
            // Qwen Models (High Quality, Free Tier)
            'qwen/qwen2.5-7b-instruct' => 'Qwen2.5 7B Instruct',
            'qwen/qwen2-7b-instruct' => 'Qwen2 7B Instruct',
            'qwen/qwen2.5-coder-7b-instruct' => 'Qwen2.5 Coder 7B Instruct',
            'qwen/qwen2.5-coder-32b-instruct' => 'Qwen2.5 Coder 32B Instruct',
            'qwen/qwq-32b' => 'QwQ 32B',
            
            // IBM Granite Models (Free Tier)
            'ibm/granite-3.3-8b-instruct' => 'Granite 3.3 8B Instruct',
            
            // Other Quality Models (Free Tier)
            'nvidia/chatqa-1-5-8b' => 'ChatQA 1.5 8B',
            'bigcode/starcoder2-7b' => 'StarCoder2 7B',
            'upstage/solar-10.7b-instruct' => 'Solar 10.7B Instruct',
            'tiiuae/falcon3-7b-instruct' => 'Falcon3 7B Instruct',
            'ai21labs/jamba-1.5-mini-instruct' => 'Jamba 1.5 Mini Instruct',
        ];
    }

    public function getImageModels(): array
    {
        return [
            // Stable Diffusion Models
            'stabilityai/stable-diffusion-3.5-large' => 'Stable Diffusion 3.5 Large',
            'stabilityai/stable-diffusion-3-medium' => 'Stable Diffusion 3 Medium',
            
            // FLUX Models (Black Forest Labs)
            'black-forest-labs/flux.1-kontext-dev' => 'FLUX.1 Kontext Dev',
            'black-forest-labs/flux.1-dev' => 'FLUX.1 Dev',
            'black-forest-labs/flux.1-schnell' => 'FLUX.1 Schnell',
            
            // NVIDIA Cosmos Models
            'nvidia/cosmos-transfer1-7b' => 'Cosmos Transfer1 7B',
            'nvidia/cosmos-predict1-5b' => 'Cosmos Predict1 5B',
            
            // Microsoft
            'microsoft/trellis' => 'TRELLIS (Text/Image to 3D)',
            
            // Google
            'google/google-paligemma' => 'PaliGemma',
        ];
    }

    public function countTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    protected function isVisionModel(string $model): bool
    {
        $visionModels = [
            'nvidia/llama-4-maverick-17b-128e-instruct',
            'nvidia/llama-4-scout-17b-16e-instruct',
            'nvidia/vila',
            'microsoft/phi-4-vision-128k-instruct',
            'nvidia/nemotron-nano-12b-v2-vl',
        ];

        return in_array($model, $visionModels);
    }

    protected function addImagesToMessages(array $messages, array $images): array
    {
        // Find last user message
        $lastUserIndex = null;
        foreach ($messages as $index => $message) {
            if ($message['role'] === 'user') {
                $lastUserIndex = $index;
            }
        }

        if ($lastUserIndex === null) {
            return $messages;
        }

        // Convert last user message to multimodal format
        $content = [
            ['type' => 'text', 'text' => $messages[$lastUserIndex]['content']],
        ];

        foreach ($images as $imageInfo) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:' . $imageInfo['mime_type'] . ';base64,' . $imageInfo['data'],
                ],
            ];
        }

        $messages[$lastUserIndex]['content'] = $content;

        return $messages;
    }
}
