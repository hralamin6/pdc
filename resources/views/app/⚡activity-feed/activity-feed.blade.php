<div>
    <x-header :title="__('Activity Feed')" :subtitle="__('All system activities')" separator>
        <x-slot:actions>
            <x-button
                wire:click="toggleStats"
                :label="$showStats ? __('Hide Stats') : __('Show Stats')"
                icon="o-chart-bar"
                class="btn-ghost btn-sm"
            />
            @can('activity.delete')
                <x-button
                    wire:click="openClearModal"
                    :label="__('Clear Activities')"
                    icon="o-trash"
                    class="btn-error btn-sm"
                />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- Dashboard Statistics --}}
    @if($showStats && $this->stats)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Total Activities --}}
            <x-stat
                :title="__('Total Activities')"
                :value="number_format($this->stats['total'])"
                icon="o-document-text"
                color="text-primary"
            />

            {{-- Unique Users --}}
            <x-stat
                :title="__('Active Users')"
                :value="number_format($this->stats['unique_users'])"
                icon="o-user-group"
                color="text-success"
            />

            {{-- By Log Type --}}
            <x-card class="col-span-1">
                <div class="text-sm font-semibold mb-2">{{ __('By Log Type') }}</div>
                <div class="space-y-1">
                    @forelse($this->stats['by_log']->take(3) as $log)
                        <div class="flex justify-between text-xs">
                            <span class="badge badge-sm badge-ghost">{{ $log->log_name }}</span>
                            <span class="font-medium">{{ number_format($log->count) }}</span>
                        </div>
                    @empty
                        <span class="text-xs text-base-content/60">{{ __('No data') }}</span>
                    @endforelse
                </div>
            </x-card>

            {{-- By Event --}}
            <x-card class="col-span-1">
                <div class="text-sm font-semibold mb-2">{{ __('By Event') }}</div>
                <div class="space-y-1">
                    @forelse($this->stats['by_event']->take(3) as $event)
                        <div class="flex justify-between text-xs">
                            <span class="badge badge-sm badge-{{ $this->getEventColor($event->event) }}">{{ $event->event }}</span>
                            <span class="font-medium">{{ number_format($event->count) }}</span>
                        </div>
                    @empty
                        <span class="text-xs text-base-content/60">{{ __('No data') }}</span>
                    @endforelse
                </div>
            </x-card>
        </div>

        {{-- Time Range Selector --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium">{{ __('Stats Time Range:') }}</span>
                <x-radio wire:model.live="timeRange" :options="[
                    ['id' => '7', 'name' => __('7 Days')],
                    ['id' => '30', 'name' => __('30 Days')],
                    ['id' => '90', 'name' => __('90 Days')],
                ]" inline />
            </div>
        </x-card>
    @endif

    {{-- Filters --}}
    <x-card :title="__('Filters')" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input
                wire:model.live.debounce="filters.search"
                :label="__('Search')"
                :placeholder="__('Search activities...')"
                icon="o-magnifying-glass"
            />

            <x-select
                wire:model.live="filters.log_name"
                :label="__('Log Type')"
                :options="$this->logNames->map(fn($name) => ['id' => $name, 'name' => ucfirst($name)])"
                :placeholder="__('All types')"
            />

            <x-select
                wire:model.live="filters.event"
                :label="__('Event')"
                :options="$this->events->map(fn($event) => ['id' => $event, 'name' => ucfirst(str_replace('_', ' ', $event))])"
                :placeholder="__('All events')"
            />

            <x-input
                wire:model.live="filters.date_from"
                :label="__('From Date')"
                type="date"
            />

            <x-input
                wire:model.live="filters.date_to"
                :label="__('To Date')"
                type="date"
            />

            <div class="flex items-end">
                <x-button
                    wire:click="clearFilters"
                    :label="__('Clear Filters')"
                    icon="o-x-mark"
                    class="btn-ghost w-full"
                />
            </div>
        </div>
    </x-card>

    {{-- Activity Timeline --}}
    <x-card>
        <div class="space-y-4">
            @forelse($this->activities as $activity)
                <div class="flex gap-4 pb-4 border-b border-base-300 last:border-0">
                    {{-- Timeline dot --}}
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                        @if(!$loop->last)
                            <div class="w-0.5 h-full bg-base-300 mt-1"></div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 -mt-1">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-base-content">
                                    {{ $activity->description }}
                                </p>
                                <div class="flex items-center gap-2 mt-1 text-sm text-base-content/60">
                                    @if($activity->causer)
                                        <x-icon name="o-user" class="w-4 h-4" />
                                        <span>{{ $activity->causer->name }}</span>
                                        <span>•</span>
                                    @endif
                                    <x-icon name="o-clock" class="w-4 h-4" />
                                    <span>{{ $activity->created_at->format('M d, Y h:i A') }}</span>
                                    <span>({{ $activity->created_at->diffForHumans() }})</span>
                                    @if($activity->log_name)
                                        <span class="badge badge-sm badge-ghost ml-2">{{ $activity->log_name }}</span>
                                    @endif
                                    @if($activity->event)
                                        <span class="badge badge-sm badge-primary">{{ ucfirst($activity->event) }}</span>
                                    @endif
                                </div>

                                {{-- Subject Info --}}
                                @if($activity->subject)
                                    <div class="mt-2 text-sm">
                                        <span class="text-base-content/60">{{ __('Related to:') }}</span>
                                        <span class="font-medium">{{ class_basename($activity->subject_type) }}</span>
                                        @if(method_exists($activity->subject, 'getActivityLabel'))
                                            <span class="text-base-content/60">- {{ $activity->subject->getActivityLabel() }}</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Changes Details --}}
                                @if($activity->properties && $activity->event === 'updated')
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-sm text-primary hover:underline">
                                            {{ __('View changes') }}
                                        </summary>
                                        <div class="mt-2 p-3 bg-base-200 rounded text-sm">
                                            @if(isset($activity->properties['old']))
                                                <div class="space-y-1">
                                                    @foreach($activity->properties['attributes'] as $key => $value)
                                                        @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                                            <div>
                                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                <span class="line-through text-error">
                                                                    {{ is_array($activity->properties['old'][$key]) ? json_encode($activity->properties['old'][$key]) : $activity->properties['old'][$key] }}
                                                                </span>
                                                                <x-icon name="o-arrow-right" class="w-3 h-3 inline" />
                                                                <span class="text-success">
                                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                @endif

                                {{-- Device Info --}}
                                @if($activity->ip_address)
                                    <div class="mt-2 text-xs text-base-content/40 flex items-center gap-2">
                                        <x-icon name="o-globe-alt" class="w-3 h-3" />
                                        <span>{{ $activity->ip_address }}</span>
                                        @if($activity->user_agent)
                                            <span>•</span>
                                            <span class="truncate max-w-xs">{{ $activity->user_agent }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-base-content/60">
                    <x-icon name="o-inbox" class="w-16 h-16 mx-auto mb-3 opacity-50" />
                    <p class="text-lg font-medium">{{ __('No activities found') }}</p>
                    <p class="text-sm">{{ __('Activities will appear here as they occur') }}</p>
                </div>
            @endforelse
        </div>

        @if($this->activities->hasPages())
            <div class="mt-6">{{ $this->activities->links() }}</div>
        @endif
    </x-card>

    {{-- Clear Activities Modal --}}
    <x-modal wire:model="showClearModal" title="Clear Activities" subtitle="Permanently delete activities from the system">
        @if($showClearModal)
            <div class="space-y-4">
                {{-- Stats --}}
                @if($this->clearStats)
                    <x-alert icon="o-information-circle" class="alert-info">
                        <div class="text-sm">
                            <div><strong>Total Activities:</strong> {{ number_format($this->clearStats['total']) }}</div>
                            @if($this->clearStats['oldest'])
                                <div><strong>Oldest:</strong> {{ $this->clearStats['oldest']->format('M d, Y H:i') }}</div>
                            @endif
                            @if($this->clearStats['newest'])
                                <div><strong>Newest:</strong> {{ $this->clearStats['newest']->format('M d, Y H:i') }}</div>
                            @endif
                        </div>
                    </x-alert>
                @endif

                {{-- Clear Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        wire:model.live="clearFilters.days"
                        label="{{ __('Delete activities older than (days)') }}"
                        type="number"
                        min="1"
                        :placeholder="__('e.g., 90')"
                        hint="{{ __('Leave empty to not filter by date') }}"
                    />

                    <x-select
                        wire:model.live="clearFilters.log_name"
                        label="{{ __('Log Type') }}"
                        :options="$this->logNames->map(fn($name) => ['id' => $name, 'name' => ucfirst($name)])"
                        :placeholder="__('All types')"
                    />

                    <x-select
                        wire:model.live="clearFilters.event"
                        label="{{ __('Event') }}"
                        :options="$this->events->map(fn($event) => ['id' => $event, 'name' => ucfirst(str_replace('_', ' ', $event))])"
                        :placeholder="__('All events')"
                    />
                </div>

                {{-- Preview Count --}}
                <x-alert icon="o-exclamation-triangle" class="alert-warning">
                    <strong>{{ number_format($this->previewCount) }}</strong> activities will be deleted based on current filters.
                </x-alert>

                {{-- Confirmation Checkbox --}}
                <x-checkbox
                    wire:model="confirmDelete"
                    label="I understand this action cannot be undone"
                    hint="Please confirm deletion"
                />

                {{-- Action Buttons --}}
                <div class="flex justify-between gap-2 mt-6">
                    <div class="flex gap-2">
                        <x-button
                            wire:click="clearActivities"
                            label="Clear Filtered Activities"
                            icon="o-trash"
                            class="btn-error"
                            :disabled="!$confirmDelete || $this->previewCount === 0"
                        />
                        <x-button
                            wire:click="clearAllActivities"
                            label="Clear All Activities"
                            icon="o-trash"
                            class="btn-error btn-outline"
                            :disabled="!$confirmDelete"
                        />
                    </div>
                    <x-button
                        wire:click="closeClearModal"
                        label="Cancel"
                        class="btn-ghost"
                    />
                </div>
            </div>
        @endif
    </x-modal>
</div>

@script
<script>
    // Helper methods would be better in the component, but for quick implementation:
    window.getEventColor = (event) => {
        const colors = {
            'created': 'success',
            'updated': 'info',
            'deleted': 'error',
            'login': 'primary',
            'logout': 'warning',
            'failed_login': 'error',
        };
        return colors[event] || 'base-300';
    };

    window.getEventIcon = (event) => {
        const icons = {
            'created': 'o-plus-circle',
            'updated': 'o-pencil-square',
            'deleted': 'o-trash',
            'login': 'o-arrow-right-on-rectangle',
            'logout': 'o-arrow-left-on-rectangle',
            'failed_login': 'o-exclamation-triangle',
        };
        return icons[event] || 'o-document-text';
    };
</script>
@endscript
