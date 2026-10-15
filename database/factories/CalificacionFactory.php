<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calificacion>
 */
class CalificacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'curso_id' => Curso::factory(),
            'valoracion_1' => 'A',
            'valoracion_2' => 'A',
            'valoracion_3' => 'A',
            'valoracion_curso' => 'A',
            'calificacion_curso' => '18',
            'calificacion_sistema' => '18',
        ];
    }
}
