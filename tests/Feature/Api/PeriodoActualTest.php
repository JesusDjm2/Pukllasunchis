<?php

namespace Tests\Feature\Api;

use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodoActualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->forget('periodo_actual_fid');
    }

    public function test_returns_the_active_period(): void
    {
        PeriodoActual::factory()->create(['actual' => false]);
        $activo = PeriodoActual::factory()->actual()->create(['nombre' => 'Ciclo 2026-II']);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/periodo-actual');

        $response->assertOk()
            ->assertJsonPath('data.id', $activo->id)
            ->assertJsonPath('data.nombre', 'Ciclo 2026-II');
    }

    public function test_returns_404_when_no_active_period(): void
    {
        PeriodoActual::factory()->create(['actual' => false]);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/periodo-actual');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_fetch_periodo_actual(): void
    {
        $response = $this->getJson('/api/v1/periodo-actual');

        $response->assertStatus(401);
    }
}
