<div class="max-w-7xl mx-auto py-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.halaqah-series')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Program Control Console') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Manage sessions, rosters, and send program announcements.') }}</p>
            </div>
        </div>
    </div>

    {{-- Main Split Pane Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Pane: Program Profile & Status Controls --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden p-6 space-y-6">
                @if($series->banner_path)
                    <div class="w-full h-40 rounded-2xl overflow-hidden border border-base-content/5 mb-2">
                        <img src="{{ Storage::url($series->banner_path) }}" alt="{{ $series->title }}" class="object-cover w-full h-full" />
                    </div>
                @else
                    <div class="w-full h-40 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-base-content/5 flex items-center justify-center text-primary mb-2">
                        <x-icon name="o-academic-cap" class="w-16 h-16 opacity-30" />
                    </div>
                @endif

                <div>
                    <h2 class="text-xl font-black text-base-content leading-tight">{{ $series->title }}</h2>
                    <p class="text-xs font-bold text-base-content/40 uppercase tracking-widest mt-1.5">{{ __('Audience:') }} {{ $series->target_audience_level }}</p>
                </div>

                <div class="divide-y divide-base-content/5 text-sm space-y-4">
                    <div class="pt-1">
                        <p class="text-xs font-bold text-base-content/50 mb-1 uppercase tracking-wider">{{ __('Outline & Syllabus') }}</p>
                        <p class="text-xs text-base-content/70 leading-relaxed">{{ $series->description ?? __('No description provided.') }}</p>
                    </div>

                    <div class="pt-4 flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-10 h-10 rounded-full bg-primary/10">
                                <img src="{{ $series->mentor?->avatar_url }}" alt="{{ $series->mentor?->name }}" />
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-base-content/40 uppercase tracking-wider">{{ __('Lead Instructor') }}</p>
                            <p class="font-bold text-sm text-base-content">{{ $series->mentor?->name ?? __('Unassigned') }}</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <x-select
                            :label="__('Program Status')"
                            wire:model.live="series.status"
                            :options="[
                                ['id' => 'draft', 'name' => __('Draft')],
                                ['id' => 'active', 'name' => __('Active')],
                                ['id' => 'completed', 'name' => __('Completed')],
                                ['id' => 'cancelled', 'name' => __('Cancelled')],
                            ]"
                            class="select-sm rounded-xl border-base-content/10 shadow-sm"
                            wire:change="updateStatus($event.target.value)"
                        />
                    </div>
                </div>

                {{-- Action Panel --}}
                <div class="space-y-2 pt-2">
                    <x-button
                        :label="__('Broadcast Announcement')"
                        icon="o-megaphone"
                        wire:click="openAnnouncementModal"
                        class="btn-secondary btn-sm w-full rounded-xl font-bold"
                    />
                </div>
            </div>
        </div>

        {{-- Right Pane: Tabbed Console Areas --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden p-6">
                {{-- Custom Tab Buttons --}}
                <div class="tabs tabs-boxed bg-base-200/50 p-1 mb-6 rounded-2xl">
                    <button wire:click="$set('activeTab', 'sessions')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'sessions' ? 'tab-active bg-primary text-primary-content' : '' }}">
                        <x-icon name="o-calendar" class="w-4 h-4 mr-1.5" />
                        {{ __('Sessions Outline') }}
                    </button>
                    <button wire:click="$set('activeTab', 'roster')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'roster' ? 'tab-active bg-primary text-primary-content' : '' }}">
                        <x-icon name="o-users" class="w-4 h-4 mr-1.5" />
                        {{ __('Enrolled Roster') }}
                    </button>
                    <button wire:click="$set('activeTab', 'analytics')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'analytics' ? 'tab-active bg-primary text-primary-content' : '' }}">
                        <x-icon name="o-chart-bar" class="w-4 h-4 mr-1.5" />
                        {{ __('Performance & Analytics') }}
                    </button>
                </div>

                {{-- Tab Content 1: Sessions --}}
                @if($activeTab === 'sessions')
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-base-content/50 uppercase tracking-wider">{{ __('Sessions in Curriculum') }}</h3>
                            <x-button
                                :label="__('Add Session')"
                                icon="o-plus"
                                wire:click="openSessionModal"
                                class="btn-primary btn-xs rounded-xl font-bold"
                            />
                        </div>

                        <div class="divide-y divide-base-content/5">
                            @forelse($sessions as $session)
                                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-base-200/20 px-3 rounded-2xl transition-colors">
                                    <div>
                                        <h4 class="font-bold text-sm text-base-content">{{ $session->title }}</h4>
                                        <p class="text-xs text-base-content/50 font-semibold mt-0.5">{{ __('Topic:') }} {{ $session->topic }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="badge badge-sm font-bold uppercase tracking-wider badge-ghost text-[9px]">{{ str_replace('_', ' ', $session->gender_restriction) }}</span>
                                            <span class="text-xs text-base-content/40 font-semibold">{{ $session->scheduled_at ? $session->scheduled_at->format('M j, g:i A') : __('Not Scheduled') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                        @if($session->status === 'completed')
                                            <span class="badge badge-success badge-sm font-bold">{{ __('Completed') }}</span>
                                        @elseif($session->status === 'published')
                                            <span class="badge badge-info badge-sm font-bold">{{ __('Published') }}</span>
                                        @else
                                            <span class="badge badge-warning badge-sm font-bold">{{ __('Draft') }}</span>
                                        @endif
                                        <x-button
                                            icon="o-eye"
                                            :link="route('app.halaqahs.show', ['halaqah' => $session->id])"
                                            class="btn-ghost btn-circle btn-sm text-indigo-500"
                                            tooltip="{{ __('Manage Session') }}"
                                            wire:navigate
                                        />
                                        <x-button
                                            icon="o-qr-code"
                                            wire:click="showQrCode({{ $session->id }})"
                                            class="btn-ghost btn-circle btn-sm text-indigo-500"
                                            tooltip="{{ __('Check-In QR') }}"
                                        />
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-base-content/40">
                                    <x-icon name="o-calendar" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                    <p class="text-sm font-medium">{{ __('No sessions scheduled under this series yet.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- Tab Content 2: Roster --}}
                @if($activeTab === 'roster')
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-base-content/50 uppercase tracking-wider">{{ __('Enrolled Members') }}</h3>

                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                                        <th>{{ __('Member') }}</th>
                                        <th class="text-center">{{ __('RSVPs') }}</th>
                                        <th class="text-center">{{ __('Attended') }}</th>
                                        <th class="text-center">{{ __('Attendance Rate') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-content/5">
                                    @forelse($roster as $member)
                                        <tr class="hover:bg-base-200/30 transition-colors">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar">
                                                        <div class="w-9 h-9 rounded-full bg-primary/10">
                                                            <img src="{{ $member['avatar_url'] }}" alt="{{ $member['name'] }}" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-xs text-base-content">{{ $member['name'] }}</div>
                                                        <div class="text-[10px] text-base-content/50">{{ $member['email'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center font-semibold text-xs text-base-content/75">{{ $member['rsvps'] }}</td>
                                            <td class="text-center font-semibold text-xs text-base-content/75">{{ $member['attended'] }}</td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <div class="w-12 bg-base-200 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ $member['rate'] }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-bold text-base-content">{{ $member['rate'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-base-content/40 text-sm">
                                                {{ __('No members enrolled in this program series.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Tab Content 3: Analytics --}}
                @if($activeTab === 'analytics')
                    <div class="space-y-6">
                        <h3 class="text-sm font-black text-base-content/50 uppercase tracking-wider">{{ __('Performance Insights') }}</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-base-200/30 p-5 rounded-2xl border border-base-content/5 text-center">
                                <p class="text-2xl font-black text-indigo-600">{{ $analytics['total_sessions'] }}</p>
                                <p class="text-[10px] font-bold text-base-content/50 uppercase mt-1">{{ __('Total Sessions') }}</p>
                            </div>
                            <div class="bg-base-200/30 p-5 rounded-2xl border border-base-content/5 text-center">
                                <p class="text-2xl font-black text-emerald-600">{{ $analytics['total_enrolled'] }}</p>
                                <p class="text-[10px] font-bold text-base-content/50 uppercase mt-1">{{ __('Active Enrolled') }}</p>
                            </div>
                            <div class="bg-base-200/30 p-5 rounded-2xl border border-base-content/5 text-center">
                                <p class="text-2xl font-black text-amber-600">{{ $analytics['avg_attendance_rate'] }}%</p>
                                <p class="text-[10px] font-bold text-base-content/50 uppercase mt-1">{{ __('Avg Attendance') }}</p>
                            </div>
                        </div>

                        {{-- Completion Progress Card --}}
                        <div class="p-6 rounded-2xl bg-base-200/30 border border-base-content/5 space-y-4">
                            <div>
                                <h4 class="font-bold text-sm text-base-content">{{ __('Program Completion Progression') }}</h4>
                                <p class="text-xs text-base-content/50 mt-0.5">{{ __('Ratio of completed classes to total scheduled sessions.') }}</p>
                            </div>
                            @php
                                $progress = $analytics['total_sessions'] > 0 ? round(($analytics['completed_sessions'] / $analytics['total_sessions']) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <span class="text-base-content/75">{{ $analytics['completed_sessions'] }} / {{ $analytics['total_sessions'] }} {{ __('Sessions Finished') }}</span>
                                    <span class="text-primary">{{ $progress }}%</span>
                                </div>
                                <div class="w-full bg-base-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-primary h-3 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Broadcast Announcement Modal --}}
    <x-modal wire:model="showAnnouncementModal" class="backdrop-blur">
        {{-- Custom Premium Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-5 text-white shadow-md -mx-3 -mt-3 mb-5">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur mb-2 border border-white/10 uppercase tracking-widest">
                    <x-icon name="o-megaphone" class="w-3 h-3" />
                    <span>{{ __('Alert Dispatch') }}</span>
                </span>
                <h3 class="text-lg font-black tracking-tight">{{ __('Broadcast to Members') }}</h3>
                <p class="text-xs text-white/80 mt-0.5 leading-relaxed">{{ __('Dispatch an instant push and dashboard notification to all enrolled users.') }}</p>
            </div>
            <div class="absolute -right-12 -top-12 w-36 h-36 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-4">
            <x-input
                :label="__('Notification Title')"
                wire:model="announcementTitle"
                class="rounded-xl border-base-content/10 shadow-sm"
            />
            <x-textarea
                :label="__('Message')"
                wire:model="announcementMessage"
                rows="4"
                :placeholder="__('e.g. Assalamu alaikum. Please remember that tonight\'s session has been rescheduled to tomorrow after Isha.')"
                class="rounded-xl border-base-content/10 shadow-sm font-medium"
            />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showAnnouncementModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Send Broadcast')" wire:click="sendAnnouncement" class="btn-primary rounded-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-none" icon="o-paper-airplane" spinner="sendAnnouncement" />
        </x-slot:actions>
    </x-modal>

    {{-- Quick Add Session Modal --}}
    <x-modal wire:model="showSessionModal" :title="__('Add Session directly to Series')" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <x-input
                :label="__('Session Title')"
                wire:model="sessionTitle"
                :placeholder="__('e.g. Session 1: Life in Makkah')"
                class="rounded-xl border-base-content/10"
            />
            <x-input
                :label="__('Discussion Topic')"
                wire:model="sessionTopic"
                :placeholder="__('e.g. The First Revelations')"
                class="rounded-xl border-base-content/10"
            />
            <x-textarea
                :label="__('Brief Description')"
                wire:model="sessionDescription"
                rows="3"
                class="rounded-xl border-base-content/10"
            />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Speaker / Lecturer')"
                    wire:model="sessionSpeakerId"
                    :options="$speakers"
                    placeholder="{{ __('Select Speaker') }}"
                    class="rounded-xl border-base-content/10"
                />
                <x-input
                    type="datetime-local"
                    :label="__('Schedule Timing')"
                    wire:model="sessionScheduledAt"
                    class="rounded-xl border-base-content/10"
                />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    :label="__('Location / Room')"
                    wire:model="sessionLocation"
                    class="rounded-xl border-base-content/10"
                />
                <x-input
                    :label="__('Virtual Meeting Link')"
                    wire:model="sessionMeetingLink"
                    :placeholder="__('https://zoom.us/j/...')"
                    class="rounded-xl border-base-content/10"
                />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Gender Restrictions')"
                    wire:model="sessionGenderRestriction"
                    :options="[
                        ['id' => 'none', 'name' => __('Open to All')],
                        ['id' => 'brothers_only', 'name' => __('Brothers Only')],
                        ['id' => 'sisters_only', 'name' => __('Sisters Only')],
                    ]"
                    class="rounded-xl border-base-content/10"
                />
                <x-input
                    type="number"
                    :label="__('Max Capacity (optional)')"
                    wire:model="sessionMaxCapacity"
                    class="rounded-xl border-base-content/10"
                />
            </div>
            <div class="flex items-center justify-between p-3 rounded-2xl bg-base-200/50 border border-base-content/5">
                <div>
                    <p class="font-bold text-sm text-base-content">{{ __('Allow Registrations') }}</p>
                </div>
                <x-toggle wire:model="sessionIsRegistrationOpen" class="toggle-primary" />
            </div>
            <x-select
                :label="__('Session Status')"
                wire:model="sessionStatus"
                :options="[
                    ['id' => 'draft', 'name' => __('Draft')],
                    ['id' => 'published', 'name' => __('Published')],
                    ['id' => 'completed', 'name' => __('Completed')],
                    ['id' => 'cancelled', 'name' => __('Cancelled')],
                ]"
                class="rounded-xl border-base-content/10"
            />
            <x-file
                wire:model="sessionMaterialsFile"
                :label="__('Upload PDF / Handout')"
                hint="{{ __('Max 10MB') }}"
            />
        </div>
        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showSessionModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Save Session')" wire:click="saveSession" class="btn-primary rounded-xl font-bold" spinner="saveSession" />
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
</div>
