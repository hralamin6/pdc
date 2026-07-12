<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Admin Supervision Portal') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Monitor community consistency, inspect progress, and send reminders.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-button
                :label="__('Remind All Pending')"
                icon="o-bell"
                wire:click="remindAll"
                wire:confirm="{{ __('Send daily report reminder notifications to all pending members?') }}"
                class="btn-primary btn-sm rounded-xl font-bold"
            />
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-icon name="o-users" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['total_users'] }}</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __('Total Members') }}</p>
            </div>
        </div>

        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center text-success shrink-0">
                <x-icon name="o-check-circle" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['submitted_today'] }}</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __('Submitted Today') }}</p>
            </div>
        </div>

        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                <x-icon name="o-clock" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['missed_today'] }}</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __('Pending Today') }}</p>
            </div>
        </div>

        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                <x-icon name="o-chart-bar" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['submission_rate'] }}%</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __("Today's Rate") }}</p>
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <x-input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search by name or email...')"
                icon="o-magnifying-glass"
                class="input-sm rounded-xl border-base-content/10 w-full sm:w-72"
            />
            <x-select
                wire:model.live="filter"
                :options="[
                    ['id' => 'all', 'name' => __('All Members')],
                    ['id' => 'missed_today', 'name' => __('Missed Today')],
                    ['id' => 'submitted_today', 'name' => __('Submitted Today')],
                ]"
                class="select-sm rounded-xl border-base-content/10"
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                        <th>{{ __('Member') }}</th>
                        <th>{{ __("Today's Status") }}</th>
                        <th>{{ __('Current Streak') }}</th>
                        <th>{{ __('Longest Streak') }}</th>
                        <th>{{ __('Last Log') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-content/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-base-200/30 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-10 h-10 rounded-full bg-primary/10">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-base-content">{{ $user->name }}</div>
                                        <div class="text-xs text-base-content/50">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->daily_reports_count > 0)
                                    <span class="badge badge-success badge-sm font-bold">{{ __('Submitted') }}</span>
                                @else
                                    <span class="badge badge-warning badge-sm font-bold">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1 font-bold text-sm">
                                    <x-icon name="o-fire" class="w-4 h-4 text-orange-500" />
                                    {{ $user->userStreak?->current_streak ?? 0 }}d
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-semibold text-base-content/70">{{ $user->userStreak?->longest_streak ?? 0 }}d</span>
                            </td>
                            <td class="text-xs text-base-content/60">
                                {{ $user->userStreak?->last_report_date ? $user->userStreak->last_report_date->diffForHumans() : __('Never') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <x-button 
                                        icon="o-eye" 
                                        wire:click="inspectUser({{ $user->id }})" 
                                        class="btn-ghost btn-circle btn-sm text-base-content/70 hover:text-primary"
                                        tooltip="{{ __('Inspect Submissions') }}"
                                    />
                                    @if($user->daily_reports_count == 0)
                                        <x-button 
                                            icon="o-bell" 
                                            wire:click="remindUser({{ $user->id }})" 
                                            class="btn-ghost btn-circle btn-sm text-primary"
                                            tooltip="{{ __('Send Reminder') }}"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/50 text-sm">
                                {{ __('No members found matching your filter criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-base-content/5">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Member Inspection Modal --}}
    <x-modal wire:model="inspectingUserId" :title="__('Member Report Inspection')" class="backdrop-blur">
        @if($inspectedUser)
            <div class="space-y-6">
                {{-- User summary card --}}
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-base-200/50 border border-base-content/5">
                    <div class="avatar">
                        <div class="w-12 h-12 rounded-full bg-primary/10">
                            <img src="{{ $inspectedUser['avatar_url'] }}" alt="{{ $inspectedUser['name'] }}" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-base text-base-content">{{ $inspectedUser['name'] }}</h4>
                        <p class="text-xs text-base-content/50">{{ $inspectedUser['email'] }}</p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center gap-1 font-black text-orange-500 text-sm">
                            <x-icon name="o-fire" class="w-4 h-4" />
                            {{ $inspectedUser['current_streak'] }} {{ __('Day Streak') }}
                        </div>
                        <p class="text-[11px] text-base-content/50 mt-0.5">{{ __('Best:') }} {{ $inspectedUser['longest_streak'] }} {{ __('days') }}</p>
                    </div>
                </div>

                {{-- Recent Logs --}}
                <div class="space-y-3">
                    <h5 class="text-xs font-bold text-base-content/50 uppercase tracking-wider">{{ __('Recent Reports (Past 7 Days)') }}</h5>

                    @if(empty($inspectedUser['recent_reports']))
                        <p class="text-xs text-base-content/40 italic py-4 text-center">{{ __('No submitted reports found for this member yet.') }}</p>
                    @else
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @foreach($inspectedUser['recent_reports'] as $report)
                                <div class="p-3 rounded-xl bg-base-200/30 border border-base-content/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-base-content">{{ $report['date'] }}</span>
                                        <span class="badge badge-sm badge-success font-bold">{{ $report['completion_percentage'] }}% {{ __('Done') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-base-content/60">
                                        <span>{{ __('Items:') }} <strong>{{ $report['completed_count'] }}/{{ $report['total_items_count'] }}</strong></span>
                                        <span class="capitalize">{{ __('Privacy:') }} {{ $report['privacy_level'] }}</span>
                                    </div>
                                    @if(!empty($report['notes']))
                                        <p class="text-xs text-base-content/70 italic bg-base-100 p-2 rounded-lg border border-base-content/5">"{{ $report['notes'] }}"</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-button :label="__('Close')" wire:click="closeInspectModal" class="btn-ghost rounded-xl" />
        </x-slot:actions>
    </x-modal>
</div>
