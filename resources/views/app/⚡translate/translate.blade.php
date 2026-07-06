<div class="space-y-6" x-data="{
    addKeyModal: false,
    addLanguageModal: false,
    importModal: false,
    scanModal: false,
    aiTranslateModal: false
}">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Translation Manager</h1>
                <p class="text-sm text-base-content/60">Manage translations for multiple languages</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="addKeyModal = true; $wire.call('openAddKeyModal')" class="btn btn-primary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Key
                </button>
                <button @click="addLanguageModal = true; $wire.call('openAddLanguageModal')" class="btn btn-outline btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                    </svg>
                    Add Language
                </button>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        More
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                        <li><a @click="scanModal = true; $wire.call('openScanModal')">Scan Code</a></li>
                        <li><a @click="importModal = true; $wire.call('openImportModal')">Import</a></li>
                        <li><a @click="aiTranslateModal = true; $wire.call('openAITranslateModal')">AI Translate</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="relative">
            <input
                type="text"
                wire:model.live.debounce="search"
                placeholder="Search translations..."
                class="input input-bordered w-full pl-10"
            />
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </div>
    </div>

    {{-- Language Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        @foreach($statistics as $code => $stat)
            <div class="bg-base-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">{{ $stat['language'] }}</h3>
                    <span class="badge badge-sm">{{ $code }}</span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-bold {{ $stat['percentage'] == 100 ? 'text-success' : ($stat['percentage'] >= 50 ? 'text-warning' : 'text-error') }}">
                            {{ $stat['percentage'] }}%
                        </div>
                        <div class="text-xs text-base-content/60">{{ $stat['translated'] }}/{{ $stat['total_keys'] }}</div>
                    </div>
                    <div class="flex gap-1">
                        <button wire:click="exportLanguage('{{ $code }}')" class="btn btn-ghost btn-xs" title="Export">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </button>
                        @if($code !== 'en')
                            <button wire:click="deleteLanguage('{{ $code }}')" wire:confirm="Delete {{ $stat['language'] }}?" class="btn btn-ghost btn-xs text-error" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-base-200 rounded-lg p-4 mb-6 flex flex-wrap gap-4 items-center">
        <select wire:model.live="selectedLanguage" class="select select-bordered select-sm">
            <option value="">All Languages</option>
            @foreach($languages as $lang)
                <option value="{{ $lang['code'] }}">{{ $lang['name'] }}</option>
            @endforeach
        </select>

        <div class="join">
            <input wire:model.live="viewMode" type="radio" name="viewMode" value="all" class="join-item btn btn-sm" aria-label="All" />
            <input wire:model.live="viewMode" type="radio" name="viewMode" value="missing" class="join-item btn btn-sm" aria-label="Missing" />
            <input wire:model.live="viewMode" type="radio" name="viewMode" value="translated" class="join-item btn btn-sm" aria-label="Translated" />
        </div>

        <select wire:model.live="perPage" class="select select-bordered select-sm">
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
        </select>

        <div class="ml-auto">
            <button wire:click="saveAllTranslations" class="btn btn-success btn-sm gap-2" wire:loading.attr="disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span wire:loading.remove wire:target="saveAllTranslations">Save All</span>
                <span wire:loading wire:target="saveAllTranslations">Saving...</span>
            </button>
        </div>
    </div>

    {{-- Translations List --}}
    <div class="space-y-4">
        @forelse($this->filteredTranslations as $index => $item)
            <div wire:key="item-{{ $item['key'] }}" class="bg-base-100 rounded-lg border border-base-300 hover:shadow-md transition-shadow">
                {{-- Key Header --}}
                <div class="bg-base-200 px-4 py-3 flex items-center justify-between border-b border-base-300">
                    <div class="flex items-center gap-3">
                        <span class="badge badge-ghost">{{ $this->offset() + $index + 1 }}</span>
                        <code class="text-sm font-mono">{{ $item['key'] }}</code>
                    </div>
                    <button wire:click="deleteKey('{{ $item['key'] }}')" wire:confirm="Delete this key?" class="btn btn-ghost btn-xs text-error">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>

                {{-- Translations Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                    @foreach($languages as $lang)
                        @php
                            $value = $item[$lang['code']] ?? '';
                            $isEmpty = empty($value);
                            $isPlaceholder = $value === $item['key'];
                        @endphp
                        <div>
                            <label class="label label-text text-xs font-medium flex items-center gap-2">
                                <span>{{ $lang['name'] }}</span>
                                <span class="badge badge-xs">{{ $lang['code'] }}</span>
                            </label>
                            <x-input
                                wire:model.defer="editableTranslations.{{ $item['key'] }}.{{ $lang['code'] }}"
                                :placeholder="__('Enter :lang translation...', ['lang' => $lang['name']])"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-base-content/20 mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                </svg>
                <p class="text-lg font-medium text-base-content/60">No translations found</p>
                <p class="text-sm text-base-content/40 mb-4">Try adjusting your filters or add a new key</p>
                <button @click="addKeyModal = true; $wire.call('openAddKeyModal')" class="btn btn-primary btn-sm">Add Translation Key</button>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($this->totalFilteredCount > 0)
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-base-200 rounded-lg p-4">
            <div class="text-sm text-base-content/60">
                Showing {{ $this->offset() + 1 }} to {{ min($this->offset() + $this->perPage, $this->totalFilteredCount) }} of {{ $this->totalFilteredCount }} {{ $this->totalFilteredCount === 1 ? 'key' : 'keys' }}
            </div>
            <div class="flex items-center gap-2">
                <div class="join">
                    <button wire:click="previousPage" @if($this->getPage() <= 1) disabled @endif class="join-item btn btn-sm">«</button>
                    <button class="join-item btn btn-sm">Page {{ $this->getPage() }}</button>
                    <button wire:click="nextPage" @if($this->offset() + $this->perPage >= $this->totalFilteredCount) disabled @endif class="join-item btn btn-sm">»</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Key Modal --}}
    <x-modal wire:model="addKeyModal" title="Add Translation Key" class="backdrop-blur" persistent>
        <div class="space-y-4">
            <x-input label="Translation Key" wire:model="newKey" placeholder="e.g., welcome_message" />

            <div class="divider text-sm">Translations</div>

            @foreach($languages as $lang)
                <x-textarea
                    label="{{ $lang['name'] }} ({{ $lang['code'] }})"
                    wire:model="newKeyValues.{{ $lang['code'] }}"
                    rows="2"
                    placeholder="Enter translation..."
                />
            @endforeach
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeAddKeyModal()" />
            <x-button label="Save" class="btn-primary" wire:click="saveNewKey" spinner="saveNewKey" />
        </x-slot:actions>
    </x-modal>

    {{-- Add Language Modal --}}
    <x-modal wire:model="addLanguageModal" title="Add New Language" subtitle="2-letter ISO 639-1 language code" class="backdrop-blur" persistent>
        <div class="space-y-4">
            <x-input
                label="Language Code"
                wire:model="newLanguageCode"
                placeholder="e.g., es, fr, de"
                maxlength="2"
                hint="2-letter language code"
            />

            <x-alert icon="o-information-circle" class="alert-info">
                English translations will be used as placeholders
            </x-alert>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeAddLanguageModal()" />
            <x-button label="Add Language" class="btn-primary" wire:click="saveNewLanguage" spinner="saveNewLanguage" />
        </x-slot:actions>
    </x-modal>

    {{-- Import Modal --}}
    <x-modal wire:model="importModal" title="Import Translations" class="backdrop-blur max-w-2xl" persistent>
        <div class="space-y-4">
            <x-select
                label="Select Language"
                wire:model="importLanguage"
                :options="$languages"
                option-value="code"
                option-label="name"
            />

            <x-textarea
                label="JSON Content"
                wire:model="importJson"
                rows="12"
                class="font-mono text-xs"
                placeholder='{"key": "value"}'
            />

            <x-alert icon="o-exclamation-triangle" class="alert-warning">
                This will overwrite existing translations
            </x-alert>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeImportModal()" />
            <x-button label="Import" class="btn-primary" wire:click="importLanguageFile" spinner="importLanguageFile" />
        </x-slot:actions>
    </x-modal>

    {{-- Scan Modal --}}
    <x-modal wire:model="scanModal" title="Scan Code for Translation Keys" class="backdrop-blur max-w-4xl" persistent>
        <div class="space-y-4">
            <x-alert icon="o-information-circle" class="alert-info">
                Scans for __(), trans(), and lang() functions
            </x-alert>

            <x-button label="Start Scan" class="btn-primary" wire:click="scanForKeys" spinner="scanForKeys" />

            @if(count($scannedKeys) > 0)
                <div class="overflow-x-auto max-h-96 border rounded-lg">
                    <table class="table table-sm">
                        <thead class="bg-base-200 sticky top-0">
                            <tr>
                                <th>#</th>
                                <th>Key</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scannedKeys as $key => $count)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><code class="text-xs">{{ $key }}</code></td>
                                    <td><span class="badge badge-sm">{{ $count }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeScanModal()" />
            @if(count($scannedKeys) > 0)
                <x-button label="Sync Keys" class="btn-primary" wire:click="syncScannedKeys" spinner="syncScannedKeys" />
            @endif
        </x-slot:actions>
    </x-modal>

    {{-- AI Translate Modal --}}
    <x-modal wire:model="aiTranslateModal" title="AI Auto-Translate" subtitle="Translate missing keys using Google Translate" class="backdrop-blur" persistent>
        <div class="space-y-4">
            <x-select
                label="Target Language"
                wire:model="aiTargetLanguage"
                :options="collect($languages)->where('code', '!=', 'en')"
                option-value="code"
                option-label="name"
                placeholder="Choose language"
            />

            <x-alert icon="o-information-circle" class="alert-info">
                Uses Google Translate - No API key required!
            </x-alert>

            <x-alert icon="o-exclamation-triangle" class="alert-warning">
                Translations are processed in batches of 20 to prevent timeouts. This may take a moment.
            </x-alert>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeAITranslateModal()" />
            <x-button label="Translate" class="btn-primary" wire:click="autoTranslate" spinner="autoTranslate" />
        </x-slot:actions>
    </x-modal>

    {{-- Download Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('download-file', (event) => {
                const {content, filename, type} = event[0];
                const blob = new Blob([content], {type});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.click();
                URL.revokeObjectURL(url);
            });
        });
    </script>
</div>

