<?php

namespace Database\Factories;

use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'title' => $this->faker->jobTitle(),
        ];
    }
}
