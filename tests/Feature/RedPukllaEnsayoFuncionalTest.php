<?php

namespace Tests\Feature;

use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\BolsaTrabajoOferta;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\User;
use App\Notifications\RedPukllaAviso;
use App\Services\SeguimientoEgresado\GrupoFormacionService;
use Database\Seeders\RedPukllaEnsayoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RedPukllaEnsayoFuncionalTest extends TestCase
{
    use DatabaseTransactions;

    private bool $cambioBaseAislada = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Garantizar que este ensayo funcional corra siempre sobre la base aislada puklla_ensayo
        if (DB::connection()->getDatabaseName() !== 'puklla_ensayo') {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', 'puklla_ensayo');
            DB::reconnect('mysql');
            DB::beginTransaction();
            $this->cambioBaseAislada = true;
        }

        Config::set('app.red_puklla_demo_seed', true);
        putenv('RED_PUKLLA_DEMO_SEED=true');
        $_ENV['RED_PUKLLA_DEMO_SEED'] = 'true';

        if (!User::where('email', 'superadmin.demo@example.test')->exists()) {
            $this->seed(RedPukllaEnsayoSeeder::class);
        }
    }

    protected function tearDown(): void
    {
        if ($this->cambioBaseAislada) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', env('DB_DATABASE', 'puklla'));
            DB::reconnect('mysql');
        }

        parent::tearDown();
    }

    private function cuenta(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_candados_de_seguridad_del_seeder_e_idempotencia(): void
    {
        $conteoUsuariosAntes = User::count();
        $conteoCampanasAntes = Campana::count();
        $conteoAyllusAntes = AylluGrupo::deRedPuklla()->count();
        $conteoPublicacionesAntes = AylluPublicacion::deRedPuklla()->count();
        $conteoOfertasAntes = BolsaTrabajoOferta::withoutGlobalScopes()->count();

        // 1. Re-ejecutar el seeder no duplica ningún registro
        $this->seed(RedPukllaEnsayoSeeder::class);

        $this->assertSame($conteoUsuariosAntes, User::count());
        $this->assertSame($conteoCampanasAntes, Campana::count());
        $this->assertSame($conteoAyllusAntes, AylluGrupo::deRedPuklla()->count());
        $this->assertSame($conteoPublicacionesAntes, AylluPublicacion::deRedPuklla()->count());
        $this->assertSame($conteoOfertasAntes, BolsaTrabajoOferta::withoutGlobalScopes()->count());

        // 2. Desactivar bandera explícita rechaza la ejecución
        Config::set('app.red_puklla_demo_seed', false);
        putenv('RED_PUKLLA_DEMO_SEED=false');
        $_ENV['RED_PUKLLA_DEMO_SEED'] = 'false';

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('RED_PUKLLA_DEMO_SEED=true');
            (new RedPukllaEnsayoSeeder())->run();
        } finally {
            Config::set('app.red_puklla_demo_seed', true);
            putenv('RED_PUKLLA_DEMO_SEED=true');
            $_ENV['RED_PUKLLA_DEMO_SEED'] = 'true';
        }
    }

    public function test_clasificacion_institucional_fid_ppd_dual_y_por_verificar(): void
    {
        $servicio = app(GrupoFormacionService::class);

        // 12 FID + 1 facilitador FID = 13 personas con grupo FID
        for ($i = 1; $i <= 12; $i++) {
            $uFid = $this->cuenta(sprintf('egresado.fid.%02d@example.test', $i));
            $uFid->load(GrupoFormacionService::RELACIONES);
            $res = $servicio->resolver($uFid);
            $this->assertSame(['FID'], $res['grupos'], "FID #{$i} debe resolverse como FID por evidencia institucional");
            $this->assertSame('FID', $res['etiqueta']);
        }

        // 12 PPD
        for ($i = 1; $i <= 12; $i++) {
            $uPpd = $this->cuenta(sprintf('egresado.ppd.%02d@example.test', $i));
            $uPpd->load(GrupoFormacionService::RELACIONES);
            $res = $servicio->resolver($uPpd);
            $this->assertSame(['PPD'], $res['grupos'], "PPD #{$i} debe resolverse como PPD por evidencia institucional");
            $this->assertSame('PPD', $res['etiqueta']);
        }

        // Persona con egresos confirmados en ambos grupos (FID y PPD)
        $uDual = $this->cuenta('egresado.dual.fidppd@example.test');
        $uDual->load(GrupoFormacionService::RELACIONES);
        $resDual = $servicio->resolver($uDual);
        $this->assertSame(['FID', 'PPD'], $resDual['grupos']);
        $this->assertSame('FID y PPD', $resDual['etiqueta']);
        $this->assertSame('trayectoria_evento_egreso', $resDual['fuente']);

        // Persona con grupo por verificar (conflicto de fichas sin evento de egreso)
        $uPorVerificar = $this->cuenta('egresado.porverificar@example.test');
        $uPorVerificar->load(GrupoFormacionService::RELACIONES);
        $resPorVer = $servicio->resolver($uPorVerificar);
        $this->assertSame([], $resPorVer['grupos']);
        $this->assertTrue($resPorVer['por_verificar']);
        $this->assertTrue($resPorVer['conflicto_fichas']);
        $this->assertSame('Por verificar', $resPorVer['etiqueta']);
    }

    public function test_menu_lateral_y_conservacion_de_funciones_institucionales_por_cuenta(): void
    {
        $docenteAula = $this->cuenta('docente.aula@example.test');
        $docenteCoord = $this->cuenta('docente.coordinador@example.test');
        $docenteEntrev = $this->cuenta('docente.entrevistador@example.test');
        $docenteCoordEntrev = $this->cuenta('docente.coord.entrev@example.test');
        $estudianteActivo = $this->cuenta('estudiante.activo@example.test');

        // Docente de aula sin funciones de seguimiento conserva Mis cursos, entra a Red Puklla, pero NO ve Coordinación ni Moderación
        $this->actingAs($docenteAula)->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Mis cursos')
            ->assertSee('Red Puklla de Egresados')
            ->assertDontSee('Coordinación de egresados')
            ->assertDontSee('href="' . route('red.moderacion') . '"', false);

        // Docente entrevistador ve Mis cursos, Red Puklla y Mis entrevistas, pero NO Coordinación de egresados ni Moderación general
        $this->actingAs($docenteEntrev)->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Mis cursos')
            ->assertSee('Mis entrevistas')
            ->assertDontSee('Coordinación de egresados')
            ->assertDontSee('href="' . route('red.moderacion') . '"', false);

        // Docente coordinador y entrevistador simultáneamente ve tanto Coordinación de egresados como Mis entrevistas y Moderación
        $this->actingAs($docenteCoordEntrev)->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Mis cursos')
            ->assertSee('Coordinación de egresados')
            ->assertSee('Mis entrevistas')
            ->assertSee('href="' . route('red.moderacion') . '"', false);

        // Docente coordinador accede al panel de coordinación
        $this->actingAs($docenteCoord)->get(route('seguimiento.panel.coordinacion'))
            ->assertOk()
            ->assertSee('Coordinación de egresados');

        // Estudiante activo es rechazado (403) al intentar ingresar a Red Puklla o paneles de seguimiento
        $this->actingAs($estudianteActivo)->get(route('red.inicio'))->assertForbidden();
        $this->actingAs($estudianteActivo)->get(route('seguimiento.mi-espacio'))->assertForbidden();
        $this->actingAs($estudianteActivo)->get(route('seguimiento.panel.coordinacion'))->assertForbidden();
    }

    public function test_primera_confirmacion_personal_y_perfil_actualizado_por_entrevistador(): void
    {
        $egSinConfirmar = $this->cuenta('egresado.fid.12@example.test');
        $egActualizadoPorEntrevistador = $this->cuenta('egresado.fid.11@example.test');

        // 1. Egresado sin ninguna confirmación es redirigido a Mi espacio al abrir Red Puklla
        $this->actingAs($egSinConfirmar)->get(route('red.inicio'))
            ->assertRedirect(route('seguimiento.mi-espacio'));

        // 2. Egresado cuyo perfil fue actualizado por entrevistador (registrado_por != user_id)
        //    también debe confirmar personalmente sus datos principales en su primera visita a la comunidad
        $this->actingAs($egActualizadoPorEntrevistador)->get(route('red.inicio'))
            ->assertRedirect(route('seguimiento.mi-espacio'));

        // En Mi espacio ve los datos precargados por la entrevista y puede confirmarlos
        $this->actingAs($egActualizadoPorEntrevistador)->get(route('seguimiento.mi-espacio'))
            ->assertOk()
            ->assertSee('IE Rural Ocongate');

        $this->actingAs($egActualizadoPorEntrevistador)->postJson(route('seguimiento.mi-espacio.guardar'), [
            'contacto' => [
                'programa' => 'Educación Primaria EIB',
                'egreso' => 2024,
                'correo' => $egActualizadoPorEntrevistador->email,
            ],
        ])->assertOk();

        // Tras confirmar personalmente su núcleo, ya ingresa a Red Puklla
        $this->actingAs($egActualizadoPorEntrevistador)->get(route('red.inicio'))
            ->assertOk()
            ->assertSee('Red Puklla de Egresados');
    }

    public function test_campanas_asignaciones_vencimiento_reasignacion_contacto_alterno_y_casos(): void
    {
        $docenteAula = $this->cuenta('docente.aula@example.test');
        $entrev = $this->cuenta('docente.entrevistador@example.test');
        $coordEntrev = $this->cuenta('docente.coord.entrev@example.test');
        $bienestar = $this->cuenta('bienestar.demo@example.test');

        $campanaVigente = Campana::where('codigo', 'CAMP-DEMO-2026-II')->firstOrFail();
        $campanaFinalizada = Campana::where('codigo', 'CAMP-DEMO-2026-I')->firstOrFail();
        $campanaFutura = Campana::where('codigo', 'CAMP-DEMO-2027-I')->firstOrFail();

        $fid01 = $this->cuenta('egresado.fid.01@example.test'); // Asignado a $entrev con contacto alterno autorizado
        $fid02 = $this->cuenta('egresado.fid.02@example.test'); // Asignado a $entrev con contacto alterno NO autorizado
        $fid05 = $this->cuenta('egresado.fid.05@example.test'); // Reasignado de $entrev a $coordEntrev
        $fid10 = $this->cuenta('egresado.fid.10@example.test'); // Asignado solo en campaña futura
        $ppd10 = $this->cuenta('egresado.ppd.10@example.test'); // Asignado solo en campaña vencida/cerrada

        // 1. Docente sin asignaciones recibe 403 en Mis asignaciones y en fichas de entrevista
        $this->actingAs($docenteAula)->get(route('seguimiento-egresados.mis-asignaciones'))->assertForbidden();
        $this->actingAs($docenteAula)->get(route('seguimiento.panel.entrevista', ['egresado' => $fid01->id, 'campana_id' => $campanaVigente->id]))->assertForbidden();

        // 2. Entrevistador consulta su lista de trabajo: ve el contacto alterno autorizado de fid01,
        //    pero NO ve el contacto alterno no autorizado de fid02 ni el contacto de emergencia (984190002)
        $resLista = $this->actingAs($entrev)->get(route('seguimiento-egresados.mis-asignaciones'));
        $resLista->assertOk()
            ->assertSee('Hermana Autorizada Mamani')
            ->assertSee('984111222')
            ->assertDontSee('Familiar No Autorizado Huallpa')
            ->assertDontSee('984999888')
            ->assertDontSee('984190002');

        // 3. Pérdida de acceso tras reasignación (fid05), vencimiento de campaña (ppd10) o campaña futura (fid10)
        $this->actingAs($entrev)->get(route('seguimiento.panel.entrevista', [
            'egresado' => $fid05->id,
            'campana_id' => $campanaVigente->id,
        ]))->assertForbidden();

        $this->actingAs($entrev)->postJson(route('seguimiento-egresados.llamada'), [
            'id' => $fid05->id,
            'campana_id' => $campanaVigente->id,
            'tipo_resultado' => 'intento_sin_respuesta',
            'comment' => 'Intento tras haber sido reasignado.',
        ])->assertForbidden();

        $this->actingAs($entrev)->get(route('seguimiento.panel.entrevista', [
            'egresado' => $ppd10->id,
            'campana_id' => $campanaFinalizada->id,
        ]))->assertForbidden();

        $this->actingAs($entrev)->get(route('seguimiento.panel.entrevista', [
            'egresado' => $fid10->id,
            'campana_id' => $campanaFutura->id,
        ]))->assertForbidden();

        // Mientras tanto, la nueva docente asignada ($coordEntrev) sí accede a fid05 y el historial previo sigue intacto
        $this->actingAs($coordEntrev)->get(route('seguimiento.panel.entrevista', [
            'egresado' => $fid05->id,
            'campana_id' => $campanaVigente->id,
        ]))->assertOk();
        $this->assertSame(2, AsignacionCampana::where('campana_id', $campanaVigente->id)->where('egresado_user_id', $fid05->id)->count());

        // 4. Segregación de notas confidenciales de bienestar
        $casoBienestar = Caso::where('codigo', 'CASO-DEMO-BIENESTAR-02')->firstOrFail();
        $this->assertSame(
            '[Información reservada de bienestar institucional]',
            $casoBienestar->getPropositoVisible($entrev)
        );
        $this->assertStringContainsString(
            'NOTA_CONFIDENCIAL_BIENESTAR_DEMO',
            $casoBienestar->getPropositoVisible($bienestar)
        );
    }

    public function test_filtros_fid_ppd_comparar_por_verificar_e_informe_congelado_de_campana(): void
    {
        $coord = $this->cuenta('docente.coordinador@example.test');

        // 1. Filtros en Red Puklla (inicio/comunidad)
        foreach (['TODOS', 'FID', 'PPD', 'COMPARAR', 'POR_VERIFICAR'] as $filtro) {
            $this->actingAs($coord)->get(route('red.inicio', ['grupo_formacion' => $filtro]))->assertOk();
            $this->actingAs($coord)->get(route('seguimiento.panel.coordinacion', ['grupo_formacion' => $filtro]))->assertOk();
            $this->actingAs($coord)->get(route('seguimiento.laboratorio', ['grupo_formacion' => $filtro]))->assertOk();
        }

        // Separación y combinación FID / PPD / COMPARAR en Red Puklla:
        // - Filtro FID incluye autores FID y dual (FID+PPD), pero excluye autores puramente PPD y docentes
        $this->actingAs($coord)->get(route('red.inicio', ['grupo_formacion' => 'FID']))
            ->assertOk()
            ->assertSee('Juegos tradicionales andinos para matemática intercultural (Pukllay)')
            ->assertSee('Cocreación de cancionero bilingüe para ciclo III y IV (Ruray)')
            ->assertDontSee('¿Cómo acompañamos la transición de primaria EIB a secundaria rural? (Yuyay)')
            ->assertDontSee('Encuentro presencial de egresados EIB en Sicuani este sábado (Rimay)');

        // - Filtro PPD incluye autores PPD y dual (FID+PPD), pero excluye autores puramente FID
        $this->actingAs($coord)->get(route('red.inicio', ['grupo_formacion' => 'PPD']))
            ->assertOk()
            ->assertSee('¿Cómo acompañamos la transición de primaria EIB a secundaria rural? (Yuyay)')
            ->assertSee('Cocreación de cancionero bilingüe para ciclo III y IV (Ruray)')
            ->assertDontSee('Juegos tradicionales andinos para matemática intercultural (Pukllay)');

        // - Filtro COMPARAR une FID y PPD juntos (excluyendo publicaciones de docentes sin grupo de formación de egreso)
        $this->actingAs($coord)->get(route('red.inicio', ['grupo_formacion' => 'COMPARAR']))
            ->assertOk()
            ->assertSee('Cocreación de cancionero bilingüe para ciclo III y IV (Ruray)')
            ->assertSee('Juegos tradicionales andinos para matemática intercultural (Pukllay)')
            ->assertSee('¿Cómo acompañamos la transición de primaria EIB a secundaria rural? (Yuyay)')
            ->assertDontSee('Encuentro presencial de egresados EIB en Sicuani este sábado (Rimay)');

        // 2. Observatorio conectado excluye coordenadas sintéticas (declarada_pendiente) de los puntos del mapa oficial
        $resObs = $this->actingAs($coord)->getJson(route('seguimiento.observatorio-conectado.datos', ['grupo_formacion' => 'TODOS']));
        $resObs->assertOk();
        $jsonObs = $resObs->json();
        $this->assertSame([], $jsonObs['puntos'], 'Las coordenadas sintéticas de demostración no deben graficarse como puntos oficiales verificados');
        $this->assertSame(1, $jsonObs['resumen_grupos']['con_ambos_egresos']);
        $this->assertSame(1, $jsonObs['resumen_grupos']['por_grupo']['POR_VERIFICAR']);

        // 3. Informe congelado de la campaña finalizada y cierre en vivo de la campaña activa
        $corte = DB::table('egresado_campana_cortes')->first();
        $this->assertNotNull($corte);
        $this->actingAs($coord)->get(route('seguimiento.panel.informe', ['corte' => $corte->id]))
            ->assertOk()
            ->assertSee('Campaña de Seguimiento Egresados 2026-I (Finalizada)')
            ->assertDontSee('egresado.fid.01@example.test');

        $campanaVigente = Campana::where('codigo', 'CAMP-DEMO-2026-II')->firstOrFail();
        $resCierre = $this->actingAs($coord)->post(route('seguimiento.panel.cerrar', ['campana' => $campanaVigente->id]), [
            'balance' => 'Cierre de prueba funcional con métricas separadas FID/PPD.',
            'horas' => 36,
            'costo' => 900,
            'presupuesto_siguiente' => 1100,
        ]);
        $resCierre->assertRedirect();
        $this->assertSame('cerrada', $campanaVigente->fresh()->estado);
    }

    public function test_comunidad_privacidad_de_ayllus_difusion_sin_duplicacion_y_separacion_de_bolsa_publica(): void
    {
        $facilitador = $this->cuenta('facilitador.ayllu@example.test');
        $miembroFid1 = $this->cuenta('egresado.fid.01@example.test');
        $pendientePpd5 = $this->cuenta('egresado.ppd.05@example.test');

        $aylluPrivado = AylluGrupo::deRedPuklla()->where('slug', 'taller-materiales-quechua-demo')->firstOrFail();
        $pubPrivada = AylluPublicacion::deRedPuklla()->where('titulo', 'Cocreación de cancionero bilingüe para ciclo III y IV (Ruray)')->firstOrFail();
        $pubDifundida = AylluPublicacion::deRedPuklla()->where('titulo', 'Relatos del agua y alfabetización inicial en quechua (Yachay)')->firstOrFail();

        // 1. Solicitud pendiente en ayllu privado NO puede leer publicaciones privadas ni enviar chat
        $this->actingAs($pendientePpd5)->get(route('red.publicacion', ['id' => $pubPrivada->id]))->assertNotFound();
        $this->actingAs($pendientePpd5)->post(route('red.mensaje', ['id' => $aylluPrivado->id]), [
            'mensaje' => 'Intento con membresía aún pendiente.',
        ])->assertForbidden();

        // 2. Facilitador acepta la solicitud pendiente -> ahora sí accede y participa
        $this->actingAs($facilitador)->post(route('red.membresia', ['id' => $aylluPrivado->id]), [
            'accion' => 'aceptar',
            'user_id' => $pendientePpd5->id,
        ])->assertRedirect();

        $this->actingAs($pendientePpd5)->get(route('red.publicacion', ['id' => $pubPrivada->id]))->assertOk();

        // 3. La difusión en múltiples ayllus mantiene una única fila en ayllu_publicaciones (sin duplicar)
        $this->assertSame(
            1,
            AylluPublicacion::deRedPuklla()->where('titulo', $pubDifundida->titulo)->count()
        );
        $this->assertSame(
            2,
            DB::table('red_puklla_difusiones')->where('publicacion_id', $pubDifundida->id)->count()
        );

        // 4. Separación entre oportunidades de Red Puklla y Bolsa de Trabajo pública
        $this->actingAs($miembroFid1)->get(route('red.oportunidades'))
            ->assertOk()
            ->assertSee('Docente EIB Primaria Bilingüe en UGEL Quispicanchi')
            ->assertSee('Diplomado en Revitalización Lingüística y Pedagogía Intercultural')
            ->assertDontSee('Oferta Exclusiva de Bolsa Pública Externa (No Red Puklla)');

        // El modelo público de BolsaTrabajoOferta no incluye ofertas internas de Red Puklla
        $this->assertFalse(
            BolsaTrabajoOferta::where('nombre', 'Docente EIB Primaria Bilingüe en UGEL Quispicanchi')->exists()
        );
    }

    public function test_moderacion_retiro_global_solicitud_revision_y_proteccion_de_adjuntos(): void
    {
        $docenteAula = $this->cuenta('docente.aula@example.test');
        $facilitador = $this->cuenta('facilitador.ayllu@example.test');
        $coord = $this->cuenta('docente.coordinador@example.test');
        $autorRetirado = $this->cuenta('egresado.fid.03@example.test');
        $lector = $this->cuenta('egresado.fid.01@example.test');

        $pubRetirada = AylluPublicacion::deRedPuklla()->where('titulo', 'Publicación retirada de circulación por incumplir política comunitaria')->firstOrFail();
        $pubAylluInactivo = AylluPublicacion::deRedPuklla()->where('titulo', 'Memoria del círculo piloto 2022 en ayllu inactivo')->firstOrFail();
        $aylluAbierto = AylluGrupo::deRedPuklla()->where('slug', 'circulo-saberes-eib-demo')->firstOrFail();
        $aylluPrivado = AylluGrupo::deRedPuklla()->where('slug', 'taller-materiales-quechua-demo')->firstOrFail();

        // 1. Docente sin permisos ni facilitador de ayllu pueden moderar
        foreach ([$docenteAula, $facilitador] as $sinPermiso) {
            $this->actingAs($sinPermiso)->get(route('red.moderacion'))->assertForbidden();
            $this->actingAs($sinPermiso)->post(route('red.moderar'), [
                'tipo' => 'publicacion',
                'referencia_id' => $pubRetirada->id,
                'accion' => 'restaurar',
                'motivo' => 'Intento no autorizado de restaurar publicación.',
            ])->assertForbidden();
        }

        // 2. La publicación retirada desapareció del muro general, del ayllu origen, del ayllu donde se difundió y de los guardados
        $this->actingAs($lector)->get(route('red.inicio'))->assertOk()->assertDontSee($pubRetirada->titulo);
        $this->actingAs($lector)->get(route('red.grupo', ['id' => $aylluAbierto->id]))->assertOk()->assertDontSee($pubRetirada->titulo);
        $this->actingAs($lector)->get(route('red.grupo', ['id' => $aylluPrivado->id]))->assertOk()->assertDontSee($pubRetirada->titulo);
        $this->actingAs($lector)->get(route('red.espacio'))
            ->assertOk()
            ->assertSee('Contenido ya no disponible para tu cuenta');

        // 3. El autor recibió la notificación interna obligatoria (a pesar de tener avisos = false)
        //    y su solicitud de revisión figura en el panel de moderación sin haber restaurado el contenido
        $this->assertSame('retirado', $pubRetirada->estado_red);
        $avisoAutor = $autorRetirado->notifications()->where('type', RedPukllaAviso::class)->first();
        $this->assertNotNull($avisoAutor);
        $this->assertStringContainsString('retirado', $avisoAutor->data['mensaje']);

        $this->actingAs($coord)->get(route('red.moderacion'))
            ->assertOk()
            ->assertSee('solicitar_revision')
            ->assertSee('Solicito revisión de la decisión: he retirado los datos personales');

        // 4. Coordinación autorizada puede revisar e intervenir sobre una publicación en un ayllu inactivo
        $this->actingAs($coord)->get(route('red.publicacion', ['id' => $pubAylluInactivo->id]))
            ->assertOk()
            ->assertSee('Memoria del círculo piloto 2022 en ayllu inactivo');

        // 5. Protección de adjuntos mediante ArchivoRedVisible: bloquea al autor y lectores cuando está retirada
        Route::middleware(['web', 'auth', \App\Http\Middleware\ArchivoRedVisible::class])
            ->get('/_test/ensayo/publicaciones/{publicacionId}/adjunto', fn () => response('ADJUNTO_OK', 200));

        $this->actingAs($lector)->get("/_test/ensayo/publicaciones/{$pubRetirada->id}/adjunto")->assertNotFound();
        $this->actingAs($autorRetirado)->get("/_test/ensayo/publicaciones/{$pubRetirada->id}/adjunto")->assertNotFound();
        $this->actingAs($coord)->get("/_test/ensayo/publicaciones/{$pubRetirada->id}/adjunto")->assertOk();
    }

    public function test_formularios_validacion_persistencia_notificaciones_y_ausencia_de_likes(): void
    {
        $fid09 = $this->cuenta('egresado.fid.09@example.test');
        $fid01 = $this->cuenta('egresado.fid.01@example.test');
        $fid02 = $this->cuenta('egresado.fid.02@example.test'); // Tiene avisos = false
        $entrev = $this->cuenta('docente.entrevistador@example.test');
        $campanaVigente = Campana::where('codigo', 'CAMP-DEMO-2026-II')->firstOrFail();

        // 1. Validación RM 389-2024-MINEDU: fortalecimiento no admite escenario 4 (422)
        $this->actingAs($fid09)->postJson(route('seguimiento.mi-espacio.guardar'), [
            'contacto' => [
                'programa' => 'Educación Primaria EIB',
                'egreso' => 2024,
                'correo' => $fid09->email,
            ],
            'trabaja' => 'si',
            'trabajos' => [[
                'institucion' => 'IE EIB Prueba',
                'cargo' => 'Docente',
                'eib' => 'si',
                'forma_eib' => 'fortalecimiento',
                'escenario' => 4,
            ]],
        ])->assertStatus(422);

        // 2. Persistencia separada de localidad y comunidad campesina en Mi espacio
        $resGuardar = $this->actingAs($fid09)->postJson(route('seguimiento.mi-espacio.guardar'), [
            'contacto' => [
                'programa' => 'Educación Primaria EIB',
                'egreso' => 2024,
                'correo' => $fid09->email,
            ],
            'residencia' => [
                'departamento' => 'Cusco',
                'provincia' => 'Canchis',
                'distrito' => 'Tinta',
                'localidad' => 'Barrio Tinta Centro',
                'comunidad_tipo' => 'campesina',
                'comunidad_nombre' => 'Comunidad Campesina Tinta',
            ],
            'trabaja' => 'si',
            'trabajos' => [[
                'institucion' => 'IE 56012 Tinta',
                'cargo' => 'Docente EIB',
                'inicio' => '2025-03-01',
                'educativo' => true,
                'docencia' => true,
                'eib' => 'si',
                'forma_eib' => 'fortalecimiento',
                'escenario' => 1,
                'lugar' => [
                    'departamento' => 'Cusco',
                    'provincia' => 'Canchis',
                    'distrito' => 'San Pablo',
                    'localidad' => 'Anexo Santa Bárbara',
                    'comunidad_tipo' => 'campesina',
                    'comunidad_nombre' => 'Comunidad Campesina Santa Bárbara',
                ],
            ]],
        ]);
        $resGuardar->assertOk()->assertJsonPath('avance.porcentaje', 100);
        $this->assertSame('Barrio Tinta Centro', $resGuardar->json('datos.residencia.localidad'));
        $this->assertSame('Comunidad Campesina Tinta', $resGuardar->json('datos.residencia.comunidad_nombre'));

        // 3. Entrevista docente exige confirmacion_verbal = accepted y no permite inyectar apoyo/contacto_alterno
        $this->actingAs($entrev)->postJson(route('seguimiento.panel.entrevista.guardar', ['egresado' => $fid01->id]), [
            'campana_id' => $campanaVigente->id,
            'contacto' => [
                'programa' => 'Educación Primaria EIB',
                'egreso' => 2021,
                'correo' => $fid01->email,
            ],
        ])->assertStatus(422);

        $this->actingAs($entrev)->postJson(route('seguimiento.panel.entrevista.guardar', ['egresado' => $fid01->id]), [
            'campana_id' => $campanaVigente->id,
            'confirmacion_verbal' => '1',
            'contacto' => [
                'programa' => 'Educación Primaria EIB',
                'egreso' => 2021,
                'correo' => $fid01->email,
            ],
        ])->assertOk();

        // 4. Avisos opcionales desactivados (fid02) no generan notificación al recibir comentario comunitario
        $pubPukllay = AylluPublicacion::deRedPuklla()->where('user_id', $fid02->id)->where('estado_red', 'visible')->firstOrFail();
        $conteoAvisosAntes = $fid02->notifications()->count();

        $this->actingAs($fid01)->post(route('red.comentar', ['id' => $pubPukllay->id]), [
            'contenido' => 'Aporte pedagógico a tu experiencia de taptana.',
        ])->assertRedirect();

        $this->assertSame($conteoAvisosAntes, $fid02->notifications()->count(), 'Con avisos comunitarios desactivados no se envía notificación opcional');

        // 5. Ausencia de likes, rankings o contadores de popularidad en las vistas de Red Puklla
        $htmlInicio = $this->actingAs($fid01)->get(route('red.inicio'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Me gusta', $htmlInicio);
        $this->assertStringNotContainsString('Ranking', $htmlInicio);
        $this->assertStringContainsString('No genera puntos ni rankings', $this->actingAs($fid01)->get(route('red.publicacion', ['id' => $pubPukllay->id]))->getContent());
    }
}
