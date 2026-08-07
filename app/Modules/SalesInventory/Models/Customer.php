<?php

namespace App\Modules\SalesInventory\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'customer_type', 'npwp', 'address', 'phone', 'email',
    ];

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
