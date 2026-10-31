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
        ];
    }
}
