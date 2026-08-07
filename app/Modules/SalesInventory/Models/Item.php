<?php

namespace App\Modules\SalesInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ItemFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'item_type', 'item_category_id',
        'unit_of_measure', 'unit_price', 'cost_price', 'is_active',
    ];

    protected static function newFactory()
    {
        return ItemFactory::new();
    }

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function stockLevel(): HasOne
    {
        return $this->hasOne(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function isPhysicalGood(): bool
    {
        return $this->item_type === 'physical_good';
    }
}
