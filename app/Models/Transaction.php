<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'account_id', 'type', 'amount', 'balance_after',
        'category', 'description', 'transaction_date',
        'referenceable_type', 'referenceable_id',
        'transfer_to_account_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'balance_after'    => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}