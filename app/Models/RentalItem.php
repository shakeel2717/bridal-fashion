<?php

// app/Models/RentalItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalItem extends Model
{
    protected $fillable = [
        'rental_id', 'product_id', 'product_name', 'product_code',
        'rental_price', 'custom_option_label', 'custom_option_price',
        'pickup_status', 'picked_up_at', 'returned_at',
        'returned_received_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'rental_price' => 'decimal:2',
            'custom_option_price' => 'decimal:2',
            'picked_up_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_received_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(RentalTask::class);
    }

    public function pickedUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_up_by');
    }
}
