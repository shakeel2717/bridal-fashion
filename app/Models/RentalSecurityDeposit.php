<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalSecurityDeposit extends Model
{
    protected $fillable = [
        'rental_id', 'item_name', 'amount',
        'is_paid', 'is_refunded', 'refunded_at',
        'refunded_by', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'is_paid'     => 'boolean',
            'is_refunded' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}