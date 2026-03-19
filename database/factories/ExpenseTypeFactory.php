<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseType>
 */
class ExpenseTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expense_type_name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
