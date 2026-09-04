<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoPerfilTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(?Alumno $alumno = null): User
    {
        $user = User::factory()->create();
        $alumno = $alumno ?? Alumno::factory()->create(['user_id' => $user->id]);
        $alumno->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_alumno_can_fetch_own_profile(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/alumno/perfil');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->alumno->id)
            ->assertJsonPath('data.email', $user->alumno->email);
    }

    public function test_user_without_alumno_gets_404_on_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/alumno/perfil');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_fetch_profile(): void
    {
        $response = $this->getJson('/api/v1/alumno/perfil');

        $response->assertStatus(401);
    }

    public function test_alumno_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create([
            'user_id' => $user->id,
            'fecha_nacimiento' => '1999-01-01',
            'genero' => 'Femenino',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $payload = [
            'numero' => '999888777',
            'numero_referencia' => '999111222',
            'direccion' => 'Nueva dirección 123',
            'estado_civil' => 'Casado',
            'p_m_soltero' => false,
            'num_hijos' => 2,
            'sector_socioeconomico' => 'Alto',
            'trabajas' => 'Si',
            'egreso' => '500',
            'hrs_laboradas_sem' => 20,
            'ayuda_economica' => false,
            'tiempo_ayuda' => 'N/A',
            'tipo_apoyo_formacion' => 'N/A',
            'convivientes' => 'Pareja',
            'quien_mantiene' => 'Yo mismo',
            'cant_dependientes_child' => '1',
            'cant_dependientes_old' => '0',
            'cant_dependientes_otros' => '0',
            'tipo_vivienda' => 'Alquilada',
            'situacion_vivienda' => 'Alquilada',
            'dormitorios_vivienda' => 1,
            'banos_vivienda' => 1,
            'material_vivienda' => 'Material noble',
            'hrs_disponibles_agua' => 24,
            'hrs_disponibles_desague' => 24,
            'hrs_disponibles_luz' => 24,
            'tipo_seguro' => 'EsSalud',
            'bienes_vivienda' => ['Televisor', 'Refrigeradora'],
            'estudio_beca' => 'No',
            'permanencia_vivienda' => 'Propia',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $payload);

        $response->assertOk()
            ->assertJsonPath('data.direccion', 'Nueva dirección 123')
            ->assertJsonPath('data.num_hijos', 2);

        $this->assertSame('Nueva dirección 123', $alumno->fresh()->direccion);
    }

    public function test_update_profile_requires_valid_data(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', []);

        $response->assertStatus(422);
    }
}
