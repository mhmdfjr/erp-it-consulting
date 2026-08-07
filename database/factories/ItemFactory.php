<?php

namespace Database\Factories;

use App\Modules\SalesInventory\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-####'),
            'name' => fake()->words(3, true),
            'item_type' => 'physical_good',
            'unit_of_measure' => 'pcs',
            'unit_price' => fake()->randomFloat(2, 10000, 5000000),
            'cost_price' => fake()->randomFloat(2, 5000, 3000000),
            'is_active' => true,
        ];
    }
}
