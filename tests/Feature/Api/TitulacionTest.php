<?php

namespace Tests\Feature\Api;

use App\Models\Titulacion\Pti;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TitulacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/alumno/titulacion')->assertStatus(401);
    }

    public function test_alumno_activo_sin_ciclo_de_titulacion_no_la_tiene_disponible(): void
    {
        Sanctum::actingAs(User::factory()->create(['condicion' => 'Regular']));

        $this->getJson('/api/v1/alumno/titulacion')
            ->assertOk()
            ->assertJsonPath('data.es_egresado', false)
            ->assertJsonPath('data.disponible', false)
            ->assertJsonPath('data.tiene_proceso', false)
            ->assertJsonPath('data.etapas', []);
    }

    public function test_egresado_sin_proceso_ve_la_guia_y_su_primer_paso(): void
    {
        Sanctum::actingAs(User::factory()->create(['condicion' => 'Egresado']));

        $this->getJson('/api/v1/alumno/titulacion')
            ->assertOk()
            ->assertJsonPath('data.es_egresado', true)
            ->assertJsonPath('data.disponible', true)
            ->assertJsonPath('data.puede_iniciar', true)
            ->assertJsonPath('data.estado', null)
            ->assertJsonCount(3, 'data.etapas')
            ->assertJsonPath('data.etapas.0.clave', 'pti')
            ->assertJsonPath('data.solicitud_pti.estado', null)
            ->assertJsonPath('data.solicitud_pti.boton', 'Solicitar aprobación del PTI')
            ->assertJsonPath('data.solicitud_pti.url', route('mi-titulacion.solicitud-pti'))
            ->assertJsonPath('data.solicitud_pti.formulario_app', 'solicitud-pti')
            ->assertJsonPath('data.tramites.items.0.formulario.formulario_app', 'solicitud-pti')
            ->assertJsonPath('data.tramites.clave', 'pti')
            ->assertJsonPath('data.tramites.items.0.clave', 'pti_aprobacion');
    }

    public function test_egresado_con_pti_aprobado_ve_su_avance_y_la_carta(): void
    {
        $user = User::factory()->create(['condicion' => 'Egresado']);
        $seguimiento = SeguimientoTitulacion::create([
            'user_id' => $user->id,
            'estado_titulacion_id' => DB::table('estados_titulacion')->value('id'),
        ]);
        Pti::create([
            'seguimiento_id' => $seguimiento->id,
            'titulo' => 'Mi investigación',
            'fecha_pti' => now()->subMonths(3)->toDateString(),
            'rd_pti' => 'RD-001-2026',
            'estado' => 'aprobado',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alumno/titulacion')
            ->assertOk()
            ->assertJsonPath('data.tiene_proceso', true)
            ->assertJsonPath('data.puede_iniciar', false)
            ->assertJsonPath('data.estado.clave', 'pti_aprobado')
            ->assertJsonPath('data.siguiente_paso.clave', 'pti_carta')
            ->assertJsonPath('data.etapas.0.hitos.0.situacion', 'logrado')
            ->assertJsonPath('data.vigencia_pti.vencido', false)
            ->assertJsonPath('data.carta.estado', null)
            ->assertJsonPath('data.carta.url', route('mi-titulacion.carta'));
    }

    public function test_me_informa_si_es_egresado(): void
    {
        Sanctum::actingAs(User::factory()->create(['condicion' => 'Egresado']));

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.condicion', 'Egresado')
            ->assertJsonPath('data.es_egresado', true);
    }

    public function test_guia_pdf_de_investigaciones(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->get('/api/v1/taqe/guia-pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
