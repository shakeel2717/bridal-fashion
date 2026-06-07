<?php
// app/Models/PurchaseOrderPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderPayment extends Model
{
    protected $fillable = [
        'purchase_order_id', 'amount', 'payment_date',
        'payment_method', 'type', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}