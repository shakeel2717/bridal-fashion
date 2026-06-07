<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;

class AccountService
{
    public static function credit(
        int     $accountId,
        float   $amount,
        string  $category,
        string  $description,
        ?string $date = null,
        ?object $referenceable = null,
        ?int    $createdBy = null
    ): Transaction {
        $account = Account::findOrFail($accountId);
        $account->credit($amount);
        $account->refresh();

        return Transaction::create([
            'account_id'          => $accountId,
            'type'                => 'credit',
            'amount'              => $amount,
            'balance_after'       => $account->current_balance,
            'category'            => $category,
            'description'         => $description,
            'transaction_date'    => $date ?? now()->toDateString(),
            'referenceable_type'  => $referenceable ? get_class($referenceable) : null,
            'referenceable_id'    => $referenceable?->id,
            'created_by'          => $createdBy ?? auth()->id(),
        ]);
    }

    public static function debit(
        int     $accountId,
        float   $amount,
        string  $category,
        string  $description,
        ?string $date = null,
        ?object $referenceable = null,
        ?int    $createdBy = null
    ): Transaction {
        $account = Account::findOrFail($accountId);
        $account->debit($amount);
        $account->refresh();

        return Transaction::create([
            'account_id'          => $accountId,
            'type'                => 'debit',
            'amount'              => $amount,
            'balance_after'       => $account->current_balance,
            'category'            => $category,
            'description'         => $description,
            'transaction_date'    => $date ?? now()->toDateString(),
            'referenceable_type'  => $referenceable ? get_class($referenceable) : null,
            'referenceable_id'    => $referenceable?->id,
            'created_by'          => $createdBy ?? auth()->id(),
        ]);
    }

    public static function transfer(
        int     $fromAccountId,
        int     $toAccountId,
        float   $amount,
        string  $description,
        ?string $date = null,
        ?int    $createdBy = null
    ): array {
        $from = Account::findOrFail($fromAccountId);
        $to   = Account::findOrFail($toAccountId);

        $from->debit($amount);
        $from->refresh();

        $to->credit($amount);
        $to->refresh();

        $debitTxn = Transaction::create([
            'account_id'            => $fromAccountId,
            'type'                  => 'debit',
            'amount'                => $amount,
            'balance_after'         => $from->current_balance,
            'category'              => 'transfer_out',
            'description'           => "Transfer to {$to->name}: {$description}",
            'transaction_date'      => $date ?? now()->toDateString(),
            'transfer_to_account_id'=> $toAccountId,
            'created_by'            => $createdBy ?? auth()->id(),
        ]);

        $creditTxn = Transaction::create([
            'account_id'            => $toAccountId,
            'type'                  => 'credit',
            'amount'                => $amount,
            'balance_after'         => $to->current_balance,
            'category'              => 'transfer_in',
            'description'           => "Transfer from {$from->name}: {$description}",
            'transaction_date'      => $date ?? now()->toDateString(),
            'transfer_to_account_id'=> $fromAccountId,
            'created_by'            => $createdBy ?? auth()->id(),
        ]);

        return [$debitTxn, $creditTxn];
    }

    public static function ownerWithdrawal(
        int     $accountId,
        float   $amount,
        string  $description,
        ?string $date = null,
        ?int    $createdBy = null
    ): Transaction {
        return self::debit(
            $accountId, $amount,
            'owner_withdrawal',
            $description ?: 'Owner withdrawal',
            $date, null, $createdBy
        );
    }
}