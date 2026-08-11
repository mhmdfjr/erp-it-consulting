<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\PayrollComponentFactory;

class PayrollComponent extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return PayrollComponentFactory::new();
    }

    protected $fillable = ['name', 'type', 'calculation_type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function payrollRunItems(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class);
    }
}
