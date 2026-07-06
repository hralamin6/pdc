<?php

namespace App\Ai\Gateways;

use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Gateway\ImageGateway;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Responses\Data\GeneratedImage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\ImageResponse;

class PollinationsGateway implements ImageGateway
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
        // Use authenticated endpoint
        $encodedPrompt = rawurlencode($prompt);
        $baseUrl = "https://gen.pollinations.ai/image/{$encodedPrompt}";
        
        $apiKey = $provider->providerCredentials()['key'] ?? env('POLLINATIONS_API_KEY');

        // Download the image content to get base64
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'User-Agent' => 'TallKit/1.0',
            'Accept' => 'image/*',
        ])->timeout($timeout ?? 60)->get($baseUrl, [
            'width' => $width,
            'height' => $height,
            'seed' => $seed,
            'model' => $model,
        ]);

        if ($response->failed()) {
            throw new \Exception("Pollinations image generation failed: " . $response->body());
        }

        $contentType = $response->header('Content-Type');
        $base64 = base64_encode($response->body());

        /** @var \Laravel\Ai\Providers\Provider $provider */
        return new ImageResponse(
            collect([new GeneratedImage($base64, $contentType)]),
            new Usage(0, 0, 0, 0),
            new Meta($provider->name(), $model)
        );
    }
}

