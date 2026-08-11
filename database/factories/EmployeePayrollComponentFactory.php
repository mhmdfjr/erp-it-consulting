<?php

namespace Database\Factories;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeePayrollComponentFactory extends Factory
{
    protected $model = \App\Modules\HR\Models\EmployeePayrollComponent::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'payroll_component_id' => PayrollComponent::factory(),
            'amount' => 1000000,
            'percentage' => null,
            'effective_date' => now()->subYear()->toDateString(),
            'end_date' => null,
        ];
    }

    public function percentage(float $percentage): static
    {
        return $this->state(fn () => [
            'amount' => null,
            'percentage' => $percentage,
        ]);
    }
}
