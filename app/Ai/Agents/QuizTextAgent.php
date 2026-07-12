<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Agent responsible for generating raw text responses, like explanations or grading reasoning,
 * without forcing a structured JSON output.
 */
#[Temperature(0.5)]
class QuizTextAgent implements Agent
{
    use Promptable;

    /**
     * System instructions for the agent.
     */
    public function instructions(): Stringable|string
    {
        return 'You are an expert Islamic educator and assessment designer. '
            .'Your task is to provide clear, accurate, and educational text based on the prompt. '
            .'Do not use markdown formatting unless specifically requested. '
            .'Do not wrap your response in JSON formatting.';
    }

    /**
     * No persistent conversation — one-shot generation.
     */
    public function messages(): iterable
    {
        return [];
    }
}
