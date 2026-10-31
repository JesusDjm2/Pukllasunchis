<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BolsaTrabajoOferta>
 */
class BolsaTrabajoOfertaFactory extends Factory
{
    public function definition(): array
    {
        $fechaPublicacion = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'nombre' => fake()->jobTitle(),
            'detalles' => fake()->paragraph(),
            'fecha_publicacion' => $fechaPublicacion->format('Y-m-d'),
            'nombre_publicador' => fake()->name(),
            'telefono_publicador' => fake()->phoneNumber(),
            'relacion_publicador' => fake()->randomElement(['Egresado', 'Docente', 'Externo']),
            'numero_correo' => fake()->numerify('###'),
            'anio' => (int) $fechaPublicacion->format('Y'),
            'mes' => (int) $fechaPublicacion->format('n'),
        ];
    }
}
