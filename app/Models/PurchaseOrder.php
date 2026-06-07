<?php
// app/Models/PurchaseOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'vendor_bill_number', 'vendor_id',
        'order_date', 'expected_date', 'received_date',
        'status', 'total_amount', 'amount_paid',
        'balance_due', 'discount', 'notes',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date'    => 'date',
            'expected_date' => 'date',
            'received_date' => 'date',
            'total_amount'  => 'decimal:2',
            'amount_paid'   => 'decimal:2',
            'balance_due'   => 'decimal:2',
            'discount'      => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchaseOrderPayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}