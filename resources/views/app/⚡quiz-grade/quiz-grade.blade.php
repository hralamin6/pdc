<div>
    <x-header :title="__('Grade Short Answers')" :subtitle="__('Review AI-graded short text answers and confirm them.')" separator>
        <x-slot:actions>
            <x-select wire:model.live="quizId" :options="$this->quizzes" option-label="title" option-value="id" placeholder="{{ __('All Quizzes') }}" class="select-bordered select-sm max-w-xs" />
            
            <div class="join">
                <button wire:click="$set('filterStatus', 'pending')" class="btn btn-sm join-item {{ $filterStatus === 'pending' ? 'btn-primary' : '' }}">{{ __('Pending') }}</button>
                <button wire:click="$set('filterStatus', 'graded')" class="btn btn-sm join-item {{ $filterStatus === 'graded' ? 'btn-primary' : '' }}">{{ __('Graded') }}</button>
            </div>

            @if($filterStatus === 'pending')
                <x-button label="{{ __('Evaluate Pending with AI') }}" icon="o-sparkles" class="btn-primary btn-sm ml-2" wire:click="evaluatePendingWithAi" spinner tooltip="{{ __('Run AI grading on all un-evaluated answers') }}" />
                <x-button label="{{ __('Re-evaluate All') }}" icon="o-arrow-path" class="btn-warning btn-sm ml-2" wire:click="reevaluateAllWithAi" wire:confirm="{{ __('Re-run AI evaluation for ALL pending answers? This may take some time.') }}" spinner tooltip="{{ __('Force AI to re-evaluate all pending answers') }}" />
                <x-button label="{{ __('Auto-Confirm High Confidence') }}" icon="o-check-badge" class="btn-success btn-sm ml-2" wire:click="autoConfirmHighConfidence" spinner tooltip="{{ __('Confirms all pending answers with AI Grade >= 85%') }}" />
            @endif
        </x-slot:actions>
    </x-header>

    @if($this->answers->isEmpty())
        <div class="py-12 text-center text-base-content/50">
            <x-icon name="o-check-circle" class="w-12 h-12 mx-auto mb-3 opacity-20" />
            <p>{{ __('No :status answers found.', ['status' => __($filterStatus)]) }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($this->answers as $ans)
                <div class="bg-base-100 rounded-xl border border-base-content/10 p-5 shadow-sm" wire:key="ans-{{ $ans->id }}">
                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="badge badge-primary badge-sm font-bold">{{ $ans->attempt->user->name }}</span>
                                <span class="text-xs text-base-content/50">{{ __('in') }} {{ $ans->attempt->quiz->title }}</span>
                                <span class="text-xs text-base-content/50 flex items-center gap-1">
                                    <x-icon name="o-clock" class="w-3 h-3" /> {{ $ans->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="font-bold text-sm">{{ $ans->question->question_text }}</p>
                            <p class="text-xs text-base-content/50 mt-1">{{ $ans->question->marks }} {{ __('max marks') }}</p>
                        </div>
                        
                        <div class="flex items-center gap-2 bg-base-200/50 p-2 rounded-lg border border-base-content/10">
                            <div class="text-xs text-right mr-2">
                                <span class="block text-base-content/50">{{ __('AI Grade') }}</span>
                                <span class="font-black {{ $ans->ai_grade >= 0.85 ? 'text-success' : ($ans->ai_grade <= 0.3 ? 'text-error' : 'text-warning') }}">
                                    {{ !is_null($ans->ai_grade) ? round($ans->ai_grade * 100) . '%' : 'N/A' }}
                                </span>
                            </div>
                            
                            <div class="border-l border-base-content/10 pl-3">
                                <label class="text-[10px] font-bold text-base-content/50 block mb-1">{{ __('Admin Grade (0-1)') }}</label>
                                <div class="flex items-center gap-1">
                                    <x-input type="number" step="0.1" min="0" max="1" wire:model="adminGrades.{{ $ans->id }}" class="input-sm w-20 text-center font-bold" />
                                    <x-button icon="o-check" class="btn-primary btn-sm" wire:click="saveGrade({{ $ans->id }})" spinner="saveGrade({{ $ans->id }})" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Answer & Reasoning --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-success/5 rounded-lg p-3 border border-success/10">
                            <p class="text-xs font-bold text-success uppercase mb-2">{{ __('Ideal Answer') }}</p>
                            <p class="text-sm">{{ $ans->question->ideal_answer ?? __('No ideal answer set.') }}</p>
                        </div>

                        <div class="bg-base-200/50 rounded-lg p-3 border border-base-content/5">
                            <p class="text-xs font-bold text-base-content/40 uppercase mb-2">{{ __('Student\'s Answer') }}</p>
                            <p class="text-sm">{{ $ans->text_answer ?? __('No answer provided.') }}</p>
                        </div>
                        
                        <div class="bg-info/5 rounded-lg p-3 border border-info/10">
                            <p class="text-xs font-bold text-info uppercase mb-2 flex items-center gap-1">
                                <x-icon name="o-sparkles" class="w-3 h-3" /> {{ __('AI Reasoning') }}
                            </p>
                            <p class="text-sm text-base-content/70 italic">{{ $ans->ai_grade_reason ?? __('No AI reasoning available.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $this->answers->links() }}
        </div>
    @endif
</div>
