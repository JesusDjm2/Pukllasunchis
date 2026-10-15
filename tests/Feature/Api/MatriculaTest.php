<?php

namespace Tests\Feature\Api;

use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatriculaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_can_list_own_matriculas(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $periodo = PeriodoActual::factory()->create(['nombre' => 'Ciclo 2026-II']);
        Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'periodo_actual_id' => $periodo->id,
            'estado' => 'matriculado',
        ]);

        $otroAlumno = Alumno::factory()->create();
        Matricula::factory()->create(['alumno_id' => $otroAlumno->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/matriculas');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.periodo_nombre', 'Ciclo 2026-II')
            ->assertJsonPath('data.0.estado', 'matriculado');
    }

    public function test_user_without_alumno_gets_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/matriculas');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_list_matriculas(): void
    {
        $response = $this->getJson('/api/v1/matriculas');

        $response->assertStatus(401);
    }
}
