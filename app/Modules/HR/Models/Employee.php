<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\EmployeeFactory;

class Employee extends Model
{
    use SoftDeletes;

    use HasFactory;

    protected static function newFactory()
    {
        return EmployeeFactory::new();
    }

    protected $fillable = [
        'user_id',
        'employee_code',
        'full_name',
        'nik',
        'npwp',
        'gender',
        'birth_date',
        'ptkp_status',
        'position_id',
        'base_salary',
        'hire_date',
        'termination_date',
        'employment_status',
        'bank_name',
        'bank_account_number',
        'address',
        'phone',
        'email',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }
}
