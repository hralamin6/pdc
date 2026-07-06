# Pollinations AI Integration

This document details the integration of Pollinations AI into the Laravel application.

## Overview

The integration supports both **Text Generation** and **Image Generation** using a custom `PollinationsProvider`.

- **Text Model:** `nova-micro` (and others via OpenAI-compatible endpoint)
- **Image Model:** `flux`, `turbo`, etc.
- **Driver:** `pollinations` (Custom driver)

## Configuration

The integration is configured in `config/ai.php`:

```php
'pollinations' => [
    'driver' => 'pollinations',
    'key' => env('POLLINATIONS_API_KEY'),
    'url' => 'https://gen.pollinations.ai/v1', // Base URL for text generation
],
```

Ensure `POLLINATIONS_API_KEY` is set in your `.env` file.

## Text Generation

Text generation uses the standard OpenAI-compatible API provided by Pollinations (`/chat/completions`).

We utilize the **Groq driver** under the hood for text generation because:
1. It uses the standard `/chat/completions` endpoint (unlike Laravel's OpenAI driver which uses `/responses`).
2. It is fully compatible with Pollinations' API structure.

### Usage

```php
use App\Ai\Agents\PostWriter;

$response = PostWriter::make()->prompt('Write a short poem');
echo $response->text;
```

Or using the facade:

```php
use Laravel\Ai\Ai;

$response = Ai::textProvider('pollinations')
    ->chat()
    ->model('nova-micro')
    ->send('Hello!');
```

## Image Generation

Image generation is handled by a custom `PollinationsGateway` which communicates with the `gen.pollinations.ai` authenticated endpoint.

### Features
- **Authenticated:** Uses Bearer token for authorized access.
- **Models:** Supports models like `flux`, `turbo`.
- **Parameters:** Supports width, height, seed, nologo.

### Usage

```php
use Laravel\Ai\Ai;

$response = Ai::imageProvider('pollinations')->image(
    prompt: 'A futuristic city',
    model: 'flux'
);

$image = $response->images[0];
$path = $image->store('images');
```

## Implementation Details

### `App\Ai\Providers\PollinationsProvider`

This custom provider extends the base `Provider` and implements `TextProvider` and `ImageProvider`.

- **Text:** Delegates to `groq` driver (returns `'groq'` in `driver()` method) to leverage existing Prism integration for OpenAI-compatible chat.
- **Image:** Uses custom `PollinationsGateway` for image generation logic.

### `App\Ai\Gateways\PollinationsGateway`

Handles the actual HTTP request to `https://gen.pollinations.ai/image/{prompt}`.
- Adds `Authorization: Bearer {key}` header.
- Helper method `generateImage` processes parameters and returns `ImageResponse`.

## Troubleshooting

- **Text 404 Error:** Ensure `url` in config is `https://gen.pollinations.ai/v1`. If it's `https://text.pollinations.ai/`, it might expect different paths.
- **Image 1033 Error:** This is a Cloudflare error. Ensure `User-Agent` header is set (handled by Gateway) and API key is valid.
- **Image 401 Error:** API Key is missing or invalid. Check `.env`.

## Available Models

**Text:**
- `nova-micro`
- `gpt-4o`
- `claude-3-5-sonnet`
- (Check Pollinations docs for full list)

**Image:**
- `flux`
- `flux-realism`
- `turbo`
