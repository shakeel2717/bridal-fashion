<?php

// app/Models/Rental.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_ref', 'customer_id', 'customer_name', 'customer_phone1', 'walkin_photo', 'walkin_cnic_front', 'walkin_cnic_back',
        'customer_phone2', 'customer_whatsapp', 'customer_cnic', 'delivery_address',
        'booking_date', 'pickup_date', 'return_date', 'stitching_date', 'advance_payment_method',
        'stitching_instructions', 'status', 'total_amount', 'advance_paid', 'phone1_gender', 'phone2_gender', 'whatsapp_gender',
        'remaining_balance', 'refund_amount', 'refund_type', 'refund_date','discount_type','discount_value','discount_amount',
        'refund_note', 'employee_id', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'advance_paid' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function scopeOverdue($query)
    {
        return $query->whereRaw('DATE(return_date) < ?', [now()->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereRaw('DATE(return_date) = ?', [now()->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned']);
    }

    public function scopePickupToday($query)
    {
        return $query->whereRaw('DATE(pickup_date) = ?', [now()->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned']);
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
        return $this->hasMany(RentalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(RentalTask::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentalPayment::class);
    }

    public function securityDeposits(): HasMany
    {
        return $this->hasMany(RentalSecurityDeposit::class);
    }
}
