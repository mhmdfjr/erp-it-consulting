<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\PayrollPeriodFactory;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return PayrollPeriodFactory::new();
    }

    const UPDATED_AT = null;

    protected $fillable = ['period_month', 'period_year', 'status', 'processed_at'];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }
}
