<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->forget('periodo_actual_fid');
    }

    public function test_alumno_can_list_cursos_of_active_period(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $periodo = PeriodoActual::factory()->actual()->create();
        $curso = Curso::factory()->create(['nombre' => 'Matemática']);
        $otroCurso = Curso::factory()->create(['nombre' => 'Sin período']);

        $alumno->cursosDelPeriodo($periodo->id)->attach($curso->id, ['periodo_actual_id' => $periodo->id]);
        $alumno->cursos()->attach($otroCurso->id, ['periodo_actual_id' => null]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cursos');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Matemática');
    }

    public function test_returns_empty_list_when_no_active_period(): void
    {
        $user = User::factory()->create();
        Alumno::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cursos');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_alumno_can_list_own_calificaciones(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $curso = Curso::factory()->create(['nombre' => 'Comunicación']);
        Calificacion::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'calificacion_curso' => '17',
        ]);

        $otroAlumno = Alumno::factory()->create();
        Calificacion::factory()->create(['alumno_id' => $otroAlumno->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/calificaciones');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.curso_nombre', 'Comunicación')
            ->assertJsonPath('data.0.calificacion_curso', '17');
    }

    public function test_unauthenticated_user_cannot_list_cursos_or_calificaciones(): void
    {
        $this->getJson('/api/v1/cursos')->assertStatus(401);
        $this->getJson('/api/v1/calificaciones')->assertStatus(401);
    }
}
