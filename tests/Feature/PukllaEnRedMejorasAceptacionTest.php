<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\User;
use App\Services\SeguimientoEgresado\CampanaAsignacionService;
use App\Services\SeguimientoEgresado\GrupoFormacionService;
use App\Services\SeguimientoEgresado\SeguimientoAnaliticaService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PukllaEnRedMejorasAceptacionTest extends TestCase
{
    use DatabaseTransactions;

    protected CampanaAsignacionService $campanaService;
    protected GrupoFormacionService $grupoService;
    protected SeguimientoAutorizacionService $authService;
    protected User $coordinador;
    protected User $docente;
    protected Campana $campanaActiva;
    protected Campana $campanaAnterior;

    protected function crearUsuario(array $attrs = []): User
    {
        $dni = $attrs['dni'] ?? ('8' . sprintf('%07d', rand(1, 9999999)));
        $email = $attrs['email'] ?? ('test_u_' . rand(100000, 999999) . '@puklla.test');
        $defaults = [
            'name' => 'Usuario Test',
            'apellidos' => 'Puklla Test',
            'email' => $email,
            'dni' => $dni,
            'password' => bcrypt('password123'),
            'condicion' => 'Egresado',
        ];
        return User::create(array_merge($defaults, $attrs));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'egresado', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);

        $this->campanaService = app(CampanaAsignacionService::class);
        $this->grupoService = app(GrupoFormacionService::class);
        $this->authService = app(SeguimientoAutorizacionService::class);

        $this->coordinador = $this->crearUsuario([
            'name' => 'Coordinador Aceptación',
            'apellidos' => 'Test',
            'condicion' => 'Docente',
        ]);
        $this->coordinador->assignRole(['coordinacion-empleabilidad', 'admin']);

        $this->docente = $this->crearUsuario([
            'name' => 'Docente Entrevistador',
            'apellidos' => 'Test',
            'condicion' => 'Docente',
        ]);
        $this->docente->assignRole(['docente', 'seguimiento-egresados']);
        Docente::firstOrCreate(
            ['user_id' => $this->docente->id],
            [
                'nombre' => $this->docente->name,
                'apellidos' => $this->docente->apellidos,
                'email' => $this->docente->email,
                'dni' => $this->docente->dni,
                'activo' => true,
            ]
        );

        $this->campanaActiva = Campana::create([
            'nombre' => 'Campaña Activa 2026-II Test',
            'codigo' => 'CAMP-ACT-TEST-' . rand(1000, 9999),
            'anio' => 2026,
            'semestre' => 'II',
            'fecha_inicio' => now()->subDays(5)->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'estado' => 'activa',
            'descripcion' => 'Campaña activa para pruebas de aceptación',
            'es_ensayo' => true,
        ]);

        $this->campanaAnterior = Campana::create([
            'nombre' => 'Campaña Anterior 2026-I Test',
            'codigo' => 'CAMP-ANT-TEST-' . rand(1000, 9999),
            'anio' => 2026,
            'semestre' => 'I',
            'fecha_inicio' => now()->subMonths(6)->toDateString(),
            'fecha_fin' => now()->subMonths(3)->toDateString(),
            'estado' => 'cerrada',
            'descripcion' => 'Campaña histórica anterior',
            'es_ensayo' => true,
        ]);
    }

    /**
     * Prueba 1: FID, distrito, campaña activa.
     * Datos sintéticos: 10 elegibles, 3 contactos efectivos, 2 intentos sin respuesta dentro de los 7 pendientes.
     * Resultado: 7 sin contacto efectivo (70%), los 2 intentos siguen entre los 7, y el padrón devuelve los mismos 7 casos.
     */
    public function test_01_fid_distrito_sintetico_sin_contacto_efectivo_y_paridad_con_padron(): void
    {
        $distritoPrueba = 'San Jeronimo ' . rand(100, 999);
        $egresados = [];

        for ($i = 1; $i <= 10; $i++) {
            $u = $this->crearUsuario([
                'name' => "Egresado Sintetico FID $i",
                'apellidos' => 'Test Aceptacion',
                'telefono' => '984' . sprintf('%06d', rand(1, 999999)),
                'programa_id' => 1,
            ]);
            $u->assignRole('egresado');

            // Versión de perfil con distrito y coordenadas
            DB::table('egresado_perfil_versiones')->insert([
                'user_id' => $u->id,
                'registrado_por' => $u->id,
                'confirmado_at' => now(),
                'datos' => json_encode([
                    'contacto' => ['telefono' => $u->telefono, 'email' => $u->email],
                    'residencia' => [
                        'departamento' => 'Cusco',
                        'provincia' => 'Cusco',
                        'distrito' => $distritoPrueba,
                        'localidad' => $distritoPrueba,
                        'precision' => 'localidad',
                        'latitud' => -13.5350,
                        'longitud' => -71.8950,
                    ],
                    'trabajos' => [],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $egresados[] = $u;
        }

        // 3 Contactos efectivos en la campaña activa (asignación + llamada efectiva)
        for ($i = 0; $i < 3; $i++) {
            $asig = AsignacionCampana::create([
                'campana_id' => $this->campanaActiva->id,
                'docente_user_id' => $this->docente->id,
                'egresado_user_id' => $egresados[$i]->id,
                'asignado_por' => $this->coordinador->id,
                'fecha_asignacion' => now(),
                'estado' => 'contactado',
                'es_ensayo' => true,
            ]);

            Llamada::create([
                'user_id' => $egresados[$i]->id,
                'docente_user_id' => $this->docente->id,
                'campana_id' => $this->campanaActiva->id,
                'resultado' => 'efectiva',
                'es_contacto_efectivo' => true,
                'fecha_hora' => now(),
            ]);
        }

        // 2 Intentos sin respuesta en la campaña activa (entre los 7 pendientes)
        for ($i = 3; $i < 5; $i++) {
            $asig = AsignacionCampana::create([
                'campana_id' => $this->campanaActiva->id,
                'docente_user_id' => $this->docente->id,
                'egresado_user_id' => $egresados[$i]->id,
                'asignado_por' => $this->coordinador->id,
                'fecha_asignacion' => now(),
                'estado' => 'en_proceso',
                'es_ensayo' => true,
            ]);

            Llamada::create([
                'user_id' => $egresados[$i]->id,
                'docente_user_id' => $this->docente->id,
                'campana_id' => $this->campanaActiva->id,
                'resultado' => 'no_contesta',
                'es_contacto_efectivo' => false,
                'fecha_hora' => now(),
            ]);
        }
        // Los otros 5 (índices 5..9) permanecen sin intento

        // Verificar clasificación de estado de contacto para cada uno
        $sinContactoCount = 0;
        $conIntentoSinRespCount = 0;
        foreach ($egresados as $egr) {
            $st = $this->campanaService->clasificarEstadoContacto($egr->id, $this->campanaActiva->id);
            if ($st['sin_contacto_efectivo']) {
                $sinContactoCount++;
            }
            if ($st['intento_sin_respuesta']) {
                $conIntentoSinRespCount++;
            }
        }

        $this->assertEquals(7, $sinContactoCount, 'Deben haber exactamente 7 sin contacto efectivo de 10');
        $this->assertEquals(2, $conIntentoSinRespCount, 'Deben haber exactamente 2 intentos sin respuesta dentro de los 7');

        // Padrón institucional con filtro distrito y sin_contacto_efectivo
        $padronSinContacto = $this->campanaService->obtenerPadronEgresadosElegibles(
            $this->campanaActiva->id,
            false,
            'FID',
            ['distrito' => $distritoPrueba, 'estado_contacto' => 'sin_contacto_efectivo']
        );

        $this->assertCount(7, $padronSinContacto, 'El padrón debe devolver los mismos 7 casos sin contacto efectivo');
        $padronIds = $padronSinContacto->pluck('user_id')->all();

        // Verificar que los 2 con intento sin respuesta están presentes en el padrón
        $this->assertContains($egresados[3]->id, $padronIds);
        $this->assertContains($egresados[4]->id, $padronIds);
    }

    /**
     * Prueba 2: Evento efectivo de una campaña anterior y caso sin canal válido.
     * Resultado: El evento anterior no modifica la cobertura de la campaña actual.
     * «Sin canal válido» funciona como filtro aparte.
     */
    public function test_02_evento_anterior_no_altera_cobertura_actual_y_sin_canal_valido(): void
    {
        $egrAnterior = $this->crearUsuario([
            'name' => 'Egresado Campana Anterior',
        ]);
        $egrAnterior->assignRole('egresado');

        // Asignación y llamada efectiva en la CAMPAÑA ANTERIOR
        $asigAnt = AsignacionCampana::create([
            'campana_id' => $this->campanaAnterior->id,
            'docente_user_id' => $this->docente->id,
            'egresado_user_id' => $egrAnterior->id,
            'asignado_por' => $this->coordinador->id,
            'fecha_asignacion' => now()->subMonths(4),
            'estado' => 'contactado',
            'es_ensayo' => true,
        ]);
        Llamada::create([
            'user_id' => $egrAnterior->id,
            'docente_user_id' => $this->docente->id,
            'campana_id' => $this->campanaAnterior->id,
            'resultado' => 'efectiva',
            'es_contacto_efectivo' => true,
            'fecha_hora' => now()->subMonths(4),
        ]);

        // En la campaña anterior es efectivo
        $stAnt = $this->campanaService->clasificarEstadoContacto($egrAnterior->id, $this->campanaAnterior->id);
        $this->assertTrue($stAnt['contacto_efectivo']);

        // En la campaña actual debe aparecer como SIN CONTACTO EFECTIVO (y sin intento)
        $stAct = $this->campanaService->clasificarEstadoContacto($egrAnterior->id, $this->campanaActiva->id);
        $this->assertTrue($stAct['sin_contacto_efectivo'], 'Evento de campaña anterior no debe alterar campaña actual');
        $this->assertTrue($stAct['sin_intento']);

        // Caso sin canal válido: usuario sin teléfono ni correo institucional válido
        $egrSinCanal = $this->crearUsuario([
            'name' => 'Egresado Sin Canal Valido',
            'email' => 'invalido_sin_canal',
            'telefono' => null,
        ]);
        $stCanal = $this->campanaService->clasificarEstadoContacto($egrSinCanal->id, $this->campanaActiva->id);
        $this->assertTrue($stCanal['sin_canal_valido']);
        $this->assertTrue($stCanal['sin_contacto_efectivo'], 'Sin canal coexiste con sin contacto efectivo');
    }

    /**
     * Prueba 3: Persona sin ubicación verificable.
     * Resultado: Visible en el total y el padrón (con sin_ubicacion=1), sin punto artificial en el mapa.
     */
    public function test_03_persona_sin_ubicacion_verificable_en_total_y_padron_sin_punto_artificial(): void
    {
        $egrSinUbicacion = $this->crearUsuario([
            'name' => 'Egresado Sin Coordenadas',
        ]);
        $egrSinUbicacion->assignRole('egresado');

        // Versión con residencia sin coordenadas válidas ni precisión requerida
        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $egrSinUbicacion->id,
            'registrado_por' => $egrSinUbicacion->id,
            'confirmado_at' => now(),
            'datos' => json_encode([
                'residencia' => [
                    'departamento' => 'Cusco',
                    'provincia' => null,
                    'distrito' => null,
                    'latitud' => null,
                    'longitud' => null,
                ],
                'trabajos' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->coordinador)
            ->getJson(route('seguimiento.observatorio-conectado.datos', [
                'grupo_formacion' => 'TODOS',
                'campana_id' => $this->campanaActiva->id,
            ]));

        $response->assertStatus(200);
        $json = $response->json();

        // Debe registrarse en sin_ubicacion
        $this->assertGreaterThanOrEqual(1, $json['sin_ubicacion']['total']);

        // No debe generar puntos con latitud null o artificiales
        foreach ($json['puntos'] as $pt) {
            $this->assertNotNull($pt['latitud']);
            $this->assertNotNull($pt['longitud']);
        }

        // Debe ser consultable en padrón con sin_ubicacion=1
        $padronSinUbicacion = $this->campanaService->obtenerPadronEgresadosElegibles(
            $this->campanaActiva->id,
            false,
            'TODOS',
            ['sin_ubicacion' => true]
        );
        $this->assertGreaterThanOrEqual(1, $padronSinUbicacion->count());
        $this->assertContains($egrSinUbicacion->id, $padronSinUbicacion->pluck('user_id'));
    }

    /**
     * Prueba 4: Persona con 2 empleos en la misma zona y un empleo en otra.
     * Resultado: Una aparición por zona y una en el total global.
     */
    public function test_04_persona_con_multiples_empleos_deduplicacion_por_zona_y_global(): void
    {
        $egrMultiEmpleo = $this->crearUsuario([
            'name' => 'Egresado Multi Empleo',
        ]);
        $egrMultiEmpleo->assignRole('egresado');

        // Episodio 1 y 2 en Localidad A (San Sebastian)
        $ep1 = EpisodioLaboral::create([
            'user_id' => $egrMultiEmpleo->id,
            'es_actual' => true,
            'cargo' => 'Docente A',
            'institucion' => 'Colegio A',
        ]);
        $ep2 = EpisodioLaboral::create([
            'user_id' => $egrMultiEmpleo->id,
            'es_actual' => true,
            'cargo' => 'Docente B',
            'institucion' => 'Colegio B',
        ]);
        // Episodio 3 en Localidad B (Pisac)
        $ep3 = EpisodioLaboral::create([
            'user_id' => $egrMultiEmpleo->id,
            'es_actual' => true,
            'cargo' => 'Docente C',
            'institucion' => 'Colegio C',
        ]);

        $locA_Lat = -13.5250;
        $locA_Lng = -71.9300;
        $locB_Lat = -13.4200;
        $locB_Lng = -71.8500;

        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $egrMultiEmpleo->id,
            'registrado_por' => $egrMultiEmpleo->id,
            'confirmado_at' => now(),
            'datos' => json_encode([
                'residencia' => ['precision' => 'departamento'],
                'trabajos' => [
                    [
                        'episodio_id' => $ep1->id,
                        'lugar' => [
                            'localidad' => 'San Sebastian',
                            'distrito' => 'San Sebastian',
                            'precision' => 'localidad',
                            'latitud' => $locA_Lat,
                            'longitud' => $locA_Lng,
                        ]
                    ],
                    [
                        'episodio_id' => $ep2->id,
                        'lugar' => [
                            'localidad' => 'San Sebastian',
                            'distrito' => 'San Sebastian',
                            'precision' => 'localidad',
                            'latitud' => $locA_Lat,
                            'longitud' => $locA_Lng,
                        ]
                    ],
                    [
                        'episodio_id' => $ep3->id,
                        'lugar' => [
                            'localidad' => 'Pisac',
                            'distrito' => 'Pisac',
                            'precision' => 'localidad',
                            'latitud' => $locB_Lat,
                            'longitud' => $locB_Lng,
                        ]
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->coordinador)
            ->getJson(route('seguimiento.observatorio-conectado.datos', [
                'grupo_formacion' => 'TODOS',
                'tipo_ubicacion' => 'trabajo',
            ]));

        $response->assertStatus(200);
        $puntos = $response->json('puntos');

        // Encontrar punto de San Sebastian y Pisac
        $ptA = collect($puntos)->first(fn ($p) => $p['nombre'] === 'San Sebastian' && $p['tipo'] === 'trabajo');
        $ptB = collect($puntos)->first(fn ($p) => $p['nombre'] === 'Pisac' && $p['tipo'] === 'trabajo');

        $this->assertNotNull($ptA);
        $this->assertNotNull($ptB);
        // Cada localidad cuenta a la persona una sola vez (a pesar de tener 2 empleos en San Sebastian)
        $this->assertEquals(1, $ptA['n']);
        $this->assertEquals(1, $ptB['n']);
    }

    /**
     * Prueba 5: Persistencia de filtros entre Observatorio y Padrón.
     */
    public function test_05_persistencia_de_filtros_entre_observatorio_y_padron(): void
    {
        $response = $this->actingAs($this->coordinador)
            ->get(route('seguimiento.campanas.gestion', [
                'campana_id' => $this->campanaActiva->id,
                'grupo_formacion' => 'FID',
                'distrito' => 'Cusco',
                'estado_contacto' => 'sin_contacto_efectivo',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('filtros');
        $filtros = $response->viewData('filtros');

        $this->assertEquals('Cusco', $filtros['distrito']);
        $this->assertEquals('sin_contacto_efectivo', $filtros['estado_contacto']);
        $this->assertEquals('FID', $response->viewData('grupoFormacion'));
    }

    /**
     * Prueba 6: Protección de celdas pequeñas ($n < 5$) y guardia nominal.
     */
    public function test_06_proteccion_celdas_pequenas_y_guardia_nominal(): void
    {
        // 1. Puntos agregados con < 5 casos deben marcar suprimido = true
        $egrPoco = $this->crearUsuario([
            'name' => 'Egresado Zona Aislada',
        ]);
        $egrPoco->assignRole('egresado');

        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $egrPoco->id,
            'registrado_por' => $egrPoco->id,
            'confirmado_at' => now(),
            'datos' => json_encode([
                'residencia' => [
                    'localidad' => 'Zona Muy Lejana ' . rand(100, 999),
                    'distrito' => 'Distrito Aislado',
                    'precision' => 'localidad',
                    'latitud' => -13.1111,
                    'longitud' => -71.2222,
                ],
                'trabajos' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->coordinador)
            ->getJson(route('seguimiento.observatorio-conectado.datos', [
                'grupo_formacion' => 'TODOS',
            ]));

        $response->assertStatus(200);
        $ptAislado = collect($response->json('puntos'))->first(fn ($p) => $p['distrito'] === 'Distrito Aislado');
        $this->assertNotNull($ptAislado);
        $this->assertTrue($ptAislado['suprimido'], 'Celda con menos de 5 casos debe ser suprimida');
        $this->assertEquals('<5', $ptAislado['valor_seguro']);

        // 2. Guardia nominal: sesión no autorizada (egresado común) no puede acceder al padrón ni asignación
        $responseNonAuth = $this->actingAs($egrPoco)
            ->get(route('seguimiento.campanas.gestion'));
        $responseNonAuth->assertStatus(403);
    }

    /**
     * Prueba 7: Vista previa y asignación transaccional con concurrencia ACID (lockForUpdate).
     */
    public function test_07_vista_previa_y_asignacion_transaccional_acid(): void
    {
        $egresados = [];
        for ($i = 1; $i <= 4; $i++) {
            $u = $this->crearUsuario([
                'name' => "Egresado Asignable $i",
            ]);
            $u->assignRole('egresado');
            $egresados[] = $u;
        }

        $egrIds = collect($egresados)->pluck('id')->all();

        // 1. Preview
        $preview = $this->campanaService->asignarSeleccionados(
            $this->campanaActiva->id,
            [$this->docente->id],
            $egrIds,
            $this->coordinador->id,
            true, // esEnsayo
            true  // previewOnly
        );

        $this->assertEquals(4, $preview['asignados']);
        $this->assertEquals(0, $preview['ya_asignados']);

        // 2. Ejecución real transaccional
        $real = $this->campanaService->asignarSeleccionados(
            $this->campanaActiva->id,
            [$this->docente->id],
            $egrIds,
            $this->coordinador->id,
            true,
            false // commit real
        );

        $this->assertEquals(4, $real['asignados']);
        $this->assertEquals(0, $real['ya_asignados']);

        // 3. Reintento inmediato (idempotencia y concurrencia): ya deben estar asignados
        $reintento = $this->campanaService->asignarSeleccionados(
            $this->campanaActiva->id,
            [$this->docente->id],
            $egrIds,
            $this->coordinador->id,
            true,
            false
        );

        $this->assertEquals(0, $reintento['asignados']);
        $this->assertEquals(4, $reintento['ya_asignados']);

        // Verificar que no existen filas duplicadas en la base de datos
        $conteoTotal = AsignacionCampana::activos()->where('campana_id', $this->campanaActiva->id)
            ->whereIn('egresado_user_id', $egrIds)
            ->count();
        $this->assertEquals(4, $conteoTotal);
    }

    /**
     * Prueba 8: Piloto beta: enlace común, confirmación sin cambios y redirección a Red Puklla.
     */
    public function test_08_piloto_beta_enlace_comun_confirmar_sin_cambios_y_red_puklla(): void
    {
        $egrPiloto = $this->crearUsuario([
            'name' => 'Egresado Beta Piloto',
        ]);
        $egrPiloto->assignRole('egresado');

        // 1. Visitar enlace común del piloto sin autenticación -> redirige a login preservando intended
        $guestResp = $this->get('/seguimiento/piloto/PUKLLA-BETA-PILOTO');
        $guestResp->assertRedirect('/login');

        // 2. Visitar autenticado -> abre Mi Espacio con el banner del piloto activo
        $authResp = $this->actingAs($egrPiloto)->get('/seguimiento/piloto/PUKLLA-BETA-PILOTO');
        $authResp->assertStatus(200);
        $authResp->assertViewIs('seguimiento_egresados.mi_espacio');
        $authResp->assertViewHas('esPiloto', true);
        $authResp->assertViewHas('codigoPiloto', 'PUKLLA-BETA-PILOTO');
        $this->assertTrue(session('es_piloto_beta'));
        $this->assertEquals('PUKLLA-BETA-PILOTO', session('codigo_piloto'));

        // 3. Confirmar sin cambios
        $postResp = $this->actingAs($egrPiloto)->postJson(route('seguimiento.mi-espacio.confirmar-sin-cambios'), [
            'codigo_piloto' => 'PUKLLA-BETA-PILOTO',
        ]);

        $postResp->assertStatus(200);
        $postResp->assertJson(['success' => true]);

        // Verificar que se registró la actualización ligada al piloto
        $act = Actualizacion::where('user_id', $egrPiloto->id)
            ->where('es_confirmacion_sin_cambios', true)
            ->first();
        $this->assertNotNull($act);
        $this->assertEquals('PUKLLA-BETA-PILOTO', $act->datos_extra['codigo_piloto'] ?? null);

        // 4. Red Puklla habilitada
        $redResp = $this->actingAs($egrPiloto)->get(route('red.inicio'));
        $redResp->assertStatus(200);
    }

    /**
     * Prueba 9: Campaña vencida y participante no elegible -> rechazo por el servidor.
     */
    public function test_09_campana_vencida_y_participante_no_elegible_rechazo_servidor(): void
    {
        // Campaña cerrada
        $egr = $this->crearUsuario();
        $egr->assignRole('egresado');

        $respCerrada = $this->actingAs($this->coordinador)->postJson(route('seguimiento.campanas.asignar-seleccionados'), [
            'campana_id' => $this->campanaAnterior->id,
            'docentes' => [$this->docente->id],
            'egresados' => [$egr->id],
        ]);
        $respCerrada->assertStatus(422);

        // Intento de acceso a entrevista por un docente no asignado
        $docenteNoAsignado = $this->crearUsuario(['condicion' => 'Docente']);
        $docenteNoAsignado->assignRole('docente');

        $respNoAsig = $this->actingAs($docenteNoAsignado)->get(route('seguimiento.panel.entrevista', ['egresado' => $egr->id]));
        $respNoAsig->assertStatus(403);
    }

    /**
     * Prueba 10: Fuente Encuesta 2026: 113 vínculos y 112 personas confirmadas contra conciliación local.
     */
    public function test_10_fuente_encuesta_2026_113_vinculos_y_112_personas(): void
    {
        $analitica = app(SeguimientoAnaliticaService::class);
        $uni = $analitica->calcularUniversos([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'TODOS',
        ]);

        $this->assertEquals(113, $uni['registros_vinculados']);
        $this->assertEquals(112, $uni['personas_unicas']);
        $this->assertEquals(1, $uni['diferencia_registros_personas']);
        $this->assertCount(1, $uni['duplicados_fuente']);
    }
}
