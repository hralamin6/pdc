<div class="max-w-4xl mx-auto py-6 space-y-8">
    {{-- Header & Date Selection --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Daily Reflection Log') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-base-content/60 shrink-0">Log Date:</label>
            <x-select 
                wire:model.live="date" 
                :options="$availableDates" 
                class="select-sm rounded-xl border-base-content/10 font-bold bg-base-200/50" 
            />
        </div>
    </div>

    {{-- Live Completion Progress Tracker --}}
    @php
        $totalItems = count($trackingItems);
        $completedCount = 0;
        foreach ($trackingItems as $item) {
            $e = $entries[$item->id] ?? [];
            $done = ($e['boolean_value'] ?? false) 
                || (isset($e['numeric_value']) && (int)$e['numeric_value'] > 0)
                || !empty(trim($e['text_value'] ?? ''));
            if ($done) {
                $completedCount++;
            }
        }
        $progressPct = $totalItems > 0 ? (int) round(($completedCount / $totalItems) * 100) : 0;
    @endphp

    <div class="bg-gradient-to-r from-primary/10 via-secondary/5 to-base-100 p-6 rounded-3xl border border-primary/20 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="o-sparkles" class="w-5 h-5 text-primary" />
                <span class="font-extrabold text-sm text-base-content">Overall Progress</span>
            </div>
            <span class="text-sm font-black text-primary">{{ $completedCount }} / {{ $totalItems }} Items Completed ({{ $progressPct }}%)</span>
        </div>

        <div class="w-full bg-base-200/80 rounded-full h-3 overflow-hidden">
            <div class="bg-primary h-3 rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
        </div>
    </div>

    {{-- Main Form --}}
    <form wire:submit="saveReport" class="space-y-8">
        
        {{-- Group items by category --}}
        @php
            $groupedItems = collect($trackingItems)->groupBy(function($item) {
                return $item->template->category ?? 'General';
            });

            $categoryIcons = [
                'Ibadah' => 'o-sparkles',
                'Quran & Adhkar' => 'o-book-open',
                'Study & Action' => 'o-academic-cap',
                'Personal & Community' => 'o-user-group',
            ];
        @endphp

        @foreach($groupedItems as $category => $items)
            <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-base-content/5 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-primary/10 text-primary rounded-xl">
                            <x-icon :name="$categoryIcons[$category] ?? 'o-list-bullet'" class="w-5 h-5" />
                        </div>
                        <h3 class="font-extrabold text-lg text-base-content">{{ $category }}</h3>
                    </div>
                    <span class="badge badge-sm badge-ghost font-semibold">{{ $items->count() }} items</span>
                </div>

                <div class="space-y-4 pt-1">
                    @foreach($items as $item)
                        @php
                            $title = $item->custom_title ?? $item->template->title;
                            $type = $item->type;
                            $entry = $entries[$item->id] ?? [];
                            $isDone = ($entry['boolean_value'] ?? false)
                                || (isset($entry['numeric_value']) && (int)$entry['numeric_value'] > 0)
                                || !empty(trim($entry['text_value'] ?? ''));
                        @endphp

                        <div class="p-4 rounded-2xl transition-all border {{ $isDone ? 'bg-primary/5 border-primary/20' : 'bg-base-200/30 border-base-content/5' }}">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                
                                {{-- Item Title & Checkbox --}}
                                <div class="flex items-center gap-3">
                                    @if(in_array($type, ['boolean', 'mixed']))
                                        <x-checkbox 
                                            wire:model.live="entries.{{ $item->id }}.boolean_value" 
                                            class="checkbox-primary checkbox-md rounded-lg" 
                                        />
                                    @else
                                        <div class="w-5 h-5 rounded-full border border-base-content/30 flex items-center justify-center text-[10px] text-base-content/50">
                                            #
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-bold text-base-content text-sm sm:text-base {{ $isDone ? 'text-primary' : '' }}">{{ $title }}</span>
                                        <span class="text-xs text-base-content/40 block capitalize">Type: {{ $type }}</span>
                                    </div>
                                </div>

                                {{-- Inputs based on Type --}}
                                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                                    
                                    {{-- Stepper for Numeric Input --}}
                                    @if($type === 'number')
                                        <div class="flex items-center gap-1 bg-base-100 border border-base-content/10 rounded-xl p-1 shadow-sm">
                                            <button 
                                                type="button" 
                                                wire:click="adjustNumber({{ $item->id }}, -1)"
                                                class="w-8 h-8 rounded-lg bg-base-200 hover:bg-base-300 flex items-center justify-center font-bold text-base-content transition-colors"
                                            >-</button>
                                            <input 
                                                type="number" 
                                                wire:model.live="entries.{{ $item->id }}.numeric_value" 
                                                placeholder="0" 
                                                class="w-16 text-center bg-transparent border-none focus:ring-0 font-extrabold text-sm text-base-content" 
                                            />
                                            <button 
                                                type="button" 
                                                wire:click="adjustNumber({{ $item->id }}, 1)"
                                                class="w-8 h-8 rounded-lg bg-primary text-primary-content hover:bg-primary-focus flex items-center justify-center font-bold transition-colors"
                                            >+</button>
                                        </div>
                                    @endif

                                    {{-- Text Notes for Text / Mixed --}}
                                    @if(in_array($type, ['text', 'mixed']))
                                        <div class="w-full md:w-64">
                                            <x-input 
                                                type="text" 
                                                wire:model.live="entries.{{ $item->id }}.text_value" 
                                                placeholder="Notes / details..." 
                                                class="input-sm rounded-xl border-base-content/10 bg-base-100" 
                                            />
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Reflection & Privacy Settings --}}
        <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-6">
            <div class="border-b border-base-content/5 pb-3">
                <h3 class="font-extrabold text-lg text-base-content flex items-center gap-2">
                    <x-icon name="o-chat-bubble-bottom-center-text" class="w-5 h-5 text-secondary" />
                    <span>General Reflection & Privacy</span>
                </h3>
            </div>

            <div class="space-y-4">
                <x-textarea 
                    label="Personal Journal / Notes for the Day" 
                    wire:model="notes" 
                    placeholder="Reflect on your struggles, achievements, or intentions today..." 
                    rows="3" 
                    class="rounded-2xl border-base-content/10" 
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-select 
                        label="Privacy Setting" 
                        wire:model="privacy_level" 
                        :options="[
                            ['id' => 'private', 'name' => '🔒 Private (Only Visible to Me)'],
                            ['id' => 'mentor_only', 'name' => '👥 Mentor Only (Visible to Me & Assigned Mentors)'],
                            ['id' => 'public', 'name' => '🌐 Public (Community Insights)'],
                        ]" 
                        class="rounded-xl border-base-content/10" 
                    />
                </div>
            </div>
        </div>

        {{-- Submission Controls --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-button label="Cancel" :link="route('app.daily-reports')" wire:navigate class="btn-ghost rounded-xl" />
            <x-button 
                label="Save Reflection Log" 
                type="submit" 
                class="btn-primary rounded-xl px-8 font-extrabold shadow-lg shadow-primary/30" 
                icon="o-check" 
                spinner="saveReport" 
            />
        </div>
    </form>
</div>
