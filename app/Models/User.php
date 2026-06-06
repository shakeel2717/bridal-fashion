<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'cnic',
        'address',
        'photo',
        'designation',
        'joining_date',
        'salary_type',
        'salary_amount',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
            'salary_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function canAccess(string $feature): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $toggle = $this->featureToggles()->where('feature', $feature)->first();

        if ($toggle) {
            return $toggle->is_enabled;
        }

        // Fall back to global default
        $global = FeatureToggle::whereNull('user_id')
            ->where('feature', $feature)
            ->first();

        return $global ? $global->is_enabled : false;
    }

    // ─── Relationships ────────────────────────────────────
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function featureToggles(): HasMany
    {
        return $this->hasMany(FeatureToggle::class, 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class, 'user_id');
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class, 'user_id');
    }

    public function rentalsHandled(): HasMany
    {
        return $this->hasMany(Rental::class, 'employee_id');
    }

    public function salesHandled(): HasMany
    {
        return $this->hasMany(Sale::class, 'employee_id');
    }
}
