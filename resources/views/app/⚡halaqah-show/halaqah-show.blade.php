<div class="max-w-7xl mx-auto py-6 space-y-6">
    {{-- Header / Quick Logistics --}}
    <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.halaqahs.schedule')" wire:navigate />
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest bg-primary/15 text-primary px-2.5 py-0.5 rounded-full mb-1 inline-block">
                        {{ $halaqah->series?->title ?? __('Independent Session') }}
                    </span>
                    <h1 class="text-xl sm:text-2xl font-black text-base-content tracking-tight">{{ $halaqah->title }}</h1>
                    <p class="text-xs text-base-content/50 font-semibold">{{ __('Topic:') }} <span class="text-base-content/75">{{ $halaqah->topic }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button
                    :label="__('Print Check-In QR')"
                    icon="o-qr-code"
                    onclick="let w = window.open(); w.document.write('<div style=&quot;text-align:center;font-family:sans-serif;margin-top:100px;&quot;><h1>{{ addslashes($halaqah->title) }} Check-In</h1><img src=&quot;https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('web.halaqah.show', ['halaqah' => $halaqah->id]) . '?qr=' . $halaqah->qr_token) }}&quot; style=&quot;width:300px;&quot;><p>Scan to Check-In</p></div>'); w.print();"
                    class="btn-outline btn-sm rounded-xl font-bold"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs font-semibold text-base-content/60">
            <div class="flex items-center gap-2">
                <x-icon name="o-user" class="w-4 h-4 text-primary" />
                <span>{{ __('Speaker:') }} <strong class="text-base-content">{{ $halaqah->speaker?->name ?? __('Unassigned') }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <x-icon name="o-calendar" class="w-4 h-4 text-primary" />
                <span>{{ __('Scheduled:') }} <strong class="text-base-content">{{ $halaqah->scheduled_at->format('M j, Y - g:i A') }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <x-icon name="o-map-pin" class="w-4 h-4 text-primary" />
                <span>{{ __('Location:') }} <strong class="text-base-content">{{ $halaqah->location }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Real-time Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm text-center">
            <p class="text-xl font-black text-primary">{{ $stats['total_present'] }} / {{ $stats['total_rsvps'] }}</p>
            <p class="text-[9px] font-bold text-base-content/50 uppercase mt-0.5">{{ __('Attendees Present') }}</p>
        </div>
        <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm text-center">
            <p class="text-xl font-black text-error">{{ $stats['total_absent'] }}</p>
            <p class="text-[9px] font-bold text-base-content/50 uppercase mt-0.5">{{ __('Attendees Absent') }}</p>
        </div>
        <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm text-center">
            <p class="text-xl font-black text-emerald-600">{{ $stats['total_prepared'] }}</p>
            <p class="text-[9px] font-bold text-base-content/50 uppercase mt-0.5">{{ __('Preparation Done') }}</p>
        </div>
        <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm text-center">
            <p class="text-xl font-black text-amber-500">{{ $stats['avg_rating'] }} ⭐</p>
            <p class="text-[9px] font-bold text-base-content/50 uppercase mt-0.5">{{ __('Avg Engagement') }}</p>
        </div>
        <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm text-center col-span-2 sm:col-span-1">
            <p class="text-xl font-black text-indigo-600">${{ number_format($stats['total_donated'], 2) }}</p>
            <p class="text-[9px] font-bold text-base-content/50 uppercase mt-0.5">{{ __('Session Donation') }}</p>
        </div>
    </div>

    {{-- Main Worksheet Layout --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden p-6 space-y-6">
        
        {{-- Navigation Tabs --}}
        <div class="tabs tabs-boxed bg-base-200/50 p-1 rounded-2xl">
            <button wire:click="$set('activeTab', 'attendance')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'attendance' ? 'tab-active bg-primary text-primary-content' : '' }}">
                <x-icon name="o-clipboard-document-check" class="w-4 h-4 mr-1.5" />
                {{ __('Attendance Sheet') }}
            </button>
            <button wire:click="$set('activeTab', 'resources')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'resources' ? 'tab-active bg-primary text-primary-content' : '' }}">
                <x-icon name="o-folder-open" class="w-4 h-4 mr-1.5" />
                {{ __('Handouts & Files') }}
            </button>
            <button wire:click="$set('activeTab', 'quizzes')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'quizzes' ? 'tab-active bg-primary text-primary-content' : '' }}">
                <x-icon name="o-academic-cap" class="w-4 h-4 mr-1.5" />
                {{ __('Session Quizzes') }}
            </button>
            <button wire:click="$set('activeTab', 'donations')" class="tab flex-1 font-bold text-xs rounded-xl {{ $activeTab === 'donations' ? 'tab-active bg-primary text-primary-content' : '' }}">
                <x-icon name="o-banknotes" class="w-4 h-4 mr-1.5" />
                {{ __('Session Donations') }}
            </button>
        </div>

        {{-- Tab 1: Attendance Worksheet (Option 1.B & 3.B) --}}
        @if($activeTab === 'attendance')
            <div class="space-y-6">
                {{-- Toolbar / Add Walk-in --}}
                <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 bg-base-200/40 p-4 rounded-2xl border border-base-content/5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                        <x-input
                            wire:model.live.debounce.300ms="rosterSearch"
                            :placeholder="__('Search student on roster...')"
                            icon="o-magnifying-glass"
                            class="input-sm rounded-xl border-base-content/10 w-full sm:w-60"
                        />
                        <div class="flex items-center gap-2">
                            <x-button
                                wire:click="markAllPresent"
                                :label="__('Present All')"
                                icon="o-check-circle"
                                class="btn-success btn-xs rounded-lg text-white font-bold"
                                wire:confirm="{{ __('Mark all rostered students as present?') }}"
                            />
                            <x-button
                                wire:click="markAllAbsent"
                                :label="__('Absent All')"
                                icon="o-x-circle"
                                class="btn-outline btn-error btn-xs rounded-lg font-bold"
                                wire:confirm="{{ __('Mark all rostered students as absent?') }}"
                            />
                            <x-button
                                wire:click="markAllPrepared"
                                :label="__('Readed All')"
                                icon="o-book-open"
                                class="btn-accent btn-xs rounded-lg text-white font-bold"
                                wire:confirm="{{ __('Mark all present students as having completed their preparation?') }}"
                            />
                        </div>
                    </div>
                    <form wire:submit.prevent="addWalkInStudent" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                        <x-select
                            wire:model="selectedUserId"
                            :options="$allUsers"
                            placeholder="{{ __('Select Student to Add') }}"
                            class="select-sm rounded-xl border-base-content/10 w-full sm:w-56"
                        />
                        <x-button
                            type="submit"
                            :label="__('Add Walk-in')"
                            icon="o-plus"
                            class="btn-primary btn-sm rounded-xl font-bold w-full sm:w-auto"
                        />
                    </form>
                </div>

                {{-- Attendance Sheet Grid / Table --}}
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                                <th>{{ __('Attendee / Student') }}</th>
                                <th class="text-center">{{ __('Present') }}</th>
                                <th class="text-center">{{ __('Preparation Done') }}</th>
                                <th class="text-center">{{ __('Engagement rating') }}</th>
                                <th class="text-right">{{ __('Check-In Method') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-content/5">
                            @forelse($attendances as $row)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full bg-primary/10">
                                                    <img src="{{ $row->user?->avatar_url }}" alt="{{ $row->user?->name }}" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-xs text-base-content">{{ $row->user?->name }}</div>
                                                <div class="text-[10px] text-base-content/40">{{ $row->user?->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <x-button
                                            wire:click="toggleAttendance({{ $row->id }})"
                                            class="btn-xs rounded-lg font-bold {{ $row->attended ? 'btn-success text-white' : 'btn-outline btn-ghost text-base-content/40' }}"
                                        >
                                            {{ $row->attended ? __('Present') : __('Absent') }}
                                        </x-button>
                                    </td>
                                    <td class="text-center">
                                        <x-button
                                            wire:click="togglePreparation({{ $row->id }})"
                                            class="btn-xs rounded-lg font-bold {{ $row->preparation_completed ? 'btn-accent text-white' : 'btn-outline btn-ghost text-base-content/40' }}"
                                            :disabled="!$row->attended"
                                        >
                                            {{ $row->preparation_completed ? __('Completed') : __('Not Done') }}
                                        </x-button>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button
                                                    wire:click="setRating({{ $row->id }}, {{ $i }})"
                                                    class="focus:outline-none transition-transform active:scale-125 {{ !$row->attended ? 'cursor-not-allowed opacity-30' : '' }}"
                                                    @if(!$row->attended) disabled @endif
                                                >
                                                    <x-icon
                                                        name="o-star"
                                                        class="w-4 h-4 {{ ($row->rating >= $i) ? 'text-amber-400 fill-amber-400' : 'text-base-content/20' }}"
                                                    />
                                                </button>
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="text-right text-xs text-base-content/50 font-semibold capitalize">
                                        @if($row->attended)
                                            <span class="badge badge-ghost badge-sm text-[10px] uppercase font-bold text-base-content/60">
                                                {{ str_replace('_', ' ', $row->check_in_method ?? 'self scan') }}
                                            </span>
                                            @if($row->checked_in_at)
                                                <span class="block text-[9px] mt-0.5 text-base-content/40">{{ $row->checked_in_at->format('g:i A') }}</span>
                                            @endif
                                        @else
                                            <span class="text-base-content/30">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-sm text-base-content/40">
                                        {{ __('No students registered on roster for this session yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Tab 2: Handouts & Resources (Option 2.C) --}}
        @if($activeTab === 'resources')
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Upload Panel --}}
                    <div class="md:col-span-1 bg-base-200/40 p-5 rounded-2xl border border-base-content/5 space-y-4 h-fit">
                        <h4 class="font-extrabold text-sm text-base-content">{{ __('Upload Study Handout') }}</h4>
                        <form wire:submit.prevent="uploadMaterial" class="space-y-3">
                            <x-input
                                :label="__('Document Title')"
                                wire:model="newMaterialTitle"
                                :placeholder="__('e.g. Surah Yaseen Handout')"
                                class="rounded-xl border-base-content/10"
                            />
                            <x-file
                                wire:model="newMaterialFile"
                                :label="__('Choose File')"
                                hint="PDF, Slides (Max 10MB)"
                            />
                            <x-button
                                type="submit"
                                :label="__('Upload File')"
                                icon="o-cloud-arrow-up"
                                class="btn-primary btn-sm w-full rounded-xl font-bold mt-2"
                                spinner="uploadMaterial"
                            />
                        </form>
                    </div>

                    {{-- Materials List --}}
                    <div class="md:col-span-2 space-y-3">
                        <h4 class="font-extrabold text-sm text-base-content/60 uppercase tracking-wider">{{ __('Session Handouts') }}</h4>
                        
                        <div class="divide-y divide-base-content/5">
                            @forelse($halaqah->resources ?? [] as $index => $res)
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 flex items-center justify-center">
                                            <x-icon name="o-document-text" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-base-content">{{ $res['title'] }}</p>
                                            <p class="text-[10px] text-base-content/40 mt-0.5">{{ __('Uploaded:') }} {{ \Carbon\Carbon::parse($res['uploaded_at'])->format('M j, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-button
                                            icon="o-arrow-down-tray"
                                            :link="Storage::url($res['path'])"
                                            target="_blank"
                                            class="btn-ghost btn-circle btn-sm text-primary"
                                            tooltip="Download"
                                        />
                                        <x-button
                                            icon="o-trash"
                                            wire:click="removeMaterial({{ $index }})"
                                            wire:confirm="Remove this material?"
                                            class="btn-ghost btn-circle btn-sm text-error"
                                            tooltip="Delete"
                                        />
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-base-content/40 text-xs">
                                    {{ __('No handouts uploaded yet for this session.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tab 3: Linked Quizzes (Option 2.C) --}}
        @if($activeTab === 'quizzes')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-base-content/50 uppercase tracking-wider">{{ __('Session Assessments') }}</h3>
                </div>

                <div class="divide-y divide-base-content/5">
                    @forelse($quizzes as $quiz)
                        <div class="py-4 space-y-4">
                            <div class="flex justify-between items-center bg-base-200/30 p-4 rounded-2xl border border-base-content/5">
                                <div>
                                    <h4 class="font-extrabold text-sm text-base-content">{{ $quiz->title }}</h4>
                                    <p class="text-xs text-base-content/50 mt-0.5">{{ $quiz->questions_count ?? 0 }} {{ __('Questions') }} • {{ __('Passing Score:') }} {{ $quiz->passing_score }}%</p>
                                </div>
                                <span class="badge badge-sm font-bold {{ $quiz->is_published ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $quiz->is_published ? __('Published') : __('Draft') }}
                                </span>
                            </div>

                            {{-- Student Scores --}}
                            <div class="overflow-x-auto pl-4">
                                <table class="table w-full text-xs">
                                    <thead>
                                        <tr class="text-base-content/40 font-bold">
                                            <th>{{ __('Student') }}</th>
                                            <th>{{ __('Attempts') }}</th>
                                            <th>{{ __('High Score') }}</th>
                                            <th>{{ __('Result') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-base-content/5">
                                        @forelse($attendances as $row)
                                            @php
                                                $studentAttempts = $quiz->attempts->where('user_id', $row->user_id);
                                                $bestAttempt = $studentAttempts->sortByDesc('score')->first();
                                            @endphp
                                            <tr>
                                                <td class="font-semibold">{{ $row->user?->name }}</td>
                                                <td>{{ $studentAttempts->count() }}</td>
                                                <td>
                                                    @if($bestAttempt)
                                                        <strong class="text-base-content">{{ $bestAttempt->score }}%</strong>
                                                    @else
                                                        <span class="text-base-content/30">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($bestAttempt)
                                                        @if($bestAttempt->score >= $quiz->passing_score)
                                                            <span class="badge badge-success badge-sm font-bold text-[9px]">{{ __('PASSED') }}</span>
                                                        @else
                                                            <span class="badge badge-error badge-sm font-bold text-[9px]">{{ __('FAILED') }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-ghost badge-sm text-[9px]">{{ __('PENDING') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-base-content/30 py-4">{{ __('No student data.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-base-content/40 text-xs">
                            {{ __('No assessments or quizzes linked to this session yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Tab 4: Donations Panel --}}
        @if($activeTab === 'donations')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-base-content/50 uppercase tracking-wider">{{ __('Donations Collected during Class') }}</h3>
                        <p class="text-xs text-base-content/40 mt-0.5">{{ __('Log offline/cash contributions directly linked to this study circle.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-button
                            :label="__('Bulk Donation')"
                            icon="o-wallet"
                            wire:click="openBulkDonationModal"
                            class="btn-secondary btn-sm rounded-xl font-bold"
                        />
                        <x-button
                            :label="__('Record Cash')"
                            icon="o-plus"
                            wire:click="openDonationModal"
                            class="btn-primary btn-sm rounded-xl font-bold"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                                <th>{{ __('Donor') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Notes / Detail') }}</th>
                                <th>{{ __('Recorded At') }}</th>
                                <th class="text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-content/5">
                            @forelse($donations as $don)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    <td>
                                        @if($don->user)
                                            <div class="font-bold text-xs text-base-content">{{ $don->user->name }}</div>
                                            <div class="text-[9px] text-base-content/40">{{ $don->user->email }}</div>
                                        @else
                                            <span class="italic text-base-content/50 text-xs font-semibold">{{ __('Anonymous Donor') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-outline badge-sm text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $don->payment_method) }}</span>
                                    </td>
                                    <td class="text-xs text-base-content/70 font-medium">
                                        {{ $don->note ?? '-' }}
                                    </td>
                                    <td class="text-xs text-base-content/50 font-semibold">
                                        {{ $don->donated_at ? $don->donated_at->format('M j, g:i A') : $don->created_at->format('M j, g:i A') }}
                                    </td>
                                    <td class="text-right font-black text-xs text-indigo-650">
                                        ${{ number_format($don->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-sm text-base-content/40">
                                        {{ __('No donations recorded for this session yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Record Offline Donation Modal --}}
    <x-modal wire:model="showDonationModal" class="backdrop-blur">
        {{-- Custom Premium Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-5 text-white shadow-md -mx-3 -mt-3 mb-5">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur mb-2 border border-white/10 uppercase tracking-widest">
                    <x-icon name="o-banknotes" class="w-3 h-3" />
                    <span>{{ __('Manual Entry') }}</span>
                </span>
                <h3 class="text-lg font-black tracking-tight">{{ __('Log Session Donation') }}</h3>
                <p class="text-xs text-white/80 mt-0.5 leading-relaxed">{{ __('Record a donation collected physically during this class session.') }}</p>
            </div>
            <div class="absolute -right-12 -top-12 w-36 h-36 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-4">
            <x-select
                :label="__('Link to Registered User (Optional)')"
                wire:model="donationUserId"
                :options="$allUsers"
                placeholder="{{ __('Anonymous / Guest Contribution') }}"
                class="rounded-xl border-base-content/10 shadow-sm"
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    type="number"
                    step="0.01"
                    :label="__('Collection Amount ($)')"
                    wire:model="donationAmount"
                    placeholder="0.00"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
                <x-select
                    :label="__('Collection Method')"
                    wire:model.live="paymentMethod"
                    :options="[
                        ['id' => 'cash', 'name' => __('Cash')],
                        ['id' => 'bkash', 'name' => __('bKash')],
                        ['id' => 'nagad', 'name' => __('Nagad')],
                        ['id' => 'bank', 'name' => __('Bank Transfer')],
                        ['id' => 'other', 'name' => __('Other')],
                    ]"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Target Bank Account')"
                    wire:model="donationBankAccountId"
                    :options="$donationBankAccounts"
                    placeholder="{{ __('Select Account (Optional)') }}"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
                <x-input
                    :label="__('Transaction ID (Optional)')"
                    wire:model="donationTransactionId"
                    placeholder="{{ __('e.g. TrxID or Receipt No') }}"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
            </div>

            <x-textarea
                :label="__('Collector Notes')"
                wire:model="donationNotes"
                rows="3"
                placeholder="{{ __('e.g. Mosque donation box, cash given directly to mentor.') }}"
                class="rounded-xl border-base-content/10 shadow-sm font-medium"
            />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showDonationModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Confirm Collection')" wire:click="recordDonation" class="btn-primary rounded-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-none" icon="o-check" spinner="recordDonation" />
        </x-slot:actions>
    </x-modal>

    {{-- Bulk Record Offline Donation Modal --}}
    <x-modal wire:model="showBulkDonationModal" class="backdrop-blur">
        {{-- Custom Premium Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 p-5 text-white shadow-md -mx-3 -mt-3 mb-5">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur mb-2 border border-white/10 uppercase tracking-widest">
                    <x-icon name="o-wallet" class="w-3 h-3" />
                    <span>{{ __('Bulk Recording') }}</span>
                </span>
                <h3 class="text-lg font-black tracking-tight">{{ __('Bulk Record Donations') }}</h3>
                <p class="text-xs text-white/80 mt-0.5 leading-relaxed">{{ __('Log individual cash amounts for all checked-in/present attendees.') }}</p>
            </div>
            <div class="absolute -right-12 -top-12 w-36 h-36 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1">
            @php
                $presentAttendees = $attendances->where('attended', true);
            @endphp

            @forelse($presentAttendees as $row)
                <div class="flex items-center justify-between gap-4 p-2 rounded-xl bg-base-200/50 border border-base-content/5">
                    <div class="flex items-center gap-2">
                        <div class="avatar">
                            <div class="w-7 h-7 rounded-full bg-primary/10">
                                <img src="{{ $row->user?->avatar_url }}" alt="{{ $row->user?->name }}" />
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-xs text-base-content">{{ $row->user?->name }}</p>
                        </div>
                    </div>
                    <div class="w-28 shrink-0">
                        <x-input
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            wire:model="bulkAmounts.{{ $row->user_id }}"
                            class="input-xs rounded-lg text-right font-bold"
                        />
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-base-content/50">
                    {{ __('No students are currently marked as present. Mark students present first to collect bulk donations.') }}
                </div>
            @endforelse
        </div>

        @if($presentAttendees->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <x-select
                    :label="__('Collection Method')"
                    wire:model.live="bulkPaymentMethod"
                    :options="[
                        ['id' => 'cash', 'name' => __('Cash')],
                        ['id' => 'bkash', 'name' => __('bKash')],
                        ['id' => 'nagad', 'name' => __('Nagad')],
                        ['id' => 'bank', 'name' => __('Bank Transfer')],
                        ['id' => 'other', 'name' => __('Other')],
                    ]"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
                <x-input
                    :label="__('General Notes')"
                    wire:model="bulkDonationNotes"
                    :placeholder="__('Weekly Circle collection')"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <x-select
                    :label="__('Target Bank Account')"
                    wire:model="bulkBankAccountId"
                    :options="$bulkBankAccounts"
                    placeholder="{{ __('Select Account (Optional)') }}"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
                <x-input
                    :label="__('Transaction ID (Optional)')"
                    wire:model="bulkTransactionId"
                    placeholder="{{ __('e.g. TrxID or Receipt No') }}"
                    class="rounded-xl border-base-content/10 shadow-sm"
                />
            </div>
        @endif

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showBulkDonationModal', false)" class="btn-ghost rounded-xl" />
            @if($presentAttendees->isNotEmpty())
                <x-button :label="__('Save Batch')" wire:click="saveBulkDonations" class="btn-primary rounded-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-650 hover:from-purple-700 hover:to-indigo-700 border-none" icon="o-check" spinner="saveBulkDonations" />
            @endif
        </x-slot:actions>
    </x-modal>
</div>
