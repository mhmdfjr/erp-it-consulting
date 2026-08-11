<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ter_category_id',
        'income_lower_bound',
        'income_upper_bound',
        'rate_percentage',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    public function terCategory(): BelongsTo
    {
        return $this->belongsTo(TerCategory::class);
    }
}
