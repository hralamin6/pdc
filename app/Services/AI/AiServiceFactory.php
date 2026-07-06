<?php

namespace App\Services\AI;

class AiServiceFactory
{
    public static function make(string $provider): AiServiceInterface
    {
        return match ($provider) {
            'openrouter' => new OpenRouterService(),
            'gemini' => new GeminiService(),
            'pollinations' => new PollinationsService(),
            'cerebras' => new CerebrasService(),
            'mistral' => new MistralService(),
            'groq' => new GroqService(),
            'nvidia' => new NvidiaService(),
            'iflow' => new IflowService(),
            'custom' => new CustomService(),
            default => throw new \InvalidArgumentException("Unsupported AI provider: {$provider}"),
        };
    }

    public static function getAvailableProviders(): array
    {
        return [
            'openrouter' => 'OpenRouter',
            'gemini' => 'Google Gemini',
            'pollinations' => 'Pollinations AI',
            'cerebras' => 'Cerebras AI',
            'mistral' => 'Mistral AI',
            'groq' => 'Groq',
            'nvidia' => 'NVIDIA AI',
            'iflow' => 'iFlow AI',
            'custom' => 'Custom AI',
        ];
    }
}
