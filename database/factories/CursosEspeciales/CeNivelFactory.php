<?php

namespace Database\Factories\CursosEspeciales;

use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursosEspeciales\CeNivel>
 */
class CeNivelFactory extends Factory
{
    protected $model = CeNivel::class;

    public function definition(): array
    {
        return [
            'curso_especial_id' => CursoEspecial::factory(),
            'nombre' => 'Nivel '.fake()->numberBetween(1, 5),
            'orden' => fake()->numberBetween(0, 5),
        ];
    }
}
