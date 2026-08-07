<?php

namespace App\Modules\SalesInventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'item_id', 'movement_type', 'quantity',
        'reference_type', 'reference_id', 'reason_code', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
