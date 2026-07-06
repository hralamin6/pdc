<?php

namespace App\Ai\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Gateway\ImageGateway;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Responses\Data\GeneratedImage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\ImageResponse;

class PollinationsImageGateway implements ImageGateway
{
    /**
     * Generate an image.
     */
    public function generateImage(
        ImageProvider $provider,
        string $model,
        string $prompt,
        array $attachments = [],
        ?string $size = null,
        ?string $quality = null,
        ?int $timeout = null,
    ): ImageResponse {
        $options = $provider->defaultImageOptions($size, $quality);
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;
        $seed = $options['seed'] ?? rand(1, 1000000);

        // Build image URL
        $encodedPrompt = urlencode($prompt);
        $imageUrl = "https://pollinations.ai/p/{$encodedPrompt}?width={$width}&height={$height}&seed={$seed}&model={$model}&nologo=true";

        // Download the image content to get base64
        $response = Http::timeout($timeout ?? 30)->get($imageUrl);

        if ($response->failed()) {
            throw new \Exception("Pollinations image generation failed: " . $response->body());
        }

        $contentType = $response->header('Content-Type');
        $base64 = base64_encode($response->body());

        return new ImageResponse(
            collect([new GeneratedImage($base64, $contentType)]),
            new Usage(0, 0, 0, 0), // Usage is tricky to calculate here
            new Meta($provider->name(), $model)
        );
    }
}
