<?php

namespace App\Nexus\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Image;
use Laravel\Ai\Tools\Request;
use Stringable;

class ImageGeneratorTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Generate an AI image from a text description. Returns the image URL that will be displayed in the chat.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'prompt' => $schema->string()->required()->description('Detailed description of the image to generate'),
            'size'   => $schema->string()->description('Aspect ratio: 1:1 (square), 16:9 (landscape), 9:16 (portrait)')->default('1:1'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $prompt = $request->get('prompt');
        $size   = $request->get('size', '1:1');

        try {
            $response = Image::of($prompt)->size($size)->timeout(60)->generate('pollinations', 'flux');
            $image    = $response->firstImage();

            $filename = 'nexus/images/'.Str::random(32).'.png';
            Storage::disk('public')->put($filename, base64_decode($image->image));
            $url = Storage::disk('public')->url($filename);

            // Return special marker so the frontend can render the image inline
            return "[[NEXUS_IMAGE:{$url}]]\n\nImage generated for prompt: *\"{$prompt}\"*";
        } catch (\Exception $e) {
            return "Image generation failed: ".$e->getMessage();
        }
    }
}
