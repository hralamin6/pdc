<div>
    <x-header title="{{ __('Halaqahs & Study Circles') }}" subtitle="{{ __('Discover and join upcoming campus study circles and courses.') }}">
        <x-slot:actions>
            @can('halaqahs.create')
                <x-button icon="o-cog-6-tooth" title="{{ __('Manage System') }}" link="{{ route('app.halaqahs.manage') }}" class="btn-outline btn-primary" wire:navigate />
            @endcan
        </x-slot:actions>
    </x-header>

    <x-tabs wire:model="activeTab" class="mt-4">
        <x-tab name="upcoming" label="{{ __('Upcoming Sessions') }}" icon="o-calendar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                @forelse ($upcoming as $halaqah)
                    <x-card class="hover:shadow-lg transition-shadow border border-base-200 flex flex-col h-full">
                        <x-slot:title>
                            <div class="flex justify-between items-start">
                                <span>{{ $halaqah->title }}</span>
                                @if($halaqah->gender_restriction !== 'none')
                                    <x-badge value="{{ str_replace('_', ' ',ucfirst($halaqah->gender_restriction)) }}" class="badge-outline badge-sm shrink-0" />
                                @endif
                            </div>
                        </x-slot:title>
                        
                        <div class="space-y-3 mt-4 flex-grow">
                            <div class="flex items-center text-sm text-base-content/70">
                                <x-icon name="o-calendar" class="w-4 h-4 mr-2 text-primary" />
                                {{ $halaqah->scheduled_at->format('M d, Y h:i A') }}
                            </div>
                            <div class="flex items-center text-sm text-base-content/70">
                                <x-icon name="o-map-pin" class="w-4 h-4 mr-2 text-primary" />
                                {{ $halaqah->location }}
                            </div>
                            @if($halaqah->speaker)
                            <div class="flex items-center text-sm text-base-content/70">
                                <x-icon name="o-user" class="w-4 h-4 mr-2 text-primary" />
                                {{ $halaqah->speaker->name }}
                            </div>
                            @endif
                            <div class="flex items-center text-sm text-base-content/70">
                                <x-icon name="o-tag" class="w-4 h-4 mr-2 text-primary" />
                                {{ __('Topic:') }} {{ $halaqah->topic }}
                            </div>
                            @if($halaqah->quizzes()->exists())
                            <div class="flex items-center text-sm font-bold text-accent">
                                <x-icon name="o-question-mark-circle" class="w-4 h-4 mr-2 text-accent" />
                                {{ __('Quiz Attached') }}
                            </div>
                            @endif
                        </div>

                        <div class="mt-6 pt-4 border-t border-base-200 flex justify-between items-center">
                            @if($halaqah->max_capacity)
                                <span class="text-xs font-semibold text-{{ $halaqah->available_seats > 0 ? 'success' : 'warning' }}">
                                    {{ $halaqah->available_seats > 0 ? $halaqah->available_seats . ' ' . __('spots left') : __('Waitlist Only') }}
                                </span>
                            @else
                                <span></span>
                            @endif
                            <x-button label="{{ __('Details') }}" icon-right="o-arrow-right" class="btn-sm btn-primary btn-outline" link="{{ route('app.halaqahs.show', $halaqah) }}" wire:navigate />
                        </div>
                    </x-card>
                @empty
                    <div class="col-span-full py-16 text-center text-base-content/50 bg-base-100 rounded-xl border border-base-200 border-dashed">
                        <x-icon name="o-calendar-days" class="w-16 h-16 mx-auto mb-4 opacity-30" />
                        <h3 class="text-lg font-bold mb-2">{{ __('No Upcoming Sessions') }}</h3>
                        <p>{{ __('Check back later for newly scheduled study circles.') }}</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-6">{{ $upcoming->links() }}</div>
        </x-tab>

        <x-tab name="series" label="{{ __('Courses & Series') }}" icon="o-academic-cap">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                @forelse ($series as $course)
                    <x-card class="hover:shadow-lg transition-shadow border border-base-200 bg-gradient-to-br from-base-100 to-base-200">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold">{{ $course->title }}</h3>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <x-badge value="{{ ucfirst($course->target_audience_level) }}" class="badge-primary badge-sm" />
                                @if($course->quizzes_count > 0)
                                    <x-badge value="{{ __('Has Quizzes') }}" class="badge-accent badge-sm" />
                                @endif
                            </div>
                        </div>
                        <p class="text-base-content/70 text-sm line-clamp-3 mb-6">{{ $course->description }}</p>
                        
                        <div class="flex justify-between items-center">
                            <div class="text-sm font-semibold text-primary">
                                <x-icon name="o-play-circle" class="w-4 h-4 inline mr-1" />
                                {{ $course->halaqahs_count }} {{ __('Sessions') }}
                            </div>
                            <x-button label="{{ __('View Series') }}" class="btn-sm btn-ghost" />
                        </div>
                    </x-card>
                @empty
                    <div class="col-span-full py-16 text-center text-base-content/50 bg-base-100 rounded-xl border border-base-200 border-dashed">
                        <x-icon name="o-academic-cap" class="w-16 h-16 mx-auto mb-4 opacity-30" />
                        <h3 class="text-lg font-bold mb-2">{{ __('No Active Series') }}</h3>
                        <p>{{ __('There are no structured courses running at the moment.') }}</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-6">{{ $series->links() }}</div>
        </x-tab>
        
        <x-tab name="past" label="{{ __('Past Sessions') }}" icon="o-clock">
            <div class="grid grid-cols-1 gap-4 mt-6">
                @forelse ($past as $halaqah)
                    <x-card class="bg-base-100 border border-base-200 hover:border-primary/50 transition-colors">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-grow">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-bold text-lg">{{ $halaqah->title }}</h4>
                                    @if($halaqah->materials_path || !empty($halaqah->resources))
                                        <x-badge value="{{ __('Materials Available') }}" class="badge-success badge-sm badge-outline" />
                                    @endif
                                    @if($halaqah->quizzes->isNotEmpty())
                                        <x-badge value="{{ __('Quiz Attached') }}" class="badge-accent badge-sm badge-outline" />
                                    @endif
                                </div>
                                <p class="text-sm text-base-content/70 flex items-center gap-4">
                                    <span><x-icon name="o-calendar" class="w-4 h-4 inline mr-1" /> {{ $halaqah->scheduled_at->format('M d, Y') }}</span>
                                    @if($halaqah->speaker)
                                        <span><x-icon name="o-user" class="w-4 h-4 inline mr-1" /> {{ $halaqah->speaker->name }}</span>
                                    @endif
                                    <span><x-icon name="o-tag" class="w-4 h-4 inline mr-1" /> {{ $halaqah->topic }}</span>
                                </p>
                            </div>
                            <div class="shrink-0 text-right flex flex-col items-end gap-2">
                                <x-button label="{{ __('View Details & Materials') }}" icon-right="o-arrow-right" class="btn-sm btn-outline" link="{{ route('app.halaqahs.show', $halaqah) }}" wire:navigate />
                            </div>
                        </div>
                    </x-card>
                @empty
                    <div class="py-12 text-center text-base-content/50">
                        <x-icon name="o-folder-open" class="w-12 h-12 mx-auto mb-4 opacity-30" />
                        <p>{{ __('No past sessions found.') }}</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-6">{{ $past->links() }}</div>
        </x-tab>
    </x-tabs>
</div>
