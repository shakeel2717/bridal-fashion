<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lender extends Model
{
    protected $fillable = [
        'name', 'phone', 'relation', 'notes', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class)->orderBy('date')->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Total borrowed from this lender
    public function totalReceived(): float
    {
        return (float) $this->transactions()->where('type', 'received')->sum('amount');
    }

    // Total paid back to this lender
    public function totalPaid(): float
    {
        return (float) $this->transactions()->where('type', 'paid')->sum('amount');
    }

    // Outstanding balance still owed to this lender
    public function outstandingBalance(): float
    {
        return max(0, $this->totalReceived() - $this->totalPaid());
    }
}
