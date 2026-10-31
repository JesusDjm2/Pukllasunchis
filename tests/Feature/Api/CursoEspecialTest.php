<?php

namespace Tests\Feature\Api;

use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeInscripcion;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CeUnidad;
use App\Models\CursosEspeciales\CursoEspecial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoEspecialTest extends TestCase
{
    use RefreshDatabase;

    private function estructuraCompleta(): array
    {
        $curso = CursoEspecial::factory()->create(['activo' => true]);
        $nivel = CeNivel::factory()->create(['curso_especial_id' => $curso->id]);
        $unidad = CeUnidad::factory()->create(['ce_nivel_id' => $nivel->id]);
        $leccion = CeLeccion::factory()->create(['ce_unidad_id' => $unidad->id]);
        $ejercicio = CeEjercicio::factory()->create(['ce_unidad_id' => $unidad->id]);

        return compact('curso', 'nivel', 'unidad', 'leccion', 'ejercicio');
    }

    public function test_lists_active_cursos_with_enrollment_status(): void
    {
        ['curso' => $curso] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);

        $token = $user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cursos-especiales');

        $response->assertOk()
            ->assertJsonPath('data.0.inscrito', true)
            ->assertJsonPath('data.0.porcentaje', 0);
    }

    public function test_alumno_can_enroll_in_curso(): void
    {
        ['curso' => $curso] = $this->estructuraCompleta();
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cursos-especiales/{$curso->id}/inscribir");

        $response->assertOk();
        $this->assertDatabaseHas('ce_inscripciones', ['user_id' => $user->id, 'curso_especial_id' => $curso->id]);
    }

    public function test_show_requires_enrollment(): void
    {
        ['curso' => $curso] = $this->estructuraCompleta();
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/cursos-especiales/{$curso->id}");

        $response->assertStatus(403);
    }

    public function test_enrolled_alumno_can_see_full_tree_without_leaking_correct_answer(): void
    {
        ['curso' => $curso, 'leccion' => $leccion, 'ejercicio' => $ejercicio] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/cursos-especiales/{$curso->id}");

        $response->assertOk()
            ->assertJsonPath('data.niveles.0.unidades.0.lecciones.0.id', $leccion->id)
            ->assertJsonPath('data.niveles.0.unidades.0.ejercicios.0.id', $ejercicio->id)
            ->assertJsonMissingPath('data.niveles.0.unidades.0.ejercicios.0.respuesta_correcta');
    }

    public function test_alumno_can_complete_leccion(): void
    {
        ['curso' => $curso, 'leccion' => $leccion] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/lecciones/{$leccion->id}/completar");

        $response->assertOk();
        $this->assertDatabaseHas('ce_progreso_alumno', [
            'user_id' => $user->id,
            'ce_leccion_id' => $leccion->id,
            'completado' => true,
        ]);
    }

    public function test_alumno_can_answer_ejercicio_correctly(): void
    {
        ['curso' => $curso, 'ejercicio' => $ejercicio] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/ejercicios/{$ejercicio->id}/responder", ['respuesta' => 'Verde']);

        $response->assertOk()
            ->assertJsonPath('data.correcta', true)
            ->assertJsonPath('data.puntaje', 10);
    }

    public function test_alumno_can_answer_ejercicio_incorrectly(): void
    {
        ['curso' => $curso, 'ejercicio' => $ejercicio] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/ejercicios/{$ejercicio->id}/responder", ['respuesta' => 'Rojo']);

        $response->assertOk()
            ->assertJsonPath('data.correcta', false)
            ->assertJsonPath('data.puntaje', 0);
    }

    public function test_progreso_endpoint_reflects_completed_items(): void
    {
        ['curso' => $curso, 'leccion' => $leccion] = $this->estructuraCompleta();
        $user = User::factory()->create();
        CeInscripcion::create(['user_id' => $user->id, 'curso_especial_id' => $curso->id, 'inscrito_at' => now()]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/lecciones/{$leccion->id}/completar");

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/cursos-especiales/{$curso->id}/progreso");

        $response->assertOk()
            ->assertJsonPath('data.lecciones_completadas', 1)
            ->assertJsonPath('data.total_lecciones', 1);
    }

    public function test_unauthenticated_user_cannot_access_cursos_especiales(): void
    {
        $this->getJson('/api/v1/cursos-especiales')->assertStatus(401);
    }
}
