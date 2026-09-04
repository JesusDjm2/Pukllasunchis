<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comunicado>
 */
class ComunicadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'archivo' => 'docs/comunicados/'.fake()->uuid().'.jpg',
            'archivo_tipo' => 'imagen',
            'fecha_publicacion' => fake()->date(),
        ];
    }
}
