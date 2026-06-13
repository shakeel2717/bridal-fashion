<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanTransaction extends Model
{
    protected $fillable = [
        'lender_id', 'type', 'amount', 'balance_after',
        'account_id', 'date', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'amount'        => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function lender(): BelongsTo
    {
        return $this->belongsTo(Lender::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
