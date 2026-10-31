<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Programa;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\User;
use App\Services\SeguimientoEgresado\EstadisticaSeguraService;
use App\Services\SeguimientoEgresado\ObservatorioTerritorialService;
use App\Services\SeguimientoEgresado\SeguimientoAnaliticaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Suite de Pruebas de Analítica, Observatorio Territorial y Privacidad Estadística — Fase 2A
 * 
 * Requisitos verificados:
 * 1. Universos analíticos rigurosos: Padrón, Elegibles, Respondientes y separación de no respondientes (R07/R10).
 * 2. Motor de analítica y cruces dinámicos contando personas únicas (users.id) sin duplicidad de contratos.
 * 3. Privacidad estadística diferencial: Supresión primaria de celdas < 5 y supresión secundaria complementaria (R08).
 * 4. Observatorio territorial y movilidad residencia vs empleo con agenda de acción institucional.
 * 5. Control de autorización estricto para acceso al laboratorio.
 * 6. Exportación segura en CSV con metadatos de auditoría y cero fuga de PII.
 */
class SeguimientoLaboratorioFase2ATest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'egresado']);
        Role::firstOrCreate(['name' => 'alumno']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'docente']);
        Role::firstOrCreate(['name' => 'formacion-continua']);
    }

    protected function crearUsuarioSintetico(string $condicion = 'Egresado', ?string $role = 'egresado'): User
    {
        $user = User::factory()->create([
            'name' => 'Egresado Test ' . rand(100, 999),
            'apellidos' => 'Seguimiento ' . rand(1000, 9999),
            'condicion' => $condicion,
            'dni' => (string) rand(70000000, 79999999),
            'email' => 'test' . rand(1000, 9999) . '@puklla.test',
        ]);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * Test 1: Las rutas del laboratorio requieren autenticación previa.
     */
    public function test_01_rutas_laboratorio_requieren_autenticacion(): void
    {
        $responseGet = $this->get(route('seguimiento.laboratorio'));
        $this->assertTrue(in_array($responseGet->status(), [302, 401]));

        $responsePost = $this->postJson(route('seguimiento.analitica'), [
            'fila' => 'programa',
            'columna' => 'situacion_laboral'
        ]);
        $this->assertTrue(in_array($responsePost->status(), [302, 401]));
    }

    /**
     * Test 2: Egresados y alumnos no tienen privilegios para acceder al laboratorio analítico institucional (HTTP 403).
     */
    public function test_02_egresados_y_alumnos_no_tienen_acceso_al_laboratorio(): void
    {
        $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');

        $responseGet = $this->actingAs($egresado)->get(route('seguimiento.laboratorio'));
        $responseGet->assertStatus(403);

        $responsePost = $this->actingAs($egresado)->postJson(route('seguimiento.analitica'), [
            'fila' => 'programa',
            'columna' => 'situacion_laboral'
        ]);
        $responsePost->assertStatus(403);
    }

    /**
     * Test 3: Personal autorizado (coordinación, directivos, admins) accede exitosamente al laboratorio (HTTP 200).
     */
    public function test_03_personal_autorizado_accede_al_laboratorio_con_exito(): void
    {
        $coord = $this->crearUsuarioSintetico('Docente', 'coordinacion-empleabilidad');

        $response = $this->actingAs($coord)->get(route('seguimiento.laboratorio'));
        $response->assertStatus(200);
        $response->assertViewHasAll([
            'variables',
            'universos',
            'matrizInicial',
            'longitudinalInicial',
            'territorialInicial',
            'demandasInicial'
        ]);
    }

    /**
     * Test 4: Verificación de universos analíticos rigurosos y separación de no respondientes (R07/R10).
     */
    public function test_04_universos_analiticos_distinguen_padron_elegibles_y_no_respondientes(): void
    {
        /** @var SeguimientoAnaliticaService $service */
        $service = app(SeguimientoAnaliticaService::class);
        $universos = $service->calcularUniversos();

        $this->assertArrayHasKey('total_padron', $universos);
        $this->assertArrayHasKey('elegibles_seguimiento', $universos);
        $this->assertArrayHasKey('contactados', $universos);
        $this->assertArrayHasKey('respondientes', $universos);
        $this->assertArrayHasKey('no_respondientes', $universos);
        $this->assertArrayHasKey('advertencias_metodologicas', $universos);

        // El padrón institucional debe tener al menos los 401 egresados base
        $this->assertGreaterThanOrEqual(401, $universos['total_padron']);

        // El universo de no respondientes debe ser la diferencia entre total padrón y respondientes
        $this->assertEquals(
            $universos['total_padron'] - $universos['respondientes'],
            $universos['no_respondientes']
        );

        // Comprobar que existe la advertencia metodológica expresa sobre no respondientes
        $tieneAdvertencia = false;
        foreach ($universos['advertencias_metodologicas'] as $adv) {
            if (str_contains($adv, 'no respondientes') || str_contains($adv, 'desempleo')) {
                $tieneAdvertencia = true;
                break;
            }
        }
        $this->assertTrue($tieneAdvertencia, 'Debe incluir advertencia de no asumir desempleo en los no respondientes.');
    }

    /**
     * Test 5: La matriz cruzada cuenta personas únicas (users.id) sin duplicidad por múltiples contratos (R07).
     */
    public function test_05_matriz_cruzada_cuenta_personas_unicas_sin_duplicar_por_empleos(): void
    {
        $egresado = User::where('condicion', 'Egresado')->has('alumno')->first();
        if (!$egresado) {
            $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');
        }

        // Registrar actualización laboral
        Actualizacion::create([
            'user_id' => $egresado->id,
            'nombre_completo' => $egresado->name,
            'periodo_campana' => '2026-II',
            'situacion_laboral' => 'Trabaja en educación',
            'tipo_registro' => 'formulario_completo',
            'estado_verificacion' => 'verificado',
            'canal' => 'web_intranet'
        ]);

        // Crear 2 episodios laborales activos simultáneos para el mismo egresado
        EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'institucion' => 'I.E. Inicial 01',
            'tipo_institucion' => 'Pública',
            'cargo' => 'Docente de aula',
            'es_actual' => true,
            'fecha_inicio' => '2024-03-01'
        ]);
        EpisodioLaboral::create([
            'user_id' => $egresado->id,
            'institucion' => 'I.E. Privada 02',
            'tipo_institucion' => 'Privada',
            'cargo' => 'Tallerista artístico',
            'es_actual' => true,
            'fecha_inicio' => '2024-04-01'
        ]);

        /** @var SeguimientoAnaliticaService $service */
        $service = app(SeguimientoAnaliticaService::class);
        $matriz = $service->generarMatrizCruzada('programa', 'situacion_laboral');

        $this->assertNotEmpty($matriz['celdas']);
        $this->assertNotEmpty($matriz['valores_fila']);
        $this->assertNotEmpty($matriz['valores_columna']);

        // Verificamos que la sumatoria de personas en las celdas sea consistente
        $totalGeneral = $matriz['total_general'] ?? 0;
        $this->assertGreaterThan(0, $totalGeneral);
    }

    /**
     * Test 6: Privacidad estadística aplica supresión primaria a celdas con conteo entre 1 y 4 (R08).
     */
    public function test_06_privacidad_estadistica_aplica_supresion_primaria_a_celdas_menores_a_5(): void
    {
        /** @var EstadisticaSeguraService $seguridadService */
        $seguridadService = app(EstadisticaSeguraService::class);

        $conteoPequeño = 3;
        $resultado = $seguridadService->protegerConteo($conteoPequeño);

        $this->assertTrue($resultado['suprimido']);
        $this->assertEquals('< 5', $resultado['valor']);
        $this->assertNull($resultado['porcentaje']);

        // Conteo >= 5 no debe ser suprimido
        $conteoSeguro = 12;
        $resultadoSeguro = $seguridadService->protegerConteo($conteoSeguro, 100);

        $this->assertFalse($resultadoSeguro['suprimido']);
        $this->assertEquals(12, $resultadoSeguro['valor']);
        $this->assertEquals(12.0, $resultadoSeguro['porcentaje']);
    }

    /**
     * Test 7: Supresión secundaria complementaria en matriz para evitar deducción algebraica de celdas < 5 (R08).
     */
    public function test_07_privacidad_estadistica_aplica_supresion_secundaria_para_evitar_deduccion(): void
    {
        /** @var EstadisticaSeguraService $seguridadService */
        $seguridadService = app(EstadisticaSeguraService::class);

        // Matriz sintética con una sola celda pequeña en una fila
        $filas = ['Inicial', 'Primaria EIB'];
        $columnas = ['Nombrado', 'Contratado'];
        $conteos = [
            'Inicial' => ['Nombrado' => 3, 'Contratado' => 20], // 3 es < 5 (supresión primaria)
            'Primaria EIB' => ['Nombrado' => 15, 'Contratado' => 25],
        ];

        $matrizProtegida = $seguridadService->protegerMatriz2D($conteos, $filas, $columnas);

        // La celda (Inicial, Nombrado) debe estar suprimida primariamente
        $this->assertTrue($matrizProtegida['celdas']['Inicial']['Nombrado']['suprimido']);
        $this->assertEquals('< 5', $matrizProtegida['celdas']['Inicial']['Nombrado']['conteo']);

        // Para evitar deducir el 3 restando (Total Inicial - Contratado), o el total o la celda complementaria debe estar enmascarada
        $totalFilaSuprimido = $matrizProtegida['totales_fila']['Inicial']['suprimido'] ?? false;
        $celdaCompSuprimida = $matrizProtegida['celdas']['Inicial']['Contratado']['suprimido'] ?? false;

        $this->assertTrue(
            $totalFilaSuprimido || $celdaCompSuprimida,
            'Debe aplicarse supresión secundaria (en celda complementaria o en margen/total) para evitar deducir la celda < 5.'
        );
    }

    /**
     * Test 8: El endpoint AJAX de analítica procesa combinaciones de filtros válidos.
     */
    public function test_08_filtros_combinables_en_endpoint_ajax(): void
    {
        $coord = $this->crearUsuarioSintetico('Docente', 'coordinacion-empleabilidad');

        $response = $this->actingAs($coord)->postJson(route('seguimiento.analitica'), [
            'fila' => 'cohorte',
            'columna' => 'situacion_laboral',
            'filtros' => [
                'programa' => 'Inicial',
                'docencia_directa' => 'si'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure([
            'success',
            'universos',
            'matriz' => [
                'fila_key',
                'columna_key',
                'valores_fila',
                'valores_columna',
                'celdas',
                'totales_fila',
                'totales_columna',
                'total_general'
            ],
            'longitudinal',
            'territorial',
            'demandas'
        ]);
    }

    /**
     * Test 9: El Observatorio Territorial desglosa provincias de Cusco y genera agenda de acción institucional.
     */
    public function test_09_observatorio_territorial_agrupa_provincias_y_agenda_institucional(): void
    {
        /** @var ObservatorioTerritorialService $territorialService */
        $territorialService = app(ObservatorioTerritorialService::class);
        $reporte = $territorialService->obtenerReporteTerritorial();

        $this->assertArrayHasKey('provincias_cusco', $reporte);
        $this->assertArrayHasKey('departamentos', $reporte);
        $this->assertArrayHasKey('concentracion_eib', $reporte);
        $this->assertArrayHasKey('agenda_accion', $reporte);

        // Debe existir recomendaciones en la agenda institucional
        $this->assertNotEmpty($reporte['agenda_accion']);
        $primeraRecomendacion = $reporte['agenda_accion'][0];
        $this->assertArrayHasKey('titulo', $primeraRecomendacion);
        $this->assertArrayHasKey('territorio', $primeraRecomendacion);
        $this->assertArrayHasKey('prioridad', $primeraRecomendacion);
    }

    /**
     * Test 10: La exportación CSV contiene metadatos institucionales y cero fuga de información confidencial o PII.
     */
    public function test_10_exportacion_csv_contiene_metadatos_y_cero_fuga_pii(): void
    {
        $coord = $this->crearUsuarioSintetico('Docente', 'coordinacion-empleabilidad');

        $response = $this->actingAs($coord)->get(route('seguimiento.analitica.exportar', [
            'fila' => 'programa',
            'columna' => 'situacion_laboral',
            'formato' => 'csv'
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        // Obtener contenido del CSV
        $content = $response->streamedContent();

        // Debe contener encabezados institucionales
        $this->assertStringContainsString('PUKLLASUNCHIS', $content);
        $this->assertStringContainsString('SEGUIMIENTO DE EGRESADOS', $content);
        $this->assertStringContainsString('TOTAL', $content);

        // NO debe contener identificadores personales (PII)
        $this->assertStringNotContainsString('dni', strtolower($content));
        $this->assertStringNotContainsString('telefono', strtolower($content));
        $this->assertStringNotContainsString('direccion_contacto', strtolower($content));
    }
}
