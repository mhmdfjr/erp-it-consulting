<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerCategory extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'description'];

    public function ptkpMappings(): HasMany
    {
        return $this->hasMany(PtkpTerMapping::class);
    }

    public function terRates(): HasMany
    {
        return $this->hasMany(TerRate::class);
    }
}
