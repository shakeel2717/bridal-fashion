<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Component;

class AccountList extends Component
{
    // Account form
    public bool   $showForm      = false;
    public ?int   $editId        = null;
    public string $name          = '';
    public string $type          = 'cash';
    public string $accountNumber = '';
    public string $bankName      = '';
    public string $openingBalance = '0';
    public bool   $isDefault     = false;
    public string $notes         = '';

    // Transfer
    public bool   $showTransferForm   = false;
    public string $transferFromId     = '';
    public string $transferToId       = '';
    public string $transferAmount     = '';
    public string $transferDate       = '';
    public string $transferDesc       = '';

    // Owner withdrawal
    public bool   $showWithdrawalForm = false;
    public string $withdrawalAccountId = '';
    public string $withdrawalAmount    = '';
    public string $withdrawalDate      = '';
    public string $withdrawalDesc      = '';

    // Selected account for transactions
    public ?int   $selectedAccountId  = null;

    public function mount(): void
    {
        $this->transferDate    = now()->format('Y-m-d');
        $this->withdrawalDate  = now()->format('Y-m-d');
    }

    public function openCreate(): void
    {
        $this->resetAccountForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $account              = Account::findOrFail($id);
        $this->editId         = $id;
        $this->name           = $account->name;
        $this->type           = $account->type;
        $this->accountNumber  = $account->account_number ?? '';
        $this->bankName       = $account->bank_name ?? '';
        $this->openingBalance = (string) $account->opening_balance;
        $this->isDefault      = $account->is_default;
        $this->notes          = $account->notes ?? '';
        $this->showForm       = true;
    }

    public function saveAccount(): void
    {
        $this->validate([
            'name'           => 'required|string|max:150',
            'type'           => 'required|in:cash,bank,mobile_wallet,other',
            'openingBalance' => 'required|numeric|min:0',
        ]);

        if ($this->editId) {
            $account = Account::findOrFail($this->editId);
            $account->update([
                'name'           => $this->name,
                'type'           => $this->type,
                'account_number' => $this->accountNumber ?: null,
                'bank_name'      => $this->bankName ?: null,
                'is_default'     => $this->isDefault,
                'notes'          => $this->notes ?: null,
                'updated_by'     => auth()->id(),
            ]);
        } else {
            $balance = (float) $this->openingBalance;
            $account = Account::create([
                'name'            => $this->name,
                'type'            => $this->type,
                'account_number'  => $this->accountNumber ?: null,
                'bank_name'       => $this->bankName ?: null,
                'opening_balance' => $balance,
                'current_balance' => $balance,
                'is_default'      => $this->isDefault,
                'notes'           => $this->notes ?: null,
                'is_active'       => true,
                'created_by'      => auth()->id(),
                'updated_by'      => auth()->id(),
            ]);

            // Record opening balance as transaction
            if ($balance > 0) {
                Transaction::create([
                    'account_id'       => $account->id,
                    'type'             => 'credit',
                    'amount'           => $balance,
                    'balance_after'    => $balance,
                    'category'         => 'opening_balance',
                    'description'      => 'Opening balance',
                    'transaction_date' => now()->toDateString(),
                    'created_by'       => auth()->id(),
                ]);
            }
        }

        // If set as default, unset others
        if ($this->isDefault) {
            Account::where('id', '!=', $account->id)
                ->update(['is_default' => false]);
        }

        $this->resetAccountForm();
        session()->flash('success', 'Account saved.');
    }

    public function saveTransfer(): void
    {
        $this->validate([
            'transferFromId' => 'required|exists:accounts,id',
            'transferToId'   => 'required|exists:accounts,id|different:transferFromId',
            'transferAmount' => 'required|numeric|min:1',
            'transferDate'   => 'required|date',
        ], [
            'transferToId.different' => 'From and To accounts must be different.',
        ]);

        AccountService::transfer(
            (int) $this->transferFromId,
            (int) $this->transferToId,
            (float) $this->transferAmount,
            $this->transferDesc ?: 'Account transfer',
            $this->transferDate,
        );

        $this->showTransferForm = false;
        $this->transferFromId   = '';
        $this->transferToId     = '';
        $this->transferAmount   = '';
        $this->transferDesc     = '';
        session()->flash('success', 'Transfer completed.');
    }

    public function saveWithdrawal(): void
    {
        $this->validate([
            'withdrawalAccountId' => 'required|exists:accounts,id',
            'withdrawalAmount'    => 'required|numeric|min:1',
            'withdrawalDate'      => 'required|date',
        ]);

        AccountService::ownerWithdrawal(
            (int) $this->withdrawalAccountId,
            (float) $this->withdrawalAmount,
            $this->withdrawalDesc ?: 'Owner withdrawal',
            $this->withdrawalDate,
        );

        $this->showWithdrawalForm  = false;
        $this->withdrawalAccountId = '';
        $this->withdrawalAmount    = '';
        $this->withdrawalDesc      = '';
        session()->flash('success', 'Withdrawal recorded.');
    }

    public function selectAccount(?int $id): void
    {
        $this->selectedAccountId = $id;
    }

    public function resetAccountForm(): void
    {
        $this->editId         = null;
        $this->name           = '';
        $this->type           = 'cash';
        $this->accountNumber  = '';
        $this->bankName       = '';
        $this->openingBalance = '0';
        $this->isDefault      = false;
        $this->notes          = '';
        $this->showForm       = false;
        $this->resetValidation();
    }

    public function render()
    {
        $accounts = Account::orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $totalBalance = $accounts->sum('current_balance');

        $transactions = collect();
        if ($this->selectedAccountId) {
            $transactions = Transaction::where('account_id', $this->selectedAccountId)
                ->with(['createdBy', 'transferToAccount'])
                ->latest('transaction_date')
                ->take(50)
                ->get();
        }

        return view('livewire.accounts.account-list',
            compact('accounts', 'totalBalance', 'transactions'));
    }
}