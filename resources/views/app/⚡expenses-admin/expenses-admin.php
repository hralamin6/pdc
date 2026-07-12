<?php

use App\Models\BankAccount;
use App\Models\DonationCampaign;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Halaqah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Expenses Management')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    // Filters
    #[Url] public string $search = '';
    #[Url] public string $category_filter = '';
    #[Url] public string $month_filter = '';

    // Modal Form
    public bool $expenseModal = false;
    public ?int $editingId = null;

    // Form Fields
    public string $title = '';
    public int $expense_category_id = 0;
    public string $amount = '';
    public string $payment_method = 'cash';
    public string $transaction_id = '';
    public int $bank_account_id = 0;
    public string $expense_date = '';
    public string $linkable_type = '';
    public string $linkable_id = '';
    public string $notes = '';
    public $receipt;

    public function mount(): void
    {
        $this->authorize('expenses.manage');
        $this->month_filter = now()->format('Y-m');
        $this->expense_date = now()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonthFilter(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->expenseModal = true;

        if ($id) {
            $this->editingId = $id;
            $expense = Expense::findOrFail($id);
            $this->title = $expense->title;
            $this->expense_category_id = $expense->expense_category_id;
            $this->amount = (string) $expense->amount;
            $this->payment_method = $expense->payment_method;
            $this->transaction_id = $expense->transaction_id ?? '';
            $this->bank_account_id = $expense->bank_account_id ?? 0;
            $this->expense_date = $expense->expense_date->format('Y-m-d');
            $this->linkable_type = $expense->linkable_type ?? '';
            $this->linkable_id = $expense->linkable_id ?? '';
            $this->notes = $expense->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'title'               => 'required|string|max:255',
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:1',
            'payment_method'      => 'required|in:cash,bkash,nagad,bank_transfer,other',
            'expense_date'        => 'required|date',
            'bank_account_id'     => 'nullable|integer|exists:bank_accounts,id',
            'receipt'             => 'nullable|image|max:5120', // 5MB max
        ]);

        $data = [
            'title'               => $this->title,
            'expense_category_id' => $this->expense_category_id,
            'amount'              => $this->amount,
            'payment_method'      => $this->payment_method,
            'transaction_id'      => $this->transaction_id ?: null,
            'bank_account_id'     => $this->bank_account_id ?: null,
            'expense_date'        => $this->expense_date,
            'linkable_type'       => $this->linkable_type ?: null,
            'linkable_id'         => $this->linkable_id ?: null,
            'notes'               => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $expense->update($data);
            $message = 'Expense updated successfully.';
        } else {
            $data['recorded_by'] = auth()->id();
            $data['status'] = 'confirmed';
            $expense = Expense::create($data);
            $message = 'Expense recorded successfully.';
        }

        if ($this->receipt) {
            $expense->clearMediaCollection('receipt');
            $expense->addMedia($this->receipt)->toMediaCollection('receipt');
        }

        $this->js("toast('{$message}', {type: 'success'})");
        $this->expenseModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $expense = Expense::findOrFail($id);
        
        // Don't allow deleting auto-generated transfer fee expenses directly
        $feeTransferCount = \App\Models\FundTransfer::where('fee_expense_id', $id)->count();
        if ($feeTransferCount > 0) {
            $this->js("toast('Cannot delete a transfer fee directly. Delete the corresponding Fund Transfer instead.', {type: 'error'})");
            return;
        }

        $expense->delete();
        $this->js("toast('Expense deleted.', {type: 'warning'})");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->expense_category_id = 0;
        $this->amount = '';
        $this->payment_method = 'cash';
        $this->transaction_id = '';
        $this->bank_account_id = 0;
        $this->expense_date = now()->format('Y-m-d');
        $this->linkable_type = '';
        $this->linkable_id = '';
        $this->notes = '';
        $this->receipt = null;
    }

    public function with(): array
    {
        $query = Expense::with(['category', 'recorder', 'bankAccount', 'linkable'])
            ->when($this->search, fn (Builder $q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category_filter, fn (Builder $q) => $q->where('expense_category_id', $this->category_filter));

        if ($this->month_filter) {
            $date = Carbon::parse($this->month_filter);
            $query->whereYear('expense_date', $date->year)
                  ->whereMonth('expense_date', $date->month);
        }

        // Stats for Chart (Category Breakdown for current month filter)
        $chartData = [];
        if ($this->month_filter) {
            $date = Carbon::parse($this->month_filter);
            $chartData = Expense::select('expense_category_id', DB::raw('SUM(amount) as total'))
                ->whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->where('status', 'confirmed')
                ->groupBy('expense_category_id')
                ->with('category')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->category->name ?? 'Unknown',
                        'total' => (float) $item->total,
                        'color' => $item->category->color ?? '#cbd5e1'
                    ];
                });
        }

        return [
            'expenses' => $query->latest('expense_date')->latest('id')->paginate(15),
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->get(),
            'campaigns' => DonationCampaign::where('status', 'active')->latest()->get(),
            'halaqahs' => Halaqah::where('status', 'published')->latest('scheduled_at')->get(),
            'totalAmount' => (clone $query)->sum('amount'),
            'chartData' => $chartData,
        ];
    }
};
