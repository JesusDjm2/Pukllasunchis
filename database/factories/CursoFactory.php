<?php

namespace Database\Factories;

use App\Models\Ciclo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'sumilla' => fake()->sentence(),
            'cc' => fake()->bothify('CC-###'),
            'horas' => '4',
            'creditos' => '3',
            'ciclo_id' => Ciclo::factory(),
        ];
    }
}
