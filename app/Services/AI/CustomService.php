<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;

class CustomService implements AiServiceInterface
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultModel = 'if/glm-5';

    public function __construct()
    {
        $this->apiKey = config('services.custom.api_key');
        $this->baseUrl = config('services.custom.base_url', 'http://139.59.25.41:20128/v1');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(300)
                ->connectTimeout(300)
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'stream' => false,
                ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? $response->body();

                Log::error('Custom AI API Error', [
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
            Log::error('Custom AI Service Error', ['error' => $e->getMessage()]);
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
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream' => true,
            ]);

            $stream = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (! $stream->eof()) {
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
            Log::error('Custom AI Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('Image generation not supported by Custom AI service.');
    }

    public function getAvailableModels(): array
    {
        return [
            // IF Models (Inference Factory)
            'if/glm-5' => 'if/glm-5 (Default)',
            'if/glm-4.7' => 'if/glm-4.7',
            'if/glm-4.6' => 'if/glm-4.6',
            'if/deepseek-v3.2-chat' => 'if/deepseek-v3.2-chat',
            'if/deepseek-r1' => 'if/deepseek-r1',
            'if/kimi-k2.5' => 'if/kimi-k2.5',
            'if/kimi-k2-thinking' => 'if/kimi-k2-thinking',
            'if/kimi-k2' => 'if/kimi-k2',
            'if/minimax-m2.5' => 'if/minimax-m2.5',
            'if/minimax-m2.1' => 'if/minimax-m2.1',
            'if/qwen3-coder-plus' => 'if/qwen3-coder-plus',

            // Qwen Models
            'qw/qwen3-coder-plus' => 'qw/qwen3-coder-plus',
            'qw/qwen3-coder-flash' => 'qw/qwen3-coder-flash',
            'qw/vision-model' => 'qw/vision-model',

            // Krea/Kroma Claude Models
            'kr/claude-sonnet-4.5' => 'kr/claude-sonnet-4.5',
            'kr/claude-haiku-4.5' => 'kr/claude-haiku-4.5',

            // GitHub Models
            // GPT Models
            'gh/gpt-5.2-codex' => 'gh/gpt-5.2-codex',
            'gh/gpt-5.2' => 'gh/gpt-5.2',
            'gh/gpt-5.1-codex-max' => 'gh/gpt-5.1-codex-max',
            'gh/gpt-5.1-codex' => 'gh/gpt-5.1-codex',
            'gh/gpt-5.1-codex-mini' => 'gh/gpt-5.1-codex-mini',
            'gh/gpt-5.1' => 'gh/gpt-5.1',
            'gh/gpt-5-codex' => 'gh/gpt-5-codex',
            'gh/gpt-5' => 'gh/gpt-5',
            'gh/gpt-5-mini' => 'gh/gpt-5-mini',
            'gh/gpt-4.1' => 'gh/gpt-4.1',
            'gh/gpt-4o' => 'gh/gpt-4o',
            'gh/gpt-4o-mini' => 'gh/gpt-4o-mini',
            'gh/gpt-4' => 'gh/gpt-4',
            'gh/gpt-3.5-turbo' => 'gh/gpt-3.5-turbo',

            // Claude Models
            'gh/claude-opus-4.6' => 'gh/claude-opus-4.6',
            'gh/claude-opus-4.5' => 'gh/claude-opus-4.5',
            'gh/claude-opus-4.1' => 'gh/claude-opus-4.1',
            'gh/claude-sonnet-4.5' => 'gh/claude-sonnet-4.5',
            'gh/claude-sonnet-4' => 'gh/claude-sonnet-4',
            'gh/claude-haiku-4.5' => 'gh/claude-haiku-4.5',

            // Gemini Models
            'gh/gemini-3-pro-preview' => 'gh/gemini-3-pro-preview',
            'gh/gemini-3-flash-preview' => 'gh/gemini-3-flash-preview',
            'gh/gemini-2.5-pro' => 'gh/gemini-2.5-pro',

            // Specialized Models
            'gh/grok-code-fast-1' => 'gh/grok-code-fast-1',
            'gh/oswe-vscode-prime' => 'gh/oswe-vscode-prime',

            // Google Cloud Gemini Models
            'gc/gemini-3-pro-preview' => 'gc/gemini-3-pro-preview',
            'gc/gemini-3-flash-preview' => 'gc/gemini-3-flash-preview',
            'gc/gemini-2.5-pro' => 'gc/gemini-2.5-pro',
            'gc/gemini-2.5-flash' => 'gc/gemini-2.5-flash',
            'gc/gemini-2.5-flash-lite' => 'gc/gemini-2.5-flash-lite',

            // Codex Models (Code Specialized)
            'cx/gpt-5.3-codex-xhigh' => 'cx/gpt-5.3-codex-xhigh',
            'cx/gpt-5.3-codex-high' => 'cx/gpt-5.3-codex-high',
            'cx/gpt-5.3-codex' => 'cx/gpt-5.3-codex',
            'cx/gpt-5.3-codex-low' => 'cx/gpt-5.3-codex-low',
            'cx/gpt-5.3-codex-none' => 'cx/gpt-5.3-codex-none',
            'cx/gpt-5.2-codex' => 'cx/gpt-5.2-codex',
            'cx/gpt-5.2' => 'cx/gpt-5.2',
            'cx/gpt-5.1-codex-max' => 'cx/gpt-5.1-codex-max',
            'cx/gpt-5.1-codex' => 'cx/gpt-5.1-codex',
            'cx/gpt-5.1-codex-mini-high' => 'cx/gpt-5.1-codex-mini-high',
            'cx/gpt-5.1-codex-mini' => 'cx/gpt-5.1-codex-mini',
            'cx/gpt-5.1' => 'cx/gpt-5.1',
            'cx/gpt-5-codex' => 'cx/gpt-5-codex',
            'cx/gpt-5-codex-mini' => 'cx/gpt-5-codex-mini',

            // OpenRouter Models
            'openrouter/openai/gpt-oss-120b:free' => 'openrouter/openai/gpt-oss-120b:free',

            // Anthropic/Google Models
            'ag/claude-opus-4-6-thinking' => 'ag/claude-opus-4-6-thinking',
            'ag/claude-opus-4-5-thinking' => 'ag/claude-opus-4-5-thinking',
            'ag/claude-sonnet-4-5-thinking' => 'ag/claude-sonnet-4-5-thinking',
            'ag/claude-sonnet-4-5' => 'ag/claude-sonnet-4-5',
            'ag/gemini-3-pro-high' => 'ag/gemini-3-pro-high',
            'ag/gemini-3-pro-low' => 'ag/gemini-3-pro-low',
            'ag/gemini-3-flash' => 'ag/gemini-3-flash',
            'ag/gemini-2.5-flash' => 'ag/gemini-2.5-flash',

            // Google Gemini Models
            'gemini/gemini-3-pro-preview' => 'gemini/gemini-3-pro-preview',
            'gemini/gemini-2.5-pro' => 'gemini/gemini-2.5-pro',
            'gemini/gemini-2.5-flash' => 'gemini/gemini-2.5-flash',
            'gemini/gemini-2.5-flash-lite' => 'gemini/gemini-2.5-flash-lite',

            // Mistral Large Models (Most Powerful)
            'ms/mistral-large-2512' => 'ms/mistral-large-2512',
            'ms/mistral-large-latest' => 'ms/mistral-large-latest',
            'ms/mistral-large-2411' => 'ms/mistral-large-2411',
            'ms/mistral-large-pixtral-2411' => 'ms/mistral-large-pixtral-2411',
            'ms/pixtral-large-2411' => 'ms/pixtral-large-2411',
            'ms/pixtral-large-latest' => 'ms/pixtral-large-latest',

            // Mistral Medium Models
            'ms/mistral-medium-2505' => 'ms/mistral-medium-2505',
            'ms/mistral-medium-2508' => 'ms/mistral-medium-2508',
            'ms/mistral-medium-latest' => 'ms/mistral-medium-latest',
            'ms/mistral-medium' => 'ms/mistral-medium',
            'ms/magistral-medium-2509' => 'ms/magistral-medium-2509',
            'ms/magistral-medium-latest' => 'ms/magistral-medium-latest',

            // Mistral Small Models
            'ms/mistral-small-2506' => 'ms/mistral-small-2506',
            'ms/mistral-small-2501' => 'ms/mistral-small-2501',
            'ms/mistral-small-latest' => 'ms/mistral-small-latest',
            'ms/labs-mistral-small-creative' => 'ms/labs-mistral-small-creative',
            'ms/magistral-small-2509' => 'ms/magistral-small-2509',
            'ms/magistral-small-latest' => 'ms/magistral-small-latest',

            // Codestral (Code Specialized)
            'ms/codestral-2508' => 'ms/codestral-2508',
            'ms/codestral-latest' => 'ms/codestral-latest',
            'ms/devstral-medium-2507' => 'ms/devstral-medium-2507',
            'ms/devstral-medium-latest' => 'ms/devstral-medium-latest',
            'ms/devstral-2512' => 'ms/devstral-2512',
            'ms/devstral-latest' => 'ms/devstral-latest',
            'ms/devstral-small-2507' => 'ms/devstral-small-2507',
            'ms/devstral-small-latest' => 'ms/devstral-small-latest',
            'ms/labs-devstral-small-2512' => 'ms/labs-devstral-small-2512',

            // Ministral (Lightweight)
            'ms/ministral-14b-2512' => 'ms/ministral-14b-2512',
            'ms/ministral-14b-latest' => 'ms/ministral-14b-latest',
            'ms/ministral-8b-2512' => 'ms/ministral-8b-2512',
            'ms/ministral-8b-latest' => 'ms/ministral-8b-latest',
            'ms/ministral-3b-2512' => 'ms/ministral-3b-2512',
            'ms/ministral-3b-latest' => 'ms/ministral-3b-latest',

            // Mistral Nemo & Tiny
            'ms/open-mistral-nemo' => 'ms/open-mistral-nemo',
            'ms/open-mistral-nemo-2407' => 'ms/open-mistral-nemo-2407',
            'ms/mistral-tiny-2407' => 'ms/mistral-tiny-2407',
            'ms/mistral-tiny-latest' => 'ms/mistral-tiny-latest',

            // Voxtral (Voice/Speech)
            'ms/voxtral-small-2507' => 'ms/voxtral-small-2507',
            'ms/voxtral-small-latest' => 'ms/voxtral-small-latest',
            'ms/voxtral-mini-2602' => 'ms/voxtral-mini-2602',
            'ms/voxtral-mini-2507' => 'ms/voxtral-mini-2507',
            'ms/voxtral-mini-latest' => 'ms/voxtral-mini-latest',
            'ms/voxtral-mini-transcribe-2507' => 'ms/voxtral-mini-transcribe-2507',

            // Mistral Vibe CLI
            'ms/mistral-vibe-cli-with-tools' => 'ms/mistral-vibe-cli-with-tools',
            'ms/mistral-vibe-cli-latest' => 'ms/mistral-vibe-cli-latest',

            // Specialized Models (Embeddings, Moderation, OCR)
            'ms/mistral-embed-2312' => 'ms/mistral-embed-2312',
            'ms/mistral-embed' => 'ms/mistral-embed',
            'ms/codestral-embed-2505' => 'ms/codestral-embed-2505',
            'ms/codestral-embed' => 'ms/codestral-embed',
            'ms/mistral-moderation-2411' => 'ms/mistral-moderation-2411',
            'ms/mistral-moderation-latest' => 'ms/mistral-moderation-latest',
            'ms/mistral-ocr-2512' => 'ms/mistral-ocr-2512',
            'ms/mistral-ocr-latest' => 'ms/mistral-ocr-latest',
            'ms/mistral-ocr-2505' => 'ms/mistral-ocr-2505',
            'ms/mistral-ocr-2503' => 'ms/mistral-ocr-2503',

            // Cerebras Models (Fast Inference)
            'cb/qwen-3-235b-a22b-instruct-2507' => 'cb/qwen-3-235b-a22b-instruct-2507',
            'cb/gpt-oss-120b' => 'cb/gpt-oss-120b',
            'cb/llama-3.3-70b' => 'cb/llama-3.3-70b',
            'cb/qwen-3-32b' => 'cb/qwen-3-32b',
            'cb/llama3.1-8b' => 'cb/llama3.1-8b',
            'cb/zai-glm-4.7' => 'cb/zai-glm-4.7',

            // Pollinations Models
            // OpenAI
            'pn/openai-large' => 'pn/openai-large',
            'pn/openai' => 'pn/openai',
            'pn/openai-fast' => 'pn/openai-fast',
            'pn/openai-audio' => 'pn/openai-audio',

            // Claude
            'pn/claude-large' => 'pn/claude-large',
            'pn/claude' => 'pn/claude',
            'pn/claude-fast' => 'pn/claude-fast',

            // Gemini
            'pn/gemini-large' => 'pn/gemini-large',
            'pn/gemini' => 'pn/gemini',
            'pn/gemini-fast' => 'pn/gemini-fast',
            'pn/gemini-search' => 'pn/gemini-search',

            // Perplexity
            'pn/perplexity-reasoning' => 'pn/perplexity-reasoning',
            'pn/perplexity-fast' => 'pn/perplexity-fast',

            // Other Major Models
            'pn/grok' => 'pn/grok',
            'pn/deepseek' => 'pn/deepseek',
            'pn/kimi' => 'pn/kimi',
            'pn/glm' => 'pn/glm',
            'pn/minimax' => 'pn/minimax',
            'pn/mistral' => 'pn/mistral',
            'pn/nova-fast' => 'pn/nova-fast',

            // Specialized Models
            'pn/qwen-coder' => 'pn/qwen-coder',
            'pn/qwen-safety' => 'pn/qwen-safety',
            'pn/qwen-character' => 'pn/qwen-character',
            'pn/chickytutor' => 'pn/chickytutor',
            'pn/midijourney' => 'pn/midijourney',

            // Groq Models (Fast Inference)
            'gq/openai/gpt-oss-120b' => 'gq/openai/gpt-oss-120b',
            'gq/meta-llama/llama-4-maverick-17b-128e-instruct' => 'gq/meta-llama/llama-4-maverick-17b-128e-instruct',
            'gq/llama-3.3-70b-versatile' => 'gq/llama-3.3-70b-versatile',
            'gq/qwen/qwen3-32b' => 'gq/qwen/qwen3-32b',
            'gq/moonshotai/kimi-k2-instruct-0905' => 'gq/moonshotai/kimi-k2-instruct-0905',
            'gq/moonshotai/kimi-k2-instruct' => 'gq/moonshotai/kimi-k2-instruct',
            'gq/openai/gpt-oss-20b' => 'gq/openai/gpt-oss-20b',
            'gq/meta-llama/llama-4-scout-17b-16e-instruct' => 'gq/meta-llama/llama-4-scout-17b-16e-instruct',
            'gq/groq/compound' => 'gq/groq/compound',
            'gq/groq/compound-mini' => 'gq/groq/compound-mini',
            'gq/llama-3.1-8b-instant' => 'gq/llama-3.1-8b-instant',
            'gq/allam-2-7b' => 'gq/allam-2-7b',
            // Safety / guard models (specialized, not general reasoning)
            'gq/meta-llama/llama-guard-4-12b' => 'gq/meta-llama/llama-guard-4-12b',
            'gq/openai/gpt-oss-safeguard-20b' => 'gq/openai/gpt-oss-safeguard-20b',
            'gq/meta-llama/llama-prompt-guard-2-86m' => 'gq/meta-llama/llama-prompt-guard-2-86m',
            'gq/meta-llama/llama-prompt-guard-2-22m' => 'gq/meta-llama/llama-prompt-guard-2-22m',
            // Speech / audio models
            'gq/whisper-large-v3' => 'gq/whisper-large-v3',
            'gq/whisper-large-v3-turbo' => 'gq/whisper-large-v3-turbo',
            // TTS / voice specialists
            'gq/canopylabs/orpheus-v1-english' => 'gq/canopylabs/orpheus-v1-english',
            'gq/canopylabs/orpheus-arabic-saudi' => 'gq/canopylabs/orpheus-arabic-saudi',

            // NVIDIA NIM Models (Most Powerful First)
            // Ultra Large Models (250B+)
            'nd/mistralai/mistral-large-3-675b-instruct-2512' => 'nd/mistralai/mistral-large-3-675b-instruct-2512',
            'nd/qwen/qwen3-coder-480b-a35b-instruct' => 'nd/qwen/qwen3-coder-480b-a35b-instruct',
            'nd/igenius/colosseum_355b_instruct_16k' => 'nd/igenius/colosseum_355b_instruct_16k',
            'nd/nvidia/nemotron-4-340b-instruct' => 'nd/nvidia/nemotron-4-340b-instruct',
            'nd/nvidia/nemotron-4-340b-reward' => 'nd/nvidia/nemotron-4-340b-reward',
            'nd/nvidia/llama-3.1-nemotron-ultra-253b-v1' => 'nd/nvidia/llama-3.1-nemotron-ultra-253b-v1',
            'nd/qwen/qwen3-235b-a22b' => 'nd/qwen/qwen3-235b-a22b',

            // Large Models (100-250B)
            'nd/mistralai/devstral-2-123b-instruct-2512' => 'nd/mistralai/devstral-2-123b-instruct-2512',
            'nd/openai/gpt-oss-120b' => 'nd/openai/gpt-oss-120b',
            'nd/writer/palmyra-creative-122b' => 'nd/writer/palmyra-creative-122b',
            'nd/stockmark/stockmark-2-100b-instruct' => 'nd/stockmark/stockmark-2-100b-instruct',

            // Medium-Large Models (70-100B)
            'nd/qwen/qwen3-next-80b-a3b-instruct' => 'nd/qwen/qwen3-next-80b-a3b-instruct',
            'nd/qwen/qwen3-next-80b-a3b-thinking' => 'nd/qwen/qwen3-next-80b-a3b-thinking',
            'nd/meta/llama-3.1-405b-instruct' => 'nd/meta/llama-3.1-405b-instruct',
            'nd/meta/llama-3.1-70b-instruct' => 'nd/meta/llama-3.1-70b-instruct',
            'nd/meta/llama-3.3-70b-instruct' => 'nd/meta/llama-3.3-70b-instruct',
            'nd/meta/llama3-70b-instruct' => 'nd/meta/llama3-70b-instruct',
            'nd/meta/codellama-70b' => 'nd/meta/codellama-70b',
            'nd/meta/llama2-70b' => 'nd/meta/llama2-70b',
            'nd/institute-of-science-tokyo/llama-3.1-swallow-70b-instruct-v0.1' => 'nd/institute-of-science-tokyo/llama-3.1-swallow-70b-instruct-v0.1',
            'nd/tokyotech-llm/llama-3-swallow-70b-instruct-v0.1' => 'nd/tokyotech-llm/llama-3-swallow-70b-instruct-v0.1',
            'nd/yentinglin/llama-3-taiwan-70b-instruct' => 'nd/yentinglin/llama-3-taiwan-70b-instruct',
            'nd/abacusai/dracarys-llama-3.1-70b-instruct' => 'nd/abacusai/dracarys-llama-3.1-70b-instruct',
            'nd/nvidia/llama3-chatqa-1.5-70b' => 'nd/nvidia/llama3-chatqa-1.5-70b',
            'nd/nvidia/llama-3.1-nemotron-70b-instruct' => 'nd/nvidia/llama-3.1-nemotron-70b-instruct',
            'nd/nvidia/llama-3.1-nemotron-70b-reward' => 'nd/nvidia/llama-3.1-nemotron-70b-reward',
            'nd/nvidia/usdcode-llama-3.1-70b-instruct' => 'nd/nvidia/usdcode-llama-3.1-70b-instruct',
            'nd/writer/palmyra-med-70b' => 'nd/writer/palmyra-med-70b',
            'nd/writer/palmyra-med-70b-32k' => 'nd/writer/palmyra-med-70b-32k',
            'nd/writer/palmyra-fin-70b-32k' => 'nd/writer/palmyra-fin-70b-32k',
            'nd/01-ai/yi-large' => 'nd/01-ai/yi-large',

            // Medium Models (30-70B)
            'nd/nvidia/llama-3.3-nemotron-super-49b-v1.5' => 'nd/nvidia/llama-3.3-nemotron-super-49b-v1.5',
            'nd/nvidia/llama-3.3-nemotron-super-49b-v1' => 'nd/nvidia/llama-3.3-nemotron-super-49b-v1',
            'nd/nvidia/llama-3.1-nemotron-51b-instruct' => 'nd/nvidia/llama-3.1-nemotron-51b-instruct',
            'nd/bytedance/seed-oss-36b-instruct' => 'nd/bytedance/seed-oss-36b-instruct',
            'nd/ibm/granite-34b-code-instruct' => 'nd/ibm/granite-34b-code-instruct',
            'nd/qwen/qwen2.5-coder-32b-instruct' => 'nd/qwen/qwen2.5-coder-32b-instruct',
            'nd/qwen/qwq-32b' => 'nd/qwen/qwq-32b',
            'nd/nvidia/nemotron-3-nano-30b-a3b' => 'nd/nvidia/nemotron-3-nano-30b-a3b',
            'nd/nvidia/nemotron-nano-3-30b-a3b' => 'nd/nvidia/nemotron-nano-3-30b-a3b',

            // Small-Medium Models (20-30B)
            'nd/google/gemma-2-27b-it' => 'nd/google/gemma-2-27b-it',
            'nd/google/gemma-3-27b-it' => 'nd/google/gemma-3-27b-it',
            'nd/mistralai/mistral-small-24b-instruct' => 'nd/mistralai/mistral-small-24b-instruct',
            'nd/mistralai/mistral-small-3.1-24b-instruct-2503' => 'nd/mistralai/mistral-small-3.1-24b-instruct-2503',
            'nd/mistralai/mixtral-8x22b-instruct-v0.1' => 'nd/mistralai/mixtral-8x22b-instruct-v0.1',
            'nd/mistralai/mixtral-8x22b-v0.1' => 'nd/mistralai/mixtral-8x22b-v0.1',
            'nd/mistralai/codestral-22b-instruct-v0.1' => 'nd/mistralai/codestral-22b-instruct-v0.1',
            'nd/nvidia/neva-22b' => 'nd/nvidia/neva-22b',
            'nd/openai/gpt-oss-20b' => 'nd/openai/gpt-oss-20b',

            // Small Models (10-20B)
            'nd/meta/llama-4-maverick-17b-128e-instruct' => 'nd/meta/llama-4-maverick-17b-128e-instruct',
            'nd/meta/llama-4-scout-17b-16e-instruct' => 'nd/meta/llama-4-scout-17b-16e-instruct',
            'nd/bigcode/starcoder2-15b' => 'nd/bigcode/starcoder2-15b',
            'nd/mistralai/ministral-14b-instruct-2512' => 'nd/mistralai/ministral-14b-instruct-2512',
            'nd/baichuan-inc/baichuan2-13b-chat' => 'nd/baichuan-inc/baichuan2-13b-chat',
            'nd/google/gemma-3-12b-it' => 'nd/google/gemma-3-12b-it',
            'nd/meta/llama-guard-4-12b' => 'nd/meta/llama-guard-4-12b',
            'nd/nvidia/nemotron-nano-12b-v2-vl' => 'nd/nvidia/nemotron-nano-12b-v2-vl',
            'nd/nv-mistralai/mistral-nemo-12b-instruct' => 'nd/nv-mistralai/mistral-nemo-12b-instruct',
            'nd/speakleash/bielik-11b-v2.6-instruct' => 'nd/speakleash/bielik-11b-v2.6-instruct',
            'nd/speakleash/bielik-11b-v2.3-instruct' => 'nd/speakleash/bielik-11b-v2.3-instruct',
            'nd/meta/llama-3.2-11b-vision-instruct' => 'nd/meta/llama-3.2-11b-vision-instruct',
            'nd/upstage/solar-10.7b-instruct' => 'nd/upstage/solar-10.7b-instruct',
            'nd/igenius/italia_10b_instruct_16k' => 'nd/igenius/italia_10b_instruct_16k',

            // Compact Models (7-10B)
            'nd/google/gemma-2-9b-it' => 'nd/google/gemma-2-9b-it',
            'nd/gotocompany/gemma-2-9b-cpt-sahabatai-instruct' => 'nd/gotocompany/gemma-2-9b-cpt-sahabatai-instruct',
            'nd/google/shieldgemma-9b' => 'nd/google/shieldgemma-9b',
            'nd/utter-project/eurollm-9b-instruct' => 'nd/utter-project/eurollm-9b-instruct',
            'nd/nvidia/nvidia-nemotron-nano-9b-v2' => 'nd/nvidia/nvidia-nemotron-nano-9b-v2',
            'nd/meta/llama-3.1-8b-instruct' => 'nd/meta/llama-3.1-8b-instruct',
            'nd/meta/llama3-8b-instruct' => 'nd/meta/llama3-8b-instruct',
            'nd/institute-of-science-tokyo/llama-3.1-swallow-8b-instruct-v0.1' => 'nd/institute-of-science-tokyo/llama-3.1-swallow-8b-instruct-v0.1',
            'nd/deepseek-ai/deepseek-r1-distill-llama-8b' => 'nd/deepseek-ai/deepseek-r1-distill-llama-8b',
            'nd/nvidia/cosmos-reason2-8b' => 'nd/nvidia/cosmos-reason2-8b',
            'nd/nvidia/llama-3.1-nemoguard-8b-content-safety' => 'nd/nvidia/llama-3.1-nemoguard-8b-content-safety',
            'nd/nvidia/llama-3.1-nemoguard-8b-topic-control' => 'nd/nvidia/llama-3.1-nemoguard-8b-topic-control',
            'nd/nvidia/llama-3.1-nemotron-nano-8b-v1' => 'nd/nvidia/llama-3.1-nemotron-nano-8b-v1',
            'nd/nvidia/llama-3.1-nemotron-nano-vl-8b-v1' => 'nd/nvidia/llama-3.1-nemotron-nano-vl-8b-v1',
            'nd/nvidia/llama-3.1-nemotron-safety-guard-8b-v3' => 'nd/nvidia/llama-3.1-nemotron-safety-guard-8b-v3',
            'nd/nvidia/llama3-chatqa-1.5-8b' => 'nd/nvidia/llama3-chatqa-1.5-8b',
            'nd/nvidia/mistral-nemo-minitron-8b-8k-instruct' => 'nd/nvidia/mistral-nemo-minitron-8b-8k-instruct',
            'nd/nvidia/mistral-nemo-minitron-8b-base' => 'nd/nvidia/mistral-nemo-minitron-8b-base',
            'nd/adept/fuyu-8b' => 'nd/adept/fuyu-8b',
            'nd/ibm/granite-3.0-8b-instruct' => 'nd/ibm/granite-3.0-8b-instruct',
            'nd/ibm/granite-3.3-8b-instruct' => 'nd/ibm/granite-3.3-8b-instruct',
            'nd/ibm/granite-8b-code-instruct' => 'nd/ibm/granite-8b-code-instruct',
            'nd/marin/marin-8b-instruct' => 'nd/marin/marin-8b-instruct',
            'nd/mistralai/mixtral-8x7b-instruct-v0.1' => 'nd/mistralai/mixtral-8x7b-instruct-v0.1',
            'nd/aisingapore/sea-lion-7b-instruct' => 'nd/aisingapore/sea-lion-7b-instruct',
            'nd/bigcode/starcoder2-7b' => 'nd/bigcode/starcoder2-7b',
            'nd/google/codegemma-1.1-7b' => 'nd/google/codegemma-1.1-7b',
            'nd/google/codegemma-7b' => 'nd/google/codegemma-7b',
            'nd/google/gemma-7b' => 'nd/google/gemma-7b',
            'nd/mediatek/breeze-7b-instruct' => 'nd/mediatek/breeze-7b-instruct',
            'nd/mistralai/mistral-7b-instruct-v0.2' => 'nd/mistralai/mistral-7b-instruct-v0.2',
            'nd/mistralai/mistral-7b-instruct-v0.3' => 'nd/mistralai/mistral-7b-instruct-v0.3',
            'nd/mistralai/mamba-codestral-7b-v0.1' => 'nd/mistralai/mamba-codestral-7b-v0.1',
            'nd/mistralai/mathstral-7b-v0.1' => 'nd/mistralai/mathstral-7b-v0.1',
            'nd/opengpt-x/teuken-7b-instruct-commercial-v0.4' => 'nd/opengpt-x/teuken-7b-instruct-commercial-v0.4',
            'nd/qwen/qwen2-7b-instruct' => 'nd/qwen/qwen2-7b-instruct',
            'nd/qwen/qwen2.5-7b-instruct' => 'nd/qwen/qwen2.5-7b-instruct',
            'nd/qwen/qwen2.5-coder-7b-instruct' => 'nd/qwen/qwen2.5-coder-7b-instruct',
            'nd/rakuten/rakutenai-7b-chat' => 'nd/rakuten/rakutenai-7b-chat',
            'nd/rakuten/rakutenai-7b-instruct' => 'nd/rakuten/rakutenai-7b-instruct',
            'nd/tiiuae/falcon3-7b-instruct' => 'nd/tiiuae/falcon3-7b-instruct',
            'nd/zyphra/zamba2-7b-instruct' => 'nd/zyphra/zamba2-7b-instruct',
            'nd/deepseek-ai/deepseek-coder-6.7b-instruct' => 'nd/deepseek-ai/deepseek-coder-6.7b-instruct',
            'nd/thudm/chatglm3-6b' => 'nd/thudm/chatglm3-6b',

            // Mini Models (3-5B)
            'nd/z-ai/glm5' => 'nd/z-ai/glm5',
            'nd/z-ai/glm4.7' => 'nd/z-ai/glm4.7',
            'nd/google/gemma-3-4b-it' => 'nd/google/gemma-3-4b-it',
            'nd/nvidia/llama-3.1-nemotron-nano-4b-v1.1' => 'nd/nvidia/llama-3.1-nemotron-nano-4b-v1.1',
            'nd/nvidia/nemotron-content-safety-reasoning-4b' => 'nd/nvidia/nemotron-content-safety-reasoning-4b',
            'nd/nvidia/nemotron-4-mini-hindi-4b-instruct' => 'nd/nvidia/nemotron-4-mini-hindi-4b-instruct',
            'nd/nvidia/nemotron-mini-4b-instruct' => 'nd/nvidia/nemotron-mini-4b-instruct',
            'nd/nvidia/riva-translate-4b-instruct' => 'nd/nvidia/riva-translate-4b-instruct',
            'nd/nvidia/riva-translate-4b-instruct-v1.1' => 'nd/nvidia/riva-translate-4b-instruct-v1.1',
            'nd/stepfun-ai/step-3.5-flash' => 'nd/stepfun-ai/step-3.5-flash',
            'nd/ibm/granite-3.0-3b-a800m-instruct' => 'nd/ibm/granite-3.0-3b-a800m-instruct',
            'nd/meta/llama-3.2-3b-instruct' => 'nd/meta/llama-3.2-3b-instruct',

            // Micro Models (1-3B)
            'nd/minimaxai/minimax-m2.1' => 'nd/minimaxai/minimax-m2.1',
            'nd/minimaxai/minimax-m2' => 'nd/minimaxai/minimax-m2',
            'nd/google/gemma-2-2b-it' => 'nd/google/gemma-2-2b-it',
            'nd/google/gemma-2b' => 'nd/google/gemma-2b',
            'nd/google/recurrentgemma-2b' => 'nd/google/recurrentgemma-2b',
            'nd/google/gemma-3n-e2b-it' => 'nd/google/gemma-3n-e2b-it',
            'nd/meta/llama-3.2-1b-instruct' => 'nd/meta/llama-3.2-1b-instruct',
            'nd/google/gemma-3-1b-it' => 'nd/google/gemma-3-1b-it',
            'nd/nvidia/llama-3.2-nemoretriever-1b-vlm-embed-v1' => 'nd/nvidia/llama-3.2-nemoretriever-1b-vlm-embed-v1',
            'nd/nvidia/llama-3.2-nv-embedqa-1b-v1' => 'nd/nvidia/llama-3.2-nv-embedqa-1b-v1',
            'nd/nvidia/llama-3.2-nv-embedqa-1b-v2' => 'nd/nvidia/llama-3.2-nv-embedqa-1b-v2',
            'nd/nvidia/llama-nemotron-embed-vl-1b-v2' => 'nd/nvidia/llama-nemotron-embed-vl-1b-v2',

            // Specialized & Large Context Models
            'nd/ai21labs/jamba-1.5-large-instruct' => 'nd/ai21labs/jamba-1.5-large-instruct',
            'nd/ai21labs/jamba-1.5-mini-instruct' => 'nd/ai21labs/jamba-1.5-mini-instruct',
            'nd/databricks/dbrx-instruct' => 'nd/databricks/dbrx-instruct',
            'nd/deepseek-ai/deepseek-r1-distill-qwen-32b' => 'nd/deepseek-ai/deepseek-r1-distill-qwen-32b',
            'nd/deepseek-ai/deepseek-r1-distill-qwen-14b' => 'nd/deepseek-ai/deepseek-r1-distill-qwen-14b',
            'nd/deepseek-ai/deepseek-r1-distill-qwen-7b' => 'nd/deepseek-ai/deepseek-r1-distill-qwen-7b',
            'nd/deepseek-ai/deepseek-v3.2' => 'nd/deepseek-ai/deepseek-v3.2',
            'nd/deepseek-ai/deepseek-v3.1' => 'nd/deepseek-ai/deepseek-v3.1',
            'nd/deepseek-ai/deepseek-v3.1-terminus' => 'nd/deepseek-ai/deepseek-v3.1-terminus',
            'nd/google/gemma-3n-e4b-it' => 'nd/google/gemma-3n-e4b-it',
            'nd/meta/llama-3.2-90b-vision-instruct' => 'nd/meta/llama-3.2-90b-vision-instruct',
            'nd/microsoft/phi-4-multimodal-instruct' => 'nd/microsoft/phi-4-multimodal-instruct',
            'nd/microsoft/phi-4-mini-instruct' => 'nd/microsoft/phi-4-mini-instruct',
            'nd/microsoft/phi-4-mini-flash-reasoning' => 'nd/microsoft/phi-4-mini-flash-reasoning',
            'nd/microsoft/phi-3.5-vision-instruct' => 'nd/microsoft/phi-3.5-vision-instruct',
            'nd/microsoft/phi-3.5-moe-instruct' => 'nd/microsoft/phi-3.5-moe-instruct',
            'nd/microsoft/phi-3.5-mini-instruct' => 'nd/microsoft/phi-3.5-mini-instruct',
            'nd/microsoft/phi-3-vision-128k-instruct' => 'nd/microsoft/phi-3-vision-128k-instruct',
            'nd/microsoft/phi-3-small-128k-instruct' => 'nd/microsoft/phi-3-small-128k-instruct',
            'nd/microsoft/phi-3-small-8k-instruct' => 'nd/microsoft/phi-3-small-8k-instruct',
            'nd/microsoft/phi-3-mini-128k-instruct' => 'nd/microsoft/phi-3-mini-128k-instruct',
            'nd/microsoft/phi-3-mini-4k-instruct' => 'nd/microsoft/phi-3-mini-4k-instruct',
            'nd/microsoft/phi-3-medium-128k-instruct' => 'nd/microsoft/phi-3-medium-128k-instruct',
            'nd/microsoft/phi-3-medium-4k-instruct' => 'nd/microsoft/phi-3-medium-4k-instruct',
            'nd/microsoft/kosmos-2' => 'nd/microsoft/kosmos-2',
            'nd/mistralai/mistral-nemotron' => 'nd/mistralai/mistral-nemotron',
            'nd/mistralai/mistral-medium-3-instruct' => 'nd/mistralai/mistral-medium-3-instruct',
            'nd/mistralai/mistral-large' => 'nd/mistralai/mistral-large',
            'nd/mistralai/mistral-large-2-instruct' => 'nd/mistralai/mistral-large-2-instruct',
            'nd/mistralai/magistral-small-2506' => 'nd/mistralai/magistral-small-2506',
            'nd/moonshotai/kimi-k2.5' => 'nd/moonshotai/kimi-k2.5',
            'nd/moonshotai/kimi-k2-thinking' => 'nd/moonshotai/kimi-k2-thinking',
            'nd/moonshotai/kimi-k2-instruct-0905' => 'nd/moonshotai/kimi-k2-instruct-0905',
            'nd/moonshotai/kimi-k2-instruct' => 'nd/moonshotai/kimi-k2-instruct',
            'nd/sarvamai/sarvam-m' => 'nd/sarvamai/sarvam-m',

            // Vision & Multimodal Models
            'nd/google/deplot' => 'nd/google/deplot',
            'nd/google/paligemma' => 'nd/google/paligemma',
            'nd/nvidia/nvclip' => 'nd/nvidia/nvclip',
            'nd/nvidia/streampetr' => 'nd/nvidia/streampetr',
            'nd/nvidia/vila' => 'nd/nvidia/vila',

            // Embedding Models
            'nd/baai/bge-m3' => 'nd/baai/bge-m3',
            'nd/nvidia/embed-qa-4' => 'nd/nvidia/embed-qa-4',
            'nd/nvidia/llama-3.2-nemoretriever-300m-embed-v1' => 'nd/nvidia/llama-3.2-nemoretriever-300m-embed-v1',
            'nd/nvidia/llama-3.2-nemoretriever-300m-embed-v2' => 'nd/nvidia/llama-3.2-nemoretriever-300m-embed-v2',
            'nd/nvidia/nemoretriever-parse' => 'nd/nvidia/nemoretriever-parse',
            'nd/nvidia/nemotron-parse' => 'nd/nvidia/nemotron-parse',
            'nd/nvidia/nv-embed-v1' => 'nd/nvidia/nv-embed-v1',
            'nd/nvidia/nv-embedcode-7b-v1' => 'nd/nvidia/nv-embedcode-7b-v1',
            'nd/nvidia/nv-embedqa-e5-v5' => 'nd/nvidia/nv-embedqa-e5-v5',
            'nd/nvidia/nv-embedqa-mistral-7b-v2' => 'nd/nvidia/nv-embedqa-mistral-7b-v2',
            'nd/snowflake/arctic-embed-l' => 'nd/snowflake/arctic-embed-l',
        ];
    }

    public function countTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
}
