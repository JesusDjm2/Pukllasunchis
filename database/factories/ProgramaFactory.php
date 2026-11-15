<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Programa>
 */
class ProgramaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            // NOT NULL desde 2026_09_29_000000_add_modalidad_nivel_eib_to_programas_table.
            'modalidad' => 'FID',
        ];
    }
}
