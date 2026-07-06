{{-- Settings Modal --}}
@unblaze
@if($showSettingsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="$set('showSettingsModal', false)"></div>

            <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">AI Settings</h3>
                        <button wire:click="$set('showSettingsModal', false)" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="updateSettings" class="space-y-4">
                        {{-- AI Provider --}}
                        <div>
                            <x-choices-offline
                                label="AI Provider"
                                wire:model.live="aiProvider"
                                :options="$this->getAvailableProviders()"
                                option-value="id"
                                option-label="name"
                                placeholder="Select AI Provider..."
                                single
                                searchable
                            />
                        </div>

                        {{-- Model --}}
                        <div>
                            <x-choices-offline
                                label="Model"
                                wire:model.live="model"
                                :options="$this->getAvailableModels()"
                                option-value="id"
                                option-label="name"
                                placeholder="Search models..."
                                single
                                clearable
                                searchable
                            />
                        </div>

                        {{-- System Prompt --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                System Prompt
                            </label>
                            <textarea
                                wire:model="systemPrompt"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="You are a helpful AI assistant..."
                            ></textarea>
                        </div>

                        {{-- Temperature --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Temperature: {{ $temperature }}
                            </label>
                            <input
                                type="range"
                                wire:model.live="temperature"
                                min="0"
                                max="2"
                                step="0.1"
                                class="w-full"
                            />
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>Precise</span>
                                <span>Balanced</span>
                                <span>Creative</span>
                            </div>
                        </div>

                        {{-- Max Tokens --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Max Tokens: {{ $maxTokens }}
                            </label>
                            <input
                                type="range"
                                wire:model.live="maxTokens"
                                min="100"
                                max="4000"
                                step="100"
                                class="w-full"
                            />
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-600">
                            <button
                                type="button"
                                wire:click="$set('showSettingsModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Image Generator Modal --}}
@if($showImageGeneratorModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="$set('showImageGeneratorModal', false)"></div>

            <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Generate Image</h3>
                        <button wire:click="$set('showImageGeneratorModal', false)" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="generateImage" class="space-y-4">
                        {{-- Model Selection --}}
                        <div>
                            <x-choices-offline
                                label="Image Model"
                                wire:model="imageModel"
                                :options="$this->getImageModels()"
                                option-value="id"
                                option-label="name"
                                placeholder="Search image models..."
                                single
                                searchable
                                hint="💡 Flux Schnell and Z-Image Turbo offer best value (5K pollen)"
                            />
                        </div>

                        {{-- Image Prompt --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Image Prompt
                            </label>
                            <textarea
                                wire:model="imagePrompt"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="Describe the image you want to generate..."
                                required
                            ></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Be descriptive for better results. Powered by Pollinations AI.
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-600">
                            <button
                                type="button"
                                wire:click="$set('showImageGeneratorModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-500 hover:bg-purple-600 rounded-lg transition disabled:opacity-50"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="generateImage">Generate Image</span>
                                <span wire:loading wire:target="generateImage">Generating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
