<div>
    <x-header :title="__('Attendance & Preparation Roster')" subtitle="{{ $halaqah->title }} - {{ $halaqah->scheduled_at->format('M d, Y') }}">
        <x-slot:actions>
            <x-button icon="o-arrow-left" title="{{ __('Back') }}" link="{{ route('app.halaqahs.manage') }}" class="btn-ghost" wire:navigate />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <x-stat title="{{ __('Total RSVPs') }}" value="{{ $totalRsvps }}" icon="o-users" class="bg-base-100 shadow-sm border border-base-200" />
        <x-stat title="{{ __('Checked In') }}" value="{{ $totalAttended }}" icon="o-check-circle" class="bg-success/10 text-success shadow-sm border border-success/20" />
        <x-stat title="{{ __('Prepared') }}" value="{{ $totalPrepared }}" icon="o-book-open" class="bg-info/10 text-info shadow-sm border border-info/20" />
        <x-stat title="{{ __('Attendance Rate') }}" value="{{ $totalRsvps > 0 ? round(($totalAttended / $totalRsvps) * 100) : 0 }}%" icon="o-chart-pie" class="bg-base-100 shadow-sm border border-base-200" />
    </div>

    <x-card>
        <div class="mb-4 flex justify-between items-center">
            <h3 class="font-bold text-lg">{{ __('Attendee List') }}</h3>
            <div class="w-1/3">
                <x-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search name...') }}" class="input-sm" clearable />
            </div>
        </div>

        <x-table :headers="[
            ['key' => 'user.name', 'label' => __('Student Name')],
            ['key' => 'status_new', 'label' => __('Status')],
            ['key' => 'checked_in_at', 'label' => __('Check In Time')],
            ['key' => 'preparation_completed', 'label' => __('Preparation')],
            ['key' => 'attended', 'label' => __('Present')],
        ]" :rows="$attendances">
            @scope('cell_status_new', $attendance)
                <x-badge :value="strtoupper($attendance->status_new)" class="{{ $attendance->status_new === 'waitlist' ? 'badge-warning' : 'badge-neutral' }}" />
            @endscope
            @scope('cell_checked_in_at', $attendance)
                @if($attendance->checked_in_at)
                    {{ $attendance->checked_in_at->format('g:i A') }}
                @else
                    <span class="opacity-30">-</span>
                @endif
            @endscope
            @scope('cell_preparation_completed', $attendance)
                <x-toggle wire:click="togglePreparation({{ $attendance->id }})" :checked="$attendance->preparation_completed" class="toggle-info toggle-sm" />
            @endscope
            @scope('cell_attended', $attendance)
                <x-checkbox wire:click="toggleAttendance({{ $attendance->id }})" :checked="$attendance->attended" class="checkbox-primary" />
            @endscope
        </x-table>
        
        @if($attendances->isEmpty())
            <div class="py-12 text-center text-base-content/50">
                <x-icon name="o-users" class="w-12 h-12 mx-auto mb-4 opacity-30" />
                {{ __('No attendees match your criteria.') }}
            </div>
        @endif
    </x-card>
</div>
