<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    private function payloadBase(): array
    {
        return [
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

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $this->payloadBase());

        $response->assertOk()
            ->assertJsonPath('data.direccion', 'Nueva dirección 123')
            ->assertJsonPath('data.num_hijos', 2)
            ->assertJsonPath('matricula_completada', false);

        $this->assertSame('Nueva dirección 123', $alumno->fresh()->direccion);
    }

    public function test_numero_igual_a_numero_referencia_falla_validacion(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('test')->plainTextToken;

        $payload = $this->payloadBase();
        $payload['numero'] = '999888777';
        $payload['numero_referencia'] = '999888777';

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('numero');
    }

    public function test_formulario_habilitado_sin_becado_requiere_comprobante(): void
    {
        $user = $this->actingUser();
        PeriodoActual::factory()->create(['actual' => true, 'formulario_habilitado' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $this->payloadBase());

        $response->assertStatus(422)->assertJsonValidationErrors('comprobante');
    }

    public function test_formulario_habilitado_con_comprobante_completa_matricula_y_notifica(): void
    {
        Mail::fake();

        $user = $this->actingUser();
        $alumno = $user->alumno;
        $periodoActual = PeriodoActual::factory()->create(['actual' => true, 'formulario_habilitado' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $payload = $this->payloadBase();
        $payload['comprobante'] = '2026-00123';

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $payload);

        $response->assertOk()->assertJsonPath('matricula_completada', true);

        $matricula = Matricula::where('alumno_id', $alumno->id)
            ->where('periodo_actual_id', $periodoActual->id)
            ->first();

        $this->assertNotNull($matricula);
        $this->assertSame('matriculado', $matricula->estado);
        $this->assertSame('2026-00123', $matricula->comprobante);
        Mail::assertSent(\App\Mail\NotificacionRegistro::class);
    }

    public function test_becado_no_requiere_comprobante_para_completar_matricula(): void
    {
        $user = $this->actingUser();
        $user->update(['beca' => true]);
        $alumno = $user->alumno;
        $periodoActual = PeriodoActual::factory()->create(['actual' => true, 'formulario_habilitado' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $this->payloadBase());

        $response->assertOk()->assertJsonPath('matricula_completada', true);

        $matricula = Matricula::where('alumno_id', $alumno->id)
            ->where('periodo_actual_id', $periodoActual->id)
            ->first();

        $this->assertNotNull($matricula);
        $this->assertNull($matricula->comprobante);
    }

    public function test_renovar_matricula_ya_existente_no_reenvia_correo(): void
    {
        Mail::fake();

        $user = $this->actingUser();
        $alumno = $user->alumno;
        $periodoActual = PeriodoActual::factory()->create(['actual' => true, 'formulario_habilitado' => true]);
        Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'periodo_actual_id' => $periodoActual->id,
            'estado' => 'matriculado',
            'comprobante' => 'viejo-123',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $payload = $this->payloadBase();
        $payload['comprobante'] = 'nuevo-456';

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', $payload);

        $response->assertOk()->assertJsonPath('matricula_completada', true);
        Mail::assertNothingSent();
    }

    public function test_update_profile_requires_valid_data(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/alumno/perfil', []);

        $response->assertStatus(422);
    }

    public function test_alumno_can_download_own_ficha_pdf(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->get('/api/v1/alumno/ficha-pdf');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_user_without_alumno_gets_404_on_ficha_pdf(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->get('/api/v1/alumno/ficha-pdf');

        $response->assertStatus(404);
    }
}
