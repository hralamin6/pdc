<?php

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FundTransfer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Fund Transfers')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    // Form
    public bool $transferModal = false;
    public ?int $editingId = null;

    public int $from_account_id = 0;
    public int $to_account_id = 0;
    public string $amount = '';
    public string $fee = '0';
    public string $transfer_date = '';
    public string $reference_id = '';
    public string $notes = '';
    public string $status = 'completed';

    public function mount(): void
    {
        $this->transfer_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function netReceived(): float
    {
        return max(0, (float) $this->amount - (float) $this->fee);
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->transferModal = true;

        if ($id) {
            $this->editingId = $id;
            $t = FundTransfer::findOrFail($id);
            $this->from_account_id = $t->from_account_id;
            $this->to_account_id = $t->to_account_id;
            $this->amount = (string) $t->amount;
            $this->fee = (string) $t->fee;
            $this->transfer_date = $t->transfer_date->format('Y-m-d');
            $this->reference_id = $t->reference_id ?? '';
            $this->notes = $t->notes ?? '';
            $this->status = $t->status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'from_account_id' => 'required|integer|exists:bank_accounts,id',
            'to_account_id'   => 'required|integer|exists:bank_accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:1',
            'fee'             => 'required|numeric|min:0',
            'transfer_date'   => 'required|date',
            'status'          => 'required|in:pending,completed,failed',
        ], [
            'to_account_id.different' => 'Source and destination accounts must be different.',
        ]);

        $data = [
            'from_account_id' => $this->from_account_id,
            'to_account_id'   => $this->to_account_id,
            'transferred_by'  => auth()->id(),
            'amount'          => $this->amount,
            'fee'             => $this->fee,
            'transfer_date'   => $this->transfer_date,
            'reference_id'    => $this->reference_id ?: null,
            'notes'           => $this->notes ?: null,
            'status'          => $this->status,
        ];

        if ($this->editingId) {
            $transfer = FundTransfer::findOrFail($this->editingId);
            $transfer->update($data);
            $this->js("toast('Transfer updated successfully.', {type: 'success'})");
        } else {
            $transfer = FundTransfer::create($data);

            // Auto-create fee expense if fee > 0 and status is completed
            if ((float) $this->fee > 0 && $this->status === 'completed') {
                $feeCategory = ExpenseCategory::where('slug', 'transfer-fees')->first();

                if ($feeCategory) {
                    $feeExpense = Expense::create([
                        'expense_category_id' => $feeCategory->id,
                        'recorded_by'         => auth()->id(),
                        'bank_account_id'     => $this->from_account_id,
                        'title'               => 'Transfer Fee: ' . BankAccount::find($this->from_account_id)->name . ' → ' . BankAccount::find($this->to_account_id)->name,
                        'amount'              => $this->fee,
                        'payment_method'      => BankAccount::find($this->from_account_id)->type,
                        'expense_date'        => $this->transfer_date,
                        'notes'               => 'Auto-generated from Fund Transfer #' . $transfer->id,
                        'status'              => 'confirmed',
                    ]);
                    $transfer->update(['fee_expense_id' => $feeExpense->id]);
                }
            }

            $this->js("toast('Transfer recorded successfully.', {type: 'success'})");
        }

        $this->transferModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $transfer = FundTransfer::findOrFail($id);
        // Also delete auto-generated fee expense
        if ($transfer->fee_expense_id) {
            Expense::find($transfer->fee_expense_id)?->delete();
        }
        $transfer->delete();
        $this->js("toast('Transfer deleted.', {type: 'warning'})");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->from_account_id = 0;
        $this->to_account_id = 0;
        $this->amount = '';
        $this->fee = '0';
        $this->transfer_date = now()->format('Y-m-d');
        $this->reference_id = '';
        $this->notes = '';
        $this->status = 'completed';
    }

    public function with(): array
    {
        return [
            'transfers' => FundTransfer::with(['fromAccount', 'toAccount', 'transferredBy'])
                ->latest('transfer_date')
                ->paginate(15),
            'accounts' => BankAccount::where('is_active', true)->get(),
            'totalTransferred' => FundTransfer::where('status', 'completed')->sum('amount'),
            'totalFees' => FundTransfer::where('status', 'completed')->sum('fee'),
        ];
    }
};
