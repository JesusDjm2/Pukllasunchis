<?php

namespace Database\Factories\CursosEspeciales;

use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeUnidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursosEspeciales\CeEjercicio>
 */
class CeEjercicioFactory extends Factory
{
    protected $model = CeEjercicio::class;

    public function definition(): array
    {
        return [
            'ce_unidad_id' => CeUnidad::factory(),
            'tipo' => 'multiple',
            'pregunta' => fake()->sentence().'?',
            'opciones' => ['Rojo', 'Verde', 'Azul'],
            'respuesta_correcta' => 'Verde',
            'puntaje_max' => 10,
            'orden' => fake()->numberBetween(0, 5),
        ];
    }
}
