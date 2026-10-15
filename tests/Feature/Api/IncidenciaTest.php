<?php

namespace Tests\Feature\Api;

use App\Models\Docente;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_can_list_own_incidencias(): void
    {
        $user = User::factory()->create();
        $docente = Docente::factory()->create(['user_id' => $user->id]);
        Incidencia::factory()->create(['docente_id' => $docente->id, 'reporte' => 'Reporte propio']);

        $otroDocente = Docente::factory()->create();
        Incidencia::factory()->create(['docente_id' => $otroDocente->id, 'reporte' => 'Reporte ajeno']);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/incidencias');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reporte', 'Reporte propio');
    }

    public function test_user_without_docente_gets_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/incidencias');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_list_incidencias(): void
    {
        $response = $this->getJson('/api/v1/incidencias');

        $response->assertStatus(401);
    }
}
