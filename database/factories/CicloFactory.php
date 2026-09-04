<?php

namespace Database\Factories;

use App\Models\Programa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ciclo>
 */
class CicloFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['I', 'II', 'III', 'IV', 'V', 'VI']),
            'programa_id' => Programa::factory(),
        ];
    }
}
