<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtkpTerMapping extends Model
{
    protected $table = 'ptkp_ter_mapping';

    public $timestamps = false;

    protected $fillable = ['ptkp_status', 'ter_category_id'];

    public function terCategory(): BelongsTo
    {
        return $this->belongsTo(TerCategory::class);
    }
}
