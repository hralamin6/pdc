<div>
    <x-header title="Borrow Requests" subtitle="Manage incoming requests and books you are borrowing" separator />

    {{-- Tabs --}}
    <div class="tabs tabs-boxed bg-base-200/50 mb-6 p-2 rounded-2xl border border-base-content/5 inline-flex">
        <button wire:click="switchTab('incoming')" class="tab font-bold {{ $activeTab === 'incoming' ? 'tab-active bg-primary text-white rounded-xl' : '' }}">
            Incoming Requests
            @php $inPending = $this->incomingRequests->where('status', 'pending')->count(); @endphp
            @if($inPending) <span class="badge badge-sm badge-error ml-2 text-white">{{ $inPending }}</span> @endif
        </button>
        <button wire:click="switchTab('outgoing')" class="tab font-bold {{ $activeTab === 'outgoing' ? 'tab-active bg-primary text-white rounded-xl' : '' }}">
            My Borrows
            @php $outActive = $this->outgoingRequests->where('status', 'active')->count(); @endphp
            @if($outActive) <span class="badge badge-sm badge-info ml-2 text-white">{{ $outActive }}</span> @endif
        </button>
    </div>

    <div class="space-y-4">
        @if($activeTab === 'incoming')
            @forelse($this->incomingRequests as $req)
                <div class="bg-base-100 rounded-2xl p-5 border border-base-content/10 shadow-sm flex flex-col md:flex-row gap-5 items-start md:items-center" wire:key="in-{{ $req->id }}">
                    <div class="flex-grow">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-bold text-lg leading-tight">{{ $req->bookCopy->book->title }}</h3>
                            @include('app.⚡borrow-requests._status_badge', ['status' => $req->status])
                        </div>
                        <p class="text-sm text-base-content/70 flex items-center gap-2">
                            <x-icon name="o-user" class="w-4 h-4" /> Requested by <span class="font-bold text-base-content">{{ $req->borrower->name }}</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-1">Requested Duration: {{ $req->requested_days }} days &bull; Updated: {{ $req->updated_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2 w-full md:w-auto mt-4 md:mt-0">
                        @if($req->status === 'pending')
                            <x-button label="Accept" icon="o-check" class="btn-success text-white btn-sm" wire:click="acceptRequest({{ $req->id }})" />
                            <x-button label="Reject" icon="o-x-mark" class="btn-error text-white btn-sm" wire:click="rejectRequest({{ $req->id }})" wire:confirm="Reject this request?" />
                        @elseif($req->status === 'accepted')
                            <x-button label="I Gave It" icon="o-hand-raised" class="btn-primary btn-sm w-full md:w-auto" wire:click="markGiven({{ $req->id }})" tooltip="Click this when you physically hand the book to them" />
                        @elseif($req->status === 'given')
                            <span class="text-sm text-warning font-medium italic">Waiting for borrower to confirm receipt...</span>
                        @elseif($req->status === 'active')
                            <div class="text-right">
                                <div class="text-xs text-base-content/50 mb-1">Due: <span class="font-bold {{ $req->due_date->isPast() ? 'text-error' : 'text-base-content' }}">{{ $req->due_date->format('M d, Y') }}</span></div>
                                <x-button label="Book Returned" icon="o-arrow-path" class="btn-info text-white btn-sm w-full" wire:click="confirmReturned({{ $req->id }})" wire:confirm="Are you sure you received the book back?" />
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-base-content/50 bg-base-200/30 rounded-2xl border border-dashed border-base-content/10">
                    <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p>No incoming borrow requests.</p>
                </div>
            @endforelse

        @else
            @forelse($this->outgoingRequests as $req)
                <div class="bg-base-100 rounded-2xl p-5 border border-base-content/10 shadow-sm flex flex-col md:flex-row gap-5 items-start md:items-center" wire:key="out-{{ $req->id }}">
                    <div class="flex-grow">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-bold text-lg leading-tight">{{ $req->bookCopy->book->title }}</h3>
                            @include('app.⚡borrow-requests._status_badge', ['status' => $req->status])
                        </div>
                        <p class="text-sm text-base-content/70 flex items-center gap-2">
                            <x-icon name="o-user" class="w-4 h-4" /> Owned by <span class="font-bold text-base-content">{{ $req->bookCopy->owner->name ?? 'Community Hub' }}</span>
                        </p>
                        @if($req->status === 'active' && $req->due_date)
                            <p class="text-sm font-medium mt-2 {{ $req->due_date->isPast() ? 'text-error' : 'text-success' }}">
                                Due Date: {{ $req->due_date->format('M d, Y') }} ({{ $req->due_date->diffForHumans() }})
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 w-full md:w-auto mt-4 md:mt-0">
                        @if(in_array($req->status, ['pending', 'accepted']))
                            <x-button label="Cancel Request" icon="o-x-mark" class="btn-ghost text-error btn-sm" wire:click="cancelRequest({{ $req->id }})" />
                        @elseif($req->status === 'given')
                            <x-button label="I Received It" icon="o-check-badge" class="btn-success text-white btn-sm w-full md:w-auto" wire:click="confirmReceived({{ $req->id }})" tooltip="Confirm you have physically received the book" />
                        @elseif($req->status === 'active')
                            <span class="text-sm text-info font-medium italic">Enjoy reading! Return by due date.</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-base-content/50 bg-base-200/30 rounded-2xl border border-dashed border-base-content/10">
                    <x-icon name="o-paper-airplane" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p>You haven't requested to borrow any books.</p>
                </div>
            @endforelse
        @endif
    </div>
</div>
