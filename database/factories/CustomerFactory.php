<?php

namespace Database\Factories;

use App\Modules\SalesInventory\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'customer_type' => fake()->randomElement(['individual', 'corporate']),
        ];
    }
}
