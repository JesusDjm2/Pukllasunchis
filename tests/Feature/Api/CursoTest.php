<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Periodo;
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

    public function test_alumno_without_explicit_assignment_falls_back_to_ciclo_cursos(): void
    {
        // Misma lógica que AlumnoController@index (web): la mayoría de
        // alumnos no tiene filas en alumno_cursos para el período actual —
        // ven los cursos de su ciclo directamente.
        $user = User::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id, 'ciclo_id' => $ciclo->id]);
        PeriodoActual::factory()->actual()->create();
        Curso::factory()->create(['nombre' => 'Álgebra', 'ciclo_id' => $ciclo->id]);
        Curso::factory()->create(['nombre' => 'Historia', 'ciclo_id' => $ciclo->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cursos');

        $response->assertOk()->assertJsonCount(2, 'data');
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

    public function test_alumno_can_list_own_calificaciones_grouped_by_periodo(): void
    {
        // Fuente real: modelo Periodo (tabla `periodos`), no Calificacion
        // (`calificacions` está vacía en producción — ver AlumnoController@calificaciones
        // en la web, que agrupa por periodoActual->nombre).
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $curso = Curso::factory()->create(['nombre' => 'Comunicación']);
        $periodo2024 = PeriodoActual::factory()->create(['nombre' => '2024-I']);
        $periodo2025 = PeriodoActual::factory()->create(['nombre' => '2025-I']);

        Periodo::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'periodo_actual_id' => $periodo2024->id,
            'calificacion_curso' => '15',
        ]);
        Periodo::factory()->create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'periodo_actual_id' => $periodo2025->id,
            'calificacion_curso' => '17',
        ]);

        $otroAlumno = Alumno::factory()->create();
        Periodo::factory()->create(['alumno_id' => $otroAlumno->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/calificaciones');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.2024-I.0.curso_nombre', 'Comunicación')
            ->assertJsonPath('data.2024-I.0.calificacion_curso', '15')
            ->assertJsonPath('data.2025-I.0.calificacion_curso', '17');
    }

    public function test_unauthenticated_user_cannot_list_cursos_or_calificaciones(): void
    {
        $this->getJson('/api/v1/cursos')->assertStatus(401);
        $this->getJson('/api/v1/calificaciones')->assertStatus(401);
    }
}
