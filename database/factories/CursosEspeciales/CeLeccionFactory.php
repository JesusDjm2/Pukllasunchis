<?php

namespace Database\Factories\CursosEspeciales;

use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursosEspeciales\CeLeccion>
 */
class CeLeccionFactory extends Factory
{
    protected $model = CeLeccion::class;

    public function definition(): array
    {
        return [
            'ce_unidad_id' => CeUnidad::factory(),
            'nombre' => fake()->unique()->sentence(3),
            'tipo' => 'texto',
            'contenido_texto' => fake()->paragraph(),
            'duracion_min' => fake()->numberBetween(2, 15),
            'orden' => fake()->numberBetween(0, 5),
        ];
    }
}
