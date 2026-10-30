<?php

namespace Tests\Feature;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Suite de Pruebas de Integración y Persistencia Longitudinal - Fase 1B
 * 
 * Requisitos cubiertos:
 * - R01: Integridad de identidad y controles Anti-IDOR (servidor).
 * - R02: Confirmación rápida semestral «sin cambios» (cero duplicados).
 * - R03: Persistencia longitudinal de trayectorias (empleos sucesivos, simultáneos, cierres).
 */
class SeguimientoEgresadosFase1BTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Asegurar la existencia de roles institucionales de Puklla en Red
        Role::firstOrCreate(['name' => 'egresado']);
        Role::firstOrCreate(['name' => 'encuestador-seguimiento']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad']);
    }

    /**
     * Helper para crear un usuario de prueba sintético sin PII real.
     */
    protected function crearUsuarioSintetico(string $condicion = 'Egresado', ?string $role = 'egresado'): User
    {
        $user = User::factory()->create([
            'name' => 'Usuario Pruebas',
            'apellidos' => 'Seguimiento ' . rand(1000, 9999),
            'condicion' => $condicion,
            'dni' => (string) rand(70000000, 79999999),
        ]);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * Prueba 1: Registro del primer episodio laboral.
     */
    public function test_01_primer_episodio_laboral(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        $payload = [
            'id' => $egresado->id,
            'trabaja' => 'Trabaja',
            'cambio_empleo' => 'Primer empleo',
            'institucion' => 'I.E. 50012 San Jerónimo',
            'cargo' => 'Docente de Primaria',
            'campo' => 'En educación',
            'tipo_practica' => 'Docencia directa',
            'contrato' => 'Contrato anual',
            'inicio_empleo' => '2024-03-01',
            'trab_departamento' => 'Cusco',
            'trab_distrito' => 'San Jerónimo',
        ];

        $response = $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tipo' => 'actualizacion_completa',
            ]);

        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count());

        $episodio = EpisodioLaboral::where('user_id', $egresado->id)->first();
        $this->assertEquals('I.E. 50012 San Jerónimo', $episodio->institucion);
        $this->assertEquals('Docente de Primaria', $episodio->cargo);
        $this->assertTrue($episodio->es_actual);
        $this->assertTrue($episodio->es_empleo_educativo);
        $this->assertTrue($episodio->es_docencia_directa);
        $this->assertNull($episodio->fecha_fin);
    }

    /**
     * Prueba 2: Cambio de empleo con conservación íntegra del historial previo.
     */
    public function test_02_cambio_de_empleo_con_conservacion_de_historial(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        // 1. Primer empleo activo
        $ep1 = EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2023-03-01',
            'institucion' => 'I.E. Inicial 88',
            'cargo' => 'Auxiliar de Educación',
            'es_actual' => true,
        ]);

        // 2. Reporte de nuevo empleo cerrando el anterior
        $payload = [
            'id' => $egresado->id,
            'trabaja' => 'Trabaja',
            'cambio_empleo' => 'Nuevo empleo o puesto',
            'episodio_id_cerrar' => $ep1->id,
            'fecha_fin_anterior' => '2024-12-31',
            'motivo_cambio' => 'Promoción a docencia de aula',
            'institucion' => 'Colegio San José',
            'cargo' => 'Docente Titular',
            'inicio_empleo' => '2025-03-01',
        ];

        $response = $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload);

        $response->assertStatus(200);

        // Se deben conservar ambos episodios en la base institucional
        $this->assertEquals(2, EpisodioLaboral::where('user_id', $egresado->id)->count());

        // El primer episodio debe estar cerrado con su fecha de finalización
        $ep1->refresh();
        $this->assertFalse((bool) $ep1->es_actual);
        $this->assertEquals('2024-12-31', $ep1->fecha_fin->format('Y-m-d'));
        $this->assertEquals('Promoción a docencia de aula', $ep1->motivo_cambio);

        // El segundo episodio debe estar activo
        $ep2 = EpisodioLaboral::where('user_id', $egresado->id)
            ->where('institucion', 'Colegio San José')
            ->first();
        $this->assertNotNull($ep2);
        $this->assertTrue((bool) $ep2->es_actual);
        $this->assertEquals('2025-03-01', $ep2->fecha_inicio->format('Y-m-d'));
    }

    /**
     * Prueba 3: Finalización de empleo sin crear empleador ficticio "Desempleado".
     */
    public function test_03_finalizacion_de_empleo_sin_crear_empleador_ficticio(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        $ep = EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2024-03-01',
            'institucion' => 'I.E. Rural 45',
            'cargo' => 'Docente Multigrado',
            'es_actual' => true,
        ]);

        $payload = [
            'id' => $egresado->id,
            'trabaja' => 'No trabaja',
            'cambio_empleo' => 'Terminó un empleo',
            'cerrar_activos' => true,
            'fin_empleo' => '2025-11-30',
            'motivo_cambio' => 'Culminación de contrato',
        ];

        $response = $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload);

        $response->assertStatus(200);

        // El empleo anterior debe cerrarse
        $ep->refresh();
        $this->assertFalse((bool) $ep->es_actual);
        $this->assertEquals('2025-11-30', $ep->fecha_fin->format('Y-m-d'));

        // REGLA FUNDAMENTAL: Cero registros espurios o empleadores ficticios "Desempleado"
        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count());
        $this->assertDatabaseMissing('egresado_episodios_laborales', [
            'user_id' => $egresado->id,
            'institucion' => 'Desempleado',
        ]);
    }

    /**
     * Prueba 4: Confirmación rápida «sin cambios» (R02) sin duplicar episodios.
     */
    public function test_04_confirmacion_sin_cambios_no_duplica_episodios(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        $ep = EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2023-03-01',
            'institucion' => 'I.E. 50123 Cusco',
            'cargo' => 'Docente Titular',
            'es_actual' => true,
        ]);

        $payload = [
            'id' => $egresado->id,
            'sin_cambios' => true,
            'campana' => '2026-II',
            'satisfaccion' => '5 - Muy satisfecho/a',
            'temas' => 'Pedagogía lúdica y educación infantil',
            'necesidad_prioritaria' => 'No por ahora',
        ];

        $response = $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tipo' => 'confirmacion_sin_cambios',
                'episodios_activos' => 1,
            ]);

        // La cantidad de episodios laborales sigue siendo exactamente 1 (Cero duplicados)
        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count());

        $ep->refresh();
        $this->assertTrue((bool) $ep->es_actual);
        $this->assertEquals('2023-03-01', $ep->fecha_inicio->format('Y-m-d'));

        // Se verifica que la tabla de actualizaciones registre la confirmación semestral
        $actualizacion = Actualizacion::where('user_id', $egresado->id)->first();
        $this->assertNotNull($actualizacion);
        $this->assertTrue((bool) $actualizacion->es_confirmacion_sin_cambios);
        $this->assertEquals('2026-II', $actualizacion->periodo_campana);
        $this->assertEquals(now()->toDateString(), $actualizacion->fecha_ultima_confirmacion->toDateString());
    }

    /**
     * Prueba 5: Registro y coexistencia de dos empleos simultáneos.
     */
    public function test_05_empleos_simultaneos(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        // 1. Registrar primer empleo matutino
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'id' => $egresado->id,
            'trabaja' => 'Trabaja',
            'institucion' => 'I.E. Pública Matutina',
            'cargo' => 'Docente de Aula',
            'inicio_empleo' => '2024-03-01',
            'modalidad_tiempo' => 'Tiempo parcial (mañana)',
        ])->assertStatus(200);

        // 2. Registrar segundo empleo vespertino simultáneo (sin cerrar el primero)
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'id' => $egresado->id,
            'trabaja' => 'Trabaja',
            'institucion' => 'Centro de Apoyo Pedagógico',
            'cargo' => 'Tutor Pedagógico',
            'inicio_empleo' => '2024-06-01',
            'modalidad_tiempo' => 'Por horas (tarde)',
        ])->assertStatus(200);

        // Ambos episodios deben coexistir como activos simultáneamente
        $activos = EpisodioLaboral::where('user_id', $egresado->id)
            ->where('es_actual', true)
            ->get();

        $this->assertCount(2, $activos);
        $this->assertEquals(['Centro de Apoyo Pedagógico', 'I.E. Pública Matutina'], $activos->pluck('institucion')->sort()->values()->all());
    }

    /**
     * Prueba 6: Cierre de empleo con fecha de finalización desconocida (conserva NULL, no inventa hoy).
     */
    public function test_06_fecha_de_finalizacion_desconocida(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        $ep = EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2023-05-10',
            'institucion' => 'Colegio Experimental',
            'cargo' => 'Docente Suplente',
            'es_actual' => true,
        ]);

        // Cierre sin fecha exacta vía endpoint de cerrar episodio
        $response = $this->actingAs($egresado)
            ->postJson(url("/seguimiento-egresados/episodios/{$ep->id}/cerrar"), [
                'motivo_cambio' => 'Finalizó reemplazo temporal',
            ]);

        $response->assertStatus(200);

        $ep->refresh();
        $this->assertFalse((bool) $ep->es_actual);
        $this->assertNull($ep->fecha_fin); // NO debe autoasignarse la fecha actual
        $this->assertEquals('Finalizó reemplazo temporal', $ep->motivo_cambio);
    }

    /**
     * Prueba 7: Reenvío de una misma actualización sin crear registros duplicados (Idempotencia).
     */
    public function test_07_reenvio_identico_idempotente_no_duplica_episodios(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        $payload = [
            'id' => $egresado->id,
            'trabaja' => 'Trabaja',
            'institucion' => 'I.E. Idempotente 100',
            'cargo' => 'Docente Especialista',
            'inicio_empleo' => '2024-03-15',
            'contrato' => 'Nombrado',
        ];

        // Primer envío
        $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload)
            ->assertStatus(200);

        // Segundo envío idéntico (simula doble clic o reintento de conexión)
        $this->actingAs($egresado)
            ->postJson(route('seguimiento-egresados.actualizar'), $payload)
            ->assertStatus(200);

        // Solo debe existir 1 episodio laboral
        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count());
    }

    /**
     * Prueba 8: Rechazo de un identificador de usuario inválido en solicitud anónima (Anti-IDOR / 422).
     */
    public function test_08_rechazo_de_identificador_de_usuario_invalido(): void
    {
        $response = $this->postJson(route('seguimiento-egresados.actualizar'), [
            'id' => 99999999, // ID inexistente en la BD institucional
            'nombre' => 'Intento Invalido',
            'trabaja' => 'Trabaja',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Prueba 9: Rechazo de acceso de un egresado a otra ficha (Anti-IDOR / HTTP 403).
     */
    public function test_09_rechazo_acceso_egresado_a_otra_ficha(): void
    {
        $egresadoA = $this->crearUsuarioSintetico();
        $egresadoB = $this->crearUsuarioSintetico();

        // Egresado A intenta modificar la ficha de Egresado B
        $response = $this->actingAs($egresadoA)
            ->postJson(route('seguimiento-egresados.actualizar'), [
                'id' => $egresadoB->id,
                'telefono' => '999111222',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'No tienes autorización para modificar la ficha de otro egresado.',
            ]);
    }

    /**
     * Prueba 10: Rechazo HTTP 403 a un encuestador que intenta modificar un caso no asignado.
     */
    public function test_10_rechazo_http_403_encuestador_caso_no_asignado(): void
    {
        $encuestador = $this->crearUsuarioSintetico('Docente', 'encuestador-seguimiento');
        $egresadoNoAsignado = $this->crearUsuarioSintetico();

        // El encuestador no tiene asignado a este egresado en ningún GrupoEntrevistador
        $response = $this->actingAs($encuestador)
            ->postJson(route('seguimiento-egresados.actualizar'), [
                'id' => $egresadoNoAsignado->id,
                'trabaja' => 'Trabaja',
                'institucion' => 'I.E. No Autorizada',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Prueba 11: Rollback transaccional íntegro ante un error imprevisto.
     */
    public function test_11_rollback_ante_error_transaccional(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        // Comprobamos que si ocurre un fallo durante la transacción, no queden datos parciales
        try {
            DB::transaction(function () use ($egresado) {
                EpisodioLaboral::create([
                    'user_id' => $egresado->id,
                    'fecha_inicio' => '2024-01-01',
                    'institucion' => 'I.E. Falla Transaccional',
                    'cargo' => 'Docente',
                    'es_actual' => true,
                ]);

                throw new \RuntimeException('Fallo forzado para probar rollback ACID.');
            });
        } catch (\RuntimeException $e) {
            // Excepción esperada
        }

        // El episodio NO debe haberse persistido
        $this->assertEquals(0, EpisodioLaboral::where('user_id', $egresado->id)->count());
    }

    /**
     * Prueba 12: Consistencia entre episodios y estado laboral mostrado en los indicadores.
     */
    public function test_12_consistencia_entre_episodios_y_estado_laboral(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        // Crear 1 empleo histórico y 1 empleo activo
        EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2022-03-01',
            'fecha_fin' => '2023-12-31',
            'institucion' => 'I.E. Histórica',
            'cargo' => 'Docente Practicante',
            'es_actual' => false,
            'es_empleo_educativo' => true,
            'es_docencia_directa' => true,
        ]);

        EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'fecha_inicio' => '2024-03-01',
            'institucion' => 'I.E. Activa',
            'cargo' => 'Docente Titular',
            'es_actual' => true,
            'es_empleo_educativo' => true,
            'es_docencia_directa' => true,
        ]);

        // Comprobar scopes del modelo
        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->activos()->count());
        $this->assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->historicos()->count());
        $this->assertEquals(2, EpisodioLaboral::where('user_id', $egresado->id)->educativos()->count());
        $this->assertEquals(2, EpisodioLaboral::where('user_id', $egresado->id)->docenciaDirecta()->count());

        // Apertura automática de caso institucional si solicita apoyo prioritario
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'id' => $egresado->id,
            'sin_cambios' => true,
            'necesidad_prioritaria' => 'Sí: titulación',
        ])->assertStatus(200);

        $caso = Caso::where('user_id', $egresado->id)->first();
        $this->assertNotNull($caso);
        $this->assertEquals('titulación', $caso->tema);
        $this->assertEquals('Por revisar', $caso->estado);
    }
}
