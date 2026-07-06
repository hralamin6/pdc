<div>
    <x-header :title="__('My Activities')" :subtitle="__('Your personal activity history')" separator />

    {{-- Filter Tabs --}}
    <div class="mb-6">
        <div class="tabs tabs-boxed">
            <a
                wire:click="setFilter('all')"
                class="tab {{ $filter === 'all' ? 'tab-active' : '' }}"
            >
                {{ __('All') }}
            </a>
            <a
                wire:click="setFilter('today')"
                class="tab {{ $filter === 'today' ? 'tab-active' : '' }}"
            >
                {{ __('Today') }}
            </a>
            <a
                wire:click="setFilter('week')"
                class="tab {{ $filter === 'week' ? 'tab-active' : '' }}"
            >
                {{ __('This Week') }}
            </a>
            <a
                wire:click="setFilter('month')"
                class="tab {{ $filter === 'month' ? 'tab-active' : '' }}"
            >
                {{ __('This Month') }}
            </a>
        </div>
    </div>

    {{-- Activities Timeline --}}
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
                    <p class="text-sm">{{ __('Your activities will appear here') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($this->activities->hasPages())
            <div class="mt-6">
                {{ $this->activities->links() }}
            </div>
        @endif
    </x-card>
</div>

