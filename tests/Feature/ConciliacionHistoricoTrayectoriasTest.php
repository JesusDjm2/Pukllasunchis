<?php

namespace Tests\Feature;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\TrayectoriaEvento;
use App\Models\User;
use App\Services\SeguimientoEgresado\ConciliacionHistoricoService;
use Database\Seeders\RedPukllaEnsayoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConciliacionHistoricoTrayectoriasTest extends TestCase
{
    use DatabaseTransactions;

    private bool $cambioBaseAislada = false;
    private string $tempPackageDir = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar siempre sobre la base aislada puklla_ensayo (0 escrituras en puklla)
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

        $this->tempPackageDir = sys_get_temp_dir() . '/puklla_hist_test_' . uniqid();
        $this->crearPaqueteSinteticoDePrueba($this->tempPackageDir);
    }

    protected function tearDown(): void
    {
        if ($this->tempPackageDir && is_dir($this->tempPackageDir)) {
            $this->eliminarDirectorioRecursivo($this->tempPackageDir);
        }

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

    private function eliminarDirectorioRecursivo(string $dir): void
    {
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->eliminarDirectorioRecursivo($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    /**
     * Genera un paquete sintético con manifiesto SHA-256 válido para ensayar los casos P001..P008 y T01..T22 sin PII real.
     */
    private function crearPaqueteSinteticoDePrueba(string $rootDir): void
    {
        mkdir($rootDir . '/01_ENCUESTA_2026', 0700, true);
        mkdir($rootDir . '/02_PADRON_HISTORICO', 0700, true);
        mkdir($rootDir . '/05_CONTROL_CALIDAD', 0700, true);

        $fid01 = User::where('email', 'egresado.fid.01@example.test')->firstOrFail();
        $dual = User::where('email', 'egresado.dual.fidppd@example.test')->firstOrFail();
        $estActivo = User::where('email', 'estudiante.activo@example.test')->firstOrFail();

        // Crear un usuario homónimo a fid02 para probar T04 (candidato ambiguo, cero vínculos automáticos)
        $fid02 = User::where('email', 'egresado.fid.02@example.test')->firstOrFail();
        User::withoutEvents(function () use ($fid02) {
            User::updateOrCreate(
                ['email' => 'homonimo.fid.02@example.test'],
                [
                    'name' => $fid02->name,
                    'apellidos' => $fid02->apellidos,
                    'dni' => '99887766',
                    'condicion' => 'Egresado',
                    'password' => 'DemoPuklla2026!',
                ]
            );
        });

        // 1. Encuesta 2026 sintética (incluye fila duplicada exacta P007, Sin dato T12, Bienestar T14, homónimo T04, sin match T05)
        $encuestaCsv = $rootDir . '/01_ENCUESTA_2026/encuesta_2026_113_respuestas_depuradas.csv';
        $fp1 = fopen($encuestaCsv, 'w');
        fputcsv($fp1, [
            'Nombre', 'Cohorte_egreso', 'Programa', 'Situacion_academica', 'Trabaja_2026',
            'Insercion_educativa_2026', 'Sector_2026', 'Tipo_practica_2026', 'Cargo_responsabilidad_2026',
            'Lugar_trabajo_2026', 'Relacion_educacion_original', 'Bienestar_2026_original',
            'Satisfaccion_laboral_archivo', 'Aporte_formacion_archivo_original', 'Necesidades_archivo_original',
            'Sugerencias_archivo_original', 'Fuente_roster', 'Match', 'Observacion_calidad',
        ]);
        // Fila 1 (P001 candidato por nombre único para revisión humana)
        $filaFid01 = [
            $fid01->name . ' ' . $fid01->apellidos, '2021', 'Primaria EIB', 'Titulado(a)', 'Trabaja',
            'En educación', 'Público / estatal', 'Docencia en aula', 'Docente de aula EIB',
            'Ocongate - Quispicanchi (texto libre)', 'Sí', 'CONFIDENCIAL_BIENESTAR_SINTETICO_P001: Solicito orientación reservada.',
            'Satisfecho(a)', 'La práctica preprofesional en comunidad.', 'Didáctica multigrado.',
            'Talleres los sábados.', 'Sí', 'Verificado', '',
        ];
        fputcsv($fp1, $filaFid01);
        // Fila 2 (P007 duplicado exacto de la fila 1 -> debe descartarse como duplicado en el lote)
        fputcsv($fp1, $filaFid01);
        // Fila 3 (P003 / T04 homónimo -> candidato ambiguo, no vincular)
        fputcsv($fp1, [
            $fid02->name . ' ' . $fid02->apellidos, '2021', 'Inicial', 'Egresado(a)', 'Sin dato',
            'Sin dato', 'Indeterminado', 'Sin dato', '',
            '', '', 'Sin dato',
            'Sin dato', '', '', '', 'Sí', 'Verificado', '',
        ]);
        // Fila 4 (T05 persona inexistente -> sin_correspondencia, no crea users)
        fputcsv($fp1, [
            'Persona Inexistente Sin Registro Puklla', '2023', 'Inicial', 'Egresado(a)', 'No trabaja',
            'No trabaja', 'Ninguno', 'Sin dato', '',
            '', '', 'Sin dato',
            'Sin dato', '', '', '', 'No', 'Pendiente', 'Sin match',
        ]);
        fclose($fp1);

        // 2. Base maestra histórica sintética
        $maestraCsv = $rootDir . '/01_ENCUESTA_2026/base_maestra_historico_y_encuesta_254_personas.csv';
        $fp2 = fopen($maestraCsv, 'w');
        fputcsv($fp2, [
            'ID_origen', 'Año_ingreso', 'Cohorte_egreso_2026', 'Programa', 'Situación_académica',
            'Nombre', 'Estado_contacto_archivo', 'Situación_laboral', 'Inserción_educativa',
            'Sector', 'Modalidad_contrato_archivo', 'Cargo_área_actual', 'Lugar_trabajo_2026',
            'Satisfacción_laboral_archivo', 'Bienestar_2026_original', 'Aporte_formación_original',
            'Necesidades_original', 'Sugerencias_original', 'Fuente_actualización', 'Observaciones_calidad',
        ]);
        fputcsv($fp2, [
            'M-001', '2017', '2021', 'Primaria EIB', 'Licenciado',
            $fid01->name . ' ' . $fid01->apellidos, 'Contactado', 'Sin dato', 'Sin dato',
            'Público', 'Contratado', 'Docente EIB', 'Ocongate',
            'Muy satisfecho', 'Sin dato', 'Enfoque intercultural', 'Quechua escrito', '', 'Archivo histórico', '',
        ]);
        fclose($fp2);

        // 3. Padrón histórico de promociones (con correo institucional para probar P001, P002/T06, P004/T07, T12, T16)
        $padronCsv = $rootDir . '/02_PADRON_HISTORICO/padron_historico_258_personas_con_nombres.csv';
        $fp3 = fopen($padronCsv, 'w');
        fputcsv($fp3, [
            'id', 'nombre', 'programa', 'egreso', 'egreso_confirmado', 'periodo_referencia',
            'condicion', 'estados_observados', 'periodos_en_listas', 'correo', 'telefono',
            'calidad', 'advertencia', 'fuentes',
        ]);
        // P001: fid01 con correo institucional único y trayectoria FID única
        fputcsv($fp3, [
            'PAD-001', $fid01->name . ' ' . $fid01->apellidos, 'Primaria EIB', '2021-II', 'True', '2021-II',
            'Egresado', 'Egresado', '2021-II', $fid01->email, '984110001',
            'completo', '', '1 - Egresados 2021-II.xlsx',
        ]);
        // P002 / T06: persona con doble trayectoria (FID y PPD) y programa 'T. Bach.' / 'Por verificar' -> identidad confirmada, trayectoria TRANSVERSAL
        fputcsv($fp3, [
            'PAD-002', $dual->name . ' ' . $dual->apellidos, 'T. Bach.', '2023-II', 'True', '2023-II',
            'Egresado', 'Egresado', '2023-II', $dual->email, '984300001',
            'completo', '', '4 - Egresados 2023-II.xlsx',
        ]);
        // P004 / T07: fuente histórica dice egreso_confirmado=True, pero en la BD es un estudiante Regular sin acto de egreso
        fputcsv($fp3, [
            'PAD-003', $estActivo->name . ' ' . $estActivo->apellidos, 'Primaria EIB', '2025-I', 'True', '2025-I',
            'Egresado en lista', 'Pendiente', '2025-I', $estActivo->email, '984100008',
            'por_verificar', 'Sin acto curricular de egreso en BD', '6 - Egresados 2025-I.xlsx',
        ]);
        fclose($fp3);

        // 4. Generar manifiesto SHA-256 oficial de los archivos del paquete sintético
        $archivos = [
            '01_ENCUESTA_2026/encuesta_2026_113_respuestas_depuradas.csv',
            '01_ENCUESTA_2026/base_maestra_historico_y_encuesta_254_personas.csv',
            '02_PADRON_HISTORICO/padron_historico_258_personas_con_nombres.csv',
        ];
        $lineasManifest = [];
        foreach ($archivos as $rel) {
            $lineasManifest[] = hash_file('sha256', $rootDir . '/' . $rel) . '  ' . $rel;
        }
        file_put_contents($rootDir . '/05_CONTROL_CALIDAD/SHA256_MANIFEST.txt', implode("\n", $lineasManifest) . "\n");
    }

    /**
     * T01, T02, T18, T22: Dry-run por defecto con 0 escrituras, rechazo de hash alterado,
     * bloqueo de --apply sobre puklla/production y salida sin PII.
     */
    public function test_t01_t02_t18_t22_dry_run_cero_escrituras_hash_y_bloqueo_produccion(): void
    {
        $servicio = app(ConciliacionHistoricoService::class);

        $conteoLotesAntes = DB::table('egresado_historico_lotes')->count();
        $conteoObsAntes = DB::table('egresado_historico_observaciones')->count();
        $conteoUsersAntes = User::count();

        // T01 + T22: Ejecución dry-run por comando Artisan realiza 0 escrituras y no vuelca PII
        $exitCode = Artisan::call('puklla:historico:conciliar', [
            '--source' => $this->tempPackageDir,
            '--dry-run' => true,
        ]);
        $salida = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertSame($conteoLotesAntes, DB::table('egresado_historico_lotes')->count());
        $this->assertSame($conteoObsAntes, DB::table('egresado_historico_observaciones')->count());
        $this->assertSame($conteoUsersAntes, User::count());
        $this->assertStringNotContainsString('egresado.fid.01@example.test', $salida);
        $this->assertStringNotContainsString('CONFIDENCIAL_BIENESTAR_SINTETICO_P001', $salida);

        // T18: Intentar --apply sin --confirm-isolated o apuntando a 'puklla' o en 'production' se aborta sin escrituras
        try {
            $servicio->verificarBaseDestinoAislada(true, false, 'puklla_ensayo');
            $this->fail('Debió rechazar --apply sin --confirm-isolated');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('--confirm-isolated', $e->getMessage());
        }

        try {
            $servicio->verificarBaseDestinoAislada(true, true, 'puklla');
            $this->fail('Debió rechazar --apply cuando la base esperada no coincide o es institucional');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('SEGURIDAD', $e->getMessage());
        }

        $envOriginal = $this->app['env'];
        try {
            $this->app['env'] = 'production';
            $servicio->verificarBaseDestinoAislada(true, true, 'puklla_ensayo');
            $this->fail('Debió rechazar --apply en production');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('PROHIBIDO', $e->getMessage());
        } finally {
            $this->app['env'] = $envOriginal;
        }

        // T02: Alterar un byte de un archivo CSV hace que falle la verificación SHA-256 con 0 escrituras
        file_put_contents(
            $this->tempPackageDir . '/01_ENCUESTA_2026/encuesta_2026_113_respuestas_depuradas.csv',
            "\n",
            FILE_APPEND
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('INTEGRIDAD RECHAZADA');
        $servicio->ejecutarPaquete($this->tempPackageDir, false);
    }

    /**
     * T03, T04, T05, T06, T07, T11, T12, T13, T16, T17:
     * Idempotencia, homónimos ambiguos, sin match, doble trayectoria FID+PPD, discrepancia de egreso,
     * empleo sin fechas, preservación de «Sin dato», no sobreescritura de campaña vigente y rollback controlado.
     */
    public function test_t03_a_t07_t11_a_t13_t16_t17_conciliacion_idempotencia_y_reversion(): void
    {
        $servicio = app(ConciliacionHistoricoService::class);

        $fid01 = User::where('email', 'egresado.fid.01@example.test')->firstOrFail();
        $dual = User::where('email', 'egresado.dual.fidppd@example.test')->firstOrFail();
        $estActivo = User::where('email', 'estudiante.activo@example.test')->firstOrFail();
        $coord = User::where('email', 'docente.coordinador@example.test')->firstOrFail();

        $conteoUsersAntes = User::count();
        $conteoEpisodiosAntes = EpisodioLaboral::count();
        $actualizacionRecienteAntes = Actualizacion::where('user_id', $fid01->id)->latest('id')->first();

        // Ejecutar importación en la base aislada puklla_ensayo dos veces seguidas (T03)
        $rep1 = $servicio->ejecutarPaquete($this->tempPackageDir, true, true, 'puklla_ensayo', $coord->id);
        $conteoRegTras1 = DB::table('egresado_historico_registros')->count();
        $conteoObsTras1 = DB::table('egresado_historico_observaciones')->count();

        $rep2 = $servicio->ejecutarPaquete($this->tempPackageDir, true, true, 'puklla_ensayo', $coord->id);
        $this->assertSame($conteoRegTras1, DB::table('egresado_historico_registros')->count(), 'T03: Segunda ejecución no duplica registros');
        $this->assertSame($conteoObsTras1, DB::table('egresado_historico_observaciones')->count(), 'T03: Segunda ejecución no duplica observaciones');

        // P007 / T03: La fila duplicada exacta dentro de encuesta_depurada_2026 fue marcada como descartado_duplicado
        $this->assertSame(1, $rep1['fuentes']['encuesta_depurada_2026']['duplicados_descartados_en_lote']);

        // T05: Persona inexistente queda en sin_correspondencia y NO se creó ningún usuario nuevo
        $this->assertSame($conteoUsersAntes, User::count());
        $this->assertSame(1, $rep1['fuentes']['encuesta_depurada_2026']['sin_correspondencia']);

        // T04: Homónimo (fid02) queda en candidato_ambiguo sin user_id_vinculado
        $regHomonimo = DB::table('egresado_historico_registros')
            ->where('fuente_codigo', 'encuesta_depurada_2026')
            ->where('estado_conciliacion', 'candidato_ambiguo')
            ->first();
        $this->assertNotNull($regHomonimo);
        $this->assertNull($regHomonimo->user_id_vinculado);

        // T06: Persona con doble trayectoria (FID y PPD) y fuente sin programa inequívoco ('T. Bach.')
        //      vincula identidad pero deja trayectoria en TRANSVERSAL (identidad_confirmada_trayectoria_pendiente)
        $regDual = DB::table('egresado_historico_registros')
            ->where('fuente_codigo', 'padron_promociones_v8')
            ->where('fila_origen', 'PAD-002')
            ->first();
        $this->assertNotNull($regDual);
        $this->assertSame($dual->id, (int) $regDual->user_id_vinculado);
        $this->assertSame('identidad_confirmada_trayectoria_pendiente', $regDual->estado_conciliacion);
        $this->assertSame('TRANSVERSAL', $regDual->trayectoria_tipo_vinculada);

        // T07: Fuente histórica dice egreso_confirmado=True para estudiante Regular, pero NO altera users.condicion
        //      ni crea evento de egreso; registra calidad = 'discrepancia_egreso'
        $estActivo->refresh();
        $this->assertSame('Regular', $estActivo->condicion);
        $this->assertFalse(TrayectoriaEvento::where('user_id', $estActivo->id)->where('tipo', 'egreso')->exists());
        $obsDiscrepancia = DB::table('egresado_historico_observaciones')->where('user_id', $estActivo->id)->first();
        $this->assertNotNull($obsDiscrepancia);
        $this->assertSame('discrepancia_egreso', $obsDiscrepancia->calidad);

        // T11, T12, T13, T16: Aprobar manualmente la candidatura de fid01 en encuesta_depurada_2026 y maestra_historica_2026
        $regEncuestaFid01 = DB::table('egresado_historico_registros')
            ->where('fuente_codigo', 'encuesta_depurada_2026')
            ->where('estado_conciliacion', 'candidato_revision')
            ->first();
        $regMaestraFid01 = DB::table('egresado_historico_registros')
            ->where('fuente_codigo', 'maestra_historica_2026')
            ->where('fila_origen', 'M-001')
            ->first();

        $servicio->resolverConciliacionManual($regEncuestaFid01->id, 'aprobar', $fid01->id, 'FID', $coord->id, 'Identidad y trayectoria FID verificadas en padrón.');
        $servicio->resolverConciliacionManual($regMaestraFid01->id, 'aprobar', $fid01->id, 'FID', $coord->id, 'Correspondencia verificada con archivo histórico.');

        // T16: La misma persona (fid01) ahora tiene 3 observaciones históricas rastreables (1 por cada fuente) sin duplicar su cuenta User
        $this->assertSame(3, DB::table('egresado_historico_observaciones')->where('user_id', $fid01->id)->count());

        // T11: No se crearon episodios laborales con fechas inventadas y observada_en se conserva NULL
        $this->assertSame($conteoEpisodiosAntes, EpisodioLaboral::count());
        $obsEncuesta = DB::table('egresado_historico_observaciones')->where('registro_origen_id', $regEncuestaFid01->id)->first();
        $this->assertNull($obsEncuesta->observada_en);

        // T12: El valor «Sin dato» en maestra_historica_2026 se conserva como 'Sin dato' y NUNCA como 'No trabaja'
        $obsMaestra = DB::table('egresado_historico_observaciones')->where('registro_origen_id', $regMaestraFid01->id)->first();
        $this->assertSame('Sin dato', $obsMaestra->situacion_laboral_declarada);

        // T13: La situación vigente de campaña 2026-II de fid01 no fue sobrescrita
        $actualizacionRecienteDespues = Actualizacion::where('user_id', $fid01->id)->latest('id')->first();
        $this->assertSame($actualizacionRecienteAntes->id, $actualizacionRecienteDespues->id);
        $this->assertSame('Trabaja', $actualizacionRecienteDespues->trabaja);

        // T17: Reversión controlada de un lote de ensayo elimina solo los registros de ese lote sin tocar datos oficiales
        $lotePadron = DB::table('egresado_historico_lotes')
            ->join('egresado_historico_fuentes', 'egresado_historico_fuentes.id', '=', 'egresado_historico_lotes.fuente_id')
            ->where('egresado_historico_fuentes.codigo', 'padron_promociones_v8')
            ->select('egresado_historico_lotes.id')
            ->first();

        $resRev = $servicio->revertirLoteEnsayo($lotePadron->id, $coord->id, 'Prueba automatizada de reversión T17');
        $this->assertSame('revertido', $resRev['estado']);
        $this->assertSame(0, DB::table('egresado_historico_registros')->where('lote_id', $lotePadron->id)->count());
        $this->assertSame($conteoUsersAntes, User::count());
    }

    /**
     * T08, T09, T10, T14, T15, T19, T20, T21:
     * Créditos no verificados sin plan, separación egreso/titulación/grado, privacidad de bienestar,
     * autorización por ID en bandeja y antecedentes, e historial de múltiples matrículas/cambios.
     */
    public function test_t08_t09_t10_t14_t15_t19_t20_t21_hitos_privacidad_bienestar_y_permisos(): void
    {
        $servicio = app(ConciliacionHistoricoService::class);

        $fid01 = User::where('email', 'egresado.fid.01@example.test')->firstOrFail();
        $fid07 = User::where('email', 'egresado.fid.07@example.test')->firstOrFail();
        $estActivo = User::where('email', 'estudiante.activo@example.test')->firstOrFail();
        $docenteAula = User::where('email', 'docente.aula@example.test')->firstOrFail();
        $entrev = User::where('email', 'docente.entrevistador@example.test')->firstOrFail();
        $coord = User::where('email', 'docente.coordinador@example.test')->firstOrFail();
        $bienestar = User::where('email', 'bienestar.demo@example.test')->firstOrFail();

        // T08: Estudiante sin evento de egreso y sin tabla de plan de estudios/convalidaciones -> 'no_verificado'
        $evalCreditos = $servicio->evaluarCumplimientoCreditos($estActivo);
        $this->assertSame('no_verificado', $evalCreditos['estado']);
        $this->assertFalse($evalCreditos['egreso_acreditado']);

        // T09: Egreso acreditado y título en trámite se mantienen separados
        $estadoEnTramite = EstadoTitulacion::updateOrCreate(['clave' => 'pti_aprobado'], ['nombre' => 'PTI aprobado', 'orden' => 2]);
        SeguimientoTitulacion::withoutEvents(function () use ($fid07, $estadoEnTramite) {
            SeguimientoTitulacion::where('user_id', $fid07->id)->update(['estado_titulacion_id' => $estadoEnTramite->id]);
        });
        $hitosFid07 = $servicio->obtenerHitosOficialesSeparados($fid07->fresh());
        $this->assertTrue($hitosFid07['egreso']['acreditado']);
        $this->assertSame('en_tramite', $hitosFid07['titulacion']['estado']);
        $this->assertFalse($hitosFid07['titulacion']['titulo_acreditado']);

        // T10: Título acreditado ('licenciado') sin resolución de grado bachiller no inventa grado_bachiller
        $estadoLic = EstadoTitulacion::updateOrCreate(['clave' => 'licenciado'], ['nombre' => 'Licenciado', 'orden' => 6]);
        SeguimientoTitulacion::withoutEvents(function () use ($fid01, $estadoLic) {
            SeguimientoTitulacion::where('user_id', $fid01->id)->update(['estado_titulacion_id' => $estadoLic->id]);
        });
        $hitosFid01 = $servicio->obtenerHitosOficialesSeparados($fid01->fresh());
        $this->assertTrue($hitosFid01['titulacion']['titulo_acreditado']);
        $this->assertFalse($hitosFid01['grado_bachiller']['acreditado']);
        $this->assertSame('No verificado en el sistema', $hitosFid01['grado_bachiller']['detalle']);

        // Cargar paquete y aprobar vínculo de fid01 para probar T14 y T15
        $servicio->ejecutarPaquete($this->tempPackageDir, true, true, 'puklla_ensayo', $coord->id);
        $regEncuesta = DB::table('egresado_historico_registros')
            ->where('fuente_codigo', 'encuesta_depurada_2026')
            ->where('estado_conciliacion', 'candidato_revision')
            ->first();
        $servicio->resolverConciliacionManual($regEncuesta->id, 'aprobar', $fid01->id, 'FID', $coord->id, 'Aprobado para prueba de privacidad T14.');

        // T14: El payload JSON estándar NO contiene el texto de bienestar, y un entrevistador recibe la máscara reservada
        $this->assertStringNotContainsString('CONFIDENCIAL_BIENESTAR_SINTETICO_P001', $regEncuesta->payload_json);

        $resEntrev = $this->actingAs($entrev)->getJson(route('seguimiento.panel.antecedentes', ['egresado' => $fid01->id]));
        $resEntrev->assertOk();
        $this->assertStringNotContainsString('CONFIDENCIAL_BIENESTAR_SINTETICO_P001', $resEntrev->getContent());
        $this->assertSame('[Información reservada de bienestar institucional]', $resEntrev->json('antecedentes_seguimiento.0.bienestar_restringido'));

        // T14: Bienestar / Coordinación autorizada sí puede leer el texto descifrado
        $antecedentesBienestar = $servicio->obtenerAntecedentesPersona($fid01->id, $bienestar);
        $conBienestar = collect($antecedentesBienestar)->firstWhere('tiene_bienestar_restringido', true);
        $this->assertNotNull($conBienestar);
        $this->assertStringContainsString('CONFIDENCIAL_BIENESTAR_SINTETICO_P001', (string) $conBienestar['bienestar_restringido']);

        // T15: Accesos no autorizados por ID a bandeja de conciliación y a antecedentes ajenos reciben 403
        $this->actingAs($docenteAula)->get(route('seguimiento.panel.conciliacion'))->assertForbidden();
        $this->actingAs($entrev)->get(route('seguimiento.panel.conciliacion'))->assertForbidden();
        $this->actingAs($fid07)->getJson(route('seguimiento.panel.antecedentes', ['egresado' => $fid01->id]))->assertForbidden();
        $this->actingAs($docenteAula)->getJson(route('seguimiento.panel.antecedentes', ['egresado' => $fid01->id]))->assertForbidden();

        // Coordinación sí accede a la bandeja restringida de conciliación y el titular ve sus antecedentes en Mi espacio
        $this->actingAs($coord)->get(route('seguimiento.panel.conciliacion'))->assertOk()->assertSee('Bandeja restringida de conciliación histórica');
        $this->actingAs($fid01)->get(route('seguimiento.mi-espacio'))->assertOk()->assertSee('Antecedentes de seguimiento');

        // T21: Usuario con múltiples eventos de trayectoria (licencia, reincorporación, cambio de programa y doble egreso) conserva todo su historial
        $dual = User::where('email', 'egresado.dual.fidppd@example.test')->firstOrFail();
        TrayectoriaEvento::create([
            'user_id' => $dual->id,
            'tipo' => 'licencia',
            'fecha_inicio' => '2018-03-01',
            'fecha_fin' => '2018-08-01',
            'motivo' => 'Licencia temporal registrada en historial',
            'origen' => 'manual',
        ]);
        $dual->refresh()->load(\App\Services\SeguimientoEgresado\GrupoFormacionService::RELACIONES);
        $this->assertGreaterThanOrEqual(3, $dual->trayectoriaEventos->count());
        $this->assertSame(['FID', 'PPD'], app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->resolver($dual)['grupos']);
    }
}
