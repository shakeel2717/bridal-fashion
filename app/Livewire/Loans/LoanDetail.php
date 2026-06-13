<?php

namespace App\Livewire\Loans;

use App\Models\Account;
use App\Models\Lender;
use App\Models\LoanTransaction;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Component;

class LoanDetail extends Component
{
    public Lender $lender;

    // ── Add Transaction Form ──────────────────────────────
    public bool $showForm = false;

    public string $txnType      = 'received'; // received | paid
    public string $txnAmount    = '';
    public string $txnAccountId = '';
    public string $txnDate      = '';
    public string $txnNote      = '';

    public ?int $editTxnId = null;

    public function mount(Lender $lender): void
    {
        $this->lender      = $lender;
        $this->txnDate     = now()->format('Y-m-d');

        $default = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->txnAccountId = $default ? (string) $default->id : '';
    }

    public function openAdd(string $type = 'received'): void
    {
        $this->resetTxnForm();
        $this->txnType  = $type;
        $this->showForm = true;
    }

    public function editTxn(int $id): void
    {
        $txn = LoanTransaction::findOrFail($id);
        $this->editTxnId    = $id;
        $this->txnType      = $txn->type;
        $this->txnAmount    = (string) $txn->amount;
        $this->txnAccountId = $txn->account_id ? (string) $txn->account_id : '';
        $this->txnDate      = $txn->date->format('Y-m-d');
        $this->txnNote      = $txn->note ?? '';
        $this->showForm     = true;
    }

    public function saveTxn(): void
    {
        $this->validate([
            'txnType'      => 'required|in:received,paid',
            'txnAmount'    => 'required|numeric|min:1',
            'txnAccountId' => 'required|exists:accounts,id',
            'txnDate'      => 'required|date',
            'txnNote'      => 'nullable|string|max:500',
        ]);

        $amount = (float) $this->txnAmount;

        if ($this->editTxnId) {
            // Simple edit — just update fields, do NOT re-touch accounts
            // (account adjustments on edits would be complex; user should delete + re-add)
            $txn = LoanTransaction::findOrFail($this->editTxnId);
            $txn->update([
                'type'       => $this->txnType,
                'amount'     => $amount,
                'account_id' => (int) $this->txnAccountId,
                'date'       => $this->txnDate,
                'note'       => $this->txnNote ?: null,
            ]);

            // Recalc all running balances for this lender
            $this->recalcBalances();

            session()->flash('success', 'Transaction updated. Note: account balance was NOT re-adjusted. Delete and re-add if needed.');
        } else {
            // Compute new running balance
            $lastBalance = (float) ($this->lender->transactions()
                ->orderByDesc('date')->orderByDesc('id')
                ->value('balance_after') ?? 0);

            $newBalance = $this->txnType === 'received'
                ? $lastBalance + $amount
                : max(0, $lastBalance - $amount);

            $txn = LoanTransaction::create([
                'lender_id'    => $this->lender->id,
                'type'         => $this->txnType,
                'amount'       => $amount,
                'balance_after'=> $newBalance,
                'account_id'   => (int) $this->txnAccountId,
                'date'         => $this->txnDate,
                'note'         => $this->txnNote ?: null,
                'created_by'   => auth()->id(),
            ]);

            // Account entry
            $desc = $this->txnType === 'received'
                ? "Borrowed from {$this->lender->name}"
                : "Paid back to {$this->lender->name}";

            if ($this->txnNote) {
                $desc .= " — {$this->txnNote}";
            }

            if ($this->txnType === 'received') {
                // Money came IN to our account
                AccountService::credit(
                    (int) $this->txnAccountId,
                    $amount,
                    'loan_received',
                    $desc,
                    $this->txnDate,
                    $txn,
                );
            } else {
                // Money went OUT of our account
                AccountService::debit(
                    (int) $this->txnAccountId,
                    $amount,
                    'loan_repayment',
                    $desc,
                    $this->txnDate,
                    $txn,
                );
            }

            session()->flash('success', 'Transaction saved & account updated.');
        }

        $this->resetTxnForm();
        $this->lender->refresh();
    }

    public function deleteTxn(int $id): void
    {
        LoanTransaction::findOrFail($id)->delete();
        $this->recalcBalances();
        $this->lender->refresh();
        session()->flash('success', 'Transaction deleted. Account balance was NOT reversed — adjust manually if needed.');
    }

    private function recalcBalances(): void
    {
        $running = 0.0;
        $txns = LoanTransaction::where('lender_id', $this->lender->id)
            ->orderBy('date')->orderBy('id')->get();

        foreach ($txns as $txn) {
            $running = $txn->type === 'received'
                ? $running + (float) $txn->amount
                : max(0, $running - (float) $txn->amount);
            $txn->update(['balance_after' => $running]);
        }
    }

    public function resetTxnForm(): void
    {
        $this->showForm   = false;
        $this->editTxnId  = null;
        $this->txnType    = 'received';
        $this->txnAmount  = '';
        $this->txnDate    = now()->format('Y-m-d');
        $this->txnNote    = '';
        $this->resetValidation();

        $default = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->txnAccountId = $default ? (string) $default->id : '';
    }

    public function render()
    {
        $transactions = LoanTransaction::where('lender_id', $this->lender->id)
            ->with('account', 'createdBy')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $totalReceived    = $transactions->where('type', 'received')->sum('amount');
        $totalPaid        = $transactions->where('type', 'paid')->sum('amount');
        $outstanding      = max(0, (float) $totalReceived - (float) $totalPaid);

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.loans.loan-detail', compact(
            'transactions', 'totalReceived', 'totalPaid', 'outstanding', 'accounts'
        ));
    }
}
