<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <x-icon name="o-megaphone" class="w-7 h-7 text-indigo-500" />
                {{ __('Fundraising Campaigns') }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Manage community projects, collect targeted donations, and track campaign completion metrics.') }}
            </p>
        </div>
        <div>
            <x-button label="{{ __('Create Campaign') }}" icon="o-plus" wire:click="createCampaign" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-sm" />
        </div>
    </div>

    {{-- Visual Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($campaigns as $campaign)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col group relative">
                
                {{-- Cover Image --}}
                <div class="h-44 w-full relative bg-slate-100 dark:bg-slate-800 shrink-0">
                    @if($campaign->cover_url)
                        <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                    @else
                        <div class="w-full h-full bg-gradient-to-tr from-rose-500/10 to-indigo-500/10 flex items-center justify-center">
                            <x-icon name="o-heart" class="w-16 h-16 text-rose-500/25" />
                        </div>
                    @endif
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-4 right-4">
                        @if($campaign->status === 'active')
                            <span class="badge badge-success font-black text-xs px-2.5 py-1 text-white shadow-sm">{{ __('Active') }}</span>
                        @elseif($campaign->status === 'completed')
                            <span class="badge badge-neutral font-black text-xs px-2.5 py-1 text-slate-800 dark:text-slate-200 shadow-sm">{{ __('Completed') }}</span>
                        @else
                            <span class="badge badge-error font-black text-xs px-2.5 py-1 text-white shadow-sm">{{ __('Cancelled') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Card Info --}}
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-black text-slate-800 dark:text-white text-base leading-snug line-clamp-2 mb-2 group-hover:text-indigo-600 transition-colors">
                            {{ $campaign->title }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed mb-4">
                            {{ $campaign->description ?: __('No description provided.') }}
                        </p>
                    </div>

                    {{-- Progress & Goals --}}
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-end justify-between text-xs">
                            <div>
                                <span class="font-black text-slate-700 dark:text-slate-300">৳{{ number_format($campaign->collected_amount) }}</span>
                                <span class="text-slate-400 dark:text-slate-500"> {{ __('of') }} ৳{{ number_format($campaign->goal_amount ?? 0) }}</span>
                            </div>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $campaign->progress_percentage }}%</span>
                        </div>
                        
                        {{-- Progress Bar --}}
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-400 to-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $campaign->progress_percentage }}%"></div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-2 pt-3">
                            <x-button label="{{ __('View Details') }}" icon="o-eye" link="{{ route('app.donations.campaign-details', $campaign->id) }}" class="btn-sm flex-1 bg-slate-100 dark:bg-slate-800 border-none text-slate-800 dark:text-slate-200 rounded-xl font-bold" />
                            <x-button label="{{ __('Edit') }}" icon="o-pencil" wire:click="editCampaign({{ $campaign->id }})" class="btn-sm bg-indigo-50 dark:bg-indigo-950/30 border-none text-indigo-600 dark:text-indigo-400 rounded-xl font-bold hover:bg-indigo-100" />
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
                <x-icon name="o-folder-open" class="w-16 h-16 text-slate-300 dark:text-slate-700 mx-auto mb-3" />
                <h3 class="font-black text-slate-800 dark:text-white text-lg mb-1">{{ __('No Campaigns Found') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">{{ __('Create fundraising campaigns to track targeted donations for specific projects.') }}</p>
                <x-button label="{{ __('Create First Campaign') }}" icon="o-plus" wire:click="createCampaign" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold" />
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($campaigns->hasPages())
        <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
            {{ $campaigns->links() }}
        </div>
    @endif

    {{-- Create/Edit Campaign Drawer ( MaryUI x-drawer or x-modal ) --}}
    <x-drawer wire:model="campaignModal" title="{{ $campaignId ? __('Edit Campaign') : __('Create New Campaign') }}" right class="w-11/12 md:w-[500px]" separator>
        <form wire:submit.prevent="saveCampaign" class="space-y-5">
            {{-- Title --}}
            <x-input label="{{ __('Campaign Title') }}" wire:model="title" placeholder="{{ __('e.g. Mosque Expansion Fund') }}" required />

            {{-- Description --}}
            <x-textarea label="{{ __('Description') }}" wire:model="description" placeholder="{{ __('Explain the details and purpose of this fund...') }}" rows="5" />

            {{-- Goal Amount --}}
            <x-input label="{{ __('Target Goal Amount (BDT)') }}" type="number" prefix="৳" wire:model="goal_amount" placeholder="{{ __('e.g. 500000') }}" />

            {{-- Dates --}}
            <div class="grid grid-cols-2 gap-4">
                <x-input label="{{ __('Starts At') }}" type="datetime-local" wire:model="starts_at" />
                <x-input label="{{ __('Ends At') }}" type="datetime-local" wire:model="ends_at" />
            </div>

            {{-- Status (Only editable on Edit) --}}
            @if($campaignId)
                <x-select label="{{ __('Status') }}" wire:model="status" :options="[
                    ['id' => 'active', 'name' => __('Active')],
                    ['id' => 'completed', 'name' => __('Completed')],
                    ['id' => 'cancelled', 'name' => __('Cancelled')]
                ]" />
            @endif

            {{-- Cover Image --}}
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Cover Image') }}</label>
                
                @if($existingCoverUrl && !$cover_image)
                    <div class="relative rounded-2xl overflow-hidden h-32 border border-slate-200 dark:border-slate-700 mb-2">
                        <img src="{{ $existingCoverUrl }}" class="w-full h-full object-cover" alt="{{ __('Existing Cover') }}">
                        <span class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-lg">{{ __('Current Image') }}</span>
                    </div>
                @endif

                @if($cover_image)
                    <div class="relative rounded-2xl overflow-hidden h-32 border border-slate-200 dark:border-slate-700 mb-2">
                        <img src="{{ $cover_image->temporaryUrl() }}" class="w-full h-full object-cover" alt="{{ __('Cover Preview') }}">
                        <span class="absolute bottom-2 left-2 bg-indigo-900/80 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-lg">{{ __('Preview Upload') }}</span>
                    </div>
                @endif

                <x-file wire:model="cover_image" accept="image/*" label="{{ __('Choose Cover File') }}" hint="{{ __('Recommended: Landscape aspect ratio, max 2MB') }}" />
            </div>

            {{-- Action buttons --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <x-button label="{{ __('Cancel') }}" wire:click="$set('campaignModal', false)" class="btn-ghost rounded-xl" />
                <x-button label="{{ __('Save Campaign') }}" type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold" spinner="saveCampaign" />
            </div>
        </form>
    </x-drawer>
</div>
