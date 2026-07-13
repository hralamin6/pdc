<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Admin Operations Dashboard') }}</h1>
            <p class="text-xs md:text-sm text-base-content/50 mt-1">
                {{ __('Real-time insights, community engagement trends, and platform moderation tools.') }}
            </p>
        </div>
        <div class="flex shrink-0">
            <button wire:click="remindAll" wire:loading.attr="disabled" class="btn btn-primary rounded-2xl shadow-lg shadow-primary/10 flex items-center gap-2">
                <span wire:loading.remove wire:target="remindAll">
                    <x-icon name="o-bell" class="w-4 h-4" />
                </span>
                <span wire:loading wire:target="remindAll" class="loading loading-spinner loading-xs"></span>
                <span>{{ __('Send Daily Reminders') }}</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards (Top Bento Row - 4 columns) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Members --}}
        <div class="bg-base-100 p-5 rounded-3xl border border-base-content/5 shadow-sm flex items-center gap-4 hover:border-primary/20 transition-all">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-icon name="o-users" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/40 uppercase tracking-wider">{{ __('Total Members') }}</p>
                <p class="text-2xl font-black text-base-content mt-0.5">{{ $metrics['total_users'] }}</p>
            </div>
        </div>

        {{-- Daily Reports Today --}}
        <div class="bg-base-100 p-5 rounded-3xl border border-base-content/5 shadow-sm flex flex-col justify-between gap-3 hover:border-emerald-500/20 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 shrink-0">
                    <x-icon name="o-check-circle" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-xs font-bold text-base-content/40 uppercase tracking-wider">{{ __('Daily Reports') }}</p>
                    <p class="text-2xl font-black text-base-content mt-0.5">
                        {{ $metrics['submitted_today'] }} <span class="text-xs font-bold text-base-content/40">/ {{ $metrics['total_users'] }}</span>
                    </p>
                </div>
            </div>
            <div class="w-full space-y-1">
                <div class="flex justify-between text-[10px] font-bold text-base-content/60">
                    <span>{{ __('Today\'s Submission Rate') }}</span>
                    <span class="text-emerald-500">{{ $metrics['report_rate'] }}%</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $metrics['report_rate'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Quiz Engagement --}}
        <div class="bg-base-100 p-5 rounded-3xl border border-base-content/5 shadow-sm flex items-center gap-4 hover:border-amber-500/20 transition-all">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 shrink-0">
                <x-icon name="o-academic-cap" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/40 uppercase tracking-wider">{{ __('Quiz Activity') }}</p>
                <p class="text-2xl font-black text-base-content mt-0.5">
                    {{ $metrics['quiz_attempts'] }}
                    <span class="text-xs font-bold text-base-content/40">attempts ({{ $metrics['total_quizzes'] }} {{ __('quizzes') }})</span>
                </p>
            </div>
        </div>

        {{-- Halaqah Sessions --}}
        <div class="bg-base-100 p-5 rounded-3xl border border-base-content/5 shadow-sm flex items-center gap-4 hover:border-indigo-500/20 transition-all">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 shrink-0">
                <x-icon name="o-calendar" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/40 uppercase tracking-wider">{{ __('Halaqah Sessions') }}</p>
                <p class="text-2xl font-black text-base-content mt-0.5">
                    {{ $metrics['total_halaqahs'] }}
                    @if($metrics['avg_attendance'] > 0)
                        <span class="text-xs font-bold text-base-content/40">({{ __('avg. :count attendees', ['count' => $metrics['avg_attendance']]) }})</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Middle Bento Row (Analytics & Financial summaries) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Area: Engagement & Leaderboards (lg:col-span-2) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- 7-Day Trend Chart --}}
            <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                        <x-icon name="o-arrow-trending-up" class="w-5 h-5 text-primary" />
                        <span>{{ __('7-Day Habit Submission Trend') }}</span>
                    </h2>
                    <a href="{{ route('web.my-report.analytics') }}" wire:navigate class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>{{ __('Full Analytics') }}</span>
                        <x-icon name="o-chevron-right" class="w-3.5 h-3.5" />
                    </a>
                </div>

                <div class="flex items-end justify-between h-48 pt-4 gap-2">
                    @foreach($reportTrend as $day)
                        <div class="flex flex-col items-center flex-1 group">
                            <span class="text-[9px] font-bold text-primary bg-primary/10 rounded px-1.5 py-0.5 mb-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $day['rate'] }}% ({{ $day['count'] }})
                            </span>
                            <div class="w-full bg-base-200 rounded-2xl overflow-hidden h-32 flex items-end">
                                <div class="bg-gradient-to-t from-primary/75 to-primary hover:from-primary hover:to-primary-focus transition-all w-full rounded-t-xl" style="height: {{ max(5, $day['rate']) }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-base-content/50 mt-2 truncate w-full text-center" title="{{ $day['date'] }}">{{ $day['day'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Streak Leaderboard --}}
            <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                        <x-icon name="o-fire" class="w-5 h-5 text-orange-500 animate-pulse" />
                        <span>{{ __('Active Consistency Leaders') }}</span>
                    </h2>
                    <span class="text-[10px] font-bold text-base-content/40 uppercase tracking-widest">{{ __('Daily Habit Streaks') }}</span>
                </div>
                <div class="divide-y divide-base-content/5">
                    @forelse($topStreaks as $index => $streak)
                        <div class="py-3.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center font-bold text-xs">
                                    #{{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-base-content">{{ $streak->user?->name }}</p>
                                    <p class="text-[10px] text-base-content/40">{{ __('Total reports logged: :count', ['count' => $streak->total_reports]) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="badge badge-warning font-black text-xs px-2.5 py-2 flex gap-1 items-center">
                                    <x-icon name="o-fire" class="w-3.5 h-3.5 shrink-0" />
                                    <span>{{ $streak->current_streak }} {{ __('days') }}</span>
                                </div>
                                <span class="text-[9px] font-bold text-base-content/30" title="{{ __('Longest Streak') }}">(max: {{ $streak->longest_streak }})</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-base-content/40 italic py-6 text-center">{{ __('No streaks recorded. Encourage members to fill reports!') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Area: Treasury & Assets (lg:col-span-1) --}}
        <div class="space-y-6">
            {{-- Treasury Snapshot --}}
            <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                        <x-icon name="o-wallet" class="w-5 h-5 text-emerald-500" />
                        <span>{{ __('Treasury (This Month)') }}</span>
                    </h2>
                    <span class="text-[10px] text-emerald-500 bg-emerald-500/10 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $treasury['active_campaigns'] }} {{ __('Campaigns') }}
                    </span>
                </div>

                <div class="space-y-3 pt-2">
                    {{-- Income --}}
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-base-content/60">{{ __('Donations Collected') }}</span>
                        <span class="font-extrabold text-emerald-500">+${{ number_format($treasury['income'], 2) }}</span>
                    </div>

                    {{-- Expenses --}}
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-base-content/60">{{ __('Expenses Paid') }}</span>
                        <span class="font-extrabold text-rose-500">-${{ number_format($treasury['expenses'], 2) }}</span>
                    </div>

                    <div class="divider my-1 opacity-50"></div>

                    {{-- Net balance --}}
                    <div class="flex justify-between items-center text-sm font-black p-2.5 rounded-2xl {{ $treasury['net'] >= 0 ? 'bg-emerald-500/5 text-emerald-600' : 'bg-rose-500/5 text-rose-600' }}">
                        <span>{{ __('Net Balance') }}</span>
                        <span>${{ number_format($treasury['net'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Platform Assets --}}
            <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                    <x-icon name="o-archive-box" class="w-5 h-5 text-sky-500" />
                    <span>{{ __('Platform Assets') }}</span>
                </h2>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    {{-- Library Hubs --}}
                    <div class="p-3.5 rounded-2xl bg-base-200/40 border border-base-content/5 space-y-1">
                        <p class="text-[10px] font-bold text-base-content/40 uppercase">{{ __('Library Catalog') }}</p>
                        <p class="text-lg font-black text-base-content">{{ $library['total_books'] }} {{ __('Books') }}</p>
                        <div class="flex items-center gap-1.5 text-[9px] font-bold mt-1 text-base-content/50">
                            <span class="text-sky-500">{{ $library['active_borrows'] }} {{ __('Active Borrows') }}</span>
                            @if($library['overdue'] > 0)
                                <span class="text-rose-500">• {{ $library['overdue'] }} {{ __('Overdue') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Blog Content --}}
                    <div class="p-3.5 rounded-2xl bg-base-200/40 border border-base-content/5 space-y-1">
                        <p class="text-[10px] font-bold text-base-content/40 uppercase">{{ __('Blog Publications') }}</p>
                        <p class="text-lg font-black text-base-content">{{ $content['blog_posts'] }} {{ __('Posts') }}</p>
                        <p class="text-[9px] font-bold mt-1 text-base-content/50">
                            {{ $content['comments'] }} {{ __('Comments logged') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Quick Shortcuts --}}
            <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('app.users') }}" wire:navigate class="flex flex-col items-center p-3 rounded-2xl bg-indigo-500/5 hover:bg-indigo-500/10 border border-indigo-500/10 text-center transition-all group">
                        <x-icon name="o-users" class="w-5 h-5 text-indigo-500 group-hover:scale-110 transition-transform" />
                        <span class="text-[9px] font-bold text-base-content/85 mt-1.5">{{ __('Members') }}</span>
                    </a>
                    <a href="{{ route('app.quiz.manage') }}" wire:navigate class="flex flex-col items-center p-3 rounded-2xl bg-emerald-500/5 hover:bg-emerald-500/10 border border-emerald-500/10 text-center transition-all group">
                        <x-icon name="o-academic-cap" class="w-5 h-5 text-emerald-500 group-hover:scale-110 transition-transform" />
                        <span class="text-[9px] font-bold text-base-content/85 mt-1.5">{{ __('Quizzes') }}</span>
                    </a>
                    <a href="{{ route('app.donations.verify') }}" wire:navigate class="flex flex-col items-center p-3 rounded-2xl bg-rose-500/5 hover:bg-rose-500/10 border border-rose-500/10 text-center transition-all group">
                        <x-icon name="o-credit-card" class="w-5 h-5 text-rose-500 group-hover:scale-110 transition-transform" />
                        <span class="text-[9px] font-bold text-base-content/85 mt-1.5">{{ __('Donations') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bento Row (Alerts, Activity Log, Upcoming Events) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Alerts & Pending Verification --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-exclamation-triangle" class="w-5 h-5 text-rose-500 animate-pulse" />
                <span>{{ __('Needs Attention') }}</span>
            </h2>

            <div class="space-y-3 pt-2">
                {{-- Pending Grading --}}
                <div class="p-3 rounded-2xl border border-base-content/5 bg-base-200/40 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                            <x-icon name="o-pencil-square" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-base-content">{{ __('Quiz Answers') }}</p>
                            <p class="text-[10px] text-base-content/50">{{ __('Require manual grading') }}</p>
                        </div>
                    </div>
                    @if($metrics['pending_grading'] > 0)
                        <a href="{{ route('app.quiz.grade') }}" wire:navigate class="badge badge-warning font-black text-xs px-2.5 py-2 hover:scale-105 transition-transform">
                            {{ $metrics['pending_grading'] }}
                        </a>
                    @else
                        <span class="badge badge-success badge-sm font-bold text-[9px] uppercase">{{ __('All Graded') }}</span>
                    @endif
                </div>

                {{-- Pending Donations --}}
                <div class="p-3 rounded-2xl border border-base-content/5 bg-base-200/40 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-base-content">{{ __('Donations') }}</p>
                            <p class="text-[10px] text-base-content/50">{{ __('Awaiting manual verification') }}</p>
                        </div>
                    </div>
                    @if($metrics['pending_donations'] > 0)
                        <a href="{{ route('app.donations.verify') }}" wire:navigate class="badge badge-error font-black text-xs px-2.5 py-2 hover:scale-105 transition-transform">
                            {{ $metrics['pending_donations'] }}
                        </a>
                    @else
                        <span class="badge badge-success badge-sm font-bold text-[9px] uppercase">{{ __('Verified') }}</span>
                    @endif
                </div>

                {{-- Pending Borrow Requests --}}
                <div class="p-3 rounded-2xl border border-base-content/5 bg-base-200/40 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 flex items-center justify-center text-sky-500">
                            <x-icon name="o-book-open" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-base-content">{{ __('Borrow Requests') }}</p>
                            <p class="text-[10px] text-base-content/50">{{ __('Hub books checkout requests') }}</p>
                        </div>
                    </div>
                    @if($metrics['pending_borrows'] > 0)
                        <a href="{{ route('app.library-hubs') }}" wire:navigate class="badge badge-info font-black text-xs px-2.5 py-2 hover:scale-105 transition-transform text-white">
                            {{ $metrics['pending_borrows'] }}
                        </a>
                    @else
                        <span class="badge badge-success badge-sm font-bold text-[9px] uppercase">{{ __('No Requests') }}</span>
                    @endif
                </div>

                {{-- Pending Feedback --}}
                <div class="p-3 rounded-2xl border border-base-content/5 bg-base-200/40 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500">
                            <x-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-base-content">{{ __('Feedback Messages') }}</p>
                            <p class="text-[10px] text-base-content/50">{{ __('New user suggestions') }}</p>
                        </div>
                    </div>
                    @if($metrics['pending_feedback'] > 0)
                        <a href="{{ route('app.feedback.admin') }}" wire:navigate class="badge badge-secondary font-black text-xs px-2.5 py-2 hover:scale-105 transition-transform">
                            {{ $metrics['pending_feedback'] }}
                        </a>
                    @else
                        <span class="badge badge-success badge-sm font-bold text-[9px] uppercase">{{ __('No Unread') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent System Activity --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-clock" class="w-5 h-5 text-indigo-500" />
                <span>{{ __('Live Activities') }}</span>
            </h2>

            <div class="space-y-3 pt-2">
                @forelse($recentActivities as $act)
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-base-200/50 hover:bg-base-200/80 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                            {{ substr($act->causer?->name ?? 'S', 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-base-content/90 truncate">
                                <span class="font-bold text-indigo-500">{{ $act->causer?->name ?? __('System') }}</span>
                                {{ $act->description }}
                            </p>
                            <p class="text-[10px] text-base-content/40 font-medium mt-0.5">{{ $act->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-base-content/40 italic flex flex-col items-center justify-center">
                        <x-icon name="o-newspaper" class="w-8 h-8 mb-2 opacity-50" />
                        <span>{{ __('No activities logged yet.') }}</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Upcoming Events & Active Content --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-6">
            {{-- Upcoming Halaqahs --}}
            <div class="space-y-3">
                <h2 class="text-sm font-extrabold uppercase tracking-widest text-base-content/40 flex items-center gap-1.5">
                    <x-icon name="o-microphone" class="w-4 h-4" />
                    <span>{{ __('Halaqahs Schedule') }}</span>
                </h2>

                <div class="space-y-2">
                    @forelse($upcomingHalaqahs as $hal)
                        <div class="p-3 rounded-2xl bg-base-200/50 border border-base-content/5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content truncate">{{ $hal->title }}</p>
                                <p class="text-[10px] text-base-content/50 mt-0.5">
                                    🎤 {{ $hal->speaker?->name ?? __('No speaker assigned') }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] font-bold text-primary">{{ $hal->scheduled_at->format('M d') }}</p>
                                <p class="text-[9px] text-base-content/40 mt-0.5">{{ $hal->scheduled_at->format('h:i A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-base-content/40 italic">
                            {{ __('No upcoming halaqahs.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Popular Quizzes --}}
            <div class="space-y-3 pt-2">
                <h2 class="text-sm font-extrabold uppercase tracking-widest text-base-content/40 flex items-center gap-1.5">
                    <x-icon name="o-trophy" class="w-4 h-4" />
                    <span>{{ __('Popular Quizzes') }}</span>
                </h2>

                <div class="space-y-2">
                    @forelse($topQuizzes as $qz)
                        <div class="p-3 rounded-2xl bg-base-200/50 border border-base-content/5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-base-content truncate">{{ $qz->title }}</p>
                                <p class="text-[10px] text-base-content/50 mt-0.5">
                                    ❓ {{ $qz->questions_count ?? $qz->questions()->count() }} {{ __('questions') }}
                                </p>
                            </div>
                            <div class="badge badge-sm badge-outline font-bold text-[9px] uppercase shrink-0 py-2">
                                {{ $qz->attempts_count }} {{ __('Attempts') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-base-content/40 italic">
                            {{ __('No quizzes available.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
