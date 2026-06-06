<?php

// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'status', 'note', 'created_by', 'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($attendance) {
            if (empty($attendance->created_by)) {
                $attendance->created_by = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
