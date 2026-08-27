<?php

namespace App\Livewire\Expenses;

use App\Livewire\Concerns\HasPeriodFilter;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Services\AccountService;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use HasPeriodFilter, WithPagination;

    public string $search     = '';
    public string $filterCat  = '';
    public string $filterAccount = '';

    // Form
    public bool   $showForm    = false;
    public ?int   $editId      = null;
    public string $categoryId  = '';
    public string $accountId   = '';
    public string $amount      = '';
    public string $expenseDate = '';
    public string $description = '';
    public string $reference   = '';

    // Category form
    public bool   $showCatForm  = false;
    public string $catName      = '';
    public string $catColor     = '#718096';
    public string $catParentId  = '';

    // Delete
    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->initPeriod('month');
        $this->expenseDate = now()->format('Y-m-d');
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function updatedFilterCat(): void { $this->resetPage(); }

    public function updatedFilterAccount(): void { $this->resetPage(); }

    public function setCategoryFilter(string $id): void
    {
        $this->filterCat = $this->filterCat === $id ? '' : $id;
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->expenseDate = $this->defaultExpenseDate();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $expense = Expense::findOrFail($id);

        $this->editId      = $expense->id;
        $this->categoryId  = (string) $expense->expense_category_id;
        $this->accountId   = (string) $expense->account_id;
        $this->amount      = (string) $expense->amount;
        $this->expenseDate = $expense->expense_date->format('Y-m-d');
        $this->description = $expense->description ?? '';
        $this->reference   = $expense->reference ?? '';
        $this->showForm    = true;
        $this->resetValidation();
    }

    /** When browsing a past period, default new entries into that period. */
    protected function defaultExpenseDate(): string
    {
        if ($this->isCurrentPeriod() || $this->periodEnd()->isFuture()) {
            return now()->format('Y-m-d');
        }

        return $this->periodEnd()->format('Y-m-d');
    }

    public function saveExpense(): void
    {
        $this->validate([
            'categoryId'  => 'required|exists:expense_categories,id',
            'accountId'   => 'required|exists:accounts,id',
            'amount'      => 'required|numeric|min:1',
            'expenseDate' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $category = ExpenseCategory::findOrFail($this->categoryId);

        if ($this->editId) {
            $expense = Expense::findOrFail($this->editId);

            // Reverse old transaction
            $oldAccount = Account::findOrFail($expense->account_id);
            $oldAccount->credit($expense->amount); // reverse the debit

            // Drop the superseded ledger entry so the cashbook doesn't double count
            $this->purgeTransactions($expense);

            $expense->update([
                'expense_category_id' => $this->categoryId,
                'account_id'          => $this->accountId,
                'amount'              => $this->amount,
                'expense_date'        => $this->expenseDate,
                'description'         => $this->description ?: null,
                'reference'           => $this->reference ?: null,
                'updated_by'          => auth()->id(),
            ]);

            // New debit
            AccountService::debit(
                (int) $this->accountId,
                (float) $this->amount,
                'expense',
                "{$category->name}" . ($this->description ? ": {$this->description}" : ''),
                $this->expenseDate,
                $expense,
            );

            session()->flash('success', 'Expense updated.');
        } else {
            $expense = Expense::create([
                'expense_category_id' => $this->categoryId,
                'account_id'          => $this->accountId,
                'amount'              => $this->amount,
                'expense_date'        => $this->expenseDate,
                'description'         => $this->description ?: null,
                'reference'           => $this->reference ?: null,
                'created_by'          => auth()->id(),
                'updated_by'          => auth()->id(),
            ]);

            AccountService::debit(
                (int) $this->accountId,
                (float) $this->amount,
                'expense',
                "{$category->name}" . ($this->description ? ": {$this->description}" : ''),
                $this->expenseDate,
                $expense,
            );

            session()->flash('success', 'Expense recorded.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'catName'  => 'required|string|max:150',
            'catColor' => 'required|string|max:20',
        ]);

        $cat = ExpenseCategory::create([
            'name'      => $this->catName,
            'color'     => $this->catColor,
            'parent_id' => $this->catParentId ?: null,
            'is_active' => true,
        ]);

        $this->categoryId   = (string) $cat->id;
        $this->showCatForm  = false;
        $this->catName      = '';
        $this->catColor     = '#718096';
        $this->catParentId  = '';
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $expense = Expense::findOrFail($this->deleteId);

        // Reverse account debit
        $account = Account::findOrFail($expense->account_id);
        $account->credit($expense->amount);

        // Remove the matching ledger entry so it disappears from the cashbook too
        $this->purgeTransactions($expense);

        $expense->delete();
        $this->deleteId = null;
        session()->flash('success', 'Expense deleted and account balance restored.');
    }

    protected function purgeTransactions(Expense $expense): void
    {
        Transaction::where('referenceable_type', Expense::class)
            ->where('referenceable_id', $expense->id)
            ->delete();
    }

    public function resetForm(): void
    {
        $this->editId      = null;
        $this->categoryId  = '';
        $this->accountId   = '';
        $this->amount      = '';
        $this->expenseDate = now()->format('Y-m-d');
        $this->description = '';
        $this->reference   = '';
        $this->showForm    = false;
        $this->resetValidation();
    }

    /** Filters shared by the list and the totals so both always agree. */
    protected function baseQuery()
    {
        return $this->applyPeriod(Expense::query(), 'expense_date')
            ->when($this->filterCat, fn ($q) => $q->where('expense_category_id', $this->filterCat))
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%");
            }));
    }

    public function render()
    {
        $expenses = $this->baseQuery()
            ->with(['category', 'account', 'createdBy'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20);

        $periodTotal = (float) $this->baseQuery()->sum('amount');
        $periodCount = (int) $this->baseQuery()->count();

        // Previous equivalent period, same filters — for the trend line
        [$prevStart, $prevEnd] = $this->previousPeriodRange();

        $prevTotal = (float) $this->applyDateRange(
            Expense::query(), 'expense_date', $prevStart->toDateString(), $prevEnd->toDateString()
        )
            ->when($this->filterCat, fn ($q) => $q->where('expense_category_id', $this->filterCat))
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount))
            ->sum('amount');

        // Category breakdown for the current period (ignores the category chip
        // so the chips keep showing every option)
        $breakdown = $this->applyPeriod(Expense::query(), 'expense_date')
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount))
            ->selectRaw('expense_category_id, COUNT(*) as entries, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();

        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')->get()->keyBy('id');

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')->get();

        $days = max(1, (int) floor($this->periodStart()->diffInDays($this->periodEnd())) + 1);

        return view('livewire.expenses.expense-list', [
            'expenses'    => $expenses,
            'categories'  => $categories,
            'accounts'    => $accounts,
            'breakdown'   => $breakdown,
            'periodTotal' => $periodTotal,
            'periodCount' => $periodCount,
            'prevTotal'   => $prevTotal,
            'dailyAvg'    => $periodTotal / $days,
            'topCategory' => $breakdown->first(),
        ]);
    }
}
