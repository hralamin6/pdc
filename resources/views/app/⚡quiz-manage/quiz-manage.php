<?php

use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\QuizAiService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Quiz Management')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    // ─── List & Filters ──────────────────────────────────────────────────────────
    public string $search = '';
    public string $statusFilter = 'all';
    public string $modeFilter = 'all';

    // ─── Quiz Form State ─────────────────────────────────────────────────────────
    public bool $quizModal = false;
    public ?int $editingQuizId = null;

    public string $title = '';
    public string $description = '';
    public string $mode = 'async';
    public string $status = 'draft';
    public string $quizzable_type = '';  // 'halaqah' | 'series' | '' (general)
    public ?int $quizzable_id = null;
    public ?int $time_limit_minutes = null;
    public ?string $available_from = null;
    public ?string $available_until = null;
    public bool $shuffle_questions = true;
    public bool $shuffle_options = true;
    public bool $show_answers_after = true;
    public bool $negative_marking = false;
    public float $negative_mark_value = 0.25;
    public ?int $pass_mark_percent = null;
    public int $points_on_pass = 10;
    public string $bonus_points_for_rank = '{"1": 50, "2": 30, "3": 10}';

    // ─── Question Builder State ───────────────────────────────────────────────────
    public ?int $buildingQuizId = null;
    public ?Quiz $buildingQuiz = null;

    /** @var array<int, array{id: ?int, type: string, question_text: string, marks: float, ideal_answer: string, ai_explanation: string, options: array}> */
    public array $questions = [];

    // ─── AI Generation State ─────────────────────────────────────────────────────
    public bool $aiModal = false;
    public string $aiSourceType = 'text';        // 'text' | 'book' | 'halaqah'
    public string $aiSourceText = '';            // Free text input
    public ?int $aiSourceBookId = null;          // Selected book ID
    public ?int $aiSourceHalaqahId = null;       // Selected halaqah ID
    public int $aiCount = 5;                     // Number of questions
    public string $aiType = 'mcq';               // Question type
    public string $aiDifficulty = 'medium';      // Difficulty
    public string $aiProvider = '';              // AI provider
    public string $aiModel = '';                 // AI model
    public bool $aiLoading = false;

    /** @var array Staged generated questions awaiting import decision */
    public array $aiGeneratedQuestions = [];

    // ─── Computed ────────────────────────────────────────────────────────────────

    #[\Livewire\Attributes\Computed]
    public function quizzes()
    {
        return Quiz::with(['creator', 'quizzable'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->modeFilter !== 'all', fn ($q) => $q->where('mode', $this->modeFilter))
            ->latest()
            ->paginate(12);
    }

    #[\Livewire\Attributes\Computed]
    public function halaqahs()
    {
        return Halaqah::orderByDesc('scheduled_at')->limit(50)->get(['id', 'title']);
    }

    #[\Livewire\Attributes\Computed]
    public function series()
    {
        return HalaqahSeries::orderBy('title')->get(['id', 'title']);
    }

    #[\Livewire\Attributes\Computed]
    public function books()
    {
        return Book::where('status', 'approved')->orderBy('title')->limit(100)->get(['id', 'title']);
    }

    #[\Livewire\Attributes\Computed]
    public function aiProviderOptions()
    {
        $providers = array_keys(config('ai.providers', []));
        return collect($providers)->map(fn ($p) => ['id' => $p, 'name' => ucfirst($p)])->values()->toArray();
    }

    // ─── Quiz CRUD ───────────────────────────────────────────────────────────────

    public function openQuizModal(?int $id = null): void
    {
        $this->resetQuizForm();
        $this->editingQuizId = $id;

        if ($id) {
            $quiz = Quiz::findOrFail($id);
            $this->title = $quiz->title;
            $this->description = $quiz->description ?? '';
            $this->mode = $quiz->mode;
            $this->status = $quiz->status;
            $this->time_limit_minutes = $quiz->time_limit_minutes;
            $this->available_from = $quiz->available_from?->format('Y-m-d\TH:i');
            $this->available_until = $quiz->available_until?->format('Y-m-d\TH:i');
            $this->shuffle_questions = $quiz->shuffle_questions;
            $this->shuffle_options = $quiz->shuffle_options;
            $this->show_answers_after = $quiz->show_answers_after;
            $this->negative_marking = $quiz->negative_marking;
            $this->negative_mark_value = $quiz->negative_mark_value;
            $this->pass_mark_percent = $quiz->pass_mark_percent;
            $this->points_on_pass = $quiz->points_on_pass;
            $this->bonus_points_for_rank = json_encode($quiz->bonus_points_for_rank ?? ['1' => 50, '2' => 30, '3' => 10]);

            if ($quiz->quizzable_type) {
                $this->quizzable_type = class_basename($quiz->quizzable_type) === 'Halaqah' ? 'halaqah' : 'series';
                $this->quizzable_id = $quiz->quizzable_id;
            }
        }

        $this->quizModal = true;
    }

    public function saveQuiz(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'mode' => 'required|in:async,live',
            'status' => 'required|in:draft,published,live,closed',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'pass_mark_percent' => 'nullable|integer|min:1|max:100',
            'negative_mark_value' => 'required|decimal:0,2|min:0|max:1',
            'points_on_pass' => 'required|integer|min:0',
        ]);

        $quizzableType = null;
        $quizzableId = null;

        if ($this->quizzable_type === 'halaqah' && $this->quizzable_id) {
            $quizzableType = Halaqah::class;
            $quizzableId = $this->quizzable_id;
        } elseif ($this->quizzable_type === 'series' && $this->quizzable_id) {
            $quizzableType = HalaqahSeries::class;
            $quizzableId = $this->quizzable_id;
        }

        Quiz::updateOrCreate(['id' => $this->editingQuizId], [
            'created_by' => $this->editingQuizId ? Quiz::find($this->editingQuizId)->created_by : auth()->id(),
            'title' => $this->title,
            'description' => $this->description ?: null,
            'mode' => $this->mode,
            'status' => $this->status,
            'quizzable_type' => $quizzableType,
            'quizzable_id' => $quizzableId,
            'time_limit_minutes' => $this->time_limit_minutes,
            'available_from' => $this->available_from,
            'available_until' => $this->available_until,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_options' => $this->shuffle_options,
            'show_answers_after' => $this->show_answers_after,
            'negative_marking' => $this->negative_marking,
            'negative_mark_value' => $this->negative_mark_value,
            'pass_mark_percent' => $this->pass_mark_percent,
            'points_on_pass' => $this->points_on_pass,
            'bonus_points_for_rank' => json_decode($this->bonus_points_for_rank, true),
        ]);

        $this->success($this->editingQuizId ? 'Quiz updated.' : 'Quiz created.');
        $this->quizModal = false;
        unset($this->quizzes);
    }

    public function deleteQuiz(int $id): void
    {
        Quiz::findOrFail($id)->delete();
        $this->warning('Quiz deleted.');
        unset($this->quizzes);
    }

    public function publishQuiz(int $id): void
    {
        Quiz::findOrFail($id)->update(['status' => 'published']);
        $this->success('Quiz published!');
        unset($this->quizzes);
    }

    public function closeQuiz(int $id): void
    {
        Quiz::findOrFail($id)->update(['status' => 'closed']);
        $this->info('Quiz closed.');
        unset($this->quizzes);
    }

    // ─── Question Builder ─────────────────────────────────────────────────────────

    public function openBuilder(int $quizId): void
    {
        $this->buildingQuizId = $quizId;
        $this->buildingQuiz = Quiz::with(['questions.options'])->findOrFail($quizId);
        $this->loadQuestionsFromDb();
    }

    public function closeBuilder(): void
    {
        $this->buildingQuizId = null;
        $this->buildingQuiz = null;
        $this->questions = [];
    }

    private function loadQuestionsFromDb(): void
    {
        $this->questions = $this->buildingQuiz->questions->map(fn ($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'question_text' => $q->question_text,
            'marks' => $q->marks,
            'ideal_answer' => $q->ideal_answer ?? '',
            'ai_explanation' => $q->ai_explanation ?? '',
            'options' => $q->options->map(fn ($o) => [
                'id' => $o->id,
                'option_text' => $o->option_text,
                'is_correct' => $o->is_correct,
            ])->toArray(),
        ])->toArray();
    }

    public function addQuestion(): void
    {
        $this->questions[] = [
            'id' => null,
            'type' => 'mcq',
            'question_text' => '',
            'marks' => 1.0,
            'ideal_answer' => '',
            'ai_explanation' => '',
            'options' => [
                ['id' => null, 'option_text' => '', 'is_correct' => false],
                ['id' => null, 'option_text' => '', 'is_correct' => false],
                ['id' => null, 'option_text' => '', 'is_correct' => false],
                ['id' => null, 'option_text' => '', 'is_correct' => false],
            ],
        ];
    }

    public function removeQuestion(int $index): void
    {
        $question = $this->questions[$index] ?? null;
        if ($question && $question['id']) {
            QuizQuestion::find($question['id'])?->delete();
        }
        array_splice($this->questions, $index, 1);
    }

    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = [
            'id' => null,
            'option_text' => '',
            'is_correct' => false,
        ];
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        array_splice($this->questions[$questionIndex]['options'], $optionIndex, 1);
    }

    public function setCorrectOption(int $questionIndex, int $optionIndex): void
    {
        $type = $this->questions[$questionIndex]['type'];
        if ($type !== 'multi_select') {
            // Single-correct: unset all others
            foreach ($this->questions[$questionIndex]['options'] as $i => $opt) {
                $this->questions[$questionIndex]['options'][$i]['is_correct'] = ($i === $optionIndex);
            }
        } else {
            // Multi-select: toggle
            $this->questions[$questionIndex]['options'][$optionIndex]['is_correct']
                = ! $this->questions[$questionIndex]['options'][$optionIndex]['is_correct'];
        }
    }

    public function onTypeChange(int $questionIndex): void
    {
        $type = $this->questions[$questionIndex]['type'];
        if ($type === 'true_false') {
            $this->questions[$questionIndex]['options'] = [
                ['id' => null, 'option_text' => 'True', 'is_correct' => true],
                ['id' => null, 'option_text' => 'False', 'is_correct' => false],
            ];
        } elseif ($type === 'short_text') {
            $this->questions[$questionIndex]['options'] = [];
        } elseif (count($this->questions[$questionIndex]['options']) < 2) {
            $this->questions[$questionIndex]['options'] = [
                ['id' => null, 'option_text' => '', 'is_correct' => false],
                ['id' => null, 'option_text' => '', 'is_correct' => false],
            ];
        }
    }

    public function saveQuestions(): void
    {
        if (! $this->buildingQuizId) {
            return;
        }

        foreach ($this->questions as $order => $qData) {
            $question = QuizQuestion::updateOrCreate(
                ['id' => $qData['id'] ?? null],
                [
                    'quiz_id' => $this->buildingQuizId,
                    'order' => $order,
                    'type' => $qData['type'],
                    'question_text' => $qData['question_text'],
                    'marks' => $qData['marks'],
                    'ideal_answer' => $qData['ideal_answer'] ?? null,
                    'ai_explanation' => $qData['ai_explanation'] ?: null,
                ]
            );

            if ($qData['type'] !== 'short_text') {
                // Sync options
                $keepIds = [];
                foreach ($qData['options'] as $optOrder => $optData) {
                    $option = QuizOption::updateOrCreate(
                        ['id' => $optData['id'] ?? null],
                        [
                            'question_id' => $question->id,
                            'option_text' => $optData['option_text'],
                            'is_correct' => $optData['is_correct'],
                            'order' => $optOrder,
                        ]
                    );
                    $keepIds[] = $option->id;
                }
                // Delete removed options
                $question->options()->whereNotIn('id', $keepIds)->delete();
            } else {
                $question->options()->delete();
            }

            // Update question IDs so next save knows they exist
            $this->questions[$order]['id'] = $question->id;
        }

        // Reload to sync from DB
        $this->buildingQuiz = Quiz::with(['questions.options'])->findOrFail($this->buildingQuizId);
        $this->success('Questions saved!');
        unset($this->quizzes);
    }

    // ─── AI Generation ────────────────────────────────────────────────────────────

    public function openAiModal(): void
    {
        $this->aiSourceType = 'text';
        $this->aiSourceText = '';
        $this->aiSourceBookId = null;
        $this->aiSourceHalaqahId = null;
        $this->aiCount = 5;
        $this->aiType = 'mcq';
        $this->aiDifficulty = 'medium';
        $this->aiProvider = config('ai.default', '');
        $this->aiModel = '';
        $this->aiGeneratedQuestions = [];
        $this->aiModal = true;
    }

    public function runAiGeneration(): void
    {
        $this->validate([
            'aiCount' => 'required|integer|min:1|max:20',
            'aiType' => 'required|in:mcq,true_false,multi_select,short_text',
            'aiDifficulty' => 'required|in:easy,medium,hard',
        ]);

        $service = app(QuizAiService::class);

        // Build source text
        $source = match ($this->aiSourceType) {
            'book' => $this->aiSourceBookId
                ? $service->sourceFromBook(Book::findOrFail($this->aiSourceBookId))
                : '',
            'halaqah' => $this->aiSourceHalaqahId
                ? $service->sourceFromHalaqah(Halaqah::with(['speaker', 'series'])->findOrFail($this->aiSourceHalaqahId))
                : '',
            default => $this->aiSourceText,
        };

        if (empty(trim($source))) {
            $this->error('Please provide a source (text, book, or halaqah session).');
            return;
        }

        try {
            $this->aiGeneratedQuestions = $service->generateQuestions(
                source: $source,
                count: $this->aiCount,
                type: $this->aiType,
                difficulty: $this->aiDifficulty,
                provider: $this->aiProvider,
                model: $this->aiModel,
            );

            if (empty($this->aiGeneratedQuestions)) {
                $this->error('AI returned no questions. Try a different source or provider.');
            } else {
                $this->success(count($this->aiGeneratedQuestions) . ' questions generated! Review and import below.');
            }
        } catch (\Exception $e) {
            $this->error('AI generation failed: ' . $e->getMessage());
        }
    }

    public function importAiQuestion(int $index): void
    {
        $q = $this->aiGeneratedQuestions[$index] ?? null;
        if (! $q) return;

        $this->questions[] = [
            'id' => null,
            'type' => $q['type'],
            'question_text' => $q['question_text'],
            'marks' => $q['marks'],
            'ideal_answer' => $q['ideal_answer'] ?? '',
            'ai_explanation' => $q['explanation'] ?? '',
            'options' => collect($q['options'] ?? [])->map(fn ($o) => [
                'id' => null,
                'option_text' => $o['option_text'],
                'is_correct' => $o['is_correct'],
            ])->toArray(),
        ];

        // Remove from staged list
        array_splice($this->aiGeneratedQuestions, $index, 1);
        $this->success('Question imported to builder.');
    }

    public function importAllAiQuestions(): void
    {
        foreach ($this->aiGeneratedQuestions as $q) {
            $this->questions[] = [
                'id' => null,
                'type' => $q['type'],
                'question_text' => $q['question_text'],
                'marks' => $q['marks'],
                'ideal_answer' => $q['ideal_answer'] ?? '',
                'ai_explanation' => $q['explanation'] ?? '',
                'options' => collect($q['options'] ?? [])->map(fn ($o) => [
                    'id' => null,
                    'option_text' => $o['option_text'],
                    'is_correct' => $o['is_correct'],
                ])->toArray(),
            ];
        }

        $count = count($this->aiGeneratedQuestions);
        $this->aiGeneratedQuestions = [];
        $this->aiModal = false;
        $this->success("{$count} questions imported! Save when ready.");
    }

    public function generateExplanationForQuestion(int $index): void
    {
        $q = $this->questions[$index] ?? null;
        if (! $q) return;

        if (empty($q['question_text'])) {
            $this->error('Save the question text first before generating an explanation.');
            return;
        }

        // Build a temporary QuizQuestion-like object for the service
        $question = new QuizQuestion([
            'type' => $q['type'],
            'question_text' => $q['question_text'],
            'ideal_answer' => $q['ideal_answer'] ?? null,
            'ai_explanation' => $q['ai_explanation'],
        ]);
        $question->setRelation('options', collect($q['options'] ?? [])->map(function ($o) {
            $opt = new \App\Models\QuizOption($o);
            $opt->is_correct = (bool) $o['is_correct'];
            return $opt;
        }));

        try {
            $explanation = app(QuizAiService::class)->generateExplanation($question, $this->aiProvider, $this->aiModel);
            $this->questions[$index]['ai_explanation'] = $explanation;
            $this->success('Explanation generated!');
        } catch (\Exception $e) {
            $this->error('Could not generate explanation: ' . $e->getMessage());
        }
    }

    // ─── Form Helpers ─────────────────────────────────────────────────────────────

    private function resetQuizForm(): void
    {
        $this->editingQuizId = null;
        $this->title = '';
        $this->description = '';
        $this->mode = 'async';
        $this->status = 'draft';
        $this->quizzable_type = '';
        $this->quizzable_id = null;
        $this->time_limit_minutes = null;
        $this->available_from = null;
        $this->available_until = null;
        $this->shuffle_questions = true;
        $this->shuffle_options = true;
        $this->show_answers_after = true;
        $this->negative_marking = false;
        $this->negative_mark_value = 0.25;
        $this->pass_mark_percent = null;
        $this->points_on_pass = 10;
        $this->bonus_points_for_rank = '{"1": 50, "2": 30, "3": 10}';
    }
};
