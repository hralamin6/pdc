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

    public function mount(Halaqah $halaqah): void
    {
        $this->authorize('halaqahs.view');
        $this->halaqah = $halaqah->load(['speaker', 'series', 'attendances']);
        
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
};
