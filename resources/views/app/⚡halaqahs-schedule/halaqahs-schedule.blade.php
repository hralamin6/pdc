<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.dashboard')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Halaqah Session Scheduler') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Schedule individual study sessions, assign speakers, and manage logistics.') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button
                :label="__('Generate with AI')"
                icon="o-sparkles"
                wire:click="$set('showAiModal', true)"
                class="btn-outline btn-secondary btn-sm rounded-xl font-bold"
            />
            <x-button
                :label="__('Schedule Session')"
                icon="o-plus"
                wire:click="openModal"
                class="btn-primary btn-sm rounded-xl font-bold"
            />
        </div>
    </div>

    {{-- Filter controls --}}
    <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <x-input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search sessions by title or topic...')"
                icon="o-magnifying-glass"
                class="input-sm rounded-xl border-base-content/10 w-full sm:w-72"
            />
            <x-select
                wire:model.live="filterSeries"
                :options="array_merge(
                    [['id' => 'all', 'name' => __('All Programs')], ['id' => 'independent', 'name' => __('Independent Sessions')]],
                    $allSeries
                )"
                class="select-sm rounded-xl border-base-content/10"
            />
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                        <th>{{ __('Session Details') }}</th>
                        <th>{{ __('Program / Series') }}</th>
                        <th>{{ __('Speaker') }}</th>
                        <th>{{ __('Scheduled Date') }}</th>
                        <th>{{ __('Attendance/RSVP') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-content/5">
                    @forelse($sessions as $item)
                        <tr class="hover:bg-base-200/30 transition-colors">
                            <td>
                                <div>
                                    <p class="font-bold text-sm text-base-content">{{ $item->title }}</p>
                                    <p class="text-xs text-base-content/50 mt-0.5 font-semibold">{{ __('Topic:') }} {{ $item->topic }}</p>
                                    <span class="text-[10px] text-base-content/40 font-bold uppercase tracking-wider bg-base-200 px-1.5 py-0.5 rounded mt-1 inline-block">
                                        {{ str_replace('_', ' ', $item->gender_restriction) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-semibold text-base-content/75">{{ $item->series?->title ?? __('Independent') }}</span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold text-base-content/85">{{ $item->speaker?->name ?? __('TBD') }}</span>
                            </td>
                            <td class="text-xs font-semibold text-base-content/60">
                                {{ $item->scheduled_at ? $item->scheduled_at->format('M j, Y - g:i A') : __('Not Scheduled') }}
                            </td>
                            <td class="text-sm font-semibold text-base-content/70">
                                {{ $item->attendances_count }} @if($item->max_capacity) / {{ $item->max_capacity }} @endif
                            </td>
                            <td>
                                @if($item->status === 'published')
                                    <span class="badge badge-success badge-sm font-bold">{{ __('Published') }}</span>
                                @elseif($item->status === 'draft')
                                    <span class="badge badge-warning badge-sm font-bold">{{ __('Draft') }}</span>
                                @elseif($item->status === 'completed')
                                    <span class="badge badge-info badge-sm font-bold">{{ __('Completed') }}</span>
                                @else
                                    <span class="badge badge-error badge-sm font-bold">{{ __('Cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <x-button
                                        icon="o-eye"
                                        :link="route('app.halaqahs.show', ['halaqah' => $item->id])"
                                        class="btn-ghost btn-circle btn-sm text-indigo-500"
                                        tooltip="{{ __('Manage Session') }}"
                                        wire:navigate
                                    />
                                    <x-button
                                        icon="o-qr-code"
                                        wire:click="showQrCode({{ $item->id }})"
                                        class="btn-ghost btn-circle btn-sm text-indigo-500"
                                        tooltip="{{ __('Show QR check-in') }}"
                                    />
                                    <x-button
                                        icon="o-pencil"
                                        wire:click="openModal({{ $item->id }})"
                                        class="btn-ghost btn-circle btn-sm text-primary"
                                        tooltip="{{ __('Edit') }}"
                                    />
                                    <x-button
                                        icon="o-trash"
                                        wire:click="deleteSession({{ $item->id }})"
                                        wire:confirm="{{ __('Are you sure you want to cancel and delete this session?') }}"
                                        class="btn-ghost btn-circle btn-sm text-error"
                                        tooltip="{{ __('Delete') }}"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/50 text-sm">
                                {{ __('No halaqah sessions scheduled.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-base-content/5">
            {{ $sessions->links() }}
        </div>
    </div>

    {{-- Form Modal --}}
    <x-modal wire:model="showModal" :title="$editingId ? __('Edit Scheduled Session') : __('Schedule New Session')" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <x-input
                :label="__('Session Title')"
                wire:model="title"
                :placeholder="__('e.g. Session 1: Life in Makkah')"
                class="rounded-xl border-base-content/10"
            />

            <x-input
                :label="__('Discussion Topic')"
                wire:model="topic"
                :placeholder="__('e.g. The First Revelations')"
                class="rounded-xl border-base-content/10"
            />

            <x-textarea
                :label="__('Brief Description')"
                wire:model="description"
                rows="3"
                class="rounded-xl border-base-content/10"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Program Series (Optional)')"
                    wire:model="series_id"
                    :options="$allSeries"
                    placeholder="{{ __('Independent') }}"
                    class="rounded-xl border-base-content/10"
                />

                <x-select
                    :label="__('Speaker / Lecturer')"
                    wire:model="speaker_id"
                    :options="$speakers"
                    placeholder="{{ __('Select Speaker') }}"
                    class="rounded-xl border-base-content/10"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    type="datetime-local"
                    :label="__('Schedule Timing')"
                    wire:model="scheduled_at"
                    class="rounded-xl border-base-content/10"
                />

                <x-input
                    :label="__('Location / Room')"
                    wire:model="location"
                    :placeholder="__('e.g. Main Prayer Hall, Zoom Link')"
                    class="rounded-xl border-base-content/10"
                />
            </div>

            <x-input
                :label="__('Virtual Meeting Link (optional)')"
                wire:model="meeting_link"
                :placeholder="__('https://zoom.us/j/...')"
                class="rounded-xl border-base-content/10"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Gender Restrictions')"
                    wire:model="gender_restriction"
                    :options="$genderOptions"
                    class="rounded-xl border-base-content/10"
                />

                <x-input
                    type="number"
                    :label="__('Max Capacity (optional)')"
                    wire:model="max_capacity"
                    class="rounded-xl border-base-content/10"
                />
            </div>

            <div class="flex items-center justify-between p-3 rounded-2xl bg-base-200/50 border border-base-content/5">
                <div>
                    <p class="font-bold text-sm text-base-content">{{ __('Allow Registrations') }}</p>
                    <p class="text-xs text-base-content/50">{{ __('Toggle if RSVP should be open to members.') }}</p>
                </div>
                <x-toggle wire:model="is_registration_open" class="toggle-primary" />
            </div>

            <x-select
                :label="__('Session Status')"
                wire:model="status"
                :options="$statusOptions"
                class="rounded-xl border-base-content/10"
            />

            <div class="space-y-2">
                <label class="label text-xs font-bold text-base-content/70">{{ __('Handouts / Study Materials') }}</label>
                @if($existing_materials_path)
                    <p class="text-xs font-semibold text-emerald-600 mb-1">
                        ✓ {{ __('Currently uploaded:') }} <a href="{{ Storage::url($existing_materials_path) }}" target="_blank" class="underline">{{ basename($existing_materials_path) }}</a>
                    </p>
                @endif
                <x-file
                    wire:model="materials_file"
                    :label="__('Upload PDF / Handout')"
                    hint="{{ __('Max 10MB') }}"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Save Session')" wire:click="save" class="btn-primary rounded-xl font-bold" spinner="save" />
        </x-slot:actions>
    </x-modal>

    {{-- QR Checkin Modal --}}
    <x-modal wire:model="showQrModal" :title="__('Check-In QR Code')" class="backdrop-blur text-center">
        <div class="py-4 space-y-4 flex flex-col items-center">
            <h3 class="font-extrabold text-base-content text-lg">{{ $qrHalaqahTitle }}</h3>
            <p class="text-xs text-base-content/60 max-w-sm">
                {{ __('Attendees can scan this QR code using their phone camera to instantly register and check in.') }}
            </p>
            @if($qrCodeUrl)
                <div class="p-4 bg-white rounded-3xl shadow border">
                    <img src="{{ $qrCodeUrl }}" alt="{{ __('Checkin QR') }}" class="w-64 h-64" />
                </div>
            @endif
            <div class="flex gap-2">
                <x-button
                    :label="__('Print QR')"
                    icon="o-printer"
                    x-on:click="
                        let w = window.open();
                        w.document.write('<div style=&quot;text-align:center;font-family:sans-serif;margin-top:100px;&quot;><h1>' + @js($qrHalaqahTitle) + ' Check-In</h1><img src=&quot;' + @js($qrCodeUrl) + '&quot; style=&quot;width:300px;&quot;><p>Scan to Check-In</p></div>');
                        w.print();
                    "
                    class="btn-outline btn-sm rounded-xl"
                />
            </div>
        </div>
        <x-slot:actions>
            <x-button :label="__('Close')" wire:click="$set('showQrModal', false)" class="btn-ghost rounded-xl" />
        </x-slot:actions>
    </x-modal>

    {{-- AI Generator Modal --}}
    <x-modal wire:model="showAiModal" class="backdrop-blur">
        {{-- Custom Premium Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-5 text-white shadow-md -mx-3 -mt-3 mb-5">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur mb-2 border border-white/10 uppercase tracking-widest">
                    <x-icon name="o-sparkles" class="w-3 h-3" />
                    <span>{{ __('AI Co-Pilot') }}</span>
                </span>
                <h3 class="text-lg font-black tracking-tight">{{ __('Draft Session with AI') }}</h3>
                <p class="text-xs text-white/80 mt-0.5 leading-relaxed">{{ __('Describe your session details, and let AI build the schedule, titles, and tags.') }}</p>
            </div>
            <div class="absolute -right-12 -top-12 w-36 h-36 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-4">
            <x-select
                :label="__('Target Program Series (Optional)')"
                wire:model="series_id"
                :options="$allSeries"
                placeholder="{{ __('Independent / No Series') }}"
                class="rounded-xl border-base-content/10 shadow-sm"
            />
            
            <x-textarea
                :label="__('AI Prompt / Topic Details')"
                wire:model="aiPrompt"
                rows="4"
                :placeholder="__('e.g. A session about the Prophet\'s migration to Madinah, focusing on trust in Allah (Tawakkul) and brotherhood. Set the location to the Youth Lounge.')"
                class="rounded-xl border-base-content/10 shadow-sm font-medium"
            />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showAiModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Generate Schema')" wire:click="generateFromAi" class="btn-primary rounded-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-none" icon="o-sparkles" spinner="generateFromAi" />
        </x-slot:actions>
    </x-modal>
</div>
