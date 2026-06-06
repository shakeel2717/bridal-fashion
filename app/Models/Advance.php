<?php
// app/Models/Advance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'amount', 'advance_date', 'note',
        'is_deducted', 'salary_record_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'advance_date' => 'date',
            'is_deducted'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryRecord(): BelongsTo
    {
        return $this->belongsTo(SalaryRecord::class);
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