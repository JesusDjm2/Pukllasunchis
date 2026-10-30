<?php

namespace Tests\Feature;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\SeguimientoEgresado\InvitacionCampana;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Suite de Pruebas de Seguridad, Integridad Longitudinal y Anti-Suplantación — Fase 1C
 * 
 * Requisitos verificados:
 * 1. Autenticación y autorización en servidor (401/403 estricto, Anti-IDOR).
 * 2. Invitaciones criptográficas seguras WhatsApp y cuarentena anti-suplantación en accesos públicos.
 * 3. Historia longitudinal multicampaña sin sobreescritura destructiva.
 * 4. Integridad de episodios laborales (cero fechas now() inventadas, no inferencia de campos ausentes).
 */
class SeguimientoEgresadosFase1CTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'egresado']);
        Role::firstOrCreate(['name' => 'alumno']);
        Role::firstOrCreate(['name' => 'encuestador-seguimiento']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'docente']);
    }

    protected function crearUsuarioSintetico(string $condicion = 'Egresado', ?string $role = 'egresado'): User
    {
        $user = User::factory()->create([
            'name' => 'Docente Prueba ' . rand(100, 999),
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
     * Test 1: Las rutas sensibles de intranet rechazan accesos anónimos sin sesión (401 o redirección).
     */
    public function test_01_rutas_intranet_requieren_autenticacion(): void
    {
        $egresado = $this->crearUsuarioSintetico();

        // Episodios
        $this->getJson("/seguimiento-egresados/episodios/{$egresado->id}")
            ->assertStatus(401);

        // Cierre de episodio
        $this->postJson("/seguimiento-egresados/episodios/1/cerrar")
            ->assertStatus(401);

        // Acciones / Casos
        $this->postJson("/seguimiento-egresados/accion", ['tema' => 'Prueba'])
            ->assertStatus(401);

        // Grupos de entrevistadores
        $this->postJson("/seguimiento-egresados/grupos", ['nombre' => 'Equipo Test'])
            ->assertStatus(401);

        // Llamadas
        $this->postJson("/seguimiento-egresados/llamada", ['id' => $egresado->id])
            ->assertStatus(401);

        // Generar enlace WhatsApp
        $this->getJson("/seguimiento-egresados/enlace-whatsapp?user_id={$egresado->id}")
            ->assertStatus(401);
    }

    /**
     * Test 2: Control de autorización Anti-IDOR para egresados (403 Forbidden).
     */
    public function test_02_egresado_no_puede_consultar_ni_cerrar_episodios_de_otros(): void
    {
        $egresadoA = $this->crearUsuarioSintetico('Egresado', 'egresado');
        $egresadoB = $this->crearUsuarioSintetico('Egresado', 'egresado');

        $episodioB = EpisodioLaboral::create([
            'user_id' => $egresadoB->id,
            'institucion' => 'I.E. Privada San Pedro',
            'cargo' => 'Docente de Primaria',
            'es_actual' => true,
        ]);

        // A intenta consultar episodios de B -> 403 Forbidden
        $this->actingAs($egresadoA)
            ->getJson("/seguimiento-egresados/episodios/{$egresadoB->id}")
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // A intenta cerrar el episodio de B -> 403 Forbidden
        $this->actingAs($egresadoA)
            ->postJson("/seguimiento-egresados/episodios/{$episodioB->id}/cerrar", [
                'motivo_cambio' => 'Intento malicioso'
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // A sí puede consultar sus propios episodios
        $this->actingAs($egresadoA)
            ->getJson("/seguimiento-egresados/episodios/{$egresadoA->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test 3: Encuestador temporal no asignado recibe 403 al intentar acceder a egresado ajeno.
     */
    public function test_03_encuestador_no_asignado_recibe_403(): void
    {
        $encuestador = $this->crearUsuarioSintetico('Docente', 'encuestador-seguimiento');
        $egresadoNoAsignado = $this->crearUsuarioSintetico('Egresado', 'egresado');

        // Intento de ver episodios de un egresado fuera de su grupo
        $this->actingAs($encuestador)
            ->getJson("/seguimiento-egresados/episodios/{$egresadoNoAsignado->id}")
            ->assertStatus(403);

        // Intento de registrar llamada para un egresado no asignado
        $this->actingAs($encuestador)
            ->postJson("/seguimiento-egresados/llamada", [
                'id' => 'REAL-' . $egresadoNoAsignado->id,
                'result' => 'Contactado',
            ])
            ->assertStatus(403);
    }

    /**
     * Test 4: Gestión de grupos y avance de casos restringido exclusivamente a coordinación y directivos (403).
     */
    public function test_04_egresados_y_encuestadores_no_pueden_gestionar_grupos_ni_avanzar_casos(): void
    {
        $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');
        $encuestador = $this->crearUsuarioSintetico('Docente', 'encuestador-seguimiento');
        $coordinador = $this->crearUsuarioSintetico('Docente', 'coordinacion-empleabilidad');

        $caso = Caso::create([
            'codigo' => 'CASO-TEST-' . rand(100, 999),
            'user_id' => $egresado->id,
            'nombre_egresado' => $egresado->name,
            'tema' => 'Acompañamiento pedagógico',
            'responsable' => 'Coordinación',
            'estado' => 'Por revisar'
        ]);

        // Egresado intentando crear grupo -> 403
        $this->actingAs($egresado)
            ->postJson("/seguimiento-egresados/grupos", ['nombre' => 'Grupo Falso'])
            ->assertStatus(403);

        // Encuestador intentando avanzar estado de caso -> 403
        $this->actingAs($encuestador)
            ->postJson("/seguimiento-egresados/avanzar-accion/{$caso->id}")
            ->assertStatus(403);

        // Coordinador sí puede avanzar estado de caso
        $this->actingAs($coordinador)
            ->postJson("/seguimiento-egresados/avanzar-accion/{$caso->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true, 'nuevo_estado' => 'En atención']);
    }

    /**
     * Test 5: Anti-suplantación en campaña pública abierta (Cuarentena segura).
     * Un atacante anónimo con el DNI de un egresado real NO modifica su ficha verificada ni sus episodios activos.
     */
    public function test_05_intento_publico_sin_token_no_adultera_ficha_verificada(): void
    {
        $egresadoReal = $this->crearUsuarioSintetico('Egresado', 'egresado');

        // El egresado real tiene un empleo activo legítimo
        $episodioOriginal = EpisodioLaboral::create([
            'user_id' => $egresadoReal->id,
            'institucion' => 'I.E. 50012 Legítima',
            'cargo' => 'Docente Titular',
            'es_actual' => true,
            'es_empleo_educativo' => true,
        ]);

        // Intento anónimo desde la web pública usando el DNI del egresado real pero SIN token
        $response = $this->postJson(route('seguimiento-egresados.actualizar'), [
            'dni' => $egresadoReal->dni,
            'nombre' => 'Intruso Anónimo',
            'trabaja' => 'No trabaja',
            'cerrar_activos' => true,
            'institucion' => 'Empresa Inventada',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tipo' => 'pendiente_verificacion'
            ]);

        // VERIFICACIÓN CRÍTICA DE SEGURIDAD:
        // 1. El episodio laboral legítimo NO fue cerrado ni modificado
        $episodioActual = EpisodioLaboral::find($episodioOriginal->id);
        $this->assertTrue($episodioActual->es_actual, 'El empleo legítimo no debe cerrarse por un envío sin token.');
        $this->assertEquals('I.E. 50012 Legítima', $episodioActual->institucion);

        // 2. No se asoció la actualización no verificada directamente al user_id del egresado
        $actualizacionesEgresado = Actualizacion::where('user_id', $egresadoReal->id)->count();
        $this->assertEquals(0, $actualizacionesEgresado, 'No se deben crear registros verificados directos sin token.');

        // 3. Se aisló en la cola de verificación con tipo_registro = solicitud_sin_token
        $actualizacionCuarentena = Actualizacion::where('estado_verificacion', 'pendiente_verificacion')->latest()->first();
        $this->assertNotNull($actualizacionCuarentena);
        $this->assertEquals('publico_whatsapp_no_verificado', $actualizacionCuarentena->procedencia);
        $this->assertEquals('solicitud_sin_token', $actualizacionCuarentena->tipo_registro);

        // 4. Se abrió un caso de verificación institucional
        $caso = Caso::where('user_id', $egresadoReal->id)
            ->where('tema', 'Verificación de actualización pública sin credencial')
            ->first();
        $this->assertNotNull($caso, 'Debe crearse un caso para que coordinación verifique telefónicamente.');
    }

    /**
     * Test 6: Invitación legítima por WhatsApp con token criptográfico (cero PII en URL y verificación segura).
     */
    public function test_06_invitacion_criptografica_whatsapp_actualiza_con_verificacion(): void
    {
        $coordinador = $this->crearUsuarioSintetico('Docente', 'coordinacion-empleabilidad');
        $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');

        // Coordinador genera enlace seguro de WhatsApp para este egresado
        $respGen = $this->actingAs($coordinador)
            ->getJson("/seguimiento-egresados/enlace-whatsapp?user_id={$egresado->id}&campana=2026-II");

        $respGen->assertStatus(200)
            ->assertJson(['success' => true]);

        $tokenGenerado = $respGen->json('token');
        $urlGenerada = $respGen->json('url');

        // SEGURIDAD: La URL NO debe contener el DNI ni el ID del egresado
        $this->assertStringNotContainsString($egresado->dni, $urlGenerada);
        $this->assertStringNotContainsString((string)$egresado->id, $urlGenerada);
        $this->assertStringContainsString('token=' . $tokenGenerado, $urlGenerada);

        // Simular que el egresado abre el link de WhatsApp desde su teléfono sin sesión activa
        \Illuminate\Support\Facades\Auth::logout();

        // El egresado abre el link y envía su confirmación utilizando el token
        $respEnvio = $this->postJson(route('seguimiento-egresados.actualizar'), [
            'token_invitacion' => $tokenGenerado,
            'nombre' => $egresado->name,
            'sin_cambios' => true,
            'campana' => '2026-II',
        ]);

        $respEnvio->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tipo' => 'confirmacion_sin_cambios'
            ]);

        // El registro se consolida como VERIFICADO
        $actualizacion = Actualizacion::where('user_id', $egresado->id)
            ->where('periodo_campana', '2026-II')
            ->first();

        $this->assertNotNull($actualizacion);
        $this->assertEquals('verificado', $actualizacion->estado_verificacion);
        $this->assertEquals('publico_whatsapp_invitacion', $actualizacion->procedencia);
        $this->assertEquals($tokenGenerado, $actualizacion->token_invitacion);

        // El token queda marcado como usado
        $invitacionDb = InvitacionCampana::where('token', $tokenGenerado)->first();
        $this->assertTrue($invitacionDb->usado);
        $this->assertNotNull($invitacionDb->fecha_uso);

        // Un intento posterior con el mismo token ya consumido es rechazado
        $this->postJson(route('seguimiento-egresados.actualizar'), [
            'token_invitacion' => $tokenGenerado,
            'nombre' => 'Reintento con token usado',
            'trabaja' => 'Trabaja',
        ])->assertStatus(422)
          ->assertJson(['success' => false]);
    }

    /**
     * Test 7: Historia longitudinal multicampaña sin sobreescritura destructiva.
     * Las respuestas sucesivas a campañas semestrales (2025-II, 2026-I, 2026-II) se conservan intactas.
     */
    public function test_07_preservacion_multicampana_sin_sobrescritura(): void
    {
        $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');

        // Campaña 1: 2025-II
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'campana' => '2025-II',
            'trabaja' => 'Trabaja',
            'institucion' => 'I.E. Inicial 101',
            'cargo' => 'Docente Auxiliar',
        ])->assertStatus(200);

        // Campaña 2: 2026-I
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'campana' => '2026-I',
            'sin_cambios' => true,
        ])->assertStatus(200);

        // Campaña 3: 2026-II
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'campana' => '2026-II',
            'trabaja' => 'Trabaja',
            'institucion' => 'I.E. Primaria 50020',
            'cargo' => 'Docente de Aula',
        ])->assertStatus(200);

        // Se deben conservar 3 registros históricos independientes para este egresado
        $actualizaciones = Actualizacion::where('user_id', $egresado->id)->get();
        $this->assertCount(3, $actualizaciones, 'Cada campaña semestral debe mantener su instantánea histórica.');

        $campanasGuardadas = $actualizaciones->pluck('periodo_campana')->toArray();
        $this->assertContains('2025-II', $campanasGuardadas);
        $this->assertContains('2026-I', $campanasGuardadas);
        $this->assertContains('2026-II', $campanasGuardadas);

        // Reenvío en la misma campaña (2026-II) actualiza sin duplicar
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'campana' => '2026-II',
            'sin_cambios' => true,
        ])->assertStatus(200);

        $this->assertEquals(3, Actualizacion::where('user_id', $egresado->id)->count(), 'Reenvío dentro de la misma campaña debe actualizar la fila sin duplicarla.');
    }

    /**
     * Test 8: Cero invención de fechas now() y no inferencia de campos laborales no declarados.
     */
    public function test_08_no_inferencia_de_fechas_now_ni_atributos_ausentes(): void
    {
        $egresado = $this->crearUsuarioSintetico('Egresado', 'egresado');

        // Envío con fecha de inicio vacía y sin especificar campo educativo
        $this->actingAs($egresado)->postJson(route('seguimiento-egresados.actualizar'), [
            'trabaja' => 'Trabaja',
            'institucion' => 'Consultoría Andina S.A.C.',
            'cargo' => 'Asistente de Proyectos',
            'inicio_empleo' => null, // Fecha de inicio desconocida / no informada
            'campo' => 'Servicios generales', // No educativo
            'tipo_practica' => 'Gestión administrativa', // No docencia directa
            'contrato' => null, // Régimen no declarado
        ])->assertStatus(200);

        $episodio = EpisodioLaboral::where('user_id', $egresado->id)->first();
        $this->assertNotNull($episodio);

        // INTEGRIDAD: La fecha de inicio debe ser NULL, NUNCA today() o now()
        $this->assertNull($episodio->fecha_inicio, 'No se debe inventar la fecha de inicio con now() si no fue declarada.');

        // NO INFERENCIA: No debe marcarse falsamente como docencia directa ni empleo educativo
        $this->assertFalse((bool)$episodio->es_docencia_directa, 'No se debe inferir docencia directa sin datos explícitos.');
        $this->assertFalse((bool)$episodio->es_empleo_educativo, 'No se debe inferir empleo educativo en rubros no pedagógicos.');
        $this->assertNull($episodio->contrato, 'No se debe inferir contrato estable si no fue especificado.');
    }
}
