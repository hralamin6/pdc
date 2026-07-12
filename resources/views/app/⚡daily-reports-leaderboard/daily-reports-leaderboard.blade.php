<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.dashboard')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Leaderboard & Recognition') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Recognize and celebrate top performers in consistency.') }}</p>
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <x-input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search members...')"
                icon="o-magnifying-glass"
                class="input-sm rounded-xl border-base-content/10 w-full sm:w-72"
            />
            <x-select
                wire:model.live="sortBy"
                :options="[
                    ['id' => 'current_streak', 'name' => __('Sort by Current Streak')],
                    ['id' => 'longest_streak', 'name' => __('Sort by Longest Streak')],
                    ['id' => 'total_reports', 'name' => __('Sort by Total Submissions')],
                ]"
                class="select-sm rounded-xl border-base-content/10"
            />
        </div>
    </div>

    {{-- Leaderboard Table --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                        <th class="w-16 text-center">{{ __('Rank') }}</th>
                        <th>{{ __('Member') }}</th>
                        <th class="text-center">{{ __('Current Streak') }}</th>
                        <th class="text-center">{{ __('Longest Streak') }}</th>
                        <th class="text-center">{{ __('Total Logs') }}</th>
                        <th>{{ __('Last Activity') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-content/5">
                    @php $rankOffset = ($leaders->currentPage() - 1) * $leaders->perPage(); @endphp
                    @forelse($leaders as $index => $user)
                        @php $rank = $rankOffset + $index + 1; @endphp
                        <tr class="hover:bg-base-200/30 transition-colors">
                            <td class="text-center font-black">
                                @if($rank === 1)
                                    <span class="text-2xl">👑</span>
                                @elseif($rank === 2)
                                    <span class="text-xl text-slate-400">🥈</span>
                                @elseif($rank === 3)
                                    <span class="text-xl text-amber-600">🥉</span>
                                @else
                                    <span class="text-sm text-base-content/50">#{{ $rank }}</span>
                                @endif
                            </td>
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
                            <td class="text-center">
                                <div class="inline-flex items-center gap-1 font-bold text-sm text-orange-500 bg-orange-50 dark:bg-orange-950/20 px-2.5 py-1 rounded-xl">
                                    <x-icon name="o-fire" class="w-4 h-4" />
                                    {{ $user->current_streak }}d
                                </div>
                            </td>
                            <td class="text-center text-sm font-semibold text-base-content/70">
                                {{ $user->longest_streak }}d
                            </td>
                            <td class="text-center text-sm font-semibold text-base-content/70">
                                {{ $user->total_reports }}
                            </td>
                            <td class="text-xs text-base-content/60">
                                {{ $user->last_report_date ? \Carbon\Carbon::parse($user->last_report_date)->diffForHumans() : __('Never') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <x-button
                                        label="{{ __('Congratulate') }}"
                                        icon="o-sparkles"
                                        wire:click="openRecognitionModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="btn-outline btn-secondary btn-xs rounded-xl"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/50 text-sm">
                                {{ __('No leaders found matching criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-base-content/5">
            {{ $leaders->links() }}
        </div>
    </div>

    {{-- Recognition Modal --}}
    <x-modal wire:model="showModal" title="{{ __('Send Recognition Notification') }}" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <p class="text-xs text-base-content/60">
                {{ __('This will send a custom system push notification and dashboard alert to') }} <strong>{{ $selectedUserName }}</strong>.
            </p>
            <x-textarea
                :label="__('Congratulatory Message')"
                wire:model="notificationMessage"
                rows="4"
                class="rounded-xl border-base-content/10 font-medium"
            />
        </div>
        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Send Congratulations')" wire:click="sendRecognition" class="btn-primary rounded-xl font-bold" spinner />
        </x-slot:actions>
    </x-modal>
</div>
