<?php
// app/Models/SaleItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'product_name', 'product_code',
        'sale_price', 'qty', 'custom_option_label', 'custom_option_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_price'          => 'decimal:2',
            'custom_option_price' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}