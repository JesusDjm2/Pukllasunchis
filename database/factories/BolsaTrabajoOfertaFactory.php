<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BolsaTrabajoOferta>
 */
class BolsaTrabajoOfertaFactory extends Factory
{
    public function definition(): array
    {
        $inicio = fake()->date();

        return [
            'nombre' => fake()->jobTitle(),
            'detalles' => fake()->paragraph(),
            'fecha_inicio' => $inicio,
            'fecha_fin' => fake()->dateTimeBetween($inicio, '+2 months')->format('Y-m-d'),
        ];
    }
}
