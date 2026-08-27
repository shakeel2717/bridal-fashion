<?php

namespace App\Livewire\Cashbook;

use App\Livewire\Concerns\HasPeriodFilter;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Services\AccountService;
use Livewire\Component;
use Livewire\WithPagination;

class CashbookList extends Component
{
    use HasPeriodFilter, WithPagination;

    /** Categories this screen creates itself — only these may be deleted from here. */
    public const MANUAL_IN = ['cash_in', 'owner_investment', 'other_income'];

    public const MANUAL_OUT = ['cash_out', 'owner_withdrawal', 'expense'];

    public string $search = '';

    /** '' | 'in' | 'out' */
    public string $flow = '';

    public string $filterAccount = '';

    public string $filterCategory = '';

    // ── Entry form ───────────────────────────────────────
    /** '' (closed) | 'in' | 'out' */
    public string $entryMode = '';

    public string $entryAmount = '';

    public string $entryAccountId = '';

    public string $entryDate = '';

    public string $entryCategory = '';

    /** Money Out only — when set, an Expense record is created too. */
    public string $entryExpenseCategoryId = '';

    public string $entryDescription = '';

    public string $entryReference = '';

    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->initPeriod('day');
        $this->entryDate = now()->format('Y-m-d');

        $default = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        $this->entryAccountId = $default ? (string) $default->id : '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFlow(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAccount(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function setFlow(string $flow): void
    {
        $this->flow = $this->flow === $flow ? '' : $flow;
        $this->resetPage();
    }

    // ── Money In / Money Out ─────────────────────────────
    public function openEntry(string $mode): void
    {
        $this->resetEntry();
        $this->entryMode = $mode === 'in' ? 'in' : 'out';
        $this->entryCategory = $this->entryMode === 'in' ? 'cash_in' : 'cash_out';
        $this->entryDate = $this->defaultEntryDate();
    }

    /**
     * When browsing a past period, default the date to that period so the entry
     * lands where the admin is actually looking (never in the future).
     */
    protected function defaultEntryDate(): string
    {
        if ($this->isCurrentPeriod()) {
            return now()->format('Y-m-d');
        }

        return $this->periodEnd()->isFuture()
            ? now()->format('Y-m-d')
            : $this->periodEnd()->format('Y-m-d');
    }

    public function saveEntry(): void
    {
        $this->validate([
            'entryAmount' => 'required|numeric|min:1',
            'entryAccountId' => 'required|exists:accounts,id',
            'entryDate' => 'required|date',
            'entryDescription' => 'required|string|max:400',
            'entryReference' => 'nullable|string|max:100',
            'entryExpenseCategoryId' => 'nullable|exists:expense_categories,id',
        ], [], [
            'entryAmount' => 'amount',
            'entryAccountId' => 'account',
            'entryDate' => 'date',
            'entryDescription' => 'description',
        ]);

        $amount = (float) $this->entryAmount;
        $description = trim($this->entryDescription);

        if ($this->entryReference) {
            $description .= ' (Ref: '.trim($this->entryReference).')';
        }

        if ($this->entryMode === 'in') {
            $category = in_array($this->entryCategory, self::MANUAL_IN, true)
                ? $this->entryCategory
                : 'cash_in';

            AccountService::credit(
                (int) $this->entryAccountId,
                $amount,
                $category,
                $description,
                $this->entryDate,
            );

            session()->flash('success', 'Money In recorded — Rs. '.number_format($amount, 0));
        } else {
            // With an expense category picked, keep the expense module in sync.
            if ($this->entryExpenseCategoryId) {
                $expenseCategory = ExpenseCategory::findOrFail($this->entryExpenseCategoryId);

                $expense = Expense::create([
                    'expense_category_id' => $expenseCategory->id,
                    'account_id' => $this->entryAccountId,
                    'amount' => $amount,
                    'expense_date' => $this->entryDate,
                    'description' => trim($this->entryDescription) ?: null,
                    'reference' => $this->entryReference ?: null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                AccountService::debit(
                    (int) $this->entryAccountId,
                    $amount,
                    'expense',
                    $expenseCategory->name.': '.$description,
                    $this->entryDate,
                    $expense,
                );
            } else {
                $category = in_array($this->entryCategory, self::MANUAL_OUT, true)
                    ? $this->entryCategory
                    : 'cash_out';

                AccountService::debit(
                    (int) $this->entryAccountId,
                    $amount,
                    $category,
                    $description,
                    $this->entryDate,
                );
            }

            session()->flash('success', 'Money Out recorded — Rs. '.number_format($amount, 0));
        }

        $this->resetEntry();
        $this->resetPage();
    }

    public function resetEntry(): void
    {
        $this->entryMode = '';
        $this->entryAmount = '';
        $this->entryDate = now()->format('Y-m-d');
        $this->entryCategory = '';
        $this->entryExpenseCategoryId = '';
        $this->entryDescription = '';
        $this->entryReference = '';
        $this->resetValidation();
    }

    // ── Delete (manual entries only) ─────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $txn = Transaction::findOrFail($this->deleteId);

        if (! $this->isManual($txn)) {
            $this->deleteId = null;
            session()->flash('error', 'This entry belongs to another module and can only be removed there.');

            return;
        }

        $account = Account::find($txn->account_id);

        if ($account) {
            // Reverse the balance effect.
            $txn->type === 'credit'
                ? $account->debit((float) $txn->amount)
                : $account->credit((float) $txn->amount);
        }

        // A cashbook expense entry owns its Expense row — remove both.
        if ($txn->referenceable_type === Expense::class && $txn->referenceable_id) {
            Expense::where('id', $txn->referenceable_id)->delete();
        }

        $txn->delete();
        $this->deleteId = null;
        session()->flash('success', 'Entry deleted and balance restored.');
    }

    public function isManual(Transaction $txn): bool
    {
        return in_array($txn->category, array_merge(self::MANUAL_IN, self::MANUAL_OUT), true);
    }

    // ── Query ────────────────────────────────────────────
    protected function baseQuery()
    {
        return $this->applyPeriod(Transaction::query(), 'transaction_date')
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount))
            ->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))
            ->when($this->flow === 'in', fn ($q) => $q->where('type', 'credit'))
            ->when($this->flow === 'out', fn ($q) => $q->where('type', 'debit'))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('description', 'like', "%{$this->search}%")
                        ->orWhere('category', 'like', "%{$this->search}%");
                });
            });
    }

    public function render()
    {
        $from = $this->periodFrom();
        $to = $this->periodTo();

        $transactions = $this->baseQuery()
            ->with(['account:id,name,type', 'createdBy:id,name', 'transferToAccount:id,name'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25);

        // Summary figures follow the account filter only. Type/search/flow filters
        // narrow the table, not the period's cash position.
        $totalsQuery = fn () => $this->applyDateRange(Transaction::query(), 'transaction_date', $from, $to)
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount));

        $moneyIn = (float) $totalsQuery()->where('type', 'credit')->sum('amount');
        $moneyOut = (float) $totalsQuery()->where('type', 'debit')->sum('amount');

        // Balances are anchored to the accounts' live balance and wound backwards,
        // so the cashbook always agrees with the Accounts screen even where the
        // ledger predates a manual balance adjustment.
        $liveBalance = (float) Account::query()
            ->when($this->filterAccount, fn ($q) => $q->where('id', $this->filterAccount))
            ->sum('current_balance');

        $afterQuery = fn () => Transaction::query()
            ->whereDate('transaction_date', '>', $to)
            ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount));

        $afterNet = (float) $afterQuery()->where('type', 'credit')->sum('amount')
            - (float) $afterQuery()->where('type', 'debit')->sum('amount');

        $closing = $liveBalance - $afterNet;
        $opening = $closing - ($moneyIn - $moneyOut);

        // Previous period comparison
        [$prevStart, $prevEnd] = $this->previousPeriodRange();

        $prevQuery = fn () => $this->applyDateRange(
            Transaction::query(), 'transaction_date', $prevStart->toDateString(), $prevEnd->toDateString()
        )->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount));

        $prevIn = (float) $prevQuery()->where('type', 'credit')->sum('amount');
        $prevOut = (float) $prevQuery()->where('type', 'debit')->sum('amount');

        // Category breakdown honours every active filter — it describes the table.
        $breakdown = $this->baseQuery()
            ->selectRaw('category, type, COUNT(*) as entries, SUM(amount) as total')
            ->groupBy('category', 'type')
            ->orderByDesc('total')
            ->get();

        // Subtotal of exactly what the table is showing.
        $shownIn = (float) $this->baseQuery()->where('type', 'credit')->sum('amount');
        $shownOut = (float) $this->baseQuery()->where('type', 'debit')->sum('amount');

        // Dropdowns only offer active accounts; the balance panel and the closing
        // figure cover every account so they match the Accounts screen.
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $balanceAccounts = Account::orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $expenseCategories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $usedCategories = $this->applyDateRange(Transaction::query(), 'transaction_date', $from, $to)
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        return view('livewire.cashbook.cashbook-list', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'balanceAccounts' => $balanceAccounts,
            'expenseCategories' => $expenseCategories,
            'usedCategories' => $usedCategories,
            'breakdown' => $breakdown,
            'moneyIn' => $moneyIn,
            'moneyOut' => $moneyOut,
            'netFlow' => $moneyIn - $moneyOut,
            'opening' => $opening,
            'closing' => $closing,
            'shownIn' => $shownIn,
            'shownOut' => $shownOut,
            'isFiltered' => (bool) ($this->flow || $this->filterCategory || $this->search),
            'prevIn' => $prevIn,
            'prevOut' => $prevOut,
            'totalBalance' => (float) $balanceAccounts->sum('current_balance'),
        ]);
    }
}
