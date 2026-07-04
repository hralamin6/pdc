<?php

use App\Models\DailyReport;
use App\Models\UserStreak;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Reports')] #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $user = auth()->user();
        
        // Ensure streak record exists
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        // Get last 7 days of reports
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $report = DailyReport::where('user_id', $user->id)->where('date', $date)->first();
            $last7Days->push([
                'date' => Carbon::parse($date),
                'status' => $report ? $report->status : 'missing',
                'is_today' => $i === 0
            ]);
        }

        $todayReport = DailyReport::where('user_id', $user->id)->where('date', now()->format('Y-m-d'))->first();

        return [
            'streak' => $streak,
            'last7Days' => $last7Days,
            'todayReport' => $todayReport,
        ];
    }
};
?>

<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-base-content">{{ __('My Daily Reports') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Track your spiritual and personal growth journey.') }}</p>
        </div>
        <div class="flex gap-2">
            <x-button icon="o-cog-6-tooth" :link="route('app.daily-reports.settings')" wire:navigate class="btn-ghost btn-circle" tooltip="Settings" />
            @if(auth()->user()->hasRole(['mentor', 'admin', 'super-admin']))
                <x-button icon="o-shield-check" :link="route('app.daily-reports.admin')" wire:navigate class="btn-outline btn-primary" label="Admin View" />
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Streak Card --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6 flex flex-col items-center justify-center text-center">
            <x-icon name="o-fire" class="w-12 h-12 text-orange-500 mb-2" />
            <h2 class="text-4xl font-black">{{ $streak->current_streak }}</h2>
            <p class="text-sm font-bold text-base-content/70 uppercase tracking-widest mt-1">Day Streak</p>
            <p class="text-xs text-base-content/50 mt-2">Longest: {{ $streak->longest_streak }} days</p>
        </div>

        {{-- Action Card --}}
        <div class="md:col-span-2 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-2xl shadow-sm border border-primary/20 p-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-bold text-base-content mb-1">
                    @if($todayReport && $todayReport->status === 'submitted')
                        🎉 You've completed today's report!
                    @else
                        It's time for today's reflection
                    @endif
                </h3>
                <p class="text-base-content/70 text-sm">
                    @if($todayReport && $todayReport->status === 'submitted')
                        Great job maintaining your consistency. You can still update it if you forgot something.
                    @else
                        Take a few minutes to log your daily activities and Ibadah.
                    @endif
                </p>
            </div>
            <x-button 
                :label="$todayReport && $todayReport->status === 'submitted' ? 'Update Report' : 'Fill Today\'s Report'" 
                icon="o-pencil-square" 
                class="btn-primary rounded-full px-8 font-bold shadow-lg shadow-primary/30 shrink-0" 
                :link="route('app.daily-reports.form')" 
                wire:navigate 
            />
        </div>
    </div>

    {{-- Activity Chart (Last 7 Days) --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
        <h3 class="font-bold text-lg mb-4 text-base-content">Past 7 Days</h3>
        <div class="flex flex-wrap gap-4 justify-between md:justify-start">
            @foreach($last7Days as $day)
                <div class="flex flex-col items-center gap-2">
                    <p class="text-xs font-semibold text-base-content/50 uppercase">{{ $day['date']->format('D') }}</p>
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-sm transition-all
                        @if($day['status'] === 'submitted') bg-success text-success-content shadow-lg shadow-success/20
                        @elseif($day['status'] === 'draft') bg-warning text-warning-content
                        @elseif($day['is_today']) bg-base-200 border-2 border-dashed border-primary/50 text-primary
                        @else bg-base-200 text-base-content/30
                        @endif
                    ">
                        {{ $day['date']->format('d') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
