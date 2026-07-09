<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhonebookCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'created_by'];

    public function contacts(): HasMany
    {
        return $this->hasMany(PhonebookContact::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
