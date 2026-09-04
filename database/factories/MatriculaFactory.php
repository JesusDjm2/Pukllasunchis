<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\PeriodoActual;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Matricula>
 */
class MatriculaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'periodo_actual_id' => PeriodoActual::factory(),
            'estado' => 'matriculado',
            'comprobante' => null,
            'fecha_completado' => now(),
        ];
    }
}
