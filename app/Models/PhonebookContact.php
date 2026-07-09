<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhonebookContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phonebook_category_id',
        'name',
        'phone_numbers',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PhonebookCategory::class, 'phonebook_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
