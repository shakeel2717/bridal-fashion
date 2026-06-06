<?php
// app/Models/SalaryRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'month', 'year', 'base_salary', 'days_present',
        'earned_salary', 'total_advances', 'total_bonus', 'net_salary',
        'paid_date', 'status', 'note', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'base_salary'    => 'decimal:2',
            'earned_salary'  => 'decimal:2',
            'total_advances' => 'decimal:2',
            'total_bonus'    => 'decimal:2',
            'net_salary'     => 'decimal:2',
            'paid_date'      => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class);
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