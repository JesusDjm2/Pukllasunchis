<?php

namespace Tests\Feature\Api;

use App\Models\Comunicado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComunicadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_comunicados_most_recent_first(): void
    {
        Comunicado::factory()->create(['titulo' => 'Antiguo', 'fecha_publicacion' => '2026-01-01']);
        Comunicado::factory()->create(['titulo' => 'Reciente', 'fecha_publicacion' => '2026-06-01']);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/comunicados');

        $response->assertOk()
            ->assertJsonPath('data.0.titulo', 'Reciente')
            ->assertJsonPath('data.1.titulo', 'Antiguo')
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total']]);
    }

    public function test_unauthenticated_user_cannot_list_comunicados(): void
    {
        $response = $this->getJson('/api/v1/comunicados');

        $response->assertStatus(401);
    }
}
