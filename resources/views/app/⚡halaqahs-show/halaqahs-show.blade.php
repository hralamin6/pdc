<div>
    <x-header title="{{ $halaqah->title }}" subtitle="{{ $halaqah->series ? __('Part of Course: :title', ['title' => $halaqah->series->title]) : __('Standalone Session') }}">
        <x-slot:actions>
            <x-button icon="o-arrow-left" title="{{ __('Back') }}" link="{{ route('app.halaqahs') }}" class="btn-ghost" wire:navigate />
            
            @can('donations.transactions.manage')
                <x-button icon="o-banknotes" title="{{ __('Collect Donations') }}" wire:click="openDonationModal" class="btn-outline btn-success" />
            @endcan
            
            @can('halaqahs.manage-attendance')
                <x-button icon="o-qr-code" title="{{ __('QR & Attendance') }}" link="{{ route('app.halaqahs.attendance', $halaqah) }}" class="btn-outline btn-primary" wire:navigate />
            @endcan
        </x-slot:actions>
    </x-header>

    @if($halaqah->status === 'cancelled')
        <x-alert icon="o-exclamation-triangle" class="alert-error mb-6">
            {{ __('This session has been cancelled.') }}
        </x-alert>
    @elseif($halaqah->status === 'completed')
        <x-alert icon="o-check-circle" class="alert-info mb-6">
            {{ __('This session is completed.') }}
        </x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
        <div class="lg:col-span-2 space-y-6">
            
            @if($halaqah->series)
                <x-card class="bg-primary/5 border-primary/20">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-lg text-primary">{{ $halaqah->series->title }}</h3>
                        <span class="text-sm font-semibold">{{ $this->seriesProgress['completed'] }} / {{ $this->seriesProgress['total'] }} {{ __('Sessions Completed') }}</span>
                    </div>
                    <x-progress value="{{ $this->seriesProgress['percentage'] }}" max="100" class="progress-primary h-2" />
                </x-card>
            @endif

            @if($halaqah->description)
                <x-card>
                    <p class="whitespace-pre-wrap text-base-content/80">{{ $halaqah->description }}</p>
                </x-card>
            @endif
            
            <x-card>
                <h2 class="text-xl font-bold mb-4">{{ __('Topic:') }} {{ $halaqah->topic }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-user" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('Speaker:') }}</span> 
                            {{ $halaqah->speaker ? $halaqah->speaker->name : __('TBA') }}
                        </div>
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-calendar" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('Date:') }}</span>
                            {{ $halaqah->scheduled_at->format('l, F j, Y') }}
                        </div>
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-clock" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('Time:') }}</span>
                            {{ $halaqah->scheduled_at->format('g:i A') }}
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-map-pin" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('Location:') }}</span>
                            {{ $halaqah->location }}
                        </div>
                        @if($halaqah->meeting_link)
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-link" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('Online:') }}</span> 
                            <a href="{{ $halaqah->meeting_link }}" target="_blank" class="text-primary hover:underline">{{ __('Meeting Link') }}</a>
                        </div>
                        @endif
                        <div class="flex items-center text-base-content/80">
                            <x-icon name="o-users" class="w-5 h-5 mr-3 text-primary" />
                            <span class="font-semibold w-24">{{ __('For:') }}</span>
                            @php
                                $gender = match($halaqah->gender_restriction) {
                                    'brothers_only' => __('Brothers Only'),
                                    'sisters_only' => __('Sisters Only'),
                                    default => __('Open to All'),
                                };
                            @endphp
                            {{ $gender }}
                        </div>
                    </div>
                </div>
            </x-card>

            @if($halaqah->quizzes->isNotEmpty())
            <x-card title="{{ __('Session Quizzes') }}" icon="o-question-mark-circle" class="border-accent/20 bg-accent/5">
                <p class="mb-4 text-base-content/70">{{ __("Test your knowledge on this session's topics.") }}</p>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($halaqah->quizzes as $quiz)
                        <div class="flex items-center justify-between bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
                            <div>
                                <h4 class="font-bold">{{ $quiz->title }}</h4>
                                <div class="text-xs text-base-content/50 mt-1 flex gap-3">
                                    <span class="flex items-center gap-1"><x-icon name="o-clock" class="w-3 h-3" /> {{ $quiz->time_limit_minutes ?? __('No time limit') }}</span>
                                    <span class="flex items-center gap-1"><x-icon name="o-document-text" class="w-3 h-3" /> {{ $quiz->total_marks }} {{ __('Marks') }}</span>
                                </div>
                            </div>
                            <div>
                                @if($quiz->isAvailable() || $quiz->status === 'live' || $quiz->status === 'closed')
                                    @if($quiz->mode === 'live')
                                        <x-button label="{{ $quiz->status === 'closed' ? __('Results') : __('Live Quiz') }}" icon="o-signal" class="btn-sm btn-primary {{ $quiz->status === 'live' ? 'animate-pulse' : '' }}" :href="route('web.quiz.live', $quiz)" wire:navigate />
                                    @else
                                        <x-button label="{{ $quiz->status === 'closed' ? __('Results') : __('Take Quiz') }}" icon="o-play" class="btn-sm btn-success" :href="route('web.quiz.take', $quiz)" wire:navigate />
                                    @endif
                                @else
                                    <span class="badge badge-ghost">{{ ucfirst($quiz->status) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
            @endif

            @if($halaqah->materials_path || !empty($halaqah->resources))
            <x-card title="{{ __('Session Materials & Resources') }}" icon="o-document-text">
                <p class="mb-4 text-base-content/70">{{ __('Review these materials to prepare for the session.') }}</p>
                <div class="flex flex-col gap-3">
                    @if($halaqah->materials_path)
                        <x-button icon="o-arrow-down-tray" class="btn-outline btn-primary w-max" link="{{ asset('storage/' . $halaqah->materials_path) }}" external target="_blank">
                            {{ __('Download Attached File') }}
                        </x-button>
                    @endif
                    
                    @if(is_array($halaqah->resources) && count($halaqah->resources) > 0)
                        <div class="mt-2 space-y-2">
                            <h4 class="font-semibold text-sm opacity-70 uppercase tracking-wider">{{ __('External Links') }}</h4>
                            @foreach($halaqah->resources as $link)
                                <a href="{{ $link }}" target="_blank" class="flex items-center gap-2 text-primary hover:underline bg-base-200 p-3 rounded-lg w-full">
                                    <x-icon name="o-link" class="w-4 h-4 shrink-0" />
                                    <span class="truncate">{{ $link }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>
            @endif
        </div>

        <div>
            <x-card title="{{ __('Registration') }}" icon="o-ticket">
                <div class="text-center py-6">
                    @if(!$halaqah->is_registration_open)
                        <div class="w-16 h-16 rounded-full bg-base-300 flex items-center justify-center mx-auto mb-4">
                            <x-icon name="o-lock-closed" class="w-8 h-8 opacity-50" />
                        </div>
                        <h3 class="font-bold text-lg mb-2">{{ __('Registration Closed') }}</h3>
                        <p class="text-sm text-base-content/70">{{ __('You cannot RSVP at this time.') }}</p>
                    @elseif($this->hasRsvpd)
                        <div class="w-16 h-16 rounded-full bg-success/20 flex items-center justify-center mx-auto mb-4 text-success">
                            <x-icon name="o-check" class="w-8 h-8" />
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-success">{{ __('You are attending!') }}</h3>
                        <p class="text-sm text-base-content/70 mb-6">{{ __('We look forward to seeing you there.') }}</p>

                        <div class="bg-base-200 p-4 rounded-xl mb-6 text-left">
                            <h4 class="font-semibold mb-2 flex items-center gap-2">
                                <x-icon name="o-check-badge" class="w-5 h-5 text-primary" /> {{ __('Session Preparation') }}
                            </h4>
                            <p class="text-xs text-base-content/70 mb-4">{{ __('Have you reviewed the materials and prepared for this session?') }}</p>
                            <x-toggle wire:click="togglePreparation" :checked="$this->attendanceRecord->preparation_completed" label="{{ __('Preparation Completed') }}" class="toggle-success toggle-sm" />
                        </div>

                        <x-button label="{{ __('Cancel RSVP') }}" class="btn-error btn-outline btn-block" wire:click="toggleRsvp" spinner />
                    @elseif($this->isOnWaitlist)
                        <div class="w-16 h-16 rounded-full bg-warning/20 flex items-center justify-center mx-auto mb-4 text-warning">
                            <x-icon name="o-clock" class="w-8 h-8" />
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-warning">{{ __('On Waitlist') }}</h3>
                        <p class="text-sm text-base-content/70 mb-6">{{ __('The session is full. We will notify you if a spot opens up.') }}</p>
                        <x-button label="{{ __('Leave Waitlist') }}" class="btn-error btn-outline btn-block" wire:click="toggleRsvp" spinner />
                    @else
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4 text-primary">
                            <x-icon name="o-hand-raised" class="w-8 h-8" />
                        </div>
                        
                        @if($isWaitlist)
                            <h3 class="font-bold text-lg mb-2 text-warning">{{ __('Session is Full') }}</h3>
                            <p class="text-sm text-base-content/70 mb-6">{{ __('You can still join the waitlist. Spots may open up!') }}</p>
                            <x-button label="{{ __('Join Waitlist') }}" class="btn-warning btn-block" wire:click="toggleRsvp" spinner />
                        @else
                            <h3 class="font-bold text-lg mb-2">{{ __('Reserve your spot') }}</h3>
                            @if($halaqah->max_capacity)
                                <p class="text-sm text-success mb-6">{{ $halaqah->available_seats }} {{ __('spots remaining') }}</p>
                            @else
                                <p class="text-sm text-base-content/70 mb-6">{{ __('Let the organizers know you are coming.') }}</p>
                            @endif
                            <x-button label="{{ __('RSVP Now') }}" class="btn-primary btn-block" wire:click="toggleRsvp" spinner />
                        @endif
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <!-- Session Donation Collection Modal -->
    <x-modal wire:model="donationModal" title="{{ __('Collect Session Donation') }}" subtitle="{{ __('Record funds received during this Halaqah') }}" separator>
        <div class="space-y-4">
            <x-choices
                label="{{ __('Donor (Optional)') }}"
                wire:model="donorId"
                :options="\App\Models\User::orderBy('name')->get()"
                option-label="name"
                option-value="id"
                placeholder="{{ __('Search member...') }}"
                hint="{{ __('Leave empty if anonymous/guest') }}"
                single
                searchable
            />

            <x-input label="{{ __('Amount (৳)') }}" wire:model="donationAmount" type="number" prefix="৳" required />

            <x-select label="{{ __('Payment Method') }}" wire:model.live="donationPaymentMethod" :options="[['id'=>'cash', 'name'=>__('Cash')], ['id'=>'bkash', 'name'=>'bKash'], ['id'=>'nagad', 'name'=>'Nagad'], ['id'=>'bank', 'name'=>__('Bank Transfer')]]" />

            @if(in_array($donationPaymentMethod, ['bkash', 'nagad', 'bank']))
                <x-input label="{{ __('Transaction ID / Reference') }}" wire:model="donationTransactionId" placeholder="{{ __('Enter Transaction ID...') }}" required />
            @endif

            <x-textarea label="{{ __('Note (Optional)') }}" wire:model="donationNote" placeholder="{{ __('Any specific notes...') }}" rows="2" />
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('donationModal', false)" />
            <x-button label="{{ __('Record Donation') }}" class="btn-success" wire:click="saveSessionDonation" spinner />
        </x-slot:actions>
    </x-modal>
</div>
