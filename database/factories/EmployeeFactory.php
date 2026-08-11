<?php

namespace Database\Factories;

use App\Modules\HR\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = \App\Modules\HR\Models\Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'employee_code' => 'EMP-'.$this->faker->unique()->numerify('######'),
            'full_name' => $this->faker->name(),
            'nik' => $this->faker->numerify('################'),
            'npwp' => $this->faker->numerify('##.###.###.#-###.###'),
            // asumsi konvensi L/P (Laki-laki/Perempuan), belum eksplisit
            // ditetapkan di DATABASE.md — sesuaikan kalau formatnya beda
            'gender' => $this->faker->randomElement(['L', 'P']),
            'birth_date' => $this->faker->dateTimeBetween('-55 years', '-20 years'),
            'ptkp_status' => $this->faker->randomElement([
                'TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3',
            ]),
            'position_id' => Position::factory(),
            'base_salary' => $this->faker->randomElement([
                5000000, 7500000, 10000000, 15000000, 25000000,
            ]),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'termination_date' => null,
            'employment_status' => 'active',
            'bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'bank_account_number' => $this->faker->numerify('##########'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    public function resigned(): static
    {
        return $this->state(fn () => [
            'employment_status' => 'resigned',
            'termination_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
