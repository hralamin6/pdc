<?php

use App\Models\Halaqah;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Halaqah Details')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public Halaqah $halaqah;
    public bool $isWaitlist = false;

    // Session Donation State
    public bool $donationModal = false;
    public $donorId = '';
    public $donationAmount = '';
    public $donationPaymentMethod = 'cash';
    public $donationTransactionId = '';
    public $donationNote = '';

    public function mount(Halaqah $halaqah): void
    {
        $this->authorize('halaqahs.view');
        $this->halaqah = $halaqah->load(['speaker', 'series', 'attendances', 'quizzes']);
        
        // Determine if they are on a waitlist.
        // Waitlist happens if total rsvps >= max_capacity.
        $this->updateWaitlistStatus();
    }

    public function updateWaitlistStatus()
    {
        if (!$this->halaqah->max_capacity) {
            $this->isWaitlist = false;
            return;
        }
        
        $currentRsvps = $this->halaqah->attendances()->where('status_new', 'rsvp')->count();
        $this->isWaitlist = $currentRsvps >= $this->halaqah->max_capacity;
    }

    public function toggleRsvp()
    {
        if (!$this->halaqah->is_registration_open) {
            $this->error('Registration is currently closed.');
            return;
        }

        $attendance = $this->halaqah->attendances()->where('user_id', auth()->id())->first();

        if ($attendance) {
            $attendance->delete();
            $this->success('RSVP cancelled.');
            // Note: In a full system, you would automatically upgrade the first waitlisted person here.
        } else {
            $this->updateWaitlistStatus();
            
            $status = $this->isWaitlist ? 'waitlist' : 'rsvp';
            
            $this->halaqah->attendances()->create([
                'user_id' => auth()->id(),
                'status_new' => $status,
                'status' => 'rsvp' // legacy column
            ]);
            
            if ($status === 'waitlist') {
                $this->info('Added to waitlist. We will notify you if a spot opens up.');
            } else {
                $this->success('RSVP successful! See you there.');
            }
        }
    }

    public function togglePreparation()
    {
        $attendance = $this->halaqah->attendances()->where('user_id', auth()->id())->first();
        if ($attendance) {
            $attendance->update([
                'preparation_completed' => !$attendance->preparation_completed
            ]);
            $this->success($attendance->preparation_completed ? 'Preparation marked as completed!' : 'Preparation marked as incomplete.');
        }
    }

    public function getHasRsvpdProperty(): bool
    {
        return $this->halaqah->attendances()->where('user_id', auth()->id())->where('status_new', 'rsvp')->exists();
    }
    
    public function getIsOnWaitlistProperty(): bool
    {
        return $this->halaqah->attendances()->where('user_id', auth()->id())->where('status_new', 'waitlist')->exists();
    }
    
    public function getAttendanceRecordProperty()
    {
        return $this->halaqah->attendances()->where('user_id', auth()->id())->first();
    }

    public function getSeriesProgressProperty(): array
    {
        if (!$this->halaqah->series) {
            return ['completed' => 0, 'total' => 0, 'percentage' => 0];
        }

        $total = $this->halaqah->series->halaqahs()->count();
        $completed = $this->halaqah->series->halaqahs()->where('status', 'completed')->count();
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage
        ];
    }

    public function openDonationModal()
    {
        $this->authorize('donations.transactions.manage'); // Quick check for admin/accountant equivalent for now
        $this->reset(['donorId', 'donationAmount', 'donationPaymentMethod', 'donationTransactionId', 'donationNote']);
        $this->donationModal = true;
    }

    public function saveSessionDonation()
    {
        $this->authorize('donations.transactions.manage');

        $this->validate([
            'donorId' => 'nullable|exists:users,id',
            'donationAmount' => 'required|numeric|min:1',
            'donationPaymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'donationTransactionId' => 'required_if:donationPaymentMethod,bkash,nagad,bank',
        ], [
            'donationTransactionId.required_if' => 'Transaction ID is required for digital payments.'
        ]);

        \App\Models\Donation::create([
            'user_id' => $this->donorId ?: null,
            'halaqah_id' => $this->halaqah->id,
            'type' => 'halaqah',
            'amount' => $this->donationAmount,
            'payment_method' => $this->donationPaymentMethod,
            'transaction_id' => $this->donationTransactionId,
            'note' => $this->donationNote,
            'status' => 'confirmed', // Treasurer is recording it directly, so it's confirmed
            'donated_at' => now(),
            'is_anonymous' => empty($this->donorId), // Treat as anonymous guest if no user selected
        ]);

        $this->success('Session donation recorded!');
        $this->donationModal = false;
    }
};
