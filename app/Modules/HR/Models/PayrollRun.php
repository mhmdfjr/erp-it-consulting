<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'working_days',
        'absent_days',
        'base_salary',
        'gross_salary',
        'bpjs_kesehatan_deduction',
        'bpjs_jht_deduction',
        'bpjs_jp_deduction',
        'pph21_deduction',
        'ter_category_used',
        'total_deduction',
        'net_salary',
        'status',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class);
    }
}
