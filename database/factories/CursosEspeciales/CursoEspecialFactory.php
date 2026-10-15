<?php

namespace Database\Factories\CursosEspeciales;

use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursosEspeciales\CursoEspecial>
 */
class CursoEspecialFactory extends Factory
{
    protected $model = CursoEspecial::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'descripcion' => fake()->sentence(),
            'activo' => true,
            'orden' => fake()->numberBetween(0, 10),
        ];
    }
}
