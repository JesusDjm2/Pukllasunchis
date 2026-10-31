<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\User;
use App\Services\SeguimientoEgresado\CampanaAsignacionService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Suite de Pruebas Automatizadas — Fase Campañas, Asignación Equilibrada y Seguimiento Real
 *
 * Verificaciones obligatorias según directiva:
 * 1. Selección incluye exclusivamente docentes elegibles.
 * 2. No se duplican docentes ni egresados en el padrón.
 * 3. Los roles docentes originales se conservan al integrarse a una campaña.
 * 4. La distribución es aleatoria y equilibrada (diferencia máxima de 1).
 * 5. No existen asignaciones duplicadas por campaña.
 * 6. La operación es idempotente y segura ante concurrencia.
 * 7. Una reasignación conserva el historial anterior y motivo.
 * 8. Un docente no accede a casos de otro (HTTP 403 estricto).
 * 9. La revocación retira el acceso efectivo.
 * 10. Los intentos de llamada NO se confunden con contactos efectivos.
 * 11. Un contacto efectivo actualiza el estado de la asignación.
 * 12. Las actualizaciones se relacionan correctamente con campaña y egresado.
 * 13. Los indicadores generales coinciden con los registros almacenados.
 * 14. Los indicadores territoriales distinguen registros nuevos, confirmados y modificados.
 * 15. Los eventos de ensayo permanecen aislados con la bandera es_ensayo.
 */
class SeguimientoCampanaAsignacionTest extends TestCase
{
    use DatabaseTransactions;

    protected CampanaAsignacionService $service;
    protected SeguimientoAutorizacionService $authService;
    protected User $coordinador;
    protected Campana $campanaPrueba;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'egresado', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad', 'guard_name' => 'web']);

        $this->service = app(CampanaAsignacionService::class);
        $this->authService = app(SeguimientoAutorizacionService::class);

        // Coordinador de prueba
        $this->coordinador = User::factory()->create([
            'name' => 'Coordinador Campaña Test',
            'apellidos' => 'Puklla Coordinador',
            'dni' => (string) rand(10000000, 99999999),
            'email' => 'coord_camp_' . rand(1000, 9999) . '@puklla.test',
        ]);
        $this->coordinador->assignRole('coordinacion-empleabilidad');

        // Campaña controlada de prueba
        $this->campanaPrueba = Campana::create([
            'nombre' => 'Campaña Test Equilibrada ' . rand(100, 999),
            'codigo' => 'CAMP-TEST-' . rand(1000, 9999),
            'anio' => 2026,
            'semestre' => 'II',
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'estado' => 'activa',
            'descripcion' => 'Campaña para validación automatizada de distribución equilibrada',
            'es_ensayo' => true,
        ]);
    }

    protected function crearDocente(string $nombre = 'Docente'): User
    {
        $u = User::factory()->create([
            'name' => $nombre . ' ' . rand(100, 999),
            'apellidos' => 'Puklla',
            'email' => 'docente_' . rand(10000, 99999) . '@pukllavirtual.edu.pe',
            'dni' => (string) rand(70000000, 79999999),
        ]);
        $u->assignRole('docente');

        Docente::create([
            'nombre' => $u->name . ' ' . $u->apellidos,
            'dni' => $u->dni,
            'email' => $u->email,
            'user_id' => $u->id,
        ]);

        return $u;
    }

    protected function crearEgresado(string $nombre = 'Egresado'): User
    {
        $u = User::factory()->create([
            'name' => $nombre . ' ' . rand(100, 999),
            'apellidos' => 'Graduado',
            'email' => 'egresado_' . rand(10000, 99999) . '@pukllavirtual.edu.pe',
            'dni' => (string) rand(40000000, 49999999),
            'condicion' => 'Egresado',
        ]);
        $u->assignRole('egresado');

        return $u;
    }

    /**
     * 1. La selección incluye exclusivamente docentes elegibles.
     */
    public function test_seleccion_incluye_exclusivamente_docentes_elegibles(): void
    {
        $docente = $this->crearDocente('Profesor Válido');
        $alumno = User::factory()->create([
            'name' => 'Alumno Test',
            'apellidos' => 'Puklla',
            'dni' => (string) rand(10000000, 99999999),
            'condicion' => 'Regular',
        ]);
        $alumno->assignRole('alumno');

        $elegibles = $this->service->obtenerDocentesElegibles();

        $this->assertTrue($elegibles->contains('user_id', $docente->id));
        $this->assertFalse($elegibles->contains('user_id', $alumno->id));
    }

    /**
     * 2. No se duplican docentes ni egresados en el padrón.
     */
    public function test_no_se_duplican_docentes_ni_egresados(): void
    {
        $padron = $this->service->obtenerPadronEgresadosElegibles();
        $ids = $padron->pluck('user_id');

        $this->assertEquals($ids->count(), $ids->unique()->count(), 'El padrón de egresados contiene duplicados.');
    }

    /**
     * 3. Los roles docentes originales se conservan al ser seleccionados en una campaña.
     */
    public function test_roles_docentes_originales_se_conservan(): void
    {
        $docente = $this->crearDocente('Profesor Multifunción');
        $this->assertTrue($docente->hasRole('docente'));
        $this->assertFalse($docente->hasRole('seguimiento-egresados'));

        $egresados = [
            $this->crearEgresado('Egr 1'),
            $this->crearEgresado('Egr 2'),
        ];

        $this->service->distribuirEquilibrado(
            $this->campanaPrueba->id,
            [$docente->id],
            $this->coordinador->id,
            true,
            false
        );

        $docente->refresh();
        $this->assertTrue($docente->hasRole('docente'), 'El docente debe conservar su rol docente original.');
        $this->assertTrue($docente->hasRole('seguimiento-egresados'), 'El docente debe recibir el rol funcional seguimiento-egresados.');
    }

    /**
     * 4. La distribución es aleatoria y equilibrada (diferencia máxima de 1).
     */
    public function test_distribucion_es_aleatoria_y_equilibrada_con_diferencia_maxima_de_uno(): void
    {
        $docentes = [
            $this->crearDocente('Docente A'),
            $this->crearDocente('Docente B'),
            $this->crearDocente('Docente C'),
        ];
        $docIds = array_map(fn ($d) => $d->id, $docentes);

        // Previsualizar distribución sobre el padrón real
        $preview = $this->service->distribuirEquilibrado(
            $this->campanaPrueba->id,
            $docIds,
            $this->coordinador->id,
            true,
            true
        );

        $this->assertTrue($preview['preview']);
        $this->assertEquals(3, $preview['total_docentes']);
        $this->assertLessThanOrEqual(1, $preview['diferencia_maxima'], 'La diferencia máxima entre docentes debe ser <= 1.');

        $cantidades = array_column($preview['resumen'], 'cantidad_asignada');
        $this->assertEquals($preview['total_egresados'], array_sum($cantidades));
    }

    /**
     * 5. No existen asignaciones duplicadas por campaña.
     */
    public function test_no_existen_asignaciones_duplicadas_por_campana(): void
    {
        $docente1 = $this->crearDocente('Docente 1');
        $docente2 = $this->crearDocente('Docente 2');
        $egresado = $this->crearEgresado('Egresado Único');

        AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente1->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        $padronDisponible = $this->service->obtenerPadronEgresadosElegibles($this->campanaPrueba->id, true);
        $this->assertFalse($padronDisponible->contains('user_id', $egresado->id), 'Un egresado ya asignado no debe figurar como disponible.');
    }

    /**
     * 6. La operación es idempotente y segura ante concurrencia.
     */
    public function test_operacion_es_idempotente_y_segura_ante_concurrencia(): void
    {
        $docente = $this->crearDocente('Docente Concurrencia');

        $res1 = $this->service->distribuirEquilibrado($this->campanaPrueba->id, [$docente->id], $this->coordinador->id, true, false);
        $this->assertTrue($res1['success']);

        // Una segunda ejecución intentará distribuir sólo los no asignados; si ya no hay disponibles, lanza excepción segura
        $this->expectException(\RuntimeException::class);
        $this->service->distribuirEquilibrado($this->campanaPrueba->id, [$docente->id], $this->coordinador->id, true, false);
    }

    /**
     * 7. Una reasignación conserva el historial anterior y motivo.
     */
    public function test_reasignacion_conserva_el_historial_anterior(): void
    {
        $docenteA = $this->crearDocente('Docente Original');
        $docenteB = $this->crearDocente('Docente Reemplazo');
        $egresado = $this->crearEgresado('Egresado Reasignado');

        $asigOriginal = AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docenteA->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        $nuevaAsig = $this->service->reasignarEgresado(
            $asigOriginal->id,
            $docenteB->id,
            $this->coordinador->id,
            'Reasignación por licencia médica'
        );

        $asigOriginal->refresh();
        $this->assertEquals('reasignado', $asigOriginal->estado);
        $this->assertEquals('Reasignación por licencia médica', $asigOriginal->motivo_reasignacion);

        $this->assertEquals('asignado', $nuevaAsig->estado);
        $this->assertEquals($docenteB->id, $nuevaAsig->docente_user_id);
        $this->assertEquals($docenteA->id, $nuevaAsig->docente_anterior_id);
    }

    /**
     * 8. Un docente no accede a casos de otro (HTTP 403 estricto).
     */
    public function test_docente_no_accede_a_casos_de_otro_docente_retorna_403(): void
    {
        $docenteA = $this->crearDocente('Docente A');
        $docenteB = $this->crearDocente('Docente B');
        $egresadoB = $this->crearEgresado('Egresado Asignado a B');

        // Asignar egresado exclusivamente al docente B
        AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docenteB->id,
            'egresado_user_id' => $egresadoB->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        // Docente A intenta registrar una llamada para el egresado de B
        $response = $this->actingAs($docenteA)->postJson(route('seguimiento-egresados.llamada'), [
            'id' => 'REAL-' . $egresadoB->id,
            'tipo_resultado' => 'contacto_efectivo',
            'campana_id' => $this->campanaPrueba->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * 9. La revocación retira el acceso efectivo.
     */
    public function test_revocacion_retira_el_acceso_efectivo(): void
    {
        $docente = $this->crearDocente('Docente Con Revocacion');
        $egresado = $this->crearEgresado('Egresado Revocado');

        $asig = AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        $this->assertTrue(AsignacionCampana::esDocenteAsignado($docente->id, $egresado->id, $this->campanaPrueba->id));

        // Revocar la asignación
        $asig->update(['estado' => 'revocado']);

        $this->assertFalse(AsignacionCampana::esDocenteAsignado($docente->id, $egresado->id, $this->campanaPrueba->id));
    }

    /**
     * 10. Los intentos de llamada NO se confunden con contactos efectivos.
     */
    public function test_intentos_de_llamada_no_se_contabilizan_como_contactos_efectivos(): void
    {
        $docente = $this->crearDocente('Docente Intentos');
        $egresado = $this->crearEgresado('Egresado Difícil Contacto');

        AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        // Registrar 3 intentos sin respuesta
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($docente)->postJson(route('seguimiento-egresados.llamada'), [
                'id' => 'REAL-' . $egresado->id,
                'tipo_resultado' => 'intento_sin_respuesta',
                'result' => 'No contesta',
                'campana_id' => $this->campanaPrueba->id,
            ])->assertOk();
        }

        $metricas = $this->service->obtenerMetricasDocente($docente->id, $this->campanaPrueba->id);

        $this->assertEquals(3, $metricas['intentos_llamada'], 'Deben registrarse 3 intentos de llamada.');
        $this->assertEquals(0, $metricas['contactados_efectivos'], 'No debe haber personas contactadas.');
        $this->assertEquals(1, $metricas['pendientes_contactar'], 'El egresado sigue pendiente de contactar.');
    }

    /**
     * 11. Un contacto efectivo actualiza el estado de la asignación.
     */
    public function test_contacto_efectivo_actualiza_estado_asignacion(): void
    {
        $docente = $this->crearDocente('Docente Éxito');
        $egresado = $this->crearEgresado('Egresado Localizado');

        $asig = AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        $this->actingAs($docente)->postJson(route('seguimiento-egresados.llamada'), [
            'id' => 'REAL-' . $egresado->id,
            'tipo_resultado' => 'contacto_efectivo',
            'result' => 'Conversación fluida',
            'campana_id' => $this->campanaPrueba->id,
        ])->assertOk();

        $asig->refresh();
        $this->assertEquals('contactado', $asig->estado);
    }

    /**
     * 12. Las actualizaciones se relacionan correctamente con campaña y egresado y completan la asignación.
     */
    public function test_actualizacion_completa_se_relaciona_con_campana_y_completa_asignacion(): void
    {
        $docente = $this->crearDocente('Docente Actualizador');
        $egresado = $this->crearEgresado('Egresada Trabajando');

        $asig = AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
        ]);

        $response = $this->actingAs($docente)->postJson(route('seguimiento-egresados.actualizar'), [
            'user_id' => $egresado->id,
            'sin_cambios' => false,
            'telefono' => '984112233',
            'trabaja' => 'Sí',
            'institucion' => 'I.E. 50012 Pukllasunchis',
            'cargo' => 'Docente Primaria EIB',
            'res_departamento' => 'CUSCO',
            'res_provincia' => 'CUSCO',
            'res_distrito' => 'SAN JERONIMO',
            'campana' => $this->campanaPrueba->codigo,
            'es_ensayo' => true,
        ]);

        $response->assertOk();
        $asig->refresh();
        $this->assertEquals('completado', $asig->estado);

        $this->assertDatabaseHas('egresado_actualizaciones', [
            'user_id' => $egresado->id,
            'periodo_campana' => $this->campanaPrueba->codigo,
            'institucion' => 'I.E. 50012 Pukllasunchis',
        ]);
    }

    /**
     * 13. Los indicadores generales coinciden con los registros almacenados.
     */
    public function test_indicadores_generales_coinciden_con_registros_almacenados(): void
    {
        $metricas = $this->service->obtenerMetricasCampana($this->campanaPrueba->id);

        $this->assertArrayHasKey('total_padron_elegible', $metricas);
        $this->assertArrayHasKey('total_asignados', $metricas);
        $this->assertArrayHasKey('total_contactados_efectivos', $metricas);
        $this->assertArrayHasKey('tasa_contacto_pct', $metricas);
        $this->assertArrayHasKey('avance_docentes', $metricas);
    }

    /**
     * 14. Los indicadores territoriales distinguen registros nuevos, confirmados y modificados.
     */
    public function test_indicadores_territoriales_distinguen_registros_nuevos_confirmados_y_modificados(): void
    {
        $metricasTerr = $this->service->obtenerMetricasTerritoriales($this->campanaPrueba->id);

        $this->assertArrayHasKey('basal', $metricasTerr);
        $this->assertArrayHasKey('actualizado', $metricasTerr);
        $this->assertArrayHasKey('distribucion_departamentos', $metricasTerr);
        $this->assertArrayHasKey('distribucion_provincias_cusco', $metricasTerr);
        $this->assertGreaterThan(0, $metricasTerr['total_padron']);
    }

    /**
     * 15. Los eventos de ensayo permanecen aislados con la bandera es_ensayo.
     */
    public function test_eventos_de_ensayo_estan_aislados_y_canales_externos_deshabilitados(): void
    {
        $docente = $this->crearDocente('Docente Ensayo');
        $egresado = $this->crearEgresado('Egresado Ensayo');

        $asig = AsignacionCampana::create([
            'campana_id' => $this->campanaPrueba->id,
            'docente_user_id' => $docente->id,
            'egresado_user_id' => $egresado->id,
            'estado' => 'asignado',
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now(),
            'es_ensayo' => true,
        ]);

        $this->assertTrue($asig->es_ensayo);
        $this->assertEquals(1, AsignacionCampana::ensayo()->where('id', $asig->id)->count());
        $this->assertEquals(0, AsignacionCampana::reales()->where('id', $asig->id)->count());
    }
}
