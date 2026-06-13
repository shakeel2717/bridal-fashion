<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'return_number', 'sale_id', 'customer_name',
        'return_date', 'total_amount',
        'resolution', 'refund_amount', 'refund_date', 'refund_account_id',
        'status', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'return_date'  => 'date',
            'refund_date'  => 'date',
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function refundAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'refund_account_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
