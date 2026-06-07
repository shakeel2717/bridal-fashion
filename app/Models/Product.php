<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'category_id', 'vendor_id', 'size', 'type',
        'purchase_price', 'rental_price', 'sale_price', 'stock_qty', 'color', 'photo', 'group_id',
        'is_abandoned', 'abandoned_price', 'abandoned_date', 'abandoned_note',
        'notes', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'rental_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'abandoned_price' => 'decimal:2',
            'abandoned_date' => 'date',
            'is_abandoned' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_abandoned', false);
    }

    public function scopeForRental($query)
    {
        return $query->whereIn('type', ['rental', 'both']);
    }

    public function scopeForSale($query)
    {
        return $query->whereIn('type', ['sale', 'both']);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProductExpense::class);
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group_id');
    }
}
