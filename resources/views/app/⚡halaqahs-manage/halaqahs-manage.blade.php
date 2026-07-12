<div>
    <x-header title="{{ __('Halaqah Management') }}" subtitle="{{ __('Advanced dashboard for Study Circles & Courses') }}" separator>
        <x-slot:actions>
            <x-button icon="o-folder-plus" title="{{ __('New Series') }}" @click="$wire.seriesModal = true" class="btn-outline btn-primary" />
            <x-button icon="o-plus" title="{{ __('New Session') }}" wire:click="createSession" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-tabs wire:model="activeTab" class="mt-4">
        <x-tab name="sessions" label="{{ __('All Sessions') }}" icon="o-calendar-days">
            <x-card class="mt-4 border border-base-200 shadow-sm">
                <x-table :headers="[
                    ['key' => 'title', 'label' => __('Session Info')],
                    ['key' => 'scheduled_at', 'label' => __('Schedule')],
                    ['key' => 'status', 'label' => __('Status')],
                    ['key' => 'rsvp_count', 'label' => __('Attendees')],
                ]" :rows="$halaqahs" with-pagination>
                    @scope('cell_title', $halaqah)
                        <div class="flex flex-col">
                            <span class="font-bold">{{ $halaqah->title }}</span>
                            <span class="text-xs text-base-content/60">{{ $halaqah->series ? $halaqah->series->title : __('Standalone') }}</span>
                        </div>
                    @endscope
                    @scope('cell_scheduled_at', $halaqah)
                        <div class="flex flex-col">
                            <span>{{ $halaqah->scheduled_at->format('M d, Y') }}</span>
                            <span class="text-xs text-base-content/60">{{ $halaqah->scheduled_at->format('g:i A') }} • {{ $halaqah->location }}</span>
                        </div>
                    @endscope
                    @scope('cell_status', $halaqah)
                        @php
                            $badgeClass = match($halaqah->status) {
                                'published' => 'badge-success',
                                'draft' => 'badge-warning',
                                'completed' => 'badge-info',
                                'cancelled' => 'badge-error',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <x-badge :value="ucfirst($halaqah->status)" class="{{ $badgeClass }} badge-sm" />
                    @endscope
                    @scope('cell_rsvp_count', $halaqah)
                        <div class="flex items-center gap-2">
                            <x-progress value="{{ $halaqah->rsvp_count }}" max="{{ $halaqah->max_capacity ?: 100 }}" class="progress-primary w-24" />
                            <span class="text-xs">{{ $halaqah->rsvp_count }} / {{ $halaqah->max_capacity ?: '∞' }}</span>
                        </div>
                    @endscope
                    @scope('actions', $halaqah)
                        <div class="flex gap-2">
                            <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="editSession({{ $halaqah->id }})" />
                        </div>
                    @endscope
                </x-table>
            </x-card>
        </x-tab>
        
        <x-tab name="series" label="{{ __('Courses & Series') }}" icon="o-folder">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                @foreach($seriesList as $series)
                    <x-card class="border border-base-200 hover:shadow-md transition-all">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg">{{ $series->title }}</h3>
                            <x-badge value="{{ ucfirst($series->target_audience_level) }}" class="badge-outline badge-sm" />
                        </div>
                        <p class="text-sm text-base-content/70 line-clamp-2 mb-4">{{ $series->description }}</p>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-primary font-semibold">{{ __(':count Sessions', ['count' => $series->halaqahs_count]) }}</span>
                            <span class="text-base-content/50">{{ __('Status:') }} {{ ucfirst($series->status) }}</span>
                        </div>
                    </x-card>
                @endforeach
            </div>
            <div class="mt-4">{{ $seriesList->links() }}</div>
        </x-tab>
    </x-tabs>

    {{-- Advanced Session Drawer --}}
    <x-drawer wire:model="sessionModal" title="{{ $session_id ? __('Edit Session') : __('Create New Session') }}" right class="lg:w-1/3" separator>
        @if(!$session_id)
            <div class="mb-4">
                <x-button label="{{ __('Generate Details with AI') }}" icon="o-sparkles" class="btn-primary btn-sm btn-block" @click="$wire.aiModal = true" />
            </div>
        @endif
        <x-form wire:submit="saveSession">
            
            <x-tabs selected="basic">
                <x-tab name="basic" label="{{ __('Basic Info') }}" icon="o-information-circle">
                    <div class="space-y-4 pt-4">
                        <x-input label="{{ __('Title') }}" wire:model="title" placeholder="{{ __('e.g. Weekly Reflection') }}" required />
                        <x-input label="{{ __('Topic') }}" wire:model="topic" placeholder="{{ __('e.g. Sabr (Patience)') }}" required />
                        <x-select label="{{ __('Part of Series?') }}" wire:model="series_id" :options="$allSeries" option-value="id" option-label="title" placeholder="{{ __('None (Standalone)') }}" />
                        <x-select label="{{ __('Speaker/Mentor') }}" wire:model="speaker_id" :options="$speakers" option-value="id" option-label="name" placeholder="{{ __('Select a speaker (optional)') }}" />
                        <x-textarea label="{{ __('Description (Optional)') }}" wire:model="description" rows="3" placeholder="{{ __('Brief details about the session...') }}" />
                    </div>
                </x-tab>
                
                <x-tab name="logistics" label="{{ __('Logistics') }}" icon="o-map-pin">
                    <div class="space-y-4 pt-4">
                        <x-datetime label="{{ __('Date & Time') }}" wire:model="scheduled_at" type="datetime-local" required />
                        <x-input label="{{ __('Physical Location') }}" wire:model="location" placeholder="{{ __('e.g. Central Mosque') }}" required />
                        <x-input label="{{ __('Online Meeting Link (Optional)') }}" wire:model="meeting_link" placeholder="{{ __('Zoom/Meet URL') }}" icon="o-link" />
                    </div>
                </x-tab>
                
                <x-tab name="settings" label="{{ __('Settings') }}" icon="o-cog-6-tooth">
                    <div class="space-y-4 pt-4">
                        <x-select label="{{ __('Status') }}" wire:model="status" :options="$statusOptions" option-value="id" option-label="name" required />
                        <x-select label="{{ __('Gender Restriction') }}" wire:model="gender_restriction" :options="$genderOptions" option-value="id" option-label="name" required />
                        <x-input label="{{ __('Max Capacity') }}" wire:model="max_capacity" type="number" placeholder="{{ __('Leave empty for unlimited') }}" hint="{{ __('Waitlist activates if full') }}" />
                        <x-toggle label="{{ __('Registration Open') }}" wire:model="is_registration_open" class="toggle-success" />
                    </div>
                </x-tab>

                <x-tab name="materials" label="{{ __('Materials') }}" icon="o-document-text">
                    <div class="space-y-4 pt-4">
                        <x-file label="{{ __('Upload Material (PDF/Image)') }}" wire:model="materials_file" hint="{{ __('Max 10MB') }}" />
                        <x-textarea label="{{ __('Resource Links') }}" wire:model="resources" rows="3" placeholder="https://youtube.com/...&#10;https://example.com/..." hint="{{ __('One link per line') }}" />
                    </div>
                </x-tab>
            </x-tabs>
            
            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" @click="$wire.sessionModal = false" />
                <x-button label="{{ __('Save Session') }}" class="btn-primary" type="submit" spinner="saveSession" />
            </x-slot:actions>
        </x-form>
    </x-drawer>

    {{-- Series Modal --}}
    <x-modal wire:model="seriesModal" title="{{ __('Create Course / Series') }}" separator>
        <x-form wire:submit="saveSeries">
            <x-input label="{{ __('Series Title') }}" wire:model="series_title" required placeholder="{{ __('e.g. Tafseer of Surah Maryam') }}" />
            <x-textarea label="{{ __('Description') }}" wire:model="series_description" rows="3" />
            <x-select label="{{ __('Target Audience Level') }}" wire:model="series_level" :options="[['id'=>'beginner','name'=>__('Beginner')],['id'=>'intermediate','name'=>__('Intermediate')],['id'=>'advanced','name'=>__('Advanced')]]" option-value="id" option-label="name" required />
            
            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" @click="$wire.seriesModal = false" />
                <x-button label="{{ __('Create Series') }}" class="btn-primary" type="submit" spinner="saveSeries" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- AI Prompt Modal --}}
    <x-modal wire:model="aiModal" title="{{ __('AI Session Generator') }}" separator>
        <div class="space-y-4">
            <p class="text-sm text-base-content/70">
                {{ __("Provide unstructured details about the session. The AI will parse it and fill out the session's title, topic, description, gender restrictions, and capacity.") }}
            </p>
            @if($series_id)
                <x-alert icon="o-information-circle" class="alert-info text-sm py-2 mb-2">
                    {{ __('Context from the currently selected Series will be provided to the AI.') }}
                </x-alert>
            @endif
            <x-textarea
                wire:model="aiPrompt"
                label="{{ __('Session Instructions') }}"
                placeholder="{{ __('e.g. We need a session about the importance of Tahajjud for brothers only. Capacity should be 30 max.') }}"
                rows="4"
                hint="{{ __('Tip: The more details you provide, the better the AI will generate.') }}"
            />
        </div>
        
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.aiModal = false" />
            <x-button label="{{ __('Generate Now') }}" icon="o-sparkles" class="btn-primary" wire:click="generateFromAi" spinner="generateFromAi" />
        </x-slot:actions>
    </x-modal>
</div>
