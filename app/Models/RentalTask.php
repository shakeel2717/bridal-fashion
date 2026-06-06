<?php
// app/Models/RentalTask.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalTask extends Model
{
    protected $fillable = [
        'rental_id', 'rental_item_id', 'type', 'title',
        'cost', 'status', 'note', 'actioned_by',
        'actioned_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'cost'        => 'decimal:2',
            'actioned_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function rentalItem(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}