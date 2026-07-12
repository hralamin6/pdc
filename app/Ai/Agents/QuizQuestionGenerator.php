<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Agent responsible for generating MCQ quiz questions from a given topic/text.
 */
#[Temperature(0.5)]
class QuizQuestionGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * System instructions for the agent.
     */
    public function instructions(): Stringable|string
    {
        return 'You are an expert Islamic educator and assessment designer. '
            .'Your task is to generate high-quality multiple-choice quiz questions for a Muslim community learning platform. '
            .'Rules: '
            .'1. Questions must be factually accurate. '
            .'2. Return ONLY a valid JSON object. No explanation, no markdown, no preamble. '
            .'3. All generated content must strictly follow the requested language and question type. '
            .'4. For true_false questions, always provide exactly 2 options: "True" and "False". '
            .'5. For multi_select, provide multiple correct options. '
            .'6. For short_text, provide no options — instead, provide an `ideal_answer` along with the question and explanation. '
            .'7. Ensure each question has exactly one clear correct answer for mcq/true_false.';
    }

    /**
     * No persistent conversation — one-shot generation.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Structured JSON schema for the response.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'questions' => $schema->array(
                $schema->object([
                    'question_text' => $schema->string()->required(),
                    'type' => $schema->string()->required(),
                    'marks' => $schema->number()->required(),
                    'ideal_answer' => $schema->string()->nullable(),
                    'explanation' => $schema->string()->required(),
                    'options' => $schema->array(
                        $schema->object([
                            'option_text' => $schema->string()->required(),
                            'is_correct' => $schema->boolean()->required(),
                        ])
                    )->required(),
                ])
            )->required(),
        ];
    }
}
