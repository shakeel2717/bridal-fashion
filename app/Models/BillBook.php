<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillBook extends Model
{
    protected $fillable = [
        'name', 'prefix', 'range_from', 'range_to',
        'type', 'notes', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'range_from' => 'integer',
            'range_to'   => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPagesAttribute(): int
    {
        return $this->range_to - $this->range_from + 1;
    }

    // Build the full bill ref string for a given number
    public function buildRef(int $number): string
    {
        return ($this->prefix ?? '') . $number;
    }
}