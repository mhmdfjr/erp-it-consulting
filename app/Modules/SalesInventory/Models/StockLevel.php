<?php

namespace App\Modules\SalesInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    const CREATED_AT = null;

    protected $fillable = ['item_id', 'quantity_on_hand', 'quantity_reserved'];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getAvailableAttribute(): string
    {
        return bcsub((string) $this->quantity_on_hand, (string) $this->quantity_reserved, 2);
    }
}
