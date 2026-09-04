<?php

namespace Database\Factories\CursosEspeciales;

use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CeUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursosEspeciales\CeUnidad>
 */
class CeUnidadFactory extends Factory
{
    protected $model = CeUnidad::class;

    public function definition(): array
    {
        return [
            'ce_nivel_id' => CeNivel::factory(),
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'orden' => fake()->numberBetween(0, 5),
        ];
    }
}
