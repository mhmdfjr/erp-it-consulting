<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\EmployeePayrollComponentFactory;

class EmployeePayrollComponent extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return EmployeePayrollComponentFactory::new();
    }

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'payroll_component_id',
        'amount',
        'percentage',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollComponent(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class);
    }
}
