<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollComponentFactory extends Factory
{
    protected $model = \App\Modules\HR\Models\PayrollComponent::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => 'earning',
            'calculation_type' => 'fixed_amount',
            'is_active' => true,
        ];
    }

    public function deduction(): static
    {
        return $this->state(fn () => ['type' => 'deduction']);
    }

    public function percentageOfBase(): static
    {
        return $this->state(fn () => ['calculation_type' => 'percentage_of_base']);
    }
}
