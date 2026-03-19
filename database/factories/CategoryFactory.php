<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_unit' => $this->faker->randomElement(['Jade', 'Fuego Ambar', 'KIN']),
            'expense_type_id' => ExpenseType::factory(),
            'expense_name' => $this->faker->word(),
            'provider_name' => $this->faker->company(),
            'is_active' => true,
        ];
    }
}
