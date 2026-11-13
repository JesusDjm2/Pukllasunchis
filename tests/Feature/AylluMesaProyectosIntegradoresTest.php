<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ayllu\AylluPiAporteCurso;
use App\Models\Ayllu\AylluPiAsignacionMesa;
use App\Models\Ayllu\AylluPiDefinicion;
use App\Models\Ayllu\AylluPiEjecucion;
use App\Models\Ayllu\AylluPiEvidenciaVersion;
use App\Models\Ayllu\AylluPiLoteImportacion;
use App\Models\Ayllu\AylluPiSintesisComunitaria;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\User;
use App\Services\AylluPiImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AylluMesaProyectosIntegradoresTest extends TestCase
{
    use DatabaseTransactions;

    protected AylluPiImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = app(AylluPiImportService::class);
        $this->importService->asegurarImportacionInicial();
    }

    /**
     * Criterios #1, #2, #4, #5 y #12 (estado inicial):
     * - 4 años y 8 ciclos con títulos, propósitos y productos de las matrices.
     * - 114 entradas conciliadas (10, 10, 16, 14, 18, 18, 16, 12).
     * - 9 anomalías numéricas de 5 dígitos en competencias conservadas con valor bruto y formato Excel.
     * - Campos vacíos identificados como «No consignado en la fuente».
     * - Diferencias entre los Word y las matrices preservadas.
     * - Ningún avance ficticio se fabrica al importar (las 114 inician en `sin_registrar`).
     */
    public function test_criterios_01_02_04_05_12_conciliacion_de_4_anios_8_ciclos_114_entradas_y_9_anomalias(): void
    {
        $this->assertSame(114, AylluPiAporteCurso::count(), 'Deben existir exactamente las 114 posiciones documentales');

        $esperadoPorCiclo = [
            'I' => 10,
            'II' => 10,
            'III' => 16,
            'IV' => 14,
            'V' => 18,
            'VI' => 18,
            'VII' => 16,
            'VIII' => 12,
        ];

        foreach ($esperadoPorCiclo as $ciclo => $cantidad) {
            $this->assertSame(
                $cantidad,
                AylluPiAporteCurso::where('ciclo_romano', $ciclo)->count(),
                "El Ciclo {$ciclo} debe tener exactamente {$cantidad} entradas"
            );
        }

        // Las 9 anomalías de 5 dígitos en competencias
        $anomalias = AylluPiAporteCurso::where('es_anomalia_competencia', true)->orderBy('posicion_global')->get();
        $this->assertCount(9, $anomalias, 'Deben estar señaladas exactamente las 9 celdas con valores numéricos de 5 dígitos');

        $valoresBrutosAnomalos = $anomalias->pluck('competencias_fuente_bruto')->all();
        $this->assertEquals([
            '41185.0', '40424.0', '40756.0',
            '40424.0', '41189.0',
            '39236.0', '41091.0',
            '41189.0', '40727.0',
        ], $valoresBrutosAnomalos);

        foreach ($anomalias as $anom) {
            $this->assertSame('d, m, yy', $anom->competencias_formato_excel);
            $this->assertNotEmpty($anom->archivo_fuente);
            $this->assertNotEmpty($anom->celda_competencias);
        }

        // Campos vacíos conservados como «No consignado en la fuente»
        $this->assertGreaterThan(
            0,
            AylluPiAporteCurso::where('competencias_fuente_mostrar', 'No consignado en la fuente')->count()
        );
        $this->assertGreaterThan(
            0,
            AylluPiAporteCurso::where('producto_previsto_mostrar', 'No consignado en la fuente')->count()
        );

        // Diferencias entre Word 2020-2024, Copia de 03 y Matriz en Año 3 preservadas como versiones separadas
        $versionesAnio3 = AylluPiDefinicion::where('anio_formativo', 3)->get();
        $this->assertGreaterThanOrEqual(3, $versionesAnio3->count());
        $this->assertTrue(
            $versionesAnio3->contains(fn ($d) => str_contains($d->denominacion_original, 'Modos de vida, valores y  cultura'))
        );
        $this->assertTrue(
            $versionesAnio3->contains(fn ($d) => $d->codigo_fuente === 'word_copia_03' && str_contains($d->denominacion_original, 'Saber y conocer'))
        );
        $this->assertTrue(
            $versionesAnio3->contains(fn ($d) => $d->codigo_fuente === 'matriz_excel' && $d->producto_integrador === 'Material didáctico concreto: juegos, títeres, cuentos')
        );

        // Ningún estado se fabrica al importar
        $this->assertSame(
            114,
            AylluPiAporteCurso::where('estado_desarrollo', 'sin_registrar')->count(),
            'Al importar, ninguna entrada debe tener estados o avances fabricados'
        );
    }

    /**
     * Criterio #3:
     * Cambiar de programa muestra los aportes correctos y los comunes aplicables (FORMACION_GENERAL),
     * sin mezclar Primaria con Primaria EIB.
     */
    public function test_criterio_03_filtro_de_programa_incluye_comunes_y_no_mezcla_primaria_con_primaria_eib(): void
    {
        $docente = Docente::whereHas('user')->first();
        $user = $docente->user;

        // En Ciclo III (16 entradas): FORMACION_GENERAL (3), INICIAL (4), PRIMARIA (4), PRIMARIA_EIB (5)
        $resPrimaria = $this->actingAs($user)->get(route('ayllu.proyecto', [
            'anio' => 2,
            'ciclo' => 'III',
            'programa' => 'PRIMARIA',
            'vista' => 'matriz',
        ]));
        $resPrimaria->assertOk();
        $aportesVistaPrimaria = $resPrimaria->viewData('aportesCicloActivo');

        $codigosEnPrimaria = $aportesVistaPrimaria->pluck('agrupacion_programa_codigo')->unique()->values()->all();
        $this->assertContains('PRIMARIA', $codigosEnPrimaria);
        $this->assertContains('FORMACION_GENERAL', $codigosEnPrimaria);
        $this->assertNotContains('PRIMARIA_EIB', $codigosEnPrimaria, 'El filtro PRIMARIA nunca debe mezclar entradas de PRIMARIA_EIB');
        $this->assertNotContains('INICIAL', $codigosEnPrimaria);

        // Ahora con PRIMARIA_EIB
        $resPrimariaEib = $this->actingAs($user)->get(route('ayllu.proyecto', [
            'anio' => 2,
            'ciclo' => 'III',
            'programa' => 'PRIMARIA_EIB',
            'vista' => 'matriz',
        ]));
        $resPrimariaEib->assertOk();
        $aportesVistaEib = $resPrimariaEib->viewData('aportesCicloActivo');

        $codigosEnEib = $aportesVistaEib->pluck('agrupacion_programa_codigo')->unique()->values()->all();
        $this->assertContains('PRIMARIA_EIB', $codigosEnEib);
        $this->assertContains('FORMACION_GENERAL', $codigosEnEib);
        $this->assertNotContains('PRIMARIA', $codigosEnEib, 'El filtro PRIMARIA_EIB nunca debe mezclar entradas de PRIMARIA monolingüe');
    }

    /**
     * Criterio #6:
     * Repetir una importación idéntica no duplica registros.
     * Importar una revisión crea una versión nueva sin alterar la versión adoptada por una ejecución anterior.
     */
    public function test_criterio_06_importacion_idempotente_y_versionado_sin_alterar_ejecucion_anterior(): void
    {
        $conteoLotesAntes = AylluPiLoteImportacion::count();
        $conteoDefAntes = AylluPiDefinicion::count();
        $conteoAportesAntes = AylluPiAporteCurso::count();

        // 1. Repetir importación idéntica
        $resRepetida = $this->importService->ejecutarImportacion();
        $this->assertTrue($resRepetida['idempotente']);
        $this->assertSame($conteoLotesAntes, AylluPiLoteImportacion::count());
        $this->assertSame($conteoDefAntes, AylluPiDefinicion::count());
        $this->assertSame($conteoAportesAntes, AylluPiAporteCurso::count());

        // 2. Importar una revisión modificada de Año 1
        $ejecucionAnio1Antes = AylluPiEjecucion::where('anio_formativo', 1)->firstOrFail();
        $definicionOriginalId = $ejecucionAnio1Antes->definicion_id;

        $manifiestoModificado = $this->importService->cargarManifiesto();
        $manifiestoModificado['definiciones_proyectos'][0]['proposito_original'] .= ' [Revisión curricular trazable]';

        $resRevision = $this->importService->ejecutarImportacion(null, $manifiestoModificado);
        $this->assertFalse($resRevision['idempotente']);
        $this->assertSame(1, $resRevision['definiciones_creadas']);

        // Verificar que la ejecución activa del Año 1 conserva intacta su definición adoptada originalmente
        $ejecucionAnio1Despues = AylluPiEjecucion::find($ejecucionAnio1Antes->id);
        $this->assertSame($definicionOriginalId, $ejecucionAnio1Despues->definicion_id);
    }

    /**
     * Criterios #7, #8, #11 y #12:
     * - Docente autorizado registra versión de evidencia, solicita revisión y comparte con la mesa de su ciclo.
     * - Otro docente del mismo ciclo puede consultarlo y comentarlo.
     * - Docente ajeno (de otro ciclo/año) recibe 403 en ficha, edición, revisión y descarga de archivo privado.
     * - Coordinación integra la versión al producto anual dejando trazabilidad de quién la validó.
     * - Revocar asignación elimina inmediatamente los accesos derivados conservando historial.
     */
    public function test_criterios_07_08_11_12_flujo_docente_revision_integracion_revocacion_y_bloqueo_docente_ajeno(): void
    {
        Storage::fake('local');

        $aporteCicloII = AylluPiAporteCurso::where('ciclo_romano', 'II')->firstOrFail();
        $admin = User::role(['super-admin', 'admin'])->first() ?? User::first();

        // Crear dos docentes del Ciclo II y un docente ajeno (solo en Ciclo VII)
        $userDocenteAutor = $this->crearUsuarioDocentePrueba('Docente Autor Ciclo II');
        $userDocenteParCiclo = $this->crearUsuarioDocentePrueba('Docente Par Ciclo II');
        $userDocenteAjeno = $this->crearUsuarioDocentePrueba('Docente Ajeno Ciclo VII');

        // Asignar en la mesa del Ciclo II al autor (sobre el aporte) y al par (en el ciclo II)
        $asignacionAutor = AylluPiAsignacionMesa::create([
            'ejecucion_id' => $aporteCicloII->ejecucion_id,
            'aporte_curso_id' => $aporteCicloII->id,
            'user_id' => $userDocenteAutor->id,
            'rol_mesa' => 'docente_responsable_aporte',
            'programa_codigo' => $aporteCicloII->agrupacion_programa_codigo,
            'ciclo_romano' => 'II',
            'activo' => true,
            'asignado_por_user_id' => $admin->id,
        ]);

        AylluPiAsignacionMesa::create([
            'ejecucion_id' => $aporteCicloII->ejecucion_id,
            'aporte_curso_id' => null,
            'user_id' => $userDocenteParCiclo->id,
            'rol_mesa' => 'docente_responsable_aporte',
            'programa_codigo' => 'TODOS',
            'ciclo_romano' => 'II',
            'activo' => true,
            'asignado_por_user_id' => $admin->id,
        ]);

        // El docente ajeno solo tiene asignación en Ciclo VII
        AylluPiAsignacionMesa::create([
            'user_id' => $userDocenteAjeno->id,
            'rol_mesa' => 'docente_responsable_aporte',
            'programa_codigo' => 'TODOS',
            'ciclo_romano' => 'VII',
            'activo' => true,
            'asignado_por_user_id' => $admin->id,
        ]);

        // 1. Docente autor registra versión v1 con archivo privado y solicita revisión compartida en mesa_docente_ciclo
        $archivoSubido = UploadedFile::fake()->create('guia_entrevista_territorio.pdf', 120, 'application/pdf');

        $this->actingAs($userDocenteAutor)->post(route('ayllu.pi.versiones.store', $aporteCicloII->id), [
            'titulo_version' => 'Guía de entrevista sobre espacio sagrado y croquis v1',
            'descripcion_avance' => 'Instrumento articulado con los sabios de la comunidad para recoger memoria oral.',
            'audiencia' => 'mesa_docente_ciclo',
            'archivo_evidencia' => $archivoSubido,
            'reconocimiento_autoria_comunitaria' => 'Sabios de la comunidad de Chinchero',
            'restriccion_circulacion' => 'uso_interno_ciclo',
            'solicitar_revision_compartida' => '1',
            'confirmacion_politica_menores' => '1',
        ])->assertRedirect();

        $version = AylluPiEvidenciaVersion::where('aporte_curso_id', $aporteCicloII->id)->latest('id')->firstOrFail();
        $this->assertSame(1, $version->numero_version);
        $this->assertSame('para_revision_compartida', $version->estado_version);
        $this->assertSame('para_revision_compartida', $aporteCicloII->fresh()->estado_desarrollo);

        // 2. Otro docente del mismo Ciclo II puede abrir la ficha, ver la versión compartida, descargar el archivo y revisarla
        $this->actingAs($userDocenteParCiclo)
            ->get(route('ayllu.pi.aportes.show', $aporteCicloII->id))
            ->assertOk()
            ->assertSee('Guía de entrevista sobre espacio sagrado y croquis v1');

        $this->actingAs($userDocenteParCiclo)
            ->get(route('ayllu.pi.versiones.archivo', $version->id))
            ->assertOk();

        $this->actingAs($userDocenteParCiclo)
            ->post(route('ayllu.pi.versiones.revisar', $version->id), [
                'decision' => 'revisado',
                'comentario_revision' => 'Revisado en mesa del Ciclo II: excelente pertinencia en quechua.',
            ])
            ->assertRedirect();

        $this->assertSame('revisado', $aporteCicloII->fresh()->estado_desarrollo);

        // 3. Criterio #8: Docente ajeno (Ciclo VII) recibe 403 al intentar abrir la ficha por URL directa, editar, revisar o descargar el archivo
        $this->actingAs($userDocenteAjeno)
            ->get(route('ayllu.pi.aportes.show', $aporteCicloII->id))
            ->assertForbidden();

        $this->actingAs($userDocenteAjeno)
            ->put(route('ayllu.pi.aportes.update', $aporteCicloII->id), [
                'ajuste_operativo_mesa' => 'Intento de edición no autorizada',
            ])
            ->assertForbidden();

        $this->actingAs($userDocenteAjeno)
            ->get(route('ayllu.pi.versiones.archivo', $version->id))
            ->assertForbidden();

        $this->actingAs($userDocenteAjeno)
            ->post(route('ayllu.pi.versiones.revisar', $version->id), [
                'decision' => 'revisado',
                'comentario_revision' => 'Intento intruso',
            ])
            ->assertForbidden();

        // 4. Criterio #12: Coordinación valida e integra la versión v1 al producto anual
        $this->actingAs($admin)
            ->post(route('ayllu.pi.versiones.revisar', $version->id), [
                'decision' => 'integrado_al_producto_anual',
                'comentario_revision' => 'Se incorpora la v1 al Mapa Parlante anual de los Ciclos I–II.',
            ])
            ->assertRedirect();

        $aporteActualizado = $aporteCicloII->fresh();
        $this->assertSame('integrado_al_producto_anual', $aporteActualizado->estado_desarrollo);
        $this->assertSame($version->id, $aporteActualizado->version_integrada_id);
        $this->assertSame($admin->id, $aporteActualizado->integrado_por_user_id);

        // 5. Criterio #11: Revocar la asignación del docente autor elimina inmediatamente sus permisos de edición
        // conservando su autoría histórica en la versión v1
        $this->actingAs($admin)
            ->post(route('ayllu.pi.asignaciones.revocar', $asignacionAutor->id), [
                'motivo_revocacion' => 'Cierre de encargo semestral',
            ])
            ->assertRedirect();

        $this->assertFalse($asignacionAutor->fresh()->activo);

        $this->actingAs($userDocenteAutor)
            ->put(route('ayllu.pi.aportes.update', $aporteCicloII->id), [
                'ajuste_operativo_mesa' => 'Intento tras revocación',
            ])
            ->assertForbidden();

        $this->assertSame($userDocenteAutor->id, $version->fresh()->autor_user_id, 'La autoría histórica permanece intacta tras revocar la asignación');
    }

    /**
     * Criterios #9 y #10:
     * - Una cuenta de estudiante consulta su proyecto; no accede a deliberaciones reservadas de `mesa_docente_ciclo`
     *   ni descarga archivos privados de la mesa docente.
     * - Compartir una síntesis comunitaria requiere revisión editorial (`pendiente_revision_editorial` -> `aprobada_publicada`)
     *   y aprobar la síntesis NO amplía los permisos de los documentos originales restringidos.
     */
    public function test_criterios_09_10_estudiante_no_ve_deliberaciones_reservadas_y_sintesis_no_amplia_permisos_de_archivos(): void
    {
        Storage::fake('local');

        $alumno = Alumno::whereHas('user', fn ($q) => $q->where(fn ($s) => $s->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado')))->firstOrFail();
        $cicloAlumno = strtoupper(trim((string) $alumno->ciclo?->nombre)) ?: 'I';
        if (!in_array($cicloAlumno, ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'], true)) {
            $cicloEst = Ciclo::whereIn('nombre', ['I', 'II'])->first();
            $alumno->update(['ciclo_id' => $cicloEst->id]);
            $cicloAlumno = strtoupper(trim($cicloEst->nombre));
        }

        $aporte = AylluPiAporteCurso::where('ciclo_romano', $cicloAlumno)->firstOrFail();
        $admin = User::role(['super-admin', 'admin'])->first() ?? User::first();

        // 1. Docente/Coordinador registra deliberación en bitácora y versión con archivo en `mesa_docente_ciclo` y `reserva_comunitaria`
        $archivoReservado = UploadedFile::fake()->create('testimonio_reservado_comunidad.pdf', 80, 'application/pdf');
        $this->actingAs($admin)->post(route('ayllu.pi.versiones.store', $aporte->id), [
            'titulo_version' => 'DELIBERACION_INTERNA_DOCENTE_SECRETA',
            'descripcion_avance' => 'Acuerdo interno de la mesa docente no visible para estudiantes.',
            'audiencia' => 'mesa_docente_ciclo',
            'archivo_evidencia' => $archivoReservado,
            'restriccion_circulacion' => 'reserva_comunitaria',
            'confirmacion_politica_menores' => '1',
        ])->assertRedirect();

        $versionReservada = AylluPiEvidenciaVersion::where('aporte_curso_id', $aporte->id)->latest('id')->firstOrFail();

        // 2. El estudiante abre la vista del proyecto y la ficha de su ciclo: NO ve la deliberación docente reservada ni puede descargar el archivo
        $this->actingAs($alumno->user)
            ->get(route('ayllu.pi.aportes.show', $aporte->id))
            ->assertOk()
            ->assertDontSee('DELIBERACION_INTERNA_DOCENTE_SECRETA');

        $this->actingAs($alumno->user)
            ->get(route('ayllu.pi.versiones.archivo', $versionReservada->id))
            ->assertForbidden();

        // 3. Criterio #10: Se solicita una síntesis comunitaria vinculada a esa evidencia;
        // inicialmente queda en `pendiente_revision_editorial` (no publicada automáticamente)
        $this->actingAs($admin)->post(route('ayllu.pi.sintesis.store'), [
            'ejecucion_id' => $aporte->ejecucion_id,
            'aporte_curso_id' => $aporte->id,
            'evidencia_version_id' => $versionReservada->id,
            'ciclo_romano' => $cicloAlumno,
            'programa_codigo' => 'TODOS',
            'titulo' => 'Síntesis pública del Mapa Parlante del Ciclo ' . $cicloAlumno,
            'resumen_sintesis' => 'Resumen aprobado para toda la comunidad educativa.',
            'autoria_reconocida' => 'Ciclo ' . $cicloAlumno . ' y Comunidad de Chinchero',
            'audiencia_objetivo' => 'comunidad_ayllu',
        ])->assertRedirect();

        $sintesis = AylluPiSintesisComunitaria::latest('id')->firstOrFail();
        $this->assertSame('pendiente_revision_editorial', $sintesis->estado_editorial);
        $this->assertNull($sintesis->ayllu_publicacion_id, 'No debe publicarse automáticamente sin pasar por revisión editorial');

        // 4. Coordinación aprueba y publica la síntesis en la comunidad
        $this->actingAs($admin)->post(route('ayllu.pi.sintesis.revisar', $sintesis->id), [
            'estado_editorial' => 'aprobada_publicada',
            'observacion_editorial' => 'Aprobada para difusión comunitaria.',
        ])->assertRedirect();

        $this->assertSame('aprobada_publicada', $sintesis->fresh()->estado_editorial);
        $this->assertNotNull($sintesis->fresh()->ayllu_publicacion_id);

        // 5. Incluso tras publicar la síntesis, el archivo original de la evidencia conserva intacta su restricción (403 para el estudiante)
        $this->actingAs($alumno->user)
            ->get(route('ayllu.pi.versiones.archivo', $versionReservada->id))
            ->assertForbidden();
    }

    /**
     * Criterio #13:
     * Los cinco hitos del proyecto de primer año respetan la fuente y NO se presentan como calendario aprobado de los otros años.
     */
    public function test_criterio_13_cinco_hitos_solo_en_anio_1_y_no_copiados_a_otros_anios(): void
    {
        $defAnio1 = AylluPiDefinicion::where('anio_formativo', 1)->where('es_version_base_matriz', true)->firstOrFail();
        $this->assertCount(5, $defAnio1->hitos_documentados ?? []);

        foreach ([2, 3, 4] as $otroAnio) {
            $defOtro = AylluPiDefinicion::where('anio_formativo', $otroAnio)->where('es_version_base_matriz', true)->firstOrFail();
            $this->assertNull(
                $defOtro->hitos_documentados,
                "El Año {$otroAnio} no debe tener copiados los 5 hitos documentales del Año 1"
            );
        }
    }

    /**
     * Verificación de auditoría (Secciones 2, 3 y 8):
     * Contrasta las 9 celdas anómalas (archivo, hoja, celda, valor bruto, curso de la misma columna y agrupación)
     * y la distribución de las 114 entradas por ciclo/programa directamente contra el XML de los archivos .xlsx originales.
     */
    public function test_correspondencia_9_celdas_anomalas_y_distribucion_programa_contra_archivos_xlsx_originales(): void
    {
        // 1. Verificar tabla exacta de las 9 celdas anómalas en BD
        $esperadas9 = [
            ['anio' => 1, 'ciclo' => 'II',  'celda_comp' => 'C18', 'celda_curso' => 'C17', 'bruto' => '41185.0', 'curso' => 'Práctica e investigación II', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 1, 'ciclo' => 'II',  'celda_comp' => 'E18', 'celda_curso' => 'E17', 'bruto' => '40424.0', 'curso' => 'Comunicación oral en la Educación Superior', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 1, 'ciclo' => 'II',  'celda_comp' => 'F18', 'celda_curso' => 'F17', 'bruto' => '40756.0', 'curso' => 'Resolución de problemas matemáticos II', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 2, 'ciclo' => 'III', 'celda_comp' => 'D8',  'celda_curso' => 'D7',  'bruto' => '40424.0', 'curso' => 'Desarrollo Personal II', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 2, 'ciclo' => 'III', 'celda_comp' => 'E8',  'celda_curso' => 'E7',  'bruto' => '41189.0', 'curso' => 'Arte, Creatividad y Aprendizaje', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 2, 'ciclo' => 'IV',  'celda_comp' => 'D18', 'celda_curso' => 'D17', 'bruto' => '39236.0', 'curso' => 'Deliberación y Participación', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 2, 'ciclo' => 'IV',  'celda_comp' => 'E18', 'celda_curso' => 'E17', 'bruto' => '41091.0', 'curso' => 'Ciencia y Epistemologías', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 3, 'ciclo' => 'V',   'celda_comp' => 'D8',  'celda_curso' => 'D7',  'bruto' => '41189.0', 'curso' => 'Literatura y Sociedad en Contextos Diversos', 'programa' => 'FORMACION_GENERAL'],
            ['anio' => 3, 'ciclo' => 'V',   'celda_comp' => 'L8',  'celda_curso' => 'L7',  'bruto' => '40727.0', 'curso' => 'Aprendizaje de la Comunicación I', 'programa' => 'PRIMARIA'],
        ];

        $anomaliasBd = AylluPiAporteCurso::where('es_anomalia_competencia', true)->orderBy('posicion_global')->get();
        $this->assertCount(9, $anomaliasBd);

        foreach ($esperadas9 as $idx => $esp) {
            $reg = $anomaliasBd[$idx];
            $this->assertSame($esp['anio'], (int) $reg->anio_formativo);
            $this->assertSame($esp['ciclo'], $reg->ciclo_romano);
            $this->assertSame($esp['celda_comp'], $reg->celda_competencias);
            $this->assertSame($esp['celda_curso'], $reg->celda_curso);
            $this->assertSame($esp['bruto'], $reg->competencias_fuente_bruto);
            $this->assertSame($esp['curso'], trim($reg->curso_fuente_original));
            $this->assertSame($esp['programa'], $reg->agrupacion_programa_codigo);

            // Contrastar directamente contra el XML del archivo .xlsx original si está accesible en el entorno de pruebas
            $rutaXlsx = $this->resolverRutaArchivoOriginal($reg->archivo_fuente);
            if ($rutaXlsx) {
                $celdasXml = $this->extraerCeldasXlsxOriginal($rutaXlsx);
                $this->assertSame(
                    $esp['curso'],
                    trim((string) ($celdasXml[$esp['celda_curso']]['valor'] ?? '')),
                    "El curso en celda {$esp['celda_curso']} del archivo {$reg->archivo_fuente} debe coincidir con el original"
                );
                $this->assertEquals(
                    (float) $esp['bruto'],
                    (float) ($celdasXml[$esp['celda_comp']]['raw'] ?? 0),
                    "El valor numérico bruto en celda {$esp['celda_comp']} del archivo {$reg->archivo_fuente} debe coincidir con el original"
                );
            }
        }

        // 2. Verificar distribución exacta de las 114 entradas por ciclo y programa (Sección 3)
        $distribucionEsperada = [
            'I'    => ['FORMACION_GENERAL' => 5, 'INICIAL' => 1, 'PRIMARIA' => 1, 'PRIMARIA_EIB' => 3, 'TOTAL' => 10],
            'II'   => ['FORMACION_GENERAL' => 5, 'INICIAL' => 1, 'PRIMARIA' => 1, 'PRIMARIA_EIB' => 3, 'TOTAL' => 10],
            'III'  => ['FORMACION_GENERAL' => 3, 'INICIAL' => 4, 'PRIMARIA' => 4, 'PRIMARIA_EIB' => 5, 'TOTAL' => 16],
            'IV'   => ['FORMACION_GENERAL' => 4, 'INICIAL' => 3, 'PRIMARIA' => 3, 'PRIMARIA_EIB' => 4, 'TOTAL' => 14],
            'V'    => ['FORMACION_GENERAL' => 2, 'INICIAL' => 5, 'PRIMARIA' => 5, 'PRIMARIA_EIB' => 6, 'TOTAL' => 18],
            'VI'   => ['FORMACION_GENERAL' => 2, 'INICIAL' => 5, 'PRIMARIA' => 5, 'PRIMARIA_EIB' => 6, 'TOTAL' => 18],
            'VII'  => ['FORMACION_GENERAL' => 2, 'INICIAL' => 4, 'PRIMARIA' => 5, 'PRIMARIA_EIB' => 5, 'TOTAL' => 16],
            'VIII' => ['FORMACION_GENERAL' => 2, 'INICIAL' => 3, 'PRIMARIA' => 3, 'PRIMARIA_EIB' => 4, 'TOTAL' => 12],
        ];

        $totalesPrograma = ['FORMACION_GENERAL' => 0, 'INICIAL' => 0, 'PRIMARIA' => 0, 'PRIMARIA_EIB' => 0];
        foreach ($distribucionEsperada as $cicloRomano => $esperado) {
            foreach (['FORMACION_GENERAL', 'INICIAL', 'PRIMARIA', 'PRIMARIA_EIB'] as $progCod) {
                $conteo = AylluPiAporteCurso::where('ciclo_romano', $cicloRomano)
                    ->where('agrupacion_programa_codigo', $progCod)
                    ->count();
                $this->assertSame(
                    $esperado[$progCod],
                    $conteo,
                    "Ciclo {$cicloRomano} programa {$progCod} debe tener {$esperado[$progCod]} entradas"
                );
                $totalesPrograma[$progCod] += $conteo;
            }
            $this->assertSame(
                $esperado['TOTAL'],
                AylluPiAporteCurso::where('ciclo_romano', $cicloRomano)->count()
            );
        }

        $this->assertSame(25, $totalesPrograma['FORMACION_GENERAL']);
        $this->assertSame(26, $totalesPrograma['INICIAL']);
        $this->assertSame(27, $totalesPrograma['PRIMARIA']);
        $this->assertSame(36, $totalesPrograma['PRIMARIA_EIB']);
        $this->assertSame(114, array_sum($totalesPrograma));
    }

    /**
     * Verificación de auditoría (Secciones 4 y 8):
     * - Los 5 hitos documentados de Año 1 coinciden fielmente con los dos documentos Word.
     * - Ausencia total de semanas inventadas («Sem. 1, 5, 8, 12 y 16») en definiciones, ejecuciones y vistas.
     * - Estado explícito «Pendiente de definición institucional» en Años 2, 3 y 4.
     */
    public function test_hitos_primer_anio_fieles_a_word_y_ausencia_de_semanas_inventadas(): void
    {
        $plazosEsperados = [
            1 => 'No se precisa fecha.',
            2 => 'Un mes antes de finalizar el primer semestre.',
            3 => 'No se precisa fecha.',
            4 => 'A mediados del siguiente semestre.',
            5 => 'Tres semanas antes de concluir el II ciclo.',
        ];

        $definicionesAnio1 = AylluPiDefinicion::where('anio_formativo', 1)->get();
        $this->assertGreaterThanOrEqual(3, $definicionesAnio1->count());

        foreach ($definicionesAnio1 as $def) {
            $hitos = $def->hitos_documentados ?? [];
            $this->assertCount(5, $hitos);
            foreach ($hitos as $hito) {
                $orden = (int) $hito['orden'];
                $this->assertSame($plazosEsperados[$orden], $hito['plazo_documental']);
                $this->assertStringNotContainsString('Sem.', $hito['plazo_documental']);
                $this->assertStringNotContainsString('Sem.', $hito['descripcion']);
            }
        }

        $admin = User::role(['super-admin', 'admin'])->first() ?? User::first();

        // Vista de hitos Año 1: muestra los plazos documentales reales y nunca «Sem. 1, 5, 8, 12 y 16»
        $resAnio1 = $this->actingAs($admin)->get(route('ayllu.proyecto', [
            'anio' => 1,
            'ciclo' => 'I',
            'vista' => 'hitos',
        ]));
        $resAnio1->assertOk()
            ->assertSee('No se precisa fecha.')
            ->assertSee('Un mes antes de finalizar el primer semestre.')
            ->assertSee('A mediados del siguiente semestre.')
            ->assertSee('Tres semanas antes de concluir el II ciclo.')
            ->assertDontSee('Sem. 1')
            ->assertDontSee('Sem. 5')
            ->assertDontSee('Sem. 8');

        // Vista de hitos Años 2, 3 y 4: muestra estado explícito «Pendiente de definición institucional»
        foreach ([2 => 'III', 3 => 'V', 4 => 'VII'] as $anio => $ciclo) {
            $this->actingAs($admin)->get(route('ayllu.proyecto', [
                'anio' => $anio,
                'ciclo' => $ciclo,
                'vista' => 'hitos',
            ]))
                ->assertOk()
                ->assertSee('Pendiente de definición institucional');
        }
    }

    /**
     * Verificación de auditoría (Sección 6):
     * Reparación técnica acotada, transaccional e idempotente que corrige diferencias documentales
     * preservando evidencias, versiones, asignaciones, comentarios y la definición adoptada por ejecuciones.
     */
    public function test_reparacion_tecnica_idempotente_preserva_evidencias_versiones_e_historial(): void
    {
        $defAnio1 = AylluPiDefinicion::where('anio_formativo', 1)->where('es_version_base_matriz', true)->firstOrFail();
        $ejecucionAnio1 = AylluPiEjecucion::where('anio_formativo', 1)->firstOrFail();
        $aporteAnio1 = AylluPiAporteCurso::where('definicion_id', $defAnio1->id)->firstOrFail();
        $admin = User::role(['super-admin', 'admin'])->first() ?? User::first();

        // Registrar un acuerdo operativo y una versión de evidencia que deben conservarse intactos tras reparar
        $aporteAnio1->update(['ajuste_operativo_mesa' => 'Acuerdo docente que debe preservarse tras reparación']);
        $evidenciaPrevia = AylluPiEvidenciaVersion::create([
            'aporte_curso_id' => $aporteAnio1->id,
            'ejecucion_id' => $ejecucionAnio1->id,
            'numero_version' => 1,
            'titulo_version' => 'Evidencia histórica a preservar',
            'descripcion_avance' => 'Contenido de evidencia registrada antes de la reparación.',
            'autoria_tipo' => 'docente',
            'autor_user_id' => $admin->id,
            'audiencia' => 'mesa_docente_ciclo',
            'restriccion_circulacion' => 'uso_interno_ciclo',
            'estado_version' => 'en_desarrollo',
        ]);

        // Simular una discrepancia técnica en hitos_documentados de la definición #1
        $hitosAlterados = $defAnio1->hitos_documentados;
        $hitosAlterados[0]['plazo_documental'] = 'Plazo antiguo no canónico';
        $defAnio1->update(['hitos_documentados' => $hitosAlterados]);

        // 1. Vista previa detecta exactamente 1 definición afectada con valor_anterior y correccion_propuesta
        $preview = $this->importService->previsualizarReparacionTecnica();
        $this->assertSame(1, $preview['total_definiciones_afectadas']);
        $this->assertSame('Plazo antiguo no canónico', $preview['definiciones_afectadas'][0]['valor_anterior'][0]['plazo_documental']);
        $this->assertSame('No se precisa fecha.', $preview['definiciones_afectadas'][0]['correccion_propuesta'][0]['plazo_documental']);

        // 2. Ejecutar reparación técnica
        $res1 = $this->importService->ejecutarReparacionTecnica($admin->id);
        $this->assertFalse($res1['idempotente']);
        $this->assertSame(1, $res1['definiciones_reparadas']);
        $this->assertSame('No se precisa fecha.', $defAnio1->fresh()->hitos_documentados[0]['plazo_documental']);

        // Verificar que la evidencia, el acuerdo operativo y la definición adoptada por la ejecución siguen intactos
        $this->assertSame('Acuerdo docente que debe preservarse tras reparación', $aporteAnio1->fresh()->ajuste_operativo_mesa);
        $this->assertNotNull(AylluPiEvidenciaVersion::find($evidenciaPrevia->id));
        $this->assertSame($defAnio1->id, $ejecucionAnio1->fresh()->definicion_id);

        // 3. Repetir reparación técnica es estrictamente idempotente (0 cambios)
        $res2 = $this->importService->ejecutarReparacionTecnica($admin->id);
        $this->assertTrue($res2['idempotente']);
        $this->assertSame(0, $res2['definiciones_reparadas']);
        $this->assertSame(0, $res2['aportes_reparados']);
    }

    /**
     * Verificación de auditoría (Secciones 3 y 7):
     * - El almacenamiento privado se realiza en el disco `local` (`storage/app/private/ayllu_pi_evidencias/...`) fuera de `public/`.
     * - Los filtros de programa no sustituyen la autorización del servidor: un estudiante de Primaria EIB en Ciclo III
     *   no puede acceder por URL directa a la ficha operativa ni a archivos de un aporte exclusivo de Inicial o Primaria monolingüe.
     */
    public function test_almacenamiento_privado_y_restriccion_por_programa_en_servidor(): void
    {
        Storage::fake('local');

        $alumno = Alumno::whereHas('user', fn ($q) => $q->where(fn ($s) => $s->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado')))->firstOrFail();
        $cicloIII = Ciclo::whereRaw('UPPER(TRIM(nombre)) = ?', ['III'])->firstOrFail();
        $alumno->update(['ciclo_id' => $cicloIII->id]);

        // Aporte exclusivo de INICIAL en Ciclo III (ej. posicion_global #24)
        $aporteInicialCicloIII = AylluPiAporteCurso::where('ciclo_romano', 'III')
            ->where('agrupacion_programa_codigo', 'INICIAL')
            ->firstOrFail();

        // Aporte común de FORMACION_GENERAL en Ciclo III
        $aporteComunCicloIII = AylluPiAporteCurso::where('ciclo_romano', 'III')
            ->where('agrupacion_programa_codigo', 'FORMACION_GENERAL')
            ->firstOrFail();

        // Si el estudiante pertenece a Primaria EIB (no a Inicial), puede ver el aporte común de Formación General,
        // pero recibe 403 al intentar abrir por URL directa la ficha operativa exclusiva de Inicial
        if (!$alumno->ciclo?->programa?->esInicial()) {
            $this->actingAs($alumno->user)
                ->get(route('ayllu.pi.aportes.show', $aporteComunCicloIII->id))
                ->assertOk();

            $this->actingAs($alumno->user)
                ->get(route('ayllu.pi.aportes.show', $aporteInicialCicloIII->id))
                ->assertForbidden();
        }

        // Comprobar que el archivo subido se guarda en el disco 'local' bajo 'private/ayllu_pi_evidencias/...'
        $admin = User::role(['super-admin', 'admin'])->first() ?? User::first();
        $archivo = UploadedFile::fake()->create('evidencia_disco_privado.pdf', 64, 'application/pdf');

        $this->actingAs($admin)->post(route('ayllu.pi.versiones.store', $aporteInicialCicloIII->id), [
            'titulo_version' => 'Evidencia en disco privado',
            'descripcion_avance' => 'Prueba de almacenamiento privado fuera de public.',
            'audiencia' => 'ciclo_completo',
            'archivo_evidencia' => $archivo,
            'restriccion_circulacion' => 'uso_interno_ciclo',
            'confirmacion_politica_menores' => '1',
        ])->assertRedirect();

        $version = AylluPiEvidenciaVersion::where('aporte_curso_id', $aporteInicialCicloIII->id)->latest('id')->firstOrFail();
        $this->assertStringStartsWith('private/ayllu_pi_evidencias/III/', $version->archivo_privado_path);
        Storage::disk('local')->assertExists($version->archivo_privado_path);

        // El estudiante de otro programa recibe 403 al intentar descargar el archivo de Inicial incluso aunque la audiencia sea ciclo_completo
        if (!$alumno->ciclo?->programa?->esInicial()) {
            $this->actingAs($alumno->user)
                ->get(route('ayllu.pi.versiones.archivo', $version->id))
                ->assertForbidden();
        }
    }

    protected function resolverRutaArchivoOriginal(string $nombreArchivo): ?string
    {
        $directorios = [
            '/Users/pavelchaparro/.gemini/antigravity/brain/c0b1163a-6d4a-4446-8054-5299f7c0fe6e/scratch/paquete_correcciones/fuentes_originales',
            '/Users/pavelchaparro/Desktop',
        ];

        $buscadoNfc = \Normalizer::normalize($nombreArchivo, \Normalizer::FORM_C);
        foreach ($directorios as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (scandir($dir) ?: [] as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                if (\Normalizer::normalize($item, \Normalizer::FORM_C) === $buscadoNfc) {
                    return $dir . DIRECTORY_SEPARATOR . $item;
                }
            }
        }

        return null;
    }

    protected function extraerCeldasXlsxOriginal(string $rutaXlsx): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($rutaXlsx) !== true) {
            return [];
        }

        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $sx = new \SimpleXMLElement($ssXml);
            foreach ($sx->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } else {
                    $partes = [];
                    foreach ($si->r as $r) {
                        $partes[] = (string) $r->t;
                    }
                    $sharedStrings[] = implode('', $partes);
                }
            }
        }

        $celdas = [];
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml !== false) {
            $sxSheet = new \SimpleXMLElement($sheetXml);
            foreach ($sxSheet->sheetData->row as $row) {
                foreach ($row->c as $c) {
                    $coord = (string) $c['r'];
                    $tipo = (string) ($c['t'] ?? '');
                    $raw = isset($c->v) ? (string) $c->v : null;
                    $valor = $raw;
                    if ($tipo === 's' && $raw !== null && isset($sharedStrings[(int) $raw])) {
                        $valor = $sharedStrings[(int) $raw];
                    }
                    $celdas[$coord] = [
                        'raw' => $raw,
                        'valor' => $valor,
                        'tipo' => $tipo,
                    ];
                }
            }
        }

        $zip->close();
        return $celdas;
    }

    protected function crearUsuarioDocentePrueba(string $nombre): User
    {
        $dni = (string) random_int(60000000, 69999999);
        $user = User::create([
            'name' => $nombre,
            'apellidos' => 'Puklla Test',
            'dni' => $dni,
            'email' => 'docente.pi.' . uniqid() . '@puklla.edu.pe',
            'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('docente');

        Docente::create([
            'nombre' => $nombre,
            'dni' => $dni,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
