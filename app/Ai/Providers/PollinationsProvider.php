<?php

namespace App\Ai\Providers;

use Laravel\Ai\Contracts\Gateway\ImageGateway;
use Laravel\Ai\Contracts\Gateway\TextGateway;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Providers\Concerns\GeneratesImages;
use Laravel\Ai\Providers\Concerns\GeneratesText;
use Laravel\Ai\Providers\Concerns\HasImageGateway;
use Laravel\Ai\Providers\Concerns\HasTextGateway;
use Laravel\Ai\Providers\Concerns\StreamsText;
use Laravel\Ai\Providers\Provider;
use App\Ai\Gateways\PollinationsGateway;

class PollinationsProvider extends Provider implements TextProvider, ImageProvider
{
    use GeneratesImages;
    use GeneratesText;
    use HasImageGateway;
    use HasTextGateway;
    use StreamsText;

    /**
     * Get the name of the underlying AI driver.
     */
    public function driver(): string
    {
        return 'groq';
    }

    /**
     * Get the provider's text gateway.
     */
    public function textGateway(): TextGateway
    {
        return $this->gateway;
    }

    /**
     * Get the provider's image gateway.
     */
    public function imageGateway(): ImageGateway
    {
        return $this->imageGateway ??= new PollinationsGateway;
    }
    /**
     * Get the name of the default text model.
     */
    public function defaultTextModel(): string
    {
        return 'nova-micro';
    }

    /**
     * Get the name of the cheapest text model.
     */
    public function cheapestTextModel(): string
    {
        return 'openai-nano';
    }

    /**
     * Get the name of the smartest text model.
     */
    public function smartestTextModel(): string
    {
        return 'openai';
    }

    /**
     * Get the name of the default image model.
     */
    public function defaultImageModel(): string
    {
        return 'flux';
    }

    /**
     * Get the default / normalized image options for the provider.
     */
    public function defaultImageOptions(?string $size = null, $quality = null): array
    {
        [$width, $height] = match ($size) {
            '1:1' => [1024, 1024],
            '2:3' => [1024, 1536],
            '3:2' => [1536, 1024],
            '16:9' => [1920, 1080],
            '9:16' => [1080, 1920],
            default => [1024, 1024],
        };

        return [
            'width' => $width,
            'height' => $height,
            'seed' => rand(1, 1000000),
        ];
    }
}
