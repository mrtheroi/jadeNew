<?php

namespace Database\Factories;

use App\Domain\BusinessUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailySale>
 */
class DailySaleFactory extends Factory
{
    public function definition(): array
    {
        $alimentos = fake()->randomFloat(2, 500, 50000);
        $bebidas = fake()->randomFloat(2, 200, 20000);
        $otros = fake()->randomFloat(2, 0, 5000);
        $subtotal = $alimentos + $bebidas + $otros;
        $iva = round($subtotal * 0.16, 2);
        $total = $subtotal + $iva;

        $efectivoMonto = round($total * 0.3, 2);
        $debitoMonto = round($total * 0.3, 2);
        $creditoMonto = round($total * 0.3, 2);
        $creditoClienteMonto = round($total - $efectivoMonto - $debitoMonto - $creditoMonto, 2);

        return [
            'business_unit' => fake()->randomElement(BusinessUnit::values()),
            'operation_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'turno' => fake()->randomElement([1, 2]),
            'status' => 'completed',
            'alimentos' => $alimentos,
            'bebidas' => $bebidas,
            'otros' => $otros,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'efectivo_monto' => $efectivoMonto,
            'efectivo_propina' => fake()->randomFloat(2, 0, 500),
            'debito_monto' => $debitoMonto,
            'debito_propina' => fake()->randomFloat(2, 0, 500),
            'credito_monto' => $creditoMonto,
            'credito_propina' => fake()->randomFloat(2, 0, 500),
            'credito_cliente_monto' => $creditoClienteMonto,
            'credito_cliente_propina' => 0,
            'numero_personas' => fake()->numberBetween(50, 300),
            'numero_cuentas' => fake()->numberBetween(100, 700),
            'promedio_por_persona' => fake()->randomFloat(2, 100, 400),
            'cantidad_productos' => fake()->numberBetween(50, 200),
            'user_id' => User::factory(),
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'alimentos' => 0,
            'bebidas' => 0,
            'otros' => 0,
            'subtotal' => 0,
            'iva' => 0,
            'total' => 0,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'LlamaIndex extraction failed.',
        ]);
    }
}
