<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoTranslateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes timeout

    public $tries = 3;

    public $backoff = [60, 120, 180]; // Exponential backoff in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $targetLanguage
    ) {
        $this->queue = 'default';
        $this->onConnection('database');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $translationService = app(TranslationService::class);

            Log::info('Starting AI translation job', [
                'language' => $this->targetLanguage,
            ]);

            $translated = $translationService->autoTranslate($this->targetLanguage);

            Log::info('Completed AI translation job', [
                'language' => $this->targetLanguage,
                'translated_count' => count($translated),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed AI translation job', [
                'language' => $this->targetLanguage,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
