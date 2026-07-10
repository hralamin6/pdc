<?php

use App\Models\MonthlyTreasuryReport;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\FundTransfer;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Monthly Treasury Reports')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $modal = false;
    public ?int $editingId = null;

    public string $year = '';
    public string $month = '';
    public string $opening_balance = '0';
    public string $notes = '';
    
    // Auto calculated
    public float $calc_income = 0;
    public float $calc_expense = 0;
    public float $calc_fees = 0;

    public function mount(): void
    {
        $this->year = now()->format('Y');
        $this->month = now()->format('m');
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $this->editingId = $id;
            $r = MonthlyTreasuryReport::findOrFail($id);
            $this->year = (string) $r->year;
            $this->month = str_pad($r->month, 2, '0', STR_PAD_LEFT);
            $this->opening_balance = (string) $r->opening_balance;
            $this->notes = $r->notes ?? '';
            $this->calc_income = (float) $r->total_income;
            $this->calc_expense = (float) $r->total_expense;
            $this->calc_fees = (float) $r->total_transfer_fees;
        } else {
            $this->calculateTotals();
            // Try to guess opening balance from previous month report
            $prev = MonthlyTreasuryReport::where('year', $this->month == '01' ? $this->year - 1 : $this->year)
                        ->where('month', $this->month == '01' ? 12 : $this->month - 1)
                        ->first();
            if ($prev) {
                $this->opening_balance = (string) $prev->closing_balance;
            } else {
                // Calc absolute all-time balance before this month start
                $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
                
                $prevIncome = Donation::where('status', 'confirmed')->where('donated_at', '<', $start)->sum('amount');
                $prevExpense = Expense::where('status', 'confirmed')->where('expense_date', '<', $start)->sum('amount');
                $prevFees = FundTransfer::where('status', 'completed')->where('transfer_date', '<', $start)->sum('fee');
                
                $this->opening_balance = (string) ($prevIncome - $prevExpense - $prevFees);
            }
        }
        $this->modal = true;
    }

    public function updatedYear(): void
    {
        $this->calculateTotals();
    }

    public function updatedMonth(): void
    {
        $this->calculateTotals();
    }

    public function calculateTotals(): void
    {
        if (!$this->year || !$this->month) return;
        
        $data = MonthlyTreasuryReport::calculate((int) $this->year, (int) $this->month);
        $this->calc_income = $data['total_income'];
        $this->calc_expense = $data['total_expense'];
        $this->calc_fees = $data['total_transfer_fees'];
    }

    #[Computed]
    public function closingBalance(): float
    {
        return (float) $this->opening_balance + $this->calc_income - $this->calc_expense - $this->calc_fees;
    }

    public function save(): void
    {
        $this->validate([
            'year'            => 'required|numeric|min:2020|max:2100',
            'month'           => 'required|numeric|min:1|max:12',
            'opening_balance' => 'required|numeric',
        ]);

        $data = [
            'year'                => $this->year,
            'month'               => $this->month,
            'total_income'        => $this->calc_income,
            'total_expense'       => $this->calc_expense,
            'total_transfer_fees' => $this->calc_fees,
            'opening_balance'     => $this->opening_balance,
            'closing_balance'     => $this->closingBalance,
            'notes'               => $this->notes ?: null,
            'generated_by'        => auth()->id(),
        ];

        if ($this->editingId) {
            MonthlyTreasuryReport::findOrFail($this->editingId)->update($data);
            $this->js("toast('Report updated.', {type: 'success'})");
        } else {
            // Ensure unique
            $exists = MonthlyTreasuryReport::where('year', $this->year)->where('month', $this->month)->exists();
            if ($exists) {
                $this->js("toast('A report for this month already exists.', {type: 'error'})");
                return;
            }
            MonthlyTreasuryReport::create($data);
            $this->js("toast('Report generated.', {type: 'success'})");
        }

        $this->modal = false;
        $this->resetForm();
    }

    public function togglePublish(int $id): void
    {
        $r = MonthlyTreasuryReport::findOrFail($id);
        $r->update(['published_at' => $r->published_at ? null : now()]);
        $this->js("toast('Report visibility updated.', {type: 'info'})");
    }
    
    public function delete(int $id): void
    {
        MonthlyTreasuryReport::findOrFail($id)->delete();
        $this->js("toast('Report deleted.', {type: 'warning'})");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->year = now()->format('Y');
        $this->month = now()->format('m');
        $this->opening_balance = '0';
        $this->notes = '';
        $this->calc_income = 0;
        $this->calc_expense = 0;
        $this->calc_fees = 0;
    }

    public bool $detailsModal = false;
    public ?MonthlyTreasuryReport $viewingReport = null;
    public array $expenseBreakdown = [];
    public array $incomeBreakdown = [];

    public function viewDetails(int $id): void
    {
        $this->viewingReport = MonthlyTreasuryReport::findOrFail($id);
        
        $start = Carbon::create($this->viewingReport->year, $this->viewingReport->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Expense breakdown
        $this->expenseBreakdown = Expense::with('category')
            ->select('expense_category_id', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$start, $end])
            ->where('status', 'confirmed')
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn($ex) => [
                'name' => $ex->category->name ?? 'Uncategorized',
                'color' => $ex->category->color ?? '#cbd5e1',
                'total' => $ex->total,
            ])
            ->toArray();

        // Income breakdown by payment method
        $this->incomeBreakdown = Donation::select('payment_method', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total'))
            ->whereBetween('donated_at', [$start, $end])
            ->where('status', 'confirmed')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($inc) => [
                'method' => ucfirst($inc->payment_method),
                'total' => $inc->total,
            ])
            ->toArray();
            
        $this->detailsModal = true;
    }

    public function with(): array
    {
        return [
            'reports' => MonthlyTreasuryReport::with('generatedBy')->orderBy('year', 'desc')->orderBy('month', 'desc')->paginate(12),
        ];
    }
};
