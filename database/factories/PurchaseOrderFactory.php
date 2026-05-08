<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        $unit = $this->faker->randomElement(['Jade', 'Fuego Ambar', 'KIN']);
        $unitSlug = str_replace(' ', '', $unit);
        $date = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'oc_number' => sprintf('OC-%s-%s-%d', $unitSlug, $date->format('Y-m-d'), $this->faker->unique()->numberBetween(1, 9999)),
            'oc_date' => $date,
            'business_unit' => $unit,
            'total_amount' => $this->faker->randomFloat(2, 500, 50000),
            'total_items' => $this->faker->numberBetween(1, 15),
            'notes' => $this->faker->optional()->sentence(),
            'status' => PurchaseOrder::STATUS_CLOSED,
            'created_by' => User::factory(),
            'closed_at' => $date,
        ];
    }

    public function cancelled(): self
    {
        return $this->state(fn () => [
            'status' => PurchaseOrder::STATUS_CANCELLED,
        ]);
    }

    public function forUnit(string $unit): self
    {
        return $this->state(fn () => ['business_unit' => $unit]);
    }
}
