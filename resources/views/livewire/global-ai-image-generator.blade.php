<div>
    <x-drawer wire:model="show" class="w-11/12 lg:w-[450px] !z-[99999]" right>
        
        {{-- Header Area with Glassmorphic Gradient --}}
        <div class="relative -mt-6 -mx-6 p-6 mb-6 overflow-hidden rounded-b-3xl">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-pink-500/20 dark:from-indigo-900/40 dark:via-purple-900/40 dark:to-pink-900/40 backdrop-blur-3xl"></div>
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <x-icon name="s-sparkles" class="w-6 h-6 text-primary" />
                        @if($contextTitle)
                            {{ __('AI Studio') }} - <span class="text-primary truncate max-w-[200px] inline-block" title="{{ $contextTitle }}">{{ $contextTitle }}</span>
                        @else
                            {{ __('AI Studio') }}
                        @endif
                    </h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                        {{ __('Generate Magical Images') }}
                    </p>
                </div>
                <x-button icon="o-x-mark" class="btn-ghost btn-circle btn-sm text-slate-500" wire:click="$set('show', false)" />
            </div>
        </div>

        @if($generatedImageUrl)
            {{-- Image Preview Mode --}}
            <div class="flex flex-col items-center justify-center space-y-6">
                <div class="relative w-full rounded-2xl overflow-hidden shadow-2xl ring-4 ring-primary/20">
                    <img src="{{ $generatedImageUrl }}" class="w-full h-auto object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <p class="text-sm font-bold opacity-90 truncate">{{ $prompt }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 w-full">
                    <x-button label="{{ __('Discard') }}" icon="o-trash" wire:click="discardImage" class="btn-error btn-outline flex-1 rounded-xl font-bold" />
                    <x-button label="{{ __('Use this Image') }}" icon="o-check-circle" wire:click="useImage" class="btn-success flex-1 rounded-xl font-bold text-white shadow-lg shadow-success/30" />
                </div>
            </div>
        @else
            {{-- Generation Form Mode --}}
            <div class="space-y-6">
                {{-- Prompt Field --}}
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <x-icon name="o-pencil-square" class="w-4 h-4 text-primary" />
                        {{ __('Your Imagination (Prompt)') }}
                    </label>
                    <textarea 
                        wire:model.live.debounce.500ms="prompt" 
                        rows="4" 
                        class="textarea textarea-bordered w-full rounded-2xl focus:ring-2 focus:ring-primary/50 bg-slate-50 dark:bg-slate-900 shadow-inner" 
                        placeholder="{{ __('E.g. A futuristic Islamic library floating in space, high detail, unreal engine 5, beautiful lighting...') }}"></textarea>
                </div>

                <hr class="border-slate-100 dark:border-slate-800" />

                {{-- Settings Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Aspect Ratio / Size --}}
                    <div class="space-y-2 col-span-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Aspect Ratio') }}</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button wire:click="$set('size', '1:1')" class="flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all {{ $size === '1:1' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                <div class="w-6 h-6 border-2 border-current rounded-sm mb-1"></div>
                                <span class="text-[10px] font-black uppercase">{{ __('Square') }}</span>
                            </button>
                            <button wire:click="$set('size', '3:2')" class="flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all {{ $size === '3:2' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                <div class="w-8 h-6 border-2 border-current rounded-sm mb-1"></div>
                                <span class="text-[10px] font-black uppercase">{{ __('Landscape') }}</span>
                            </button>
                            <button wire:click="$set('size', '2:3')" class="flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all {{ $size === '2:3' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                <div class="w-6 h-8 border-2 border-current rounded-sm mb-1"></div>
                                <span class="text-[10px] font-black uppercase">{{ __('Portrait') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Provider --}}
                    <x-select 
                        label="{{ __('AI Provider') }}" 
                        wire:model.live="provider" 
                        :options="$this->providers" 
                        class="rounded-xl"
                    />

                    {{-- Model --}}
                    <div class="space-y-2">
                        <x-select 
                            label="{{ __('Model') }}" 
                            wire:model.live="model" 
                            :options="$this->availableModels" 
                            class="rounded-xl"
                        />
                        
                        @if($model === 'custom')
                            <x-input wire:model="customModel" placeholder="{{ __('Enter exact model name') }}" class="rounded-xl" />
                        @endif
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <div class="flex items-center gap-3 w-full justify-end mt-6">
                    <x-button label="{{ __('Cancel') }}" wire:click="$set('show', false)" class="btn-ghost rounded-xl font-bold" />
                    <x-button 
                        label="{{ __('Generate Image') }}" 
                        icon="s-sparkles" 
                        wire:click="generate" 
                        spinner="generate" 
                        class="btn-primary rounded-xl font-bold shadow-lg shadow-primary/30 flex-1" 
                        :disabled="$isGenerating || strlen(trim($prompt ?? '')) < 3"
                    />
                </div>
            </x-slot:actions>
        @endif

        {{-- Loading Overlay overlay --}}
        @if($isGenerating)
            <div class="absolute inset-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center rounded-l-2xl">
                <div class="relative w-24 h-24 mb-4">
                    <div class="absolute inset-0 rounded-full border-t-4 border-primary animate-spin"></div>
                    <div class="absolute inset-2 rounded-full border-r-4 border-secondary animate-[spin_1.5s_linear_infinite]"></div>
                    <div class="absolute inset-4 rounded-full border-b-4 border-accent animate-[spin_2s_linear_infinite]"></div>
                    <x-icon name="s-sparkles" class="absolute inset-0 m-auto w-8 h-8 text-primary animate-pulse" />
                </div>
                <h3 class="text-lg font-black bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent animate-pulse">{{ __('Dreaming up your image...') }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-2">{{ __('This magical process takes 10-15 seconds.') }}</p>
            </div>
        @endif
    </x-drawer>
</div>
