<?php

use App\Models\BankAccount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Bank Accounts')] #[Layout('layouts.app')] class extends Component
{
    public bool $modal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $type = 'cash';
    public string $account_number = '';
    public string $bank_name = '';
    public string $branch = '';
    public string $holder_name = '';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('expenses.bank-accounts.manage');
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->modal = true;
        if ($id) {
            $this->editingId = $id;
            $acc = BankAccount::findOrFail($id);
            $this->name = $acc->name;
            $this->type = $acc->type;
            $this->account_number = $acc->account_number ?? '';
            $this->bank_name = $acc->bank_name ?? '';
            $this->branch = $acc->branch ?? '';
            $this->holder_name = $acc->holder_name ?? '';
            $this->notes = $acc->notes ?? '';
            $this->is_active = $acc->is_active;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:cash,bkash,nagad,bank,other',
        ]);

        $data = [
            'name'           => $this->name,
            'type'           => $this->type,
            'account_number' => $this->account_number ?: null,
            'bank_name'      => $this->bank_name ?: null,
            'branch'         => $this->branch ?: null,
            'holder_name'    => $this->holder_name ?: null,
            'notes'          => $this->notes ?: null,
            'is_active'      => $this->is_active,
        ];

        if ($this->editingId) {
            BankAccount::findOrFail($this->editingId)->update($data);
            $this->js("toast('Account updated.', {type: 'success'})");
        } else {
            BankAccount::create($data);
            $this->js("toast('Account created.', {type: 'success'})");
        }

        $this->modal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $acc = BankAccount::withCount('expenses')->findOrFail($id);
        if ($acc->expenses_count > 0) {
            $this->js("toast('Cannot delete: this account has expenses recorded.', {type: 'error'})");

            return;
        }
        $acc->delete();
        $this->js("toast('Account deleted.', {type: 'warning'})");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = 'cash';
        $this->account_number = $this->bank_name = $this->branch = $this->holder_name = $this->notes = '';
        $this->is_active = true;
    }

    public function with(): array
    {
        return [
            'accounts' => BankAccount::withCount('expenses')->get(),
        ];
    }
};
