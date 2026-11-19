<?php

// app/Models/Rental.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_ref', 'customer_id', 'customer_name', 'customer_phone1', 'walkin_photo', 'walkin_cnic_front', 'walkin_cnic_back',
        'customer_phone2', 'customer_whatsapp', 'customer_cnic', 'delivery_address', 'customer_city', 'customer_area',
        'booking_date', 'pickup_date', 'return_date', 'stitching_date', 'stitching_sizes', 'advance_payment_method',
        'stitching_instructions', 'status', 'total_amount', 'advance_paid', 'phone1_gender', 'phone2_gender', 'whatsapp_gender',
        'remaining_balance', 'refund_amount', 'refund_type', 'refund_date', 'discount_type', 'discount_value', 'discount_amount',
        'refund_note', 'employee_id', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'advance_paid' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'stitching_sizes' => 'array',
        ];
    }

    /**
     * Work the rental status out from its items' pickup state.
     * Always reads the items fresh, so a stale loaded relation can't leave a wrong status behind.
     */
    public function statusFromItems(): string
    {
        $items = $this->items()->get();
        $total = $items->count();
        $picked = $items->where('pickup_status', 'picked_up')->count();
        $returned = $items->where('pickup_status', 'returned')->count();

        if ($total === 0) {
            return $this->status;
        }
        if ($returned === $total) {
            return 'returned';
        }
        if ($picked === $total) {
            return 'picked_up';
        }
        if ($picked > 0 || $returned > 0) {
            return 'partially_picked_up';
        }

        // Nothing picked up yet (e.g. admin reversed a pickup) — revert to a base status
        return in_array($this->status, ['booked', 'ready']) ? $this->status : 'booked';
    }

    /**
     * Bookings that still need stitching: a stitching date is set, the booking isn't
     * closed and the stitching task hasn't been marked Done (or Denied) yet.
     */
    public function scopeStitchingDue($query)
    {
        return $query->whereNotNull('stitching_date')
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->whereDoesntHave('tasks', fn ($q) => $q->where('type', 'stitching')->where('status', '!=', 'pending'));
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

    public function linkedSale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }
}
