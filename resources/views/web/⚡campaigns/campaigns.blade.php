<div class="bg-base-100 min-h-screen">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-20 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-rose-500/20 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <p class="text-rose-400 font-bold text-sm uppercase tracking-widest mb-3">Community Fund</p>
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">Support Our Causes</h1>
            <p class="text-white/50 text-lg max-w-xl mb-8">Every contribution counts. Help us build a stronger community through your generous donations.</p>
            <div class="flex gap-8">
                <div>
                    <p class="text-3xl font-black">৳{{ number_format($totalRaised) }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">Total Raised</p>
                </div>
                <div class="border-l border-white/20 pl-8">
                    <p class="text-3xl font-black">{{ $totalDonors }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">Donors</p>
                </div>
                <div class="border-l border-white/20 pl-8">
                    <p class="text-3xl font-black">{{ $activeCampaigns->count() }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">Active Campaigns</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Campaigns --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($activeCampaigns->count())
        <h2 class="text-2xl font-black text-base-content mb-8 tracking-tight">Active Campaigns</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($activeCampaigns as $campaign)
            <div class="bg-base-100 rounded-2xl overflow-hidden border border-base-content/5 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                <div class="h-52 relative overflow-hidden">
                    @if($campaign->cover_url)
                        <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $campaign->title }}">
                    @else
                        <div class="w-full h-full bg-gradient-to-tr from-rose-400/20 to-pink-500/20 flex items-center justify-center">
                            <x-icon name="o-heart" class="w-16 h-16 text-rose-500/30" />
                        </div>
                    @endif
                    <div class="absolute top-4 right-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm font-black px-3 py-1.5 rounded-full text-xs shadow-lg {{ $campaign->progress_percentage >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $campaign->progress_percentage }}% Funded
                    </div>
                </div>
                <div class="p-6 flex-grow flex flex-col">
                    <h3 class="font-bold text-xl text-base-content mb-2 group-hover:text-primary transition-colors">{{ $campaign->title }}</h3>
                    <p class="text-sm text-base-content/60 line-clamp-3 mb-6 flex-grow">{{ $campaign->description }}</p>
                    <div class="space-y-3 mb-5">
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-emerald-600 dark:text-emerald-400">৳{{ number_format($campaign->collected_amount) }} raised</span>
                            <span class="text-base-content/40">of ৳{{ number_format($campaign->goal_amount) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full" style="width: {{ $campaign->progress_percentage }}%"></div>
                        </div>
                    </div>
                    @auth
                        <a href="{{ route('app.donations') }}" wire:navigate class="btn btn-primary btn-block rounded-xl font-bold shadow-lg shadow-primary/20">Donate Now</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-block rounded-xl font-bold shadow-lg shadow-primary/20">Sign in to Donate</a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
        @else
            <div class="text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-heart" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No active campaigns</h3>
                <p class="text-base-content/50 text-sm">Check back soon for new fundraising initiatives.</p>
            </div>
        @endif

        {{-- Completed Campaigns --}}
        @if($completedCampaigns->count())
        <div class="mt-20">
            <h2 class="text-2xl font-black text-base-content mb-8 tracking-tight">Completed Campaigns</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($completedCampaigns as $campaign)
                <div class="bg-base-200/50 rounded-2xl p-6 border border-base-content/5 opacity-80">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-emerald-500/10 rounded-xl flex items-center justify-center">
                            <x-icon name="o-check-circle" class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base-content">{{ $campaign->title }}</h3>
                            <p class="text-xs text-base-content/50">৳{{ number_format($campaign->collected_amount) }} raised</p>
                        </div>
                    </div>
                    <div class="w-full bg-base-300 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-full rounded-full w-full"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
