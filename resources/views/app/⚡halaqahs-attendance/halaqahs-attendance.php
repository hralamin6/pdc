<?php

use App\Models\Halaqah;
use App\Models\HalaqahAttendance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Http\Request;

new #[Title('Attendance & Preparation')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public Halaqah $halaqah;
    public string $search = '';

    public function mount(Halaqah $halaqah): void
    {
        $this->authorize('halaqahs.manage-attendance');
        $this->halaqah = $halaqah->load(['attendances.user']);
    }

    public function with(): array
    {
        return [
            'attendances' => $this->halaqah->attendances()
                ->whereHas('user', function($query) {
                    if ($this->search) {
                        $query->where('name', 'like', "%{$this->search}%");
                    }
                })
                ->with('user')
                ->get(),
            'totalRsvps' => $this->halaqah->attendances()->count(),
            'totalAttended' => $this->halaqah->attendances()->where('attended', true)->count(),
            'totalPrepared' => $this->halaqah->attendances()->where('preparation_completed', true)->count(),
        ];
    }

    public function toggleAttendance($attendanceId)
    {
        $attendance = HalaqahAttendance::findOrFail($attendanceId);
        
        $wasAttended = $attendance->attended;
        
        $attendance->update([
            'attended' => !$wasAttended,
            'check_in_method' => !$wasAttended ? 'manual' : null,
            'checked_in_at' => !$wasAttended ? now() : null,
        ]);
        
        $this->success($wasAttended ? 'Attendance revoked' : 'Checked in manually');
    }

    public function togglePreparation($attendanceId)
    {
        $attendance = HalaqahAttendance::findOrFail($attendanceId);
        $attendance->update([
            'preparation_completed' => !$attendance->preparation_completed
        ]);
        $this->success('Preparation status updated');
    }
};
