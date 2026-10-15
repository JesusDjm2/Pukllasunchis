<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PeriodoActual>
 */
class PeriodoActualFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(2, true),
            'horario' => 'Mañana',
            'fecha_inicio' => fake()->date(),
            'fecha_cierre' => fake()->date(),
            'actual' => false,
            'formulario_habilitado' => false,
        ];
    }

    public function actual(): static
    {
        return $this->state(fn (array $attributes) => ['actual' => true]);
    }
}
