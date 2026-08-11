<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollPeriodFactory extends Factory
{
    protected $model = \App\Modules\HR\Models\PayrollPeriod::class;

    public function definition(): array
    {
        return [
            'period_month' => $this->faker->numberBetween(1, 12),
            'period_year' => 2026,
            'status' => 'draft',
            'processed_at' => null,
        ];
    }
}
