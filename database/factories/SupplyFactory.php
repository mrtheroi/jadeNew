<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supply>
 */
class SupplyFactory extends Factory
{
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'payment_type' => $this->faker->randomElement(['efectivo', 'transferencia', 'tarjeta_credito', 'tarjeta_debito', 'cheque', 'otro']),
            'payment_date' => $date,
            'payment_month' => $date->format('Y-m'),
            'status' => $this->faker->randomElement(['pendiente', 'pagado', 'cancelado']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
