<?php

namespace App\Nexus\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai;

class NexusVoiceService
{
    /**
     * Transcribe audio to text using Laravel AI SDK (OpenAI Whisper).
     */
    public function transcribe(UploadedFile $audio): string
    {
        try {
            $response = Ai::transcribe($audio->getRealPath());
            return $response->text ?? '';
        } catch (\Throwable $e) {
            Log::error('NexusVoice: transcription failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Convert text to speech and return base64 audio.
     */
    public function synthesize(string $text, string $voice = 'alloy'): string
    {
        try {
            // Truncate to 4000 chars for TTS
            $text = substr($text, 0, 4000);
            $response = Ai::speech($text, $voice);

            return base64_encode($response->audio);
        } catch (\Throwable $e) {
            Log::error('NexusVoice: TTS failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
