<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'account_number', 'bank_name',
        'opening_balance', 'current_balance',
        'is_active', 'is_default', 'notes',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active'       => 'boolean',
            'is_default'      => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper to add/subtract balance
    public function credit(float $amount): void
    {
        $this->increment('current_balance', $amount);
    }

    public function debit(float $amount): void
    {
        $this->decrement('current_balance', $amount);
    }
}