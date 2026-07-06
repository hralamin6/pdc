<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;

class IflowService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://apis.iflow.cn/v1';
    protected string $defaultModel = 'iflow-rome-30ba3b';

    public function __construct()
    {
        $this->apiKey = config('services.iflow.api_key');
        $this->baseUrl = config('services.iflow.base_url', 'https://apis.iflow.cn/v1');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

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

                Log::error('iFlow API Error', [
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
            Log::error('iFlow Service Error', ['error' => $e->getMessage()]);
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
            Log::error('iFlow Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('Image generation not supported by iFlow service.');
    }

    public function getAvailableModels(): array
    {
        return [
            // 🔥 Flagship Agentic Models (Most Powerful)
            'iflow-rome-30ba3b' => '🔥 iFlow-ROME 30B MoE (3B Active) - Agentic Expert (Default)',
            'qwen3-coder-plus' => '🔥 Qwen3-Coder-Plus 480B-A35B - Agentic Coding Expert',
            'qwen3-max' => '🔥 Qwen3-Max - State-of-the-art Agent Programming',
            'kimi-k2-0905' => '🔥 Kimi-K2-Instruct-0905 - 1T Params (32B Active)',
            'kimi-k2' => '🔥 Kimi-K2 - 1T Params MoE (32B Active)',
            
            // 🔥 Vision-Language Models
            'qwen3-vl-plus' => '🔥 Qwen3-VL-Plus - Most Powerful Vision-Language',
            
            // 🔥 Reasoning & Thinking Models
            'deepseek-r1' => '🔥 DeepSeek-R1 - Advanced Reasoning (Comparable to o1)',
            'qwen3-235b-a22b-thinking-2507' => 'Qwen3-235B-A22B Thinking - State-of-the-art Reasoning',
            'deepseek-v3.2' => '🔥 DeepSeek-V3.2-Exp - Sparse Attention (128K Context)',
            
            // 🔥 Ultra-Large Models
            'deepseek-v3' => '🔥 DeepSeek-V3 671B MoE (37B Active)',
            'qwen3-235b-a22b-instruct' => 'Qwen3-235B-A22B Instruct - Enhanced Alignment',
            'qwen3-235b' => 'Qwen3-235B - Ultra-Large Foundation',
            
            // Advanced Agent Models
            'qwen3-max-preview' => '🔥 Qwen3-Max-Preview - Enhanced Tool Use',
            'glm-4.6' => '🔥 GLM-4.6 355B (32B Active) - Intelligent Agent',
            
            // General Purpose Models
            'qwen3-32b' => 'Qwen3-32B - Dense Model (128K Context)',
        ];
    }

    public function countTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($text) / 4);
    }
}
