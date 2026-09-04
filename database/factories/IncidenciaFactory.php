<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Docente;
use App\Models\Programa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incidencia>
 */
class IncidenciaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'docente_id' => Docente::factory(),
            'alumno_id' => Alumno::factory(),
            'programa_id' => Programa::factory(),
            'ciclo_id' => Ciclo::factory(),
            'fecha' => fake()->date(),
            'reporte' => fake()->paragraph(),
        ];
    }
}
