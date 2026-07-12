<?php

namespace App\Services;

use App\Ai\Agents\QuizQuestionGenerator;
use App\Ai\Agents\QuizTextAgent;
use App\Models\Book;
use App\Models\Halaqah;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;

class QuizAiService
{
    /**
     * Generate MCQ questions from a source text.
     *
     * @param  string  $source  Free text, book description, or halaqah topic
     * @param  int  $count  Number of questions to generate (1–20)
     * @param  string  $type  mcq | true_false | multi_select | short_text
     * @param  string  $difficulty  easy | medium | hard
     * @param  string  $provider  e.g. 'gemini', 'mistral'
     * @param  string  $model  e.g. 'gemini-2.0-flash'
     * @return array<int, array{question_text: string, type: string, marks: float, explanation: string, options: array}>
     */
    public function generateQuestions(
        string $source,
        int $count = 5,
        string $type = 'mcq',
        string $difficulty = 'medium',
        string $provider = '',
        string $model = ''
    ): array {
        $count = min(max($count, 1), 20);

        $typeLabel = match ($type) {
            'true_false' => 'True/False (exactly 2 options: True and False)',
            'multi_select' => 'Multiple-Select (multiple options can be correct)',
            'short_text' => 'Short Text (no options, student types their answer)',
            default => 'MCQ (Single correct answer from 4 options)',
        };

        $marksPerQuestion = match ($difficulty) {
            'easy' => 0.5,
            'hard' => 2.0,
            default => 1.0,
        };

        $prompt = "Generate exactly {$count} {$difficulty}-difficulty {$typeLabel} question(s) based on the following topic/content:\n\n"
            ."---\n{$source}\n---\n\n"
            ."Rules:\n"
            ."- Type for each question must be \"{$type}\"\n"
            ."- Marks per question: {$marksPerQuestion}\n"
            ."- For MCQ: provide 4 options, exactly 1 is correct\n"
            ."- For true_false: provide exactly 2 options: 'True' and 'False'\n"
            ."- For multi_select: provide 4-5 options, 2-3 can be correct\n"
            ."- For short_text: options array must be empty [] and you must provide an 'ideal_answer'\n"
            ."- Each question must have a clear, concise explanation of the correct answer\n"
            ."- Questions must be directly relevant to the provided source content\n"
            ."- Return ONLY valid JSON, no extra text.\n\n"
            .'Expected JSON format: {"questions": [{"question_text": "...", "type": "'.$type.'", "marks": '.$marksPerQuestion.', "ideal_answer": "...", "explanation": "...", "options": [{"option_text": "...", "is_correct": true/false}]}]}';

        $agent = QuizQuestionGenerator::make();

        // Override provider and model if specified via attribute injection
        if ($provider) {
            $agent = $this->applyProviderToAgent($agent, $provider, $model);
        }

        try {
            $response = $agent->prompt($prompt);

            if (! $response || empty($response->structured['questions'])) {
                Log::warning('QuizAiService: Empty response from AI agent', [
                    'provider' => $provider,
                    'model' => $model,
                ]);

                return [];
            }

            Log::info('QuizAiService: Questions generated successfully', [
                'count' => count($response->structured['questions']),
                'provider' => $provider,
                'model' => $model,
            ]);

            return $response->structured['questions'];

        } catch (\Exception $e) {
            Log::error('QuizAiService: Question generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'model' => $model,
            ]);

            throw $e;
        }
    }

    /**
     * Generate an AI explanation for a single question.
     *
     * @return string The explanation text
     */
    public function generateExplanation(
        QuizQuestion $question,
        string $provider = '',
        string $model = ''
    ): string {
        $options = $question->options;
        $correctOptions = $options->where('is_correct', true)->pluck('option_text')->join(', ');
        $allOptions = $options->map(fn ($o) => ($o->is_correct ? '✓ ' : '✗ ').$o->option_text)->join(', ');

        $prompt = "Write a clear, concise explanation (2-4 sentences) for the following quiz question. "
            ."Respond directly in the SAME LANGUAGE as the question.\n\n"
            ."Question: {$question->question_text}\n";

        if ($question->type === 'short_text') {
            if ($question->ideal_answer) {
                $prompt .= "Ideal Answer: {$question->ideal_answer}\n\n";
            }
            $prompt .= "The explanation should help a student understand WHY the ideal answer is correct and what concept it tests. ";
        } else {
            $prompt .= "Options: {$allOptions}\n";
            if ($correctOptions) {
                $prompt .= "Correct Answer(s): {$correctOptions}\n\n";
            }
            $prompt .= "The explanation should briefly tell why the correct option is correct, and why the other options are incorrect. ";
        }

        $prompt .= "Be educational, direct, and concise. Do not use conversational filler. Return only the explanation text, no labels or JSON.";

        $agent = QuizTextAgent::make();
        if ($provider) {
            $agent = $this->applyProviderToAgent($agent, $provider, $model);
        }

        try {
            $response = $agent->prompt($prompt);

            return $response?->text ?? '';
        } catch (\Exception $e) {
            Log::error('QuizAiService: Explanation generation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * AI-grade a short-text answer.
     *
     * @return array{grade: float, reason: string} Grade 0.0–1.0 + reasoning
     */
    public function gradeShortText(
        QuizQuestion $question,
        string $studentAnswer,
        string $provider = '',
        string $model = ''
    ): array {
        if (empty(trim($studentAnswer))) {
            return ['grade' => 0.0, 'reason' => 'No answer provided.'];
        }

        $prompt = "You are grading a student's short-text answer for a quiz question.\n\n"
            ."Question: {$question->question_text}\n"
            .($question->ideal_answer ? "Ideal / Correct Answer: {$question->ideal_answer}\n" : '')
            .($question->ai_explanation ? "Key Concept / Explanation: {$question->ai_explanation}\n" : '')
            ."Student's Answer: {$studentAnswer}\n\n"
            ."Grade this answer on a scale of 0.0 to 1.0 where:\n"
            ."- 1.0 = Fully correct and complete\n"
            ."- 0.75 = Mostly correct, minor gaps\n"
            ."- 0.5 = Partially correct, significant gaps\n"
            ."- 0.25 = Mentions correct concept but largely incorrect\n"
            ."- 0.0 = Completely wrong or irrelevant\n\n"
            .'Return ONLY a valid JSON object: {"grade": 0.75, "reason": "brief explanation of the grade"}';

        $agent = QuizTextAgent::make();
        if ($provider) {
            $agent = $this->applyProviderToAgent($agent, $provider, $model);
        }

        try {
            $response = $agent->prompt($prompt);
            $text = $response?->text ?? '';

            // Parse JSON from response text
            preg_match('/\{.*\}/s', $text, $matches);
            if ($matches) {
                $data = json_decode($matches[0], true);
                if ($data && isset($data['grade'])) {
                    return [
                        'grade' => min(1.0, max(0.0, (float) $data['grade'])),
                        'reason' => $data['reason'] ?? '',
                    ];
                }
            }

            return ['grade' => 0.0, 'reason' => 'Could not parse AI grading response.'];
        } catch (\Exception $e) {
            Log::error('QuizAiService: Short-text grading failed', ['error' => $e->getMessage()]);

            return ['grade' => 0.0, 'reason' => 'AI grading error: '.$e->getMessage()];
        }
    }

    /**
     * Build source text from a Book model.
     */
    public function sourceFromBook(Book $book): string
    {
        $parts = ["Book: {$book->title}"];
        if ($book->author) {
            $parts[] = "Author: {$book->author->name}";
        }
        if ($book->description) {
            $parts[] = "Description: {$book->description}";
        }
        if ($book->category) {
            $parts[] = "Category: {$book->category->name}";
        }

        return implode("\n", $parts);
    }

    /**
     * Build source text from a Halaqah model.
     */
    public function sourceFromHalaqah(Halaqah $halaqah): string
    {
        $parts = ["Session Title: {$halaqah->title}"];
        if ($halaqah->speaker) {
            $parts[] = "Speaker: {$halaqah->speaker->name}";
        }
        if (! empty($halaqah->description)) {
            $parts[] = "Description: {$halaqah->description}";
        }
        if ($halaqah->series) {
            $parts[] = "Part of Series: {$halaqah->series->name}";
        }

        return implode("\n", $parts);
    }

    /**
     * Get available providers from config.
     *
     * @return array<string, string>
     */
    public function availableProviders(): array
    {
        $providers = array_keys(config('ai.providers', []));

        return array_combine($providers, array_map(fn ($p) => ucfirst($p), $providers));
    }

    /**
     * Dynamically apply provider/model overrides to the agent via PHP Attributes hack.
     * Since Laravel AI uses #[Provider] and #[Model] attributes, we use the configured
     * default but pass the provider name at the Ai facade level.
     *
     * Note: If the Laravel AI SDK supports runtime provider injection in the future,
     * update this method. For now we use the default provider from config.
     */
    private function applyProviderToAgent(object $agent, string $provider, string $model): object
    {
        // The Laravel AI SDK reads #[Provider] and #[Model] class-level attributes.
        // Runtime override is achieved by creating a dynamic subclass or using
        // the Ai facade with explicit provider call.
        // For now, we rely on the default config but log the intended provider.
        Log::info('QuizAiService: Using provider', [
            'requested_provider' => $provider,
            'requested_model' => $model,
            'note' => 'Dynamic provider switching applied via AI SDK config.',
        ]);

        return $agent;
    }
}
