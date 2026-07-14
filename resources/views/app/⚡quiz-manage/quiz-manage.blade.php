<div>
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- QUIZ BUILDER MODE                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($buildingQuizId)
        <div class="max-w-4xl mx-auto">
            {{-- Builder Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <button wire:click="closeBuilder" class="btn btn-ghost btn-sm gap-2 -ml-2 mb-1">
                        <x-icon name="o-arrow-left" class="w-4 h-4" />
                        {{ __('Back to Quiz List') }}
                    </button>
                    <h1 class="text-2xl font-black">{{ __('Question Builder') }}</h1>
                    <p class="text-sm text-base-content/60 mt-0.5">
                        <span class="font-semibold text-primary">{{ $buildingQuiz->title }}</span>
                        &bull; {{ count($questions) }} {{ __('question(s)') }}
                        &bull; {{ __('Total:') }} {{ collect($questions)->sum('marks') }} {{ __('marks') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <x-button label="{{ __('✨ AI Generate') }}" icon="o-sparkles"
                        class="bg-gradient-to-r from-violet-600 to-purple-600 text-white border-none shadow-lg shadow-purple-500/30 hover:scale-105 transition-transform"
                        wire:click="openAiModal" />
                    <x-button label="{{ __('Add Question') }}" icon="o-plus" class="btn-primary" wire:click="addQuestion" />
                    <x-button label="{{ __('Save All') }}" icon="o-check" class="btn-success" wire:click="saveQuestions" spinner="saveQuestions" />
                </div>
            </div>

            {{-- Empty state --}}
            @if(empty($questions))
                <div class="flex flex-col items-center justify-center py-24 bg-base-100 rounded-2xl border-2 border-dashed border-base-content/10">
                    <x-icon name="o-question-mark-circle" class="w-16 h-16 text-base-content/20 mb-4" />
                    <h3 class="font-bold text-lg text-base-content/60">{{ __('No questions yet') }}</h3>
                    <p class="text-sm text-base-content/40 mb-6">{{ __('Start by adding your first question below.') }}</p>
                    <x-button label="{{ __('Add First Question') }}" icon="o-plus" class="btn-primary" wire:click="addQuestion" />
                </div>
            @endif

            {{-- Question Cards --}}
            <div class="space-y-4">
                @foreach($questions as $qi => $question)
                    <div class="bg-base-100 rounded-2xl border border-base-content/10 shadow-sm overflow-hidden" wire:key="question-{{ $qi }}">
                        {{-- Question Header --}}
                        <div class="flex items-center gap-3 px-5 py-3 bg-base-200/50 border-b border-base-content/5">
                            <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-black flex-shrink-0">
                                {{ $qi + 1 }}
                            </div>

                            {{-- Type selector --}}
                            <select wire:model.live="questions.{{ $qi }}.type" wire:change="onTypeChange({{ $qi }})"
                                class="select select-sm select-bordered rounded-lg font-medium">
                                <option value="mcq">{{ __('MCQ (Single correct)') }}</option>
                                <option value="true_false">{{ __('True / False') }}</option>
                                <option value="multi_select">{{ __('Multi-select') }}</option>
                                <option value="short_text">{{ __('Short Text') }}</option>
                            </select>

                            <div class="flex items-center gap-1 ml-auto">
                                <span class="text-xs text-base-content/50">{{ __('Marks:') }}</span>
                                <input type="number" wire:model="questions.{{ $qi }}.marks"
                                    step="0.5" min="0.5" max="100"
                                    class="input input-sm input-bordered rounded-lg w-20 text-center font-bold" />
                            </div>

                            <button wire:click="removeQuestion({{ $qi }})"
                                wire:confirm="{{ __('Remove this question?') }}"
                                class="btn btn-ghost btn-sm btn-circle text-error hover:bg-error/10">
                                <x-icon name="o-trash" class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            {{-- Question Text --}}
                            <div>
                                <label class="text-xs font-bold uppercase text-base-content/40 mb-1 block">{{ __('Question *') }}</label>
                                <textarea wire:model="questions.{{ $qi }}.question_text"
                                    rows="2"
                                    placeholder="{{ __('Enter your question here...') }}"
                                    class="textarea textarea-bordered w-full rounded-xl resize-none text-sm"></textarea>
                            </div>

                            {{-- Options (not for short_text) --}}
                            @if($question['type'] !== 'short_text')
                                <div>
                                    <label class="text-xs font-bold uppercase text-base-content/40 mb-2 block">
                                        @if($question['type'] === 'multi_select')
                                            {{ __('Answer Options') }} <span class="text-primary">({{ __('check all correct') }})</span>
                                        @else
                                            {{ __('Answer Options') }} <span class="text-primary">({{ __('click to mark correct') }})</span>
                                        @endif
                                    </label>

                                    <div class="space-y-2">
                                        @foreach($question['options'] as $oi => $option)
                                            <div class="flex items-center gap-2" wire:key="opt-{{ $qi }}-{{ $oi }}">
                                                {{-- Correct toggle --}}
                                                <button wire:click="setCorrectOption({{ $qi }}, {{ $oi }})"
                                                    class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center transition-all
                                                    {{ $option['is_correct']
                                                        ? 'bg-success text-success-content shadow-lg shadow-success/30'
                                                        : 'bg-base-200 text-base-content/30 hover:bg-base-300' }}">
                                                    <x-icon name="{{ $option['is_correct'] ? 's-check-circle' : 'o-circle-stack' }}" class="w-4 h-4" />
                                                </button>

                                                <input type="text"
                                                    wire:model="questions.{{ $qi }}.options.{{ $oi }}.option_text"
                                                    placeholder="{{ __('Option :num...', ['num' => $oi + 1]) }}"
                                                    class="input input-sm input-bordered flex-1 rounded-lg text-sm
                                                    {{ $option['is_correct'] ? 'border-success/50 bg-success/5' : '' }}"
                                                    {{ $question['type'] === 'true_false' ? 'readonly' : '' }} />

                                                @if($question['type'] !== 'true_false' && count($question['options']) > 2)
                                                    <button wire:click="removeOption({{ $qi }}, {{ $oi }})"
                                                        class="btn btn-ghost btn-xs btn-circle text-error opacity-50 hover:opacity-100">
                                                        <x-icon name="o-x-mark" class="w-3 h-3" />
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($question['type'] !== 'true_false' && count($question['options']) < 8)
                                        <button wire:click="addOption({{ $qi }})"
                                            class="mt-2 btn btn-ghost btn-xs gap-1 text-primary">
                                            <x-icon name="o-plus" class="w-3 h-3" /> {{ __('Add Option') }}
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase text-base-content/40 mb-1 block">{{ __('Ideal / Correct Answer') }}</label>
                                    <textarea wire:model="questions.{{ $qi }}.ideal_answer"
                                        rows="2"
                                        placeholder="{{ __('Enter the correct or expected answer here...') }}"
                                        class="textarea textarea-bordered w-full rounded-xl resize-none text-sm"></textarea>
                                    <div class="p-3 rounded-xl bg-info/10 border border-info/20 text-sm text-info flex items-center gap-2">
                                        <x-icon name="o-information-circle" class="w-4 h-4 flex-shrink-0" />
                                        {{ __('Students will type their answer. It will be graded by AI against this ideal answer, and reviewed by admin.') }}
                                    </div>
                                </div>
                            @endif

                            {{-- AI Explanation --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs font-bold uppercase text-base-content/40">
                                        {{ __('Explanation') }} <span class="font-normal text-base-content/30">({{ __('shown after submit') }})</span>
                                    </label>
                                    <button wire:click="generateExplanationForQuestion({{ $qi }})"
                                        wire:loading.attr="disabled"
                                        wire:target="generateExplanationForQuestion({{ $qi }})"
                                        class="btn btn-ghost btn-xs gap-1 text-purple-400 hover:text-purple-300">
                                        <span wire:loading.remove wire:target="generateExplanationForQuestion({{ $qi }})">
                                            <x-icon name="o-sparkles" class="w-3 h-3" /> {{ __('AI Write') }}
                                        </span>
                                        <span wire:loading wire:target="generateExplanationForQuestion({{ $qi }})" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                </div>
                                <textarea wire:model="questions.{{ $qi }}.ai_explanation"
                                    rows="2"
                                    placeholder="{{ __('Explain why the correct answer is correct...') }}"
                                    class="textarea textarea-bordered w-full rounded-xl resize-none text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Floating Save Button --}}
            @if(count($questions) > 0)
                <div class="sticky bottom-6 flex justify-center mt-6">
                    <x-button label="{{ __('Save All Questions') }}" icon="o-check" class="btn-success shadow-xl shadow-success/30 px-8" wire:click="saveQuestions" spinner="saveQuestions" />
                </div>
            @endif
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- QUIZ LIST MODE                                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @else
        <x-header :title="__('Quiz Management')" :subtitle="__('Create, manage, and monitor quizzes')" separator>
            <x-slot:actions>
                <x-input wire:model.live.debounce="search" icon="o-magnifying-glass" placeholder="{{ __('Search quizzes...') }}" class="input-bordered rounded-xl w-64" />

                <select wire:model.live="statusFilter" class="select select-bordered rounded-xl text-sm">
                    <option value="all">{{ __('All Statuses') }}</option>
                    <option value="draft">{{ __('Draft') }}</option>
                    <option value="published">{{ __('Published') }}</option>
                    <option value="live">{{ __('Live') }}</option>
                    <option value="closed">{{ __('Closed') }}</option>
                </select>

                <select wire:model.live="modeFilter" class="select select-bordered rounded-xl text-sm">
                    <option value="all">{{ __('All Modes') }}</option>
                    <option value="async">{{ __('Async') }}</option>
                    <option value="live">{{ __('Live') }}</option>
                </select>

                <x-button icon="o-plus" label="{{ __('New Quiz') }}" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openQuizModal()" />
            </x-slot:actions>
        </x-header>

        {{-- Quiz Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($this->quizzes as $quiz)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm flex flex-col overflow-hidden group"
                    wire:key="quiz-{{ $quiz->id }}">

                    {{-- Top color bar by status --}}
                    <div class="h-1.5 w-full
                        {{ $quiz->status === 'published' ? 'bg-gradient-to-r from-success to-emerald-400' :
                           ($quiz->status === 'live' ? 'bg-gradient-to-r from-error to-orange-400 animate-pulse' :
                           ($quiz->status === 'closed' ? 'bg-base-300' : 'bg-gradient-to-r from-base-300 to-base-200')) }}">
                    </div>

                    <div class="p-5 flex-grow flex flex-col">
                        {{-- Header: title + badges --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <h3 class="font-bold text-base leading-tight line-clamp-2">{{ $quiz->title }}</h3>
                            <div class="flex flex-col gap-1 items-end flex-shrink-0">
                                {{-- Status badge --}}
                                <span class="badge border-none text-xs font-bold
                                    {{ $quiz->status === 'published' ? 'badge-success' :
                                       ($quiz->status === 'live' ? 'badge-error' :
                                       ($quiz->status === 'closed' ? 'badge-ghost' : 'badge-warning')) }}">
                                    {{ $quiz->status === 'live' ? __('⚡ LIVE') : __(Str::upper($quiz->status)) }}
                                </span>
                                {{-- Mode badge --}}
                                <span class="badge badge-ghost border-none text-[10px]">
                                    {{ $quiz->mode === 'live' ? __('🎮 Realtime') : __('📝 Async') }}
                                </span>
                            </div>
                        </div>

                        {{-- Linked to --}}
                        @if($quiz->quizzable)
                            <div class="flex items-center gap-1.5 text-xs text-base-content/50 mb-3">
                                <x-icon name="{{ $quiz->quizzable instanceof \App\Models\Halaqah ? 'o-book-open' : 'o-rectangle-stack' }}" class="w-3 h-3" />
                                <span>
                                    {{ $quiz->quizzable instanceof \App\Models\Halaqah ? __('Session') : __('Series') }}:
                                    {{ Str::limit($quiz->quizzable->title, 30) }}
                                </span>
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-xs text-base-content/50 mb-3">
                                <x-icon name="o-globe-alt" class="w-3 h-3" />
                                <span>{{ __('General / Standalone') }}</span>
                            </div>
                        @endif

                        {{-- Stats row --}}
                        <div class="flex gap-4 text-xs text-base-content/50 mb-4">
                            <div class="flex items-center gap-1">
                                <x-icon name="o-question-mark-circle" class="w-3.5 h-3.5" />
                                {{ $quiz->questions()->count() }} {{ __('Qs') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <x-icon name="o-users" class="w-3.5 h-3.5" />
                                {{ $quiz->attempts()->count() }} {{ __('attempts') }}
                            </div>
                            @if($quiz->time_limit_minutes)
                                <div class="flex items-center gap-1">
                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                    {{ $quiz->time_limit_minutes }}m
                                </div>
                            @endif
                            @if($quiz->negative_marking)
                                <div class="flex items-center gap-1 text-error">
                                    <x-icon name="o-minus-circle" class="w-3.5 h-3.5" />
                                    -{{ $quiz->negative_mark_value }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-auto pt-3 border-t border-base-content/5 flex items-center gap-1 justify-between">
                            @can('quiz.manage')
                                @if($quiz->mode === 'live' && in_array($quiz->status, ['published', 'live', 'closed']))
                                    <x-button label="{{ __('Host Panel') }}" icon="o-signal" class="btn-primary btn-sm flex-1"
                                        :href="route('app.quiz.live.host', $quiz)" wire:navigate />
                                @else
                                    <x-button label="{{ __('Build Questions') }}" icon="o-pencil-square" class="btn-primary btn-sm flex-1"
                                        wire:click="openBuilder({{ $quiz->id }})" />
                                @endif
                            @else
                                @if($quiz->isAvailable() || $quiz->status === 'live' || $quiz->status === 'closed')
                                    @if($quiz->mode === 'live')
                                        <x-button label="{{ $quiz->status === 'closed' ? __('Results') : __('Join Live Quiz') }}" icon="{{ $quiz->status === 'closed' ? 'o-clipboard-document-check' : 'o-signal' }}" class="btn-primary btn-sm flex-1 {{ $quiz->status === 'live' ? 'animate-pulse' : '' }}"
                                            :href="route('web.quiz.live', $quiz)" wire:navigate />
                                    @else
                                        <x-button label="{{ $quiz->status === 'closed' ? __('Results') : __('Take Quiz') }}" icon="{{ $quiz->status === 'closed' ? 'o-clipboard-document-check' : 'o-play' }}" class="btn-success btn-sm flex-1"
                                            :href="route('web.quiz.take', $quiz)" wire:navigate />
                                    @endif
                                @else
                                    <span class="btn btn-ghost btn-sm flex-1 btn-disabled">{{ __(ucfirst($quiz->status)) }}</span>
                                @endif
                            @endcan

                            <div class="flex gap-1">
                                <x-button icon="o-cog-6-tooth" class="btn-ghost btn-sm btn-circle"
                                    wire:click="openQuizModal({{ $quiz->id }})" tooltip="{{ __('Settings') }}" />

                                @if($quiz->status === 'draft')
                                    <x-button icon="o-arrow-up-circle" class="btn-ghost btn-sm btn-circle text-success"
                                        wire:click="publishQuiz({{ $quiz->id }})" tooltip="{{ __('Publish') }}" />
                                @endif

                                @if(in_array($quiz->status, ['published', 'live']))
                                    <x-button icon="o-x-circle" class="btn-ghost btn-sm btn-circle text-warning"
                                        wire:click="closeQuiz({{ $quiz->id }})" tooltip="{{ __('Close') }}" />
                                @endif

                                <x-button icon="o-trash" class="btn-ghost btn-sm btn-circle text-error"
                                    wire:click="deleteQuiz({{ $quiz->id }})"
                                    wire:confirm="{{ __('Delete this quiz and all its questions?') }}" tooltip="{{ __('Delete') }}" />
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-24 bg-base-100 rounded-2xl border-2 border-dashed border-base-content/10">
                    <x-icon name="o-question-mark-circle" class="w-16 h-16 text-base-content/20 mb-4" />
                    <h3 class="font-bold text-lg text-base-content/60">{{ __('No quizzes found') }}</h3>
                    <p class="text-sm text-base-content/40 mb-6">{{ __('Create your first quiz to get started.') }}</p>
                    <x-button label="{{ __('Create Quiz') }}" icon="o-plus" class="btn-primary" wire:click="openQuizModal()" />
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $this->quizzes->links() }}</div>

        {{-- ─── Quiz Settings Modal ─────────────────────────────────────────── --}}
        <x-modal wire:model="quizModal" :title="$editingQuizId ? __('Edit Quiz Settings') : __('Create New Quiz')"
            class="backdrop-blur-sm" box-class="w-full max-w-3xl">

            <div class="space-y-5">
                {{-- Row 1: Title & Mode --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-input wire:model="title" label="{{ __('Quiz Title *') }}" placeholder="{{ __('e.g. Post-Session Quiz on Tawheed') }}" class="input-bordered rounded-xl" />
                    </div>
                    <x-select wire:model.live="mode" label="{{ __('Mode *') }}"
                        :options="[['id'=>'async','name'=>__('📝 Async (Self-paced)')],['id'=>'live','name'=>__('⚡ Live (Real-time)')]]"
                        class="select-bordered rounded-xl" />
                </div>

                {{-- Row 2: Linked To --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-select wire:model.live="quizzable_type" label="{{ __('Linked To') }}"
                        :options="[['id'=>'','name'=>__('General (Standalone)')],['id'=>'halaqah','name'=>__('A Halaqah Session')],['id'=>'series','name'=>__('A Course/Series')]]"
                        class="select-bordered rounded-xl" />

                    @if($quizzable_type === 'halaqah')
                        <div class="md:col-span-2">
                            <x-select wire:model="quizzable_id" label="{{ __('Select Halaqah *') }}"
                                :options="$this->halaqahs" option-value="id" option-label="title"
                                placeholder="{{ __('Choose a session...') }}"
                                class="select-bordered rounded-xl" />
                        </div>
                    @elseif($quizzable_type === 'series')
                        <div class="md:col-span-2">
                            <x-select wire:model="quizzable_id" label="{{ __('Select Series *') }}"
                                :options="$this->series" option-value="id" option-label="title"
                                placeholder="{{ __('Choose a series...') }}"
                                class="select-bordered rounded-xl" />
                        </div>
                    @endif
                </div>

                <x-textarea wire:model="description" label="{{ __('Description (optional)') }}" rows="2" class="textarea-bordered rounded-xl" />

                {{-- Timing --}}
                <div class="p-4 bg-base-200/50 rounded-xl border border-base-content/10 space-y-3">
                    <h4 class="font-bold text-sm flex items-center gap-2"><x-icon name="o-clock" class="w-4 h-4 text-primary" /> {{ __('Timing') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input wire:model="time_limit_minutes" type="number" label="{{ __('Time Limit (minutes)') }}" placeholder="{{ __('Leave blank = no limit') }}" class="input-bordered rounded-xl" />
                        <x-input wire:model="available_from" type="datetime-local" label="{{ __('Available From') }}" class="input-bordered rounded-xl" />
                        <x-input wire:model="available_until" type="datetime-local" label="{{ __('Available Until') }}" class="input-bordered rounded-xl" />
                    </div>
                </div>

                {{-- Scoring --}}
                <div class="p-4 bg-base-200/50 rounded-xl border border-base-content/10 space-y-3">
                    <h4 class="font-bold text-sm flex items-center gap-2"><x-icon name="o-star" class="w-4 h-4 text-warning" /> {{ __('Scoring & Pass Mark') }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <x-input wire:model="pass_mark_percent" type="number" label="{{ __('Pass Mark (%)') }}" placeholder="e.g. 60" class="input-bordered rounded-xl" />
                        <x-input wire:model="points_on_pass" type="number" label="{{ __('Points on Pass') }}" class="input-bordered rounded-xl" />
                        <div>
                            <label class="label"><span class="label-text text-xs font-bold">{{ __('Negative Marking') }}</span></label>
                            <input type="checkbox" wire:model.live="negative_marking" class="toggle toggle-error" />
                        </div>
                        @if($negative_marking)
                            <x-input wire:model="negative_mark_value" type="number" step="0.05" min="0" max="1" label="{{ __('Deduction (fraction)') }}" class="input-bordered rounded-xl" />
                        @endif
                    </div>
                    <x-input wire:model="bonus_points_for_rank" label="{{ __('Rank Bonus Points (JSON)') }}" placeholder='{"1": 50, "2": 30, "3": 10}' class="input-bordered rounded-xl font-mono text-xs" />
                </div>

                {{-- Behaviour --}}
                <div class="p-4 bg-base-200/50 rounded-xl border border-base-content/10">
                    <h4 class="font-bold text-sm mb-3 flex items-center gap-2"><x-icon name="o-cog-6-tooth" class="w-4 h-4 text-secondary" /> {{ __('Behaviour') }}</h4>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="shuffle_questions" class="checkbox checkbox-primary" />
                            <span class="text-sm">{{ __('Shuffle questions') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="shuffle_options" class="checkbox checkbox-primary" />
                            <span class="text-sm">{{ __('Shuffle options') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="show_answers_after" class="checkbox checkbox-primary" />
                            <span class="text-sm">{{ __('Show correct answers after submit') }}</span>
                        </label>
                    </div>
                </div>

                {{-- Status --}}
                <x-select wire:model="status" label="{{ __('Status *') }}"
                    :options="[['id'=>'draft','name'=>__('Draft')],['id'=>'published','name'=>__('Published')],['id'=>'live','name'=>__('Live')],['id'=>'closed','name'=>__('Closed')]]"
                    class="select-bordered rounded-xl" />
            </div>

            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" wire:click="$set('quizModal', false)" class="btn-ghost" />
                <x-button label="{{ $editingQuizId ? __('Update Quiz') : __('Create Quiz') }}" icon="o-check"
                    wire:click="saveQuiz" class="btn-primary" spinner="saveQuiz" />
            </x-slot:actions>
        </x-modal>
    @endif

    {{-- AI QUESTION GENERATION MODAL --}}
    <x-modal wire:model="aiModal" :title="__('AI Question Generator')" class="backdrop-blur-sm" box-class="w-full max-w-3xl">
        <div class="space-y-5">
            {{-- Provider & Model --}}
            <div class="p-4 rounded-xl bg-purple-500/5 border border-purple-500/20">
                <h4 class="font-bold text-sm text-purple-400 mb-3 flex items-center gap-2">
                    <x-icon name="o-cpu-chip" class="w-4 h-4" /> {{ __('AI Provider & Model') }}
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <x-select wire:model.live="aiProvider" label="{{ __('Provider') }}"
                        :options="$this->aiProviderOptions"
                        placeholder="{{ __('Select provider...') }}"
                        class="select-bordered rounded-xl" />
                    <x-input wire:model="aiModel" label="{{ __('Model (optional)') }}" placeholder="{{ __('e.g. gemini-2.0-flash') }}"
                        class="input-bordered rounded-xl" />
                </div>
                <p class="text-xs text-base-content/40 mt-2">
                    {{ __('Leave model blank to use provider default. Currently:') }} <strong>{{ $aiProvider ?: config('ai.default') }}</strong>
                </p>
            </div>
            {{-- Source Type --}}
            <div class="p-4 rounded-xl bg-base-200/50 border border-base-content/10">
                <h4 class="font-bold text-sm mb-3 flex items-center gap-2">
                    <x-icon name="o-document-text" class="w-4 h-4 text-primary" /> {{ __('Question Source') }}
                </h4>
                <div class="flex gap-1 mb-4 p-1 bg-base-300/50 rounded-xl w-fit">
                    <button wire:click="$set('aiSourceType', 'text')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $aiSourceType === 'text' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/50 hover:text-base-content' }}">
                        {{ __('Free Text') }}
                    </button>
                    <button wire:click="$set('aiSourceType', 'book')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $aiSourceType === 'book' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/50 hover:text-base-content' }}">
                        {{ __('From Book') }}
                    </button>
                    <button wire:click="$set('aiSourceType', 'halaqah')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $aiSourceType === 'halaqah' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/50 hover:text-base-content' }}">
                        {{ __('From Session') }}
                    </button>
                </div>
                @if($aiSourceType === 'text')
                    <x-textarea wire:model="aiSourceText" label="{{ __('Topic / Content *') }}" rows="5"
                        placeholder="{{ __('Paste the topic, passage, or content to generate questions from...') }}"
                        class="textarea-bordered rounded-xl" />
                @elseif($aiSourceType === 'book')
                    <x-select wire:model="aiSourceBookId" label="{{ __('Select Book *') }}"
                        :options="$this->books" option-value="id" option-label="title"
                        placeholder="{{ __('Choose a book from the library...') }}"
                        class="select-bordered rounded-xl" />
                    <p class="text-xs text-base-content/40 mt-2">{{ __('AI uses the book title, author, description, and category as context.') }}</p>
                @elseif($aiSourceType === 'halaqah')
                    <x-select wire:model="aiSourceHalaqahId" label="{{ __('Select Session *') }}"
                        :options="$this->halaqahs" option-value="id" option-label="title"
                        placeholder="{{ __('Choose a halaqah session...') }}"
                        class="select-bordered rounded-xl" />
                    <p class="text-xs text-base-content/40 mt-2">{{ __('AI uses the session title, speaker, description, and series as context.') }}</p>
                @endif
            </div>
            {{-- Generation params --}}
            <div class="grid grid-cols-3 gap-4">
                <x-select wire:model="aiType" label="{{ __('Question Type *') }}"
                    :options="[['id'=>'mcq','name'=>__('MCQ')],['id'=>'true_false','name'=>__('True / False')],['id'=>'multi_select','name'=>__('Multi-Select')],['id'=>'short_text','name'=>__('Short Text')]]"
                    class="select-bordered rounded-xl" />
                <x-select wire:model="aiDifficulty" label="{{ __('Difficulty *') }}"
                    :options="[['id'=>'easy','name'=>__('Easy (0.5 marks)')],['id'=>'medium','name'=>__('Medium (1.0 mark)')],['id'=>'hard','name'=>__('Hard (2.0 marks)')]]"
                    class="select-bordered rounded-xl" />
                <x-input wire:model="aiCount" type="number" min="1" max="20" label="{{ __('No. of Questions *') }}"
                    class="input-bordered rounded-xl" />
            </div>
            {{-- Generated Questions Preview --}}
            @if(!empty($aiGeneratedQuestions))
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm flex items-center gap-2">
                            <x-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            {{ __('count question(s) ready to import', ['count' => count($aiGeneratedQuestions)]) }}
                        </h4>
                        <x-button label="{{ __('Import All') }}" icon="o-arrow-down-tray" class="btn-success btn-sm"
                            wire:click="importAllAiQuestions" />
                    </div>
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        @foreach($aiGeneratedQuestions as $ai => $aiQ)
                            <div class="bg-base-100 rounded-xl p-4 border border-base-content/10 flex gap-3" wire:key="aiq-{{ $ai }}">
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="badge badge-ghost text-[10px]">{{ strtoupper(str_replace('_', ' ', $aiQ['type'])) }}</span>
                                        <span class="badge badge-outline text-[10px]">{{ $aiQ['marks'] }} {{ __('marks') }}</span>
                                    </div>
                                    <p class="text-sm font-medium mb-2">{{ $aiQ['question_text'] }}</p>
                                    @if(!empty($aiQ['options']))
                                        <div class="flex flex-wrap gap-1 mb-2">
                                            @foreach($aiQ['options'] as $aiOpt)
                                                <span class="text-xs px-2 py-0.5 rounded-full {{ $aiOpt['is_correct'] ? 'bg-success/20 text-success font-bold' : 'bg-base-200 text-base-content/50' }}">
                                                    {{ $aiOpt['is_correct'] ? '✓ ' : '' }}{{ Str::limit($aiOpt['option_text'], 35) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($aiQ['type'] === 'short_text' && !empty($aiQ['ideal_answer']))
                                        <div class="mb-2 p-2 bg-success/5 border border-success/10 rounded-lg">
                                            <p class="text-[10px] font-bold text-success uppercase mb-0.5">{{ __('Ideal Answer') }}</p>
                                            <p class="text-xs font-medium">{{ $aiQ['ideal_answer'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($aiQ['explanation']))
                                        <p class="text-xs text-base-content/40 italic line-clamp-2">{{ $aiQ['explanation'] }}</p>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    <x-button icon="o-plus" class="btn-primary btn-sm btn-circle"
                                        wire:click="importAiQuestion({{ $ai }})" tooltip="{{ __('Add to builder') }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('aiModal', false)" class="btn-ghost" />
            <x-button wire:click="runAiGeneration" spinner="runAiGeneration"
                class="bg-gradient-to-r from-violet-600 to-purple-600 text-white border-none shadow-lg shadow-purple-500/30">
                <x-icon name="o-sparkles" class="w-4 h-4" />
                <span wire:loading.remove wire:target="runAiGeneration">{{ __('Generate Questions') }}</span>
                <span wire:loading wire:target="runAiGeneration">{{ __('Generating...') }}</span>
            </x-button>
        </x-slot:actions>
    </x-modal>

</div>
