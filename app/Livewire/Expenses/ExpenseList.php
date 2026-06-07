<?php

namespace App\Livewire\Expenses;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountService;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $filterCat  = '';
    public string $filterMonth = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';

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
        $this->expenseDate  = now()->format('Y-m-d');
        $this->filterMonth  = now()->format('Y-m');
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
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

        $expense->delete();
        $this->deleteId = null;
        session()->flash('success', 'Expense deleted and account balance restored.');
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

    public function render()
    {
        $expenses = Expense::with(['category', 'account', 'createdBy'])
            ->when($this->filterCat, fn($q) => $q->where('expense_category_id', $this->filterCat))
            ->when($this->filterMonth, fn($q) =>
                $q->whereRaw("strftime('%Y-%m', expense_date) = ?", [$this->filterMonth])
            )
            ->when($this->dateFrom, fn($q) => $q->where('expense_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->where('expense_date', '<=', $this->dateTo))
            ->when($this->search, fn($q) =>
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%")
            )
            ->latest('expense_date')
            ->paginate(20);

        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')->get();

        $accounts = Account::where('is_active', true)
            ->orderBy('name')->get();

        $totalThisMonth = Expense::whereRaw("strftime('%Y-%m', expense_date) = ?",
                [now()->format('Y-m')])->sum('amount');

        $totalFiltered = Expense::when($this->filterCat, fn($q) => $q->where('expense_category_id', $this->filterCat))
            ->when($this->filterMonth, fn($q) =>
                $q->whereRaw("strftime('%Y-%m', expense_date) = ?", [$this->filterMonth])
            )->sum('amount');

        return view('livewire.expenses.expense-list',
            compact('expenses', 'categories', 'accounts', 'totalThisMonth', 'totalFiltered'));
    }
}