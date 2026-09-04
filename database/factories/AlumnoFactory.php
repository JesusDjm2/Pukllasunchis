<?php

namespace Database\Factories;

use App\Models\Ciclo;
use App\Models\Programa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alumno>
 */
class AlumnoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'dni' => fake()->unique()->numerify('########'),
            'apellidos' => fake()->lastName(),
            'nombres' => fake()->firstName(),
            'genero' => fake()->randomElement(['Masculino', 'Femenino']),
            'numero' => fake()->numerify('9########'),
            'fecha_nacimiento' => fake()->date(),
            'numero_referencia' => fake()->numerify('9########'),
            'procedencia_familiar' => 'Urbano',
            'direccion' => fake()->address(),
            'te_consideras' => 'Mestizo',
            'lengua_1' => 'Español',
            'lengua_2' => 'Quechua',
            'estado_civil' => 'Soltero',
            'p_m_soltero' => false,
            'num_hijos' => 0,
            'sector_socioeconomico' => 'Medio',
            'num_comprobante' => fake()->numerify('########'),
            'convivientes' => 'Padres',
            'quien_mantiene' => 'Padres',
            'cant_dependientes_child' => '0',
            'cant_dependientes_old' => '0',
            'cant_dependientes_otros' => '0',
            'estudio_beca' => 'No',
            'tipo_preparacion' => 'Autodidacta',
            'motivo_estudio_eesp' => 'Vocación',
            'motivo_docencia' => 'Vocación',
            'motivo_especialidad' => 'Interés personal',
            'internet' => true,
            'servicio_internet' => 'Fibra',
            'dispositivo_internet' => 'Celular',
            'propio_compartido' => true,
            'correo' => true,
            'num_hrs_estudio' => 10,
            'forma_estudio' => 'Individual',
            'trabajas' => 'No',
            'egreso' => 'Ninguno',
            'hrs_laboradas_sem' => 0,
            'ayuda_economica' => false,
            'tiempo_ayuda' => 'N/A',
            'tipo_apoyo_formacion' => 'N/A',
            'tipo_vivienda' => 'Propia',
            'situacion_vivienda' => 'Propia',
            'dormitorios_vivienda' => '2',
            'banos_vivienda' => '1',
            'material_vivienda' => 'Material noble',
            'hrs_disponibles_agua' => '24',
            'hrs_disponibles_desague' => '24',
            'hrs_disponibles_luz' => '24',
            'problemas_salud' => false,
            'tipo_seguro' => 'SIS',
            'familiar_salud' => false,
            'frecuencia_lectura' => 'Semanal',
            'acceso_lectura' => 'Biblioteca',
            'visitas_museos' => 'Rara vez',
            'actividades_internet' => 'Estudio',
            'tiempo_libre' => true,
            'programa_id' => Programa::factory(),
            'ciclo_id' => Ciclo::factory(),
        ];
    }
}
