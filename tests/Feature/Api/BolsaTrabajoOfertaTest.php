<?php

namespace Tests\Feature\Api;

use App\Models\BolsaTrabajoOferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BolsaTrabajoOfertaTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_ofertas_publicly_without_auth(): void
    {
        BolsaTrabajoOferta::factory()->create(['nombre' => 'Antigua', 'fecha_inicio' => '2026-01-01']);
        BolsaTrabajoOferta::factory()->create(['nombre' => 'Reciente', 'fecha_inicio' => '2026-06-01']);

        $response = $this->getJson('/api/v1/bolsa-trabajo');

        $response->assertOk()
            ->assertJsonPath('data.0.nombre', 'Reciente')
            ->assertJsonPath('data.1.nombre', 'Antigua');
    }

    public function test_filters_by_anio_and_mes(): void
    {
        BolsaTrabajoOferta::factory()->create(['nombre' => 'Enero 2026', 'fecha_inicio' => '2026-01-15']);
        BolsaTrabajoOferta::factory()->create(['nombre' => 'Junio 2026', 'fecha_inicio' => '2026-06-15']);

        $response = $this->getJson('/api/v1/bolsa-trabajo?anio=2026&mes=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Enero 2026');
    }
}
