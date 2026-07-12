<?php

use App\Models\Donation;
use App\Models\Halaqah;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Halaqah Details')] #[Layout('layouts.web')] class extends Component
{
    use Toast;

    public Halaqah $halaqah;
    public bool $isWaitlist = false;
    public bool $donationModal = false;
    public string $donationAmount = '';
    public string $donationPaymentMethod = 'cash';
    public string $donationTransactionId = '';
    public string $donationNote = '';
    public mixed $donorId = '';

    public function mount(Halaqah $halaqah): void
    {
        $this->halaqah = $halaqah->load(['speaker', 'series.halaqahs', 'attendances', 'quizzes.questions', 'donations']);
        $this->updateWaitlistStatus();
    }

    public function updateWaitlistStatus(): void
    {
        if (!$this->halaqah->max_capacity) { $this->isWaitlist = false; return; }
        $this->isWaitlist = $this->halaqah->attendances()->where('status_new', 'rsvp')->count() >= $this->halaqah->max_capacity;
    }

    public function toggleRsvp(): void
    {
        if (!auth()->check()) { $this->redirectRoute('login'); return; }
        if (!$this->halaqah->is_registration_open) { $this->error(__('Registration is currently closed.')); return; }
        $attendance = $this->halaqah->attendances()->where('user_id', auth()->id())->first();
        if ($attendance) {
            $attendance->delete();
            $this->success(__('RSVP cancelled.'));
        } else {
            $this->updateWaitlistStatus();
            $status = $this->isWaitlist ? 'waitlist' : 'rsvp';
            $this->halaqah->attendances()->create(['user_id' => auth()->id(), 'status_new' => $status, 'status' => 'rsvp']);
            $status === 'waitlist' ? $this->info(__('Added to waitlist!')) : $this->success(__('RSVP successful! See you there.'));
        }
        $this->halaqah->load('attendances');
        $this->updateWaitlistStatus();
    }

    public function togglePreparation(): void
    {
        $attendance = $this->halaqah->attendances()->where('user_id', auth()->id())->first();
        if ($attendance) {
            $attendance->update(['preparation_completed' => !$attendance->preparation_completed]);
            $this->success($attendance->fresh()->preparation_completed ? __('Preparation marked complete!') : __('Marked as incomplete.'));
            $this->halaqah->load('attendances');
        }
    }

    public function openDonationModal(): void
    {
        $this->authorize('donations.transactions.manage');
        $this->reset(['donorId', 'donationAmount', 'donationPaymentMethod', 'donationTransactionId', 'donationNote']);
        $this->donationModal = true;
    }

    public function saveSessionDonation(): void
    {
        $this->authorize('donations.transactions.manage');
        $this->validate([
            'donationAmount' => 'required|numeric|min:1',
            'donationPaymentMethod' => 'required|in:cash,bkash,nagad,bank',
            'donationTransactionId' => 'required_if:donationPaymentMethod,bkash,nagad,bank',
        ]);
        Donation::create([
            'user_id' => $this->donorId ?: null,
            'halaqah_id' => $this->halaqah->id,
            'type' => 'halaqah',
            'amount' => $this->donationAmount,
            'payment_method' => $this->donationPaymentMethod,
            'transaction_id' => $this->donationTransactionId ?: null,
            'note' => $this->donationNote ?: null,
            'status' => 'confirmed',
            'donated_at' => now(),
            'is_anonymous' => empty($this->donorId),
        ]);
        $this->success(__('Donation recorded!'));
        $this->donationModal = false;
        $this->halaqah->load('donations');
    }

    public function getHasRsvpdProperty(): bool
    {
        return auth()->check() && $this->halaqah->attendances()->where('user_id', auth()->id())->where('status_new', 'rsvp')->exists();
    }

    public function getIsOnWaitlistProperty(): bool
    {
        return auth()->check() && $this->halaqah->attendances()->where('user_id', auth()->id())->where('status_new', 'waitlist')->exists();
    }

    public function getAttendanceRecordProperty(): mixed
    {
        return auth()->check() ? $this->halaqah->attendances()->where('user_id', auth()->id())->first() : null;
    }

    public function getSeriesProgressProperty(): array
    {
        if (!$this->halaqah->series) return ['completed' => 0, 'total' => 0, 'percentage' => 0];
        $total = $this->halaqah->series->halaqahs->count();
        $completed = $this->halaqah->series->halaqahs->where('status', 'completed')->count();
        return ['completed' => $completed, 'total' => $total, 'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0];
    }

    public function getTotalDonationsProperty(): float
    {
        return $this->halaqah->donations->sum('amount');
    }

    public function title(): string
    {
        return $this->halaqah->title . ' | PSTU Dawah';
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- Hero banner --}}
    <div class="relative bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/15 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20">
            <a href="{{ route('web.halaqahs') }}" wire:navigate class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm font-bold mb-6 transition-colors">
                <x-icon name="o-arrow-left" class="w-4 h-4" /> {{ __('Back to all sessions') }}
            </a>

            {{-- Status banners --}}
            @if($halaqah->status === 'cancelled')
                <div class="mb-4 flex items-center gap-3 bg-red-500/20 border border-red-500/30 text-red-200 rounded-2xl px-4 py-3 font-bold text-sm">
                    <x-icon name="o-exclamation-triangle" class="w-5 h-5 shrink-0 animate-pulse" /> {{ __('This session has been cancelled.') }}
                </div>
            @elseif($halaqah->status === 'completed')
                <div class="mb-4 flex items-center gap-3 bg-blue-500/20 border border-blue-500/30 text-blue-200 rounded-2xl px-4 py-3 font-bold text-sm">
                    <x-icon name="o-check-circle" class="w-5 h-5 shrink-0" /> {{ __('This session has been completed.') }}
                </div>
            @endif

            @if($halaqah->series)
                <a href="{{ route('web.course.show', $halaqah->series) }}" wire:navigate class="text-xs font-black text-amber-300 hover:text-amber-100 uppercase tracking-widest mb-2 transition-colors block">{{ $halaqah->series->title }}</a>
            @endif
            <h1 class="text-3xl md:text-5xl font-black mb-4 leading-tight tracking-tight">{{ $halaqah->title }}</h1>
            <div class="flex flex-wrap gap-3 items-center text-white/60 text-sm">
                <span class="flex items-center gap-1.5"><x-icon name="o-calendar" class="w-4 h-4" /> {{ $halaqah->scheduled_at->format('l, F j, Y · g:i A') }}</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span class="flex items-center gap-1.5"><x-icon name="o-map-pin" class="w-4 h-4" /> {{ $halaqah->location }}</span>
                @if($halaqah->gender_restriction !== 'none')
                    <span class="badge badge-sm bg-white/10 border-white/20 text-white">{{ str_replace('_', ' ', ucfirst($halaqah->gender_restriction)) }}</span>
                @endif
            </div>

            {{-- Countdown for upcoming --}}
            @if($halaqah->scheduled_at->isFuture())
            <div class="mt-5 inline-flex items-center gap-2 bg-primary/20 border border-primary/30 text-primary-content rounded-2xl px-4 py-2 text-sm font-black"
                x-data="{ t: '', target: new Date('{{ $halaqah->scheduled_at->toIso8601String() }}') }"
                x-init="setInterval(() => { let d=target-new Date(); if(d<=0){t='{{ __('Starting now') }}';return;} let days=Math.floor(d/86400000),h=Math.floor((d%86400000)/3600000),m=Math.floor((d%3600000)/60000),s=Math.floor((d%60000)/1000); t=days>0?days+'d '+h+'h '+m+'m':(h>0?h+'h '+m+'m '+s+'s':m+'m '+s+'s'); }, 1000)">
                <x-icon name="o-clock" class="w-4 h-4 text-primary-content/70" />
                <span x-text="t">{{ $halaqah->scheduled_at->diffForHumans() }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-16">
        <div class="grid lg:grid-cols-3 gap-8">

            {{-- ═══ MAIN COLUMN ═══ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Topic & Description --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <p class="text-xs font-black text-primary/70 uppercase tracking-widest mb-1">{{ __('Topic') }}</p>
                    <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 mb-3">{{ $halaqah->topic }}</h2>
                    @if($halaqah->description)
                        <div class="prose prose-sm max-w-none text-slate-500 dark:text-slate-400">{!! nl2br(e($halaqah->description)) !!}</div>
                    @endif
                </div>

                {{-- Quizzes --}}
                @if($halaqah->quizzes->isNotEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-amber-200/60 dark:border-amber-900/40 p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-icon name="o-question-mark-circle" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">{{ __('Session Quizzes') }}</h2>
                    </div>
                    <div class="space-y-3">
                        @foreach($halaqah->quizzes as $quiz)
                        @php
                            $attempt = auth()->check() ? $quiz->userAttempt() : null;
                            $submitted = $attempt && in_array($attempt->status, ['submitted','timed_out']);
                        @endphp
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                            <div>
                                <h4 class="font-black text-slate-800 dark:text-slate-100">{{ $quiz->title }}</h4>
                                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1">
                                    <span><x-icon name="o-document-text" class="w-3 h-3 inline" /> {{ $quiz->total_marks }} {{ __('marks') }}</span>
                                    @if($quiz->time_limit_minutes)
                                        <span><x-icon name="o-clock" class="w-3 h-3 inline" /> {{ $quiz->time_limit_minutes }}m</span>
                                    @endif
                                    @if($submitted)
                                        <span class="font-black text-success">{{ round($attempt->score_percentage) }}% {{ __('scored') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @auth
                                    @if($submitted)
                                        <a href="{{ $quiz->mode === 'live' ? route('web.quiz.live', $quiz) : route('web.quiz.take', $quiz) }}" wire:navigate class="btn btn-sm btn-ghost rounded-xl">{{ __('Results') }}</a>
                                    @elseif($quiz->isAvailable())
                                        @if($quiz->mode === 'live')
                                            <a href="{{ route('web.quiz.live', $quiz) }}" wire:navigate class="btn btn-sm btn-primary rounded-xl {{ $quiz->status === 'live' ? 'animate-pulse' : '' }}">
                                                <x-icon name="o-signal" class="w-4 h-4" /> {{ $quiz->status === 'live' ? __('Join Live') : __('Waiting...') }}
                                            </a>
                                        @else
                                            <a href="{{ route('web.quiz.take', $quiz) }}" wire:navigate class="btn btn-sm btn-success rounded-xl">
                                                <x-icon name="o-play" class="w-4 h-4" /> {{ __('Take Quiz') }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="badge badge-ghost">{{ ucfirst($quiz->status) }}</span>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" wire:navigate class="btn btn-sm btn-outline btn-primary rounded-xl">{{ __('Login to take') }}</a>
                                @endauth
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Materials --}}
                @if($halaqah->materials_path || ($halaqah->resources && count($halaqah->resources)))
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-4">{{ __('Session Materials') }}</h2>
                    @if($halaqah->materials_path)
                        <a href="{{ Storage::disk('public')->url($halaqah->materials_path) }}" target="_blank" class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-primary/5 border border-slate-100 dark:border-slate-800 transition-colors mb-3 group">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0"><x-icon name="o-document-arrow-down" class="w-5 h-5" /></div>
                            <div class="flex-grow"><p class="font-black text-sm">{{ __('Download Material') }}</p><p class="text-xs text-slate-400">{{ __('PDF / Document') }}</p></div>
                            <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4 text-slate-300 group-hover:text-primary transition-colors" />
                        </a>
                    @endif
                    @if($halaqah->resources && count($halaqah->resources))
                        <div class="space-y-2">
                            @foreach($halaqah->resources as $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-info/5 border border-slate-100 dark:border-slate-800 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-info/10 text-info flex items-center justify-center shrink-0"><x-icon name="o-link" class="w-5 h-5" /></div>
                                <span class="text-sm text-slate-500 truncate flex-grow group-hover:text-primary transition-colors">{{ $link }}</span>
                                <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4 text-slate-300 group-hover:text-primary transition-colors shrink-0" />
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- Series Progress --}}
                @if($halaqah->series)
                @php $sp = $this->seriesProgress; @endphp
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <a href="{{ route('web.course.show', $halaqah->series) }}" wire:navigate class="hover:text-primary transition-colors block">
                        <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-1">{{ $halaqah->series->title }}</h2>
                    </a>
                    <p class="text-xs text-slate-400 mb-4">{{ $sp['completed'] }} / {{ $sp['total'] }} {{ __('sessions completed') }}</p>
                    <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all duration-700" style="width: {{ $sp['percentage'] }}%"></div>
                    </div>
                    <div class="space-y-1.5">
                        @foreach($halaqah->series->halaqahs->sortBy('scheduled_at') as $session)
                        <div class="flex items-center gap-3 p-2 rounded-xl {{ $session->id === $halaqah->id ? 'bg-primary/8 border border-primary/20' : '' }}">
                            @if($session->status === 'completed')
                                <div class="w-5 h-5 rounded-full bg-success/20 text-success flex items-center justify-center shrink-0"><x-icon name="o-check" class="w-3 h-3" /></div>
                            @else
                                <div class="w-5 h-5 rounded-full border-2 {{ $session->id === $halaqah->id ? 'border-primary' : 'border-slate-200 dark:border-slate-700' }} shrink-0"></div>
                            @endif
                            <span class="text-sm {{ $session->id === $halaqah->id ? 'font-black text-primary' : 'text-slate-500 dark:text-slate-400' }}">{{ $session->title }}</span>
                            <span class="text-xs text-slate-400 ml-auto shrink-0">{{ $session->scheduled_at->format('M d') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Donations --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <x-icon name="o-banknotes" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">{{ __('Session Donations') }}</h2>
                        </div>
                        @auth
                            @can('donations.transactions.manage')
                                <button wire:click="openDonationModal" class="btn btn-sm btn-success rounded-xl">
                                    <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Collect') }}
                                </button>
                            @endcan
                        @endauth
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4 text-center border border-emerald-100 dark:border-emerald-900/40">
                        <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400">৳{{ number_format($this->totalDonations, 2) }}</p>
                        <p class="text-xs text-emerald-600/70 dark:text-emerald-500 mt-1 font-bold">{{ __('Total collected for this session') }}</p>
                    </div>
                </div>

            </div>

            {{-- ═══ SIDEBAR ═══ --}}
            <div class="space-y-5">

                {{-- Session Details --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <h3 class="font-black text-slate-800 dark:text-slate-100 mb-4">{{ __('Session Details') }}</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <x-icon name="o-calendar" class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                            <div>
                                <p class="font-black text-slate-700 dark:text-slate-200">{{ $halaqah->scheduled_at->format('l, F j, Y') }}</p>
                                <p class="text-slate-400">{{ $halaqah->scheduled_at->format('g:i A') }} · {{ $halaqah->scheduled_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-icon name="o-map-pin" class="w-5 h-5 text-secondary shrink-0 mt-0.5" />
                            <p class="text-slate-500 dark:text-slate-400">{{ $halaqah->location }}</p>
                        </div>
                        @if($halaqah->speaker)
                        <div class="flex items-start gap-3">
                            <x-icon name="o-user" class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <p class="text-slate-500 dark:text-slate-400">{{ $halaqah->speaker->name }}</p>
                        </div>
                        @endif
                        @if($halaqah->meeting_link)
                        <div class="flex items-start gap-3">
                            <x-icon name="o-link" class="w-5 h-5 text-info shrink-0 mt-0.5" />
                            <a href="{{ $halaqah->meeting_link }}" target="_blank" class="text-info hover:underline font-bold">{{ __('Join Online') }}</a>
                        </div>
                        @endif
                        @if($halaqah->max_capacity)
                        @php $rsvpCount = $halaqah->attendances->where('status_new','rsvp')->count(); $pct = min(100, round(($rsvpCount / $halaqah->max_capacity) * 100)); @endphp
                        <div class="pt-2">
                            <div class="flex items-center justify-between text-xs font-black mb-1.5">
                                <span class="text-slate-500">{{ __('Capacity') }}</span>
                                <span class="{{ $pct >= 100 ? 'text-warning' : 'text-success' }}">{{ $rsvpCount }} / {{ $halaqah->max_capacity }}</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-warning' : 'bg-success' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @else
                        @php $rsvpCount = $halaqah->attendances->where('status_new','rsvp')->count(); @endphp
                        @if($rsvpCount > 0)
                        <div class="flex items-center gap-2 text-xs text-slate-400"><x-icon name="o-users" class="w-4 h-4" /> {{ $rsvpCount }} {{ __('attending') }}</div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- RSVP Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <h3 class="font-black text-slate-800 dark:text-slate-100 mb-4">{{ __('Registration') }}</h3>

                    @guest
                        <div class="text-center py-4">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-3"><x-icon name="o-hand-raised" class="w-7 h-7 text-primary" /></div>
                            <p class="text-sm text-slate-500 mb-4">{{ __('Login to RSVP and secure your spot.') }}</p>
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-block rounded-2xl font-black">{{ __('Login to RSVP') }}</a>
                        </div>
                    @else
                        @if(!$halaqah->is_registration_open)
                            <div class="text-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3"><x-icon name="o-lock-closed" class="w-7 h-7 text-slate-400" /></div>
                                <p class="font-black text-slate-700 dark:text-slate-200 mb-1">{{ __('Registration Closed') }}</p>
                                <p class="text-xs text-slate-400">{{ __('You cannot RSVP at this time.') }}</p>
                            </div>
                        @elseif($this->hasRsvpd)
                            <div class="text-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-success/10 text-success flex items-center justify-center mx-auto mb-3"><x-icon name="o-check" class="w-7 h-7" /></div>
                                <p class="font-black text-success mb-1">{{ __('You are attending!') }}</p>
                                <p class="text-xs text-slate-400 mb-5">{{ __('We look forward to seeing you.') }}</p>
                                @if($this->attendanceRecord)
                                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-3 mb-4 text-left border border-slate-100 dark:border-slate-800">
                                    <p class="text-xs font-black text-slate-500 mb-2">{{ __('Preparation Status') }}</p>
                                    <button wire:click="togglePreparation" class="flex items-center gap-2 text-sm font-bold {{ $this->attendanceRecord->preparation_completed ? 'text-success' : 'text-slate-400' }}">
                                        <x-icon name="{{ $this->attendanceRecord->preparation_completed ? 's-check-circle' : 'o-circle-stack' }}" class="w-5 h-5" />
                                        {{ $this->attendanceRecord->preparation_completed ? __('Preparation Done ✓') : __('Mark as Prepared') }}
                                    </button>
                                </div>
                                @endif
                                <button wire:click="toggleRsvp" wire:loading.attr="disabled" class="btn btn-error btn-outline btn-block rounded-2xl font-black">{{ __('Cancel RSVP') }}</button>
                            </div>
                        @elseif($this->isOnWaitlist)
                            <div class="text-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-warning/10 text-warning flex items-center justify-center mx-auto mb-3"><x-icon name="o-clock" class="w-7 h-7" /></div>
                                <p class="font-black text-warning mb-1">{{ __('You are on the Waitlist') }}</p>
                                <p class="text-xs text-slate-400 mb-5">{{ __('We will notify you if a spot opens.') }}</p>
                                <button wire:click="toggleRsvp" wire:loading.attr="disabled" class="btn btn-error btn-outline btn-block rounded-2xl font-black">{{ __('Leave Waitlist') }}</button>
                            </div>
                        @elseif($isWaitlist)
                            <div class="text-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-warning/10 text-warning flex items-center justify-center mx-auto mb-3"><x-icon name="o-users" class="w-7 h-7" /></div>
                                <p class="font-black text-slate-700 dark:text-slate-200 mb-1">{{ __('Session is Full') }}</p>
                                <p class="text-xs text-slate-400 mb-5">{{ __('Join the waitlist — spots may open!') }}</p>
                                <button wire:click="toggleRsvp" wire:loading.attr="disabled" class="btn btn-warning btn-block rounded-2xl font-black">{{ __('Join Waitlist') }}</button>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3"><x-icon name="o-hand-raised" class="w-7 h-7" /></div>
                                <p class="font-black text-slate-700 dark:text-slate-200 mb-1">{{ __('Reserve your spot') }}</p>
                                @if($halaqah->max_capacity)
                                    <p class="text-xs text-success font-bold mb-5">{{ $halaqah->available_seats }} {{ __('spots remaining') }}</p>
                                @else
                                    <p class="text-xs text-slate-400 mb-5">{{ __('Let the organizers know you are coming.') }}</p>
                                @endif
                                <button wire:click="toggleRsvp" wire:loading.attr="disabled" class="btn btn-primary btn-block rounded-2xl font-black shadow-lg shadow-primary/30">{{ __('RSVP Now') }}</button>
                            </div>
                        @endif
                    @endguest
                </div>

                {{-- Series sidebar card --}}
                @if($halaqah->series)
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                    <h3 class="font-black text-slate-800 dark:text-slate-100 mb-1">{{ __('Part of Series') }}</h3>
                    <a href="{{ route('web.course.show', $halaqah->series) }}" wire:navigate class="text-primary font-black hover:text-secondary transition-colors">{{ $halaqah->series->title }}</a>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Level') }}: {{ ucfirst($halaqah->series->target_audience_level) }}</p>
                    @if($halaqah->series->description)
                        <p class="text-sm text-slate-500 mt-3 line-clamp-4">{{ $halaqah->series->description }}</p>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Donation Modal --}}
    <x-modal wire:model="donationModal" title="{{ __('Collect Session Donation') }}" separator>
        <div class="space-y-4">
            <x-choices label="{{ __('Donor (Optional)') }}" wire:model="donorId" :options="\App\Models\User::orderBy('name')->get()" option-label="name" option-value="id" placeholder="{{ __('Search member...') }}" hint="{{ __('Leave empty for anonymous') }}" single searchable />
            <x-input label="{{ __('Amount (৳)') }}" wire:model="donationAmount" type="number" prefix="৳" required />
            <x-select label="{{ __('Payment Method') }}" wire:model.live="donationPaymentMethod" :options="[['id'=>'cash','name'=>__('Cash')],['id'=>'bkash','name'=>__('bKash')],['id'=>'nagad','name'=>__('Nagad')],['id'=>'bank','name'=>__('Bank Transfer')]]" />
            @if(in_array($donationPaymentMethod, ['bkash','nagad','bank']))
                <x-input label="{{ __('Transaction ID') }}" wire:model="donationTransactionId" required />
            @endif
            <x-textarea label="{{ __('Note (Optional)') }}" wire:model="donationNote" rows="2" />
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('donationModal', false)" />
            <x-button label="{{ __('Record Donation') }}" class="btn-success" wire:click="saveSessionDonation" spinner />
        </x-slot:actions>
    </x-modal>

</div>
