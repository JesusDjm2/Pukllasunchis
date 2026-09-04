<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'dni' => fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
