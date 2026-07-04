<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center gap-4 mb-8">
        <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
        <div>
            <h1 class="text-3xl font-bold text-base-content">{{ __('Submit Daily Report') }}</h1>
            <p class="text-base-content/70 mt-1">{{ Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
        </div>
    </div>

    <form wire:submit="saveReport" class="space-y-6">
        
        {{-- Report Items --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
            <div class="space-y-6">
                @foreach($trackingItems as $item)
                    @php
                        $title = $item->custom_title ?? $item->template->title;
                        $type = $item->type;
                    @endphp
                    <div class="p-4 rounded-xl bg-base-200/30 border border-base-content/5">
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            
                            {{-- Checkbox for boolean or mixed --}}
                            @if(in_array($type, ['boolean', 'mixed']))
                                <div class="flex items-center gap-3 w-full sm:w-1/3">
                                    <x-checkbox wire:model="entries.{{ $item->id }}.boolean_value" class="checkbox-primary" />
                                    <span class="font-medium text-base-content">{{ $title }}</span>
                                </div>
                            @else
                                <div class="w-full sm:w-1/3">
                                    <span class="font-medium text-base-content">{{ $title }}</span>
                                </div>
                            @endif

                            {{-- Input for number --}}
                            @if($type === 'number')
                                <div class="w-full sm:w-2/3">
                                    <x-input type="number" wire:model="entries.{{ $item->id }}.numeric_value" placeholder="Amount / Quantity" class="input-sm" />
                                </div>
                            @endif

                            {{-- Input for text or mixed --}}
                            @if(in_array($type, ['text', 'mixed']))
                                <div class="w-full sm:w-2/3">
                                    <x-input type="text" wire:model="entries.{{ $item->id }}.text_value" placeholder="Notes / Details..." class="input-sm" />
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Meta & Privacy --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
            <h2 class="text-lg font-bold mb-4">General Reflection</h2>
            <div class="space-y-4">
                <x-textarea wire:model="notes" placeholder="Any overall thoughts, struggles, or wins for today?" rows="3" />
                
                <x-select label="Privacy Level" wire:model="privacy_level" :options="[
                    ['id' => 'private', 'name' => 'Private (Only me)'],
                    ['id' => 'mentor_only', 'name' => 'Mentor Only (Me & My Mentor)'],
                    ['id' => 'public', 'name' => 'Public (Anonymous leaderboard)'],
                ]" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <x-button label="Cancel" :link="route('app.daily-reports')" wire:navigate class="btn-ghost" />
            <x-button label="Submit Report" type="submit" class="btn-primary" icon="o-check" spinner="saveReport" />
        </div>
    </form>
</div>
