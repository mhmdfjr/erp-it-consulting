<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'npwp',
        'address',
        'phone',
        'email',
    ];

    public function vendorBills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}
