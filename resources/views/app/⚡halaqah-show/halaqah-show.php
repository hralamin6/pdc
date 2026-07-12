<?php

use App\Models\Halaqah;
use App\Models\HalaqahAttendance;
use App\Models\Donation;
use App\Models\User;
use App\Models\Quiz;
use App\Models\BankAccount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Halaqah Session Sheet')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithFileUploads;

    public Halaqah $halaqah;

    // Tab state
    public string $activeTab = 'attendance'; // attendance, resources, quizzes, donations

    // Roster search filter
    public string $rosterSearch = '';

    // Walk-in student search
    public ?int $selectedUserId = null;

    // Materials Upload
    public $newMaterialFile;
    public string $newMaterialTitle = '';

    // Donation Modal
    public bool $showDonationModal = false;
    public ?int $donationUserId = null;
    public string $donationAmount = '';
    public string $paymentMethod = 'cash'; // cash, bkash, nagad, bank, other
    public ?int $donationBankAccountId = null;
    public string $donationTransactionId = '';
    public string $donationNotes = '';

    public function mount(Halaqah $halaqah)
    {
        $this->authorize('halaqahs.view');
        $this->halaqah = $halaqah;
    }

    public function toggleAttendance(int $attendanceId)
    {
        $attendance = HalaqahAttendance::findOrFail($attendanceId);
        $attendance->update([
            'attended' => !$attendance->attended,
            'checked_in_at' => !$attendance->attended ? now() : null,
            'check_in_method' => !$attendance->attended ? 'mentor_override' : null,
            'status_new' => !$attendance->attended ? 'attended' : 'rsvp',
        ]);
        $this->success(__('Attendance status toggled!'));
    }

    public function togglePreparation(int $attendanceId)
    {
        $attendance = HalaqahAttendance::findOrFail($attendanceId);
        $attendance->update([
            'preparation_completed' => !$attendance->preparation_completed
        ]);
        $this->success(__('Preparation status toggled!'));
    }

    public function setRating(int $attendanceId, int $rating)
    {
        $attendance = HalaqahAttendance::findOrFail($attendanceId);
        $attendance->update([
            'rating' => $rating
        ]);
        $this->success(__('Engagement score updated!'));
    }

    public function addWalkInStudent()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
        ]);

        // Check if already registered
        $exists = HalaqahAttendance::where('halaqah_id', $this->halaqah->id)
            ->where('user_id', $this->selectedUserId)
            ->exists();

        if ($exists) {
            $this->error(__('Student is already on the roster.'));
            return;
        }

        HalaqahAttendance::create([
            'halaqah_id' => $this->halaqah->id,
            'user_id' => $this->selectedUserId,
            'status' => 'attended',
            'status_new' => 'attended',
            'attended' => true,
            'checked_in_at' => now(),
            'check_in_method' => 'walk_in',
        ]);

        $this->selectedUserId = null;
        $this->success(__('Walk-in student added and marked present!'));
    }

    public function uploadMaterial()
    {
        $this->validate([
            'newMaterialFile' => 'required|file|max:10240', // 10MB
            'newMaterialTitle' => 'required|string|max:255',
        ]);

        $path = $this->newMaterialFile->store('halaqah-materials', 'public');

        // Append to resources array
        $resources = $this->halaqah->resources ?? [];
        $resources[] = [
            'title' => $this->newMaterialTitle,
            'path' => $path,
            'type' => 'document',
            'uploaded_at' => now()->toDateTimeString(),
        ];

        $this->halaqah->update([
            'resources' => $resources
        ]);

        $this->reset(['newMaterialFile', 'newMaterialTitle']);
        $this->success(__('Study handout uploaded successfully!'));
    }

    public function removeMaterial(int $index)
    {
        $resources = $this->halaqah->resources ?? [];
        if (isset($resources[$index])) {
            // Delete file optionally from storage
            unset($resources[$index]);
            $this->halaqah->update([
                'resources' => array_values($resources)
            ]);
            $this->success(__('Material removed successfully.'));
        }
    }

    public function openDonationModal()
    {
        $this->reset(['donationUserId', 'donationAmount', 'paymentMethod', 'donationBankAccountId', 'donationTransactionId', 'donationNotes']);
        $this->showDonationModal = true;
    }

    public function recordDonation()
    {
        $this->validate([
            'donationAmount' => 'required|numeric|min:1',
            'donationUserId' => 'nullable|exists:users,id',
            'paymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'donationBankAccountId' => 'nullable|exists:bank_accounts,id',
            'donationTransactionId' => 'nullable|string|max:255',
            'donationNotes' => 'nullable|string|max:500',
        ]);

        Donation::create([
            'halaqah_id' => $this->halaqah->id,
            'user_id' => $this->donationUserId,
            'type' => 'halaqah',
            'amount' => $this->donationAmount,
            'payment_method' => $this->paymentMethod,
            'bank_account_id' => $this->donationBankAccountId,
            'transaction_id' => $this->donationTransactionId,
            'note' => $this->donationNotes,
            'status' => 'confirmed',
            'donated_at' => now(),
            'collected_by' => auth()->id(),
        ]);

        $this->showDonationModal = false;
        $this->success(__('Donation successfully logged for this session!'));
    }

    public function markAllPresent()
    {
        HalaqahAttendance::where('halaqah_id', $this->halaqah->id)
            ->update([
                'attended' => true,
                'checked_in_at' => now(),
                'check_in_method' => 'mentor_override',
                'status_new' => 'attended',
            ]);
        $this->success(__('All students marked Present!'));
    }

    public function markAllAbsent()
    {
        HalaqahAttendance::where('halaqah_id', $this->halaqah->id)
            ->update([
                'attended' => false,
                'checked_in_at' => null,
                'check_in_method' => null,
                'status_new' => 'rsvp',
            ]);
        $this->success(__('All students marked Absent!'));
    }

    public function markAllPrepared()
    {
        HalaqahAttendance::where('halaqah_id', $this->halaqah->id)
            ->where('attended', true)
            ->update([
                'preparation_completed' => true,
            ]);
        $this->success(__('All present students marked Prepared!'));
    }

    public bool $showBulkDonationModal = false;
    public array $bulkAmounts = [];
    public string $bulkPaymentMethod = 'cash';
    public ?int $bulkBankAccountId = null;
    public string $bulkTransactionId = '';
    public string $bulkDonationNotes = '';

    public function openBulkDonationModal()
    {
        $this->reset(['bulkAmounts', 'bulkPaymentMethod', 'bulkBankAccountId', 'bulkTransactionId', 'bulkDonationNotes']);
        
        $presentUserIds = HalaqahAttendance::where('halaqah_id', $this->halaqah->id)
            ->where('attended', true)
            ->pluck('user_id')
            ->toArray();

        foreach ($presentUserIds as $userId) {
            $this->bulkAmounts[$userId] = '';
        }

        $this->showBulkDonationModal = true;
    }

    public function saveBulkDonations()
    {
        $this->validate([
            'bulkAmounts.*' => 'nullable|numeric|min:0',
            'bulkPaymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'bulkBankAccountId' => 'nullable|exists:bank_accounts,id',
            'bulkTransactionId' => 'nullable|string|max:255',
            'bulkDonationNotes' => 'nullable|string|max:500',
        ]);

        $recordedCount = 0;
        foreach ($this->bulkAmounts as $userId => $amount) {
            if ($amount && floatval($amount) > 0) {
                Donation::create([
                    'halaqah_id' => $this->halaqah->id,
                    'user_id' => $userId,
                    'type' => 'halaqah',
                    'amount' => $amount,
                    'payment_method' => $this->bulkPaymentMethod,
                    'bank_account_id' => $this->bulkBankAccountId,
                    'transaction_id' => $this->bulkTransactionId,
                    'note' => $this->bulkDonationNotes,
                    'status' => 'confirmed',
                    'donated_at' => now(),
                    'collected_by' => auth()->id(),
                ]);
                $recordedCount++;
            }
        }

        $this->showBulkDonationModal = false;
        $this->success(__(':count bulk donations successfully recorded!', ['count' => $recordedCount]));
    }

    public function with(): array
    {
        // Roster of attendances
        $rosterQuery = HalaqahAttendance::with('user')
            ->where('halaqah_id', $this->halaqah->id);

        if (!empty($this->rosterSearch)) {
            $rosterQuery->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->rosterSearch . '%')
                  ->orWhere('email', 'like', '%' . $this->rosterSearch . '%');
            });
        }

        $attendances = $rosterQuery->get();

        // Calculate statistics
        $totalRSVPs = $attendances->count();
        $totalPresent = $attendances->where('attended', true)->count();
        $totalAbsent = $totalRSVPs - $totalPresent;
        $totalPrepared = $attendances->where('preparation_completed', true)->count();
        $avgRating = $attendances->whereNotNull('rating')->avg('rating') ?? 0;

        // Fetch donations for this session
        $donations = Donation::with('user')
            ->where('halaqah_id', $this->halaqah->id)
            ->get();
        $totalDonated = $donations->sum('amount');

        // Fetch Quizzes (morphed)
        $quizzes = $this->halaqah->quizzes()->with('attempts')->get();

        // Fetch Users for dropdowns (excluding those already in attendance)
        $existingUserIds = $attendances->pluck('user_id')->toArray();
        $allUsers = User::whereNotIn('id', $existingUserIds)->orderBy('name')->get();
        $donationBankAccounts = BankAccount::where('is_active', true)
            ->when($this->paymentMethod !== 'other', fn($q) => $q->where('type', $this->paymentMethod))
            ->get();

        $bulkBankAccounts = BankAccount::where('is_active', true)
            ->when($this->bulkPaymentMethod !== 'other', fn($q) => $q->where('type', $this->bulkPaymentMethod))
            ->get();

        return [
            'attendances' => $attendances,
            'donations' => $donations,
            'quizzes' => $quizzes,
            'allUsers' => $allUsers,
            'donationBankAccounts' => $donationBankAccounts,
            'bulkBankAccounts' => $bulkBankAccounts,
            'stats' => [
                'total_rsvps' => $totalRSVPs,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_prepared' => $totalPrepared,
                'avg_rating' => round($avgRating, 1),
                'total_donated' => $totalDonated,
            ]
        ];
    }
};
