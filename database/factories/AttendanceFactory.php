<?php

namespace Database\Factories;

use App\Modules\HR\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = \App\Modules\HR\Models\Attendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'status' => 'present',
            'note' => null,
        ];
    }

    public function absent(): static
    {
        return $this->state(fn () => [
            'status' => 'absent',
            'check_in' => null,
            'check_out' => null,
        ]);
    }

    public function leave(): static
    {
        return $this->state(fn () => [
            'status' => 'leave',
            'check_in' => null,
            'check_out' => null,
        ]);
    }

    public function sick(): static
    {
        return $this->state(fn () => [
            'status' => 'sick',
            'check_in' => null,
            'check_out' => null,
        ]);
    }
}
