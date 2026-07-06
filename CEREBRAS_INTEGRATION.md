# Cerebras AI Provider Integration for Laravel AI SDK

This document explains how Cerebras AI has been integrated as a custom provider in the Laravel AI SDK.

## Overview

Cerebras AI provides ultra-fast inference through their API, which is OpenAI-compatible. This integration allows you to use Cerebras models seamlessly within Laravel's AI SDK.

## Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
CEREBRAS_API_KEY=your_cerebras_api_key_here
CEREBRAS_BASE_URL=https://api.cerebras.ai/v1
```

### 2. AI Configuration

The Cerebras provider is configured in `config/ai.php`:

```php
'providers' => [
    // ... other providers
    
    'cerebras' => [
        'driver' => 'groq',  // Uses Groq driver for OpenAI-compatible API
        'key' => env('CEREBRAS_API_KEY'),
        'url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
    ],
],
```

**Why Groq driver?** Cerebras uses the standard OpenAI-compatible `/chat/completions` endpoint, which is what the Groq driver uses. Laravel's OpenAI driver uses the newer `/responses` endpoint which Cerebras doesn't support.

## Available Models

Cerebras offers several high-performance models:

### Production Models
- **llama3.1-8b** - Llama 3.1 8B (Fast & Efficient)
- **llama-3.3-70b** - Llama 3.3 70B (Most Capable)
- **qwen-3-32b** - Qwen 3 32B (Balanced)
- **gpt-oss-120b** - OpenAI GPT OSS 120B (Advanced Reasoning)

### Preview Models
- **qwen-3-235b-a22b-instruct-2507** - Qwen 3 235B Instruct (Preview)
- **zai-glm-4.7** - Z.ai GLM 4.7 (Preview)

## Usage

### Using with Agents

You can specify Cerebras as the provider in your agent classes using attributes:

```php
<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Provider('cerebras')]
#[Model('llama-3.3-70b')]
class MyAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Your agent instructions here...';
    }
}
```

### Using Programmatically

You can also use Cerebras directly in your code:

```php
use Laravel\Ai\Ai;

// Get the Cerebras text provider
$provider = Ai::textProvider('cerebras');

// Generate text
$response = $provider->text()
    ->model('llama3.1-8b')
    ->generate('Your prompt here');

echo $response->text;
```

### Using with Facades

```php
use Laravel\Ai\Facades\Text;

$response = Text::provider('cerebras')
    ->model('llama-3.3-70b')
    ->generate('Your prompt here');
```

## Example: PostWriter Agent

Here's an example of the `PostWriter` agent configured to use Cerebras:

```php
#[Provider('cerebras')] 
#[Model('llama-3.3-70b')]
class PostWriter implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return "Write a detailed and engaging blog post...";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'content' => $schema->string()->required(),
            'excerpt' => $schema->string()->required(),
            'image_prompt' => $schema->string()->required(),
        ];
    }
}
```

## Benefits of Cerebras

1. **Ultra-Fast Inference** - Cerebras provides some of the fastest inference speeds available
2. **OpenAI Compatible** - Easy integration using the OpenAI driver
3. **Cost-Effective** - Competitive pricing for high-performance models
4. **Multiple Models** - Access to various model sizes for different use cases

## Testing

You can test the integration using the provided test script:

```bash
php artisan tinker test-cerebras.php
```

This will verify that:
- The provider is correctly registered
- The configuration is properly loaded
- The API key is configured

## Troubleshooting

### Provider Not Found
If you get a "provider not found" error, make sure:
1. The configuration is published: `php artisan config:clear`
2. The `.env` file has the correct `CEREBRAS_API_KEY`

### API Errors - 404 Unknown Error
If you encounter a 404 error with "Unknown error":
1. **Verify the model name** - Cerebras model names are case-sensitive and specific:
   - ✅ Correct: `llama3.1-8b`, `llama-3.3-70b`, `qwen-3-32b`
   - ❌ Wrong: `llama-3.1-8b` (wrong dash placement), `gpt-4` (not a Cerebras model)
2. **Check the model exists** - Not all models listed may be available. Test with `llama3.1-8b` first
3. **Ensure you're using the Groq driver** - The config should have `'driver' => 'groq'` (NOT 'openai')
4. **Verify the base URL** - Should be `https://api.cerebras.ai/v1`

### Testing a Specific Model
To test if a model works, try this in tinker:

```php
use Laravel\Ai\Ai;

$provider = Ai::textProvider('cerebras');
$response = $provider->text()
    ->model('llama3.1-8b')  // Start with this known-working model
    ->generate('Say hello');
    
echo $response->text;
```

### Model Name Format
Cerebras uses specific model naming conventions:
- `llama3.1-8b` (note: no dash between llama and 3)
- `llama-3.3-70b` (note: dash between llama and 3.3)
- `qwen-3-32b`
- NOT `openai/gpt-oss-120b` (no provider prefix needed)

## Additional Resources

- [Cerebras AI Documentation](https://cerebras.ai/docs)
- [Laravel AI SDK Documentation](https://laravel.com/docs/12.x/ai-sdk)
- [Available Cerebras Models](https://cerebras.ai/models)

## Implementation Details

### Why Groq Driver?

Cerebras provides an OpenAI-compatible API, but there's an important distinction:

- **Laravel's OpenAI driver** uses the newer `/responses` endpoint (OpenAI's latest API format)
- **Cerebras API** uses the standard `/chat/completions` endpoint (traditional OpenAI-compatible format)
- **Groq driver** also uses `/chat/completions`, making it compatible with Cerebras

This approach:
- ✅ Works with Cerebras's current API implementation
- ✅ Reduces code duplication (no custom provider needed)
- ✅ Ensures compatibility with future Laravel AI SDK updates
- ✅ Leverages the robust Prism gateway for HTTP communication
- ✅ Maintains all standard features (streaming, structured output, etc.)

### API Endpoint Comparison

```
OpenAI (new):    POST /v1/responses
Cerebras:        POST /v1/chat/completions  ← Standard OpenAI-compatible
Groq:            POST /v1/chat/completions  ← Same as Cerebras!
```

This is why we use the Groq driver - it matches Cerebras's API structure perfectly.

### Custom Provider (Alternative Approach)

If you need custom behavior, you can create a dedicated Cerebras provider:

```php
namespace App\Ai\Providers;

use Laravel\Ai\Providers\Provider;
use Laravel\Ai\Contracts\Providers\TextProvider;

class CerebrasProvider extends Provider implements TextProvider
{
    use GeneratesText;
    use HasTextGateway;
    use StreamsText;

    public function defaultTextModel(): string
    {
        return 'llama-3.3-70b';
    }

    public function cheapestTextModel(): string
    {
        return 'llama3.1-8b';
    }

    public function smartestTextModel(): string
    {
        return 'llama-3.3-70b';
    }
}
```

Then register it in `AppServiceProvider`:

```php
use Laravel\Ai\Ai;
use App\Ai\Providers\CerebrasProvider;

public function boot(): void
{
    Ai::extend('cerebras', function ($app, array $config) {
        return new CerebrasProvider(
            new PrismGateway($app['events']),
            $config,
            $app->make(Dispatcher::class)
        );
    });
}
```

However, for most use cases, using the OpenAI driver is simpler and sufficient.
