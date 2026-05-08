<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => 'EMP-'.$this->faker->unique()->numberBetween(1000, 9999),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->optional()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'curp' => strtoupper($this->faker->bothify('????######??????##')),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-20 years'),
            'birth_place' => $this->faker->city(),
            'gender' => $this->faker->randomElement(['Masculino', 'Femenino', 'Otro']),
            'marital_status' => $this->faker->randomElement(['Soltero', 'Casado', 'Union libre', 'Divorciado', 'Viudo']),
            'nationality' => 'Mexicana',
            'children_count' => $this->faker->numberBetween(0, 4),
            'address' => $this->faker->optional()->address(),
            'business_unit' => $this->faker->randomElement(['Jade', 'Fuego Ambar', 'KIN']),
            'department' => $this->faker->randomElement(['Cocina', 'Servicio', 'Caja', 'Administración', 'Limpieza']),
            'manager_name' => $this->faker->optional()->name(),
            'hired_at' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'is_active' => true,
            'terminated_at' => null,
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'emergency_contact_relationship' => $this->faker->randomElement(['Padre', 'Madre', 'Hermano(a)', 'Conyuge', 'Hijo(a)', 'Otro']),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
            'terminated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function forUnit(string $unit): self
    {
        return $this->state(fn () => ['business_unit' => $unit]);
    }
}
