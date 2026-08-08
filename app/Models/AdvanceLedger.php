<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceLedger extends Model
{
    use SoftDeletes;

    protected $table = 'advance_ledger';

    protected $fillable = [
        'user_id', 'type', 'amount', 'ledger_date',
        'note', 'advance_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'ledger_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(Advance::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'advance'           => 'Advance Given',
            'salary_deduction'  => 'Salary Deduction',
            'cash_repayment'    => 'Cash Repayment',
            default             => ucfirst($type),
        };
    }
}
