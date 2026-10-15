<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodoTresFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Promedio',
            'valoracion_curso' => $this->faker->randomElement(['A', 'B', 'C']),
            'calificacion_curso' => (string) $this->faker->numberBetween(11, 20),
            'calificacion_sistema' => (string) $this->faker->numberBetween(11, 20),
            'observaciones' => null,
            'alumno_id' => Alumno::factory(),
            'curso_id' => Curso::factory(),
        ];
    }
}
