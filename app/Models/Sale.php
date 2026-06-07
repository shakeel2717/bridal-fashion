<?php
// app/Models/Sale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_ref', 'customer_id', 'customer_name', 'customer_phone1',
        'customer_phone2', 'customer_cnic', 'delivery_address', 'phone1_gender', 'phone2_gender',
        'sale_date', 'status', 'total_amount', 'advance_paid', 'advance_payment_method',
        'remaining_balance', 'refund_amount', 'refund_date', 'refund_note',
        'employee_id', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date'         => 'date',
            'refund_date'       => 'date',
            'total_amount'      => 'decimal:2',
            'advance_paid'      => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'refund_amount'     => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
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