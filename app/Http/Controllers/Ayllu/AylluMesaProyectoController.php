<?php

namespace App\Http\Controllers\Ayllu;

use App\Http\Controllers\Controller;
use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluPiAporteCurso;
use App\Models\Ayllu\AylluPiArticulacion;
use App\Models\Ayllu\AylluPiAsignacionMesa;
use App\Models\Ayllu\AylluPiAuditoriaEvento;
use App\Models\Ayllu\AylluPiBitacoraMesa;
use App\Models\Ayllu\AylluPiDefinicion;
use App\Models\Ayllu\AylluPiEjecucion;
use App\Models\Ayllu\AylluPiEvidenciaVersion;
use App\Models\Ayllu\AylluPiLoteImportacion;
use App\Models\Ayllu\AylluPiSintesisComunitaria;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\User;
use App\Services\AylluAuthorizationService;
use App\Services\AylluContextService;
use App\Services\AylluPiImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AylluMesaProyectoController extends Controller
{
    public const MAPA_ANIO_CICLOS = [
        1 => ['I', 'II'],
        2 => ['III', 'IV'],
        3 => ['V', 'VI'],
        4 => ['VII', 'VIII'],
    ];

    public const MAPA_CICLO_ANIO = [
        'I' => 1, 'II' => 1,
        'III' => 2, 'IV' => 2,
        'V' => 3, 'VI' => 3,
        'VII' => 4, 'VIII' => 4,
    ];

    public const PROGRAMAS_DOCUMENTADOS = [
        'TODOS' => 'Todos los programas del ciclo',
        'INICIAL' => 'Educación Inicial (+ Formación General)',
        'PRIMARIA' => 'Educación Primaria (+ Formación General)',
        'PRIMARIA_EIB' => 'Educación Primaria EIB (+ Formación General)',
        'FORMACION_GENERAL' => 'Solo Formación General (Común)',
    ];

    public function __construct(
        protected AylluContextService $contextService,
        protected AylluAuthorizationService $authService,
        protected AylluPiImportService $importService
    ) {
    }

    /**
     * Vista principal de la Mesa Docente, Vista de Estudiante y Vista de Coordinación
     * de Proyectos Integradores Anuales.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $this->importService->asegurarImportacionInicial($user->id);

        // 1. Resolver combinación válida de Año Lectivo, Año Formativo (1..4), Ciclo (I..VIII) y Programa
        $anioLectivo = $request->query('lectivo', '2026');
        [$anioFormativo, $cicloSeleccionado] = $this->resolverAnioYCicloSinCombinacionImposible($request, $ctx);
        $programaFiltro = $this->resolverProgramaFiltro($request, $ctx);
        $vistaActiva = $request->query('vista', 'matriz');
        if (!in_array($vistaActiva, ['matriz', 'integracion', 'bitacora', 'hitos', 'fuentes', 'coordinacion', 'portafolio'], true)) {
            $vistaActiva = 'matriz';
        }

        $ciclosDelAnio = self::MAPA_ANIO_CICLOS[$anioFormativo];

        // 2. Ejecución anual y definición adoptada
        $ejecucion = AylluPiEjecucion::with(['definicion', 'proyectoInstitucional', 'periodoActual'])
            ->where('anio_lectivo', $anioLectivo)
            ->where('anio_formativo', $anioFormativo)
            ->first()
            ?? AylluPiEjecucion::with(['definicion', 'proyectoInstitucional', 'periodoActual'])
                ->where('anio_formativo', $anioFormativo)
                ->first();

        $definicionActiva = $ejecucion?->definicion
            ?? AylluPiDefinicion::where('anio_formativo', $anioFormativo)
                ->where('es_version_base_matriz', true)
                ->orderByDesc('version_numero')
                ->first();

        // Todas las versiones documentales de este año (matriz_excel, word_2020_2024, word_copia_03, revisiones)
        $versionesProyectoAnio = AylluPiDefinicion::where('anio_formativo', $anioFormativo)
            ->orderBy('version_numero')
            ->get();

        // Resumen rápido de los 4 años para las tarjetas superiores de navegación
        $resumenCuatroAnios = AylluPiDefinicion::where('es_version_base_matriz', true)
            ->orderBy('anio_formativo')
            ->get()
            ->keyBy('anio_formativo');

        // 3. Aportes de los dos ciclos del año (para vista anual de continuidad y matriz del ciclo activo)
        $aportesAnio = AylluPiAporteCurso::with([
            'cursoInstitucional.docentes',
            'cursoInstitucional.ciclo.programa',
            'versionIntegrada.autor',
            'integradoPor',
            'asignacionesActivas.usuario',
            'versionesEvidencia.autor',
        ])
            ->where('definicion_id', $definicionActiva?->id)
            ->orderBy('posicion_global')
            ->get();

        // Filtrar por el programa seleccionado incluyendo los aportes comunes (FORMACION_GENERAL) que correspondan,
        // sin mezclar jamás Primaria con Primaria EIB (Criterio #3).
        $aportesAnioFiltradosPrograma = $this->filtrarAportesPorPrograma($aportesAnio, $programaFiltro);
        $aportesCicloActivo = $aportesAnioFiltradosPrograma
            ->where('ciclo_romano', $cicloSeleccionado)
            ->values();

        // 4. Permisos del usuario en el ciclo seleccionado
        $esCoordinador = $this->authService->esCoordinadorProyectos($user, $cicloSeleccionado, $ejecucion?->id, $ctx);
        $perteneceMesaCiclo = $this->authService->perteneceAMesaDocenteDelCiclo($user, $cicloSeleccionado, $ejecucion?->id, $ctx);
        $esEstudianteDelCiclo = (!$ctx['esDocente'] && !$esCoordinador)
            && $this->authService->estudiantePerteneceACicloRomano($user, $cicloSeleccionado, $ctx);

        // Permisos por aporte y filtrado de evidencias según audiencia autorizada
        $permisosPorAporte = [];
        $evidenciasVisiblesPorAporte = [];
        foreach ($aportesAnioFiltradosPrograma as $ap) {
            $permisosPorAporte[$ap->id] = [
                'ver_mesa' => $this->authService->puedeVerAporteMesa($user, $ap, $ctx),
                'editar' => $this->authService->puedeEditarAporteCurso($user, $ap, $ctx),
                'registrar_evidencia' => $this->authService->puedeRegistrarEvidenciaEnAporte($user, $ap, $ctx),
                'revisar' => $this->authService->puedeRevisarAporte($user, $ap, $ctx),
                'integrar' => $this->authService->puedeIntegrarAlProductoAnual($user, $ap, $ctx),
            ];

            $evidenciasVisiblesPorAporte[$ap->id] = $ap->versionesEvidencia
                ->filter(fn (AylluPiEvidenciaVersion $v) => $this->authService->puedeVerEvidenciaVersion($user, $v, $ctx))
                ->values();
        }

        // 5. Articulaciones y dependencias entre cursos del año
        $articulacionesAnio = $ejecucion
            ? AylluPiArticulacion::with(['aporteOrigen', 'aporteDestino', 'acordadoPor', 'validadoPor'])
                ->where('ejecucion_id', $ejecucion->id)
                ->latest()
                ->get()
            : collect();

        // 6. Bitácora cronológica filtrada estrictamente por audiencia en servidor (estudiantes nunca ven mesa_docente_ciclo)
        $bitacorasRaw = $ejecucion
            ? AylluPiBitacoraMesa::with(['aporte', 'autor', 'evidenciaVersion', 'taqeGrupo'])
                ->where('ejecucion_id', $ejecucion->id)
                ->latest()
                ->take(50)
                ->get()
            : collect();

        $bitacorasVisibles = $bitacorasRaw
            ->filter(fn (AylluPiBitacoraMesa $b) => $this->authService->puedeVerEntradaBitacora($user, $b, $ctx))
            ->values();

        // 7. Síntesis comunitarias del año/ciclo
        $sintesisComunitarias = AylluPiSintesisComunitaria::with(['aporte', 'solicitante', 'revisor', 'publicacionComunitaria'])
            ->whereIn('ciclo_romano', $ciclosDelAnio)
            ->when(!$perteneceMesaCiclo && !$esCoordinador, function ($q) {
                $q->where('estado_editorial', 'aprobada_publicada');
            })
            ->latest()
            ->get();

        // 8. Alertas de calidad documental y las 9 anomalías de competencias (de toda la matriz y del año activo)
        $todasAnomaliasCompetencias = AylluPiAporteCurso::where('es_anomalia_competencia', true)
            ->orderBy('posicion_global')
            ->get();

        $resumenConciliacion = $this->importService->previsualizarImportacion();

        // 9. Datos adicionales para Coordinación y para el Portafolio / Equipos Taq'e del estudiante
        $asignacionesMesaActivas = $esCoordinador
            ? AylluPiAsignacionMesa::with(['usuario', 'aporte', 'curso'])
                ->whereIn('ciclo_romano', array_merge($ciclosDelAnio, ['TODOS']))
                ->orderByDesc('activo')
                ->latest()
                ->get()
            : collect();

        $docentesDisponibles = $esCoordinador
            ? Docente::with('user')->whereNotNull('user_id')->orderBy('nombre')->get()
            : collect();

        $auditoriaReciente = $esCoordinador
            ? AylluPiAuditoriaEvento::with('actor')->latest('id')->take(20)->get()
            : collect();

        // Aportes formativos de cursos y portafolio docente del propio usuario (integración con Fase 1)
        $misEntregasPortafolio = AylluEntregaFormativa::with(['curso.ciclo', 'unidad', 'taqeGrupo'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('ayllu.proyecto_portafolio', array_merge($ctx, [
            'seccionActiva' => 'proyecto',
            'anioLectivo' => $anioLectivo,
            'anioFormativo' => $anioFormativo,
            'cicloSeleccionado' => $cicloSeleccionado,
            'ciclosDelAnio' => $ciclosDelAnio,
            'programaFiltro' => $programaFiltro,
            'programasDocumentados' => self::PROGRAMAS_DOCUMENTADOS,
            'vistaActiva' => $vistaActiva,
            'ejecucion' => $ejecucion,
            'definicionActiva' => $definicionActiva,
            'versionesProyectoAnio' => $versionesProyectoAnio,
            'resumenCuatroAnios' => $resumenCuatroAnios,
            'aportesAnio' => $aportesAnioFiltradosPrograma,
            'aportesCicloActivo' => $aportesCicloActivo,
            'permisosPorAporte' => $permisosPorAporte,
            'evidenciasVisiblesPorAporte' => $evidenciasVisiblesPorAporte,
            'esCoordinador' => $esCoordinador,
            'perteneceMesaCiclo' => $perteneceMesaCiclo,
            'esEstudianteDelCiclo' => $esEstudianteDelCiclo,
            'articulacionesAnio' => $articulacionesAnio,
            'bitacorasVisibles' => $bitacorasVisibles,
            'sintesisComunitarias' => $sintesisComunitarias,
            'todasAnomaliasCompetencias' => $todasAnomaliasCompetencias,
            'resumenConciliacion' => $resumenConciliacion,
            'asignacionesMesaActivas' => $asignacionesMesaActivas,
            'docentesDisponibles' => $docentesDisponibles,
            'auditoriaReciente' => $auditoriaReciente,
            'misEntregasPortafolio' => $misEntregasPortafolio,
            'estadosDesarrollo' => AylluPiAporteCurso::ESTADOS_DESARROLLO,
            'audienciasCatalogo' => AylluPiEvidenciaVersion::AUDIENCIAS,
        ]));
    }

    /**
     * Ficha detallada de un aporte de curso (fuente, celda, competencias, evidencias/versiones,
     * acuerdos de articulación y bitácora).
     * Bloquea el acceso por URL directa a docentes ajenos o estudiantes de otros ciclos (Criterio #8 y #9).
     */
    public function showAporte(Request $request, AylluPiAporteCurso $aporte)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeVerAporteMesa($user, $aporte, $ctx),
            403,
            'No tienes autorización para consultar la ficha operativa de este aporte en otro ciclo/ámbito.'
        );

        $aporte->load([
            'definicion',
            'ejecucion.periodoActual',
            'cursoInstitucional.docentes',
            'cursoInstitucional.relacionsilabo.unidades',
            'versionIntegrada.autor',
            'integradoPor',
            'asignacionesActivas.usuario',
            'versionesEvidencia.autor',
            'versionesEvidencia.revisor',
            'versionesEvidencia.taqeGrupo',
            'articulacionesEntregadas.aporteDestino',
            'articulacionesRecibidas.aporteOrigen',
            'bitacoras.autor',
        ]);

        $versionesVisibles = $aporte->versionesEvidencia
            ->filter(fn (AylluPiEvidenciaVersion $v) => $this->authService->puedeVerEvidenciaVersion($user, $v, $ctx))
            ->values();

        $bitacorasVisibles = $aporte->bitacoras
            ->filter(fn (AylluPiBitacoraMesa $b) => $this->authService->puedeVerEntradaBitacora($user, $b, $ctx))
            ->values();

        $otrosAportesAnio = AylluPiAporteCurso::where('definicion_id', $aporte->definicion_id)
            ->where('id', '!=', $aporte->id)
            ->orderBy('posicion_global')
            ->get();

        $cursosCatalogoCiclo = Curso::with('ciclo.programa')
            ->whereHas('ciclo', function ($q) use ($aporte) {
                $q->whereRaw('UPPER(TRIM(nombre)) = ?', [$aporte->ciclo_romano]);
            })
            ->orderBy('nombre')
            ->get();

        return view('ayllu.pi_ficha_aporte', array_merge($ctx, [
            'seccionActiva' => 'proyecto',
            'aporte' => $aporte,
            'versionesVisibles' => $versionesVisibles,
            'bitacorasVisibles' => $bitacorasVisibles,
            'otrosAportesAnio' => $otrosAportesAnio,
            'cursosCatalogoCiclo' => $cursosCatalogoCiclo,
            'puedeEditarAporte' => $this->authService->puedeEditarAporteCurso($user, $aporte, $ctx),
            'puedeRegistrarEvidencia' => $this->authService->puedeRegistrarEvidenciaEnAporte($user, $aporte, $ctx),
            'puedeRevisarAporte' => $this->authService->puedeRevisarAporte($user, $aporte, $ctx),
            'puedeIntegrarAnual' => $this->authService->puedeIntegrarAlProductoAnual($user, $aporte, $ctx),
            'esCoordinador' => $this->authService->esCoordinadorProyectos($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx),
            'perteneceMesaCiclo' => $this->authService->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx),
            'estadosDesarrollo' => AylluPiAporteCurso::ESTADOS_DESARROLLO,
            'audienciasCatalogo' => AylluPiEvidenciaVersion::AUDIENCIAS,
        ]));
    }

    /**
     * Actualiza los acuerdos operativos del aporte en la mesa docente (sin sobrescribir el texto fuente de la matriz).
     */
    public function actualizarAporteOperativo(Request $request, AylluPiAporteCurso $aporte)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeEditarAporteCurso($user, $aporte, $ctx),
            403,
            'Solo el docente asignado a este curso/aporte o la coordinación pueden editar sus ajustes operativos.'
        );

        $validated = $request->validate([
            'ajuste_operativo_mesa' => 'nullable|string|max:4000',
            'curso_institucional_id' => 'nullable|exists:cursos,id',
        ]);

        $datosActualizar = [
            'ajuste_operativo_mesa' => $validated['ajuste_operativo_mesa'] ?? null,
        ];

        if ($request->has('curso_institucional_id')
            && $this->authService->esCoordinadorProyectos($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)
        ) {
            $datosActualizar['curso_institucional_id'] = $validated['curso_institucional_id'] ?: null;
            $datosActualizar['estado_correspondencia'] = !empty($validated['curso_institucional_id'])
                ? 'confirmada'
                : 'propuesta_pendiente';
        }

        $aporte->update($datosActualizar);

        $this->registrarAuditoria(
            'actualizar_aporte_operativo',
            'ayllu_pi_aportes_curso',
            $aporte->id,
            $user->id,
            $aporte->ciclo_romano,
            $aporte->agrupacion_programa_codigo,
            [
                'celda_curso' => $aporte->celda_curso,
                'curso_institucional_id' => $aporte->curso_institucional_id,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Ajustes operativos del aporte guardados conservando intacta la fuente documental.');
    }

    /**
     * Registra una nueva versión de evidencia sobre un aporte (por docente o por equipo de estudiantes),
     * con control de audiencia, solicitud de revisión compartida y almacenamiento privado de archivos.
     */
    public function storeEvidenciaVersion(Request $request, AylluPiAporteCurso $aporte)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeRegistrarEvidenciaEnAporte($user, $aporte, $ctx),
            403,
            'No tienes autorización para registrar evidencias en este aporte de curso.'
        );

        $validated = $request->validate([
            'titulo_version' => 'required|string|max:220',
            'descripcion_avance' => 'required|string|max:5000',
            'audiencia' => 'required|in:borrador_personal,mesa_docente_ciclo,equipo_estudiantes,ciclo_completo',
            'enlace_recurso' => 'nullable|url|max:500',
            'archivo_evidencia' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,md,png,jpg,jpeg,mp3,mp4',
            'necesidades_articulacion' => 'nullable|string|max:2000',
            'reconocimiento_autoria_comunitaria' => 'nullable|string|max:1500',
            'restriccion_circulacion' => 'nullable|in:ninguna,uso_interno_ciclo,reserva_comunitaria',
            'solicitar_revision_compartida' => 'nullable|boolean',
            'taqe_grupo_id' => 'nullable|integer|exists:taqe_grupos,id',
            'aportes_vinculados' => 'nullable|array',
            'aportes_vinculados.*' => 'integer|exists:ayllu_pi_aportes_curso,id',
            'confirmacion_politica_menores' => 'nullable|accepted',
        ]);

        if ((!empty($validated['enlace_recurso']) || $request->hasFile('archivo_evidencia'))
            && !$request->boolean('confirmacion_politica_menores')
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'confirmacion_politica_menores' => 'Debes confirmar que el recurso o archivo cumple la política de protección de datos e imagen de niños y niñas y acuerdos comunitarios.',
                ]);
        }

        // Si quien registra es estudiante, la audiencia no puede ser mesa_docente_ciclo; se fija en equipo_estudiantes o borrador_personal
        $esDocenteOCoordinador = $this->authService->puedeEditarAporteCurso($user, $aporte, $ctx);
        $audienciaEfectiva = $validated['audiencia'];
        if (!$esDocenteOCoordinador && $audienciaEfectiva === 'mesa_docente_ciclo') {
            $audienciaEfectiva = 'equipo_estudiantes';
        }

        // Si envía taqe_grupo_id, comprobar que pertenezca al equipo
        $taqeGrupoId = !empty($validated['taqe_grupo_id']) ? (int) $validated['taqe_grupo_id'] : null;
        if ($taqeGrupoId && !$esDocenteOCoordinador && !empty($ctx['alumno'])) {
            $perteneceEquipo = DB::table('taqe_grupo_alumno')
                ->where('taqe_grupo_id', $taqeGrupoId)
                ->where('alumno_id', $ctx['alumno']->id)
                ->where('estado', 'aceptado')
                ->exists();
            abort_unless($perteneceEquipo, 403, 'Solo puedes vincular evidencias a un equipo Taq\'e al que pertenezcas.');
        }

        $archivoPath = null;
        $archivoOriginal = null;
        $archivoMime = null;
        if ($request->hasFile('archivo_evidencia')) {
            $file = $request->file('archivo_evidencia');
            $archivoOriginal = $file->getClientOriginalName();
            $archivoMime = $file->getClientMimeType();
            $archivoPath = $file->store('private/ayllu_pi_evidencias/' . $aporte->ciclo_romano, 'local');
        }

        $solicitaRevision = $request->boolean('solicitar_revision_compartida');
        $estadoVersion = $solicitaRevision ? 'para_revision_compartida' : 'en_desarrollo';

        $version = DB::transaction(function () use (
            $aporte,
            $user,
            $esDocenteOCoordinador,
            $validated,
            $audienciaEfectiva,
            $taqeGrupoId,
            $archivoPath,
            $archivoOriginal,
            $archivoMime,
            $estadoVersion
        ) {
            $maxVer = (int) AylluPiEvidenciaVersion::where('aporte_curso_id', $aporte->id)->max('numero_version');
            $nuevoNumero = $maxVer + 1;

            $nuevaVersion = AylluPiEvidenciaVersion::create([
                'aporte_curso_id' => $aporte->id,
                'ejecucion_id' => $aporte->ejecucion_id,
                'curso_id' => $aporte->curso_institucional_id,
                'taqe_grupo_id' => $taqeGrupoId,
                'numero_version' => $nuevoNumero,
                'titulo_version' => $validated['titulo_version'],
                'descripcion_avance' => $validated['descripcion_avance'],
                'autoria_tipo' => $esDocenteOCoordinador
                    ? 'docente'
                    : ($taqeGrupoId ? 'equipo_estudiantes' : 'estudiante_individual'),
                'autor_user_id' => $user->id,
                'audiencia' => $audienciaEfectiva,
                'enlace_recurso' => $validated['enlace_recurso'] ?? null,
                'archivo_privado_path' => $archivoPath,
                'archivo_nombre_original' => $archivoOriginal,
                'archivo_mime' => $archivoMime,
                'necesidades_articulacion' => $validated['necesidades_articulacion'] ?? null,
                'reconocimiento_autoria_comunitaria' => $validated['reconocimiento_autoria_comunitaria'] ?? null,
                'restriccion_circulacion' => $validated['restriccion_circulacion'] ?? 'ninguna',
                'estado_version' => $estadoVersion,
                'aportes_vinculados_json' => !empty($validated['aportes_vinculados'])
                    ? array_values(array_map('intval', $validated['aportes_vinculados']))
                    : null,
            ]);

            // Actualizar el estado del aporte si no estaba ya integrado al producto anual
            if ($aporte->estado_desarrollo !== 'integrado_al_producto_anual') {
                $aporte->update([
                    'estado_desarrollo' => $estadoVersion,
                ]);
            }

            if ($aporte->ejecucion_id && $audienciaEfectiva !== 'borrador_personal') {
                AylluPiBitacoraMesa::create([
                    'ejecucion_id' => $aporte->ejecucion_id,
                    'aporte_curso_id' => $aporte->id,
                    'evidencia_version_id' => $nuevaVersion->id,
                    'ciclo_romano' => $aporte->ciclo_romano,
                    'programa_codigo' => $aporte->agrupacion_programa_codigo,
                    'tipo_entrada' => 'avance',
                    'audiencia' => $audienciaEfectiva,
                    'titulo' => "Versión v{$nuevoNumero} registrada en «{$aporte->curso_fuente_normalizado}»: {$nuevaVersion->titulo_version}",
                    'contenido' => $validated['descripcion_avance'],
                    'autor_user_id' => $user->id,
                    'taqe_grupo_id' => $taqeGrupoId,
                ]);
            }

            return $nuevaVersion;
        });

        $this->registrarAuditoria(
            'registrar_evidencia_version',
            'ayllu_pi_evidencia_versiones',
            $version->id,
            $user->id,
            $aporte->ciclo_romano,
            $aporte->agrupacion_programa_codigo,
            [
                'aporte_curso_id' => $aporte->id,
                'numero_version' => $version->numero_version,
                'audiencia' => $version->audiencia,
                'estado_version' => $version->estado_version,
                'tiene_archivo_privado' => $archivoPath !== null,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', "Versión v{$version->numero_version} registrada correctamente con audiencia «" . (AylluPiEvidenciaVersion::AUDIENCIAS[$version->audiencia]['label'] ?? $version->audiencia) . "».");
    }

    /**
     * Revisión compartida, devolución a desarrollo o integración de una versión específica al producto anual.
     */
    public function revisarOIntegrarVersion(Request $request, AylluPiEvidenciaVersion $version)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $aporte = $version->aporte ?? AylluPiAporteCurso::findOrFail($version->aporte_curso_id);

        $validated = $request->validate([
            'decision' => 'required|in:revisado,devuelto_a_desarrollo,integrado_al_producto_anual',
            'comentario_revision' => 'required|string|max:3000',
        ]);

        if ($validated['decision'] === 'integrado_al_producto_anual') {
            abort_unless(
                $this->authService->puedeIntegrarAlProductoAnual($user, $aporte, $ctx),
                403,
                'Solo la coordinación del proyecto/ciclo puede validar la integración final de una versión al producto anual.'
            );
        } else {
            abort_unless(
                $this->authService->puedeRevisarAporte($user, $aporte, $ctx),
                403,
                'No perteneces a la mesa docente autorizada de este ciclo para revisar este aporte.'
            );
        }

        DB::transaction(function () use ($version, $aporte, $user, $validated) {
            $version->update([
                'estado_version' => $validated['decision'],
                'revisado_por_user_id' => $user->id,
                'revisado_at' => now(),
                'comentario_revision' => $validated['comentario_revision'],
            ]);

            if ($validated['decision'] === 'integrado_al_producto_anual') {
                $aporte->update([
                    'estado_desarrollo' => 'integrado_al_producto_anual',
                    'version_integrada_id' => $version->id,
                    'integrado_por_user_id' => $user->id,
                    'integrado_at' => now(),
                    'nota_integracion_anual' => $validated['comentario_revision'],
                ]);
            } elseif ($validated['decision'] === 'devuelto_a_desarrollo') {
                $aporte->update([
                    'estado_desarrollo' => 'en_desarrollo',
                    'version_integrada_id' => (int) $aporte->version_integrada_id === (int) $version->id ? null : $aporte->version_integrada_id,
                ]);
            } else {
                if ($aporte->estado_desarrollo !== 'integrado_al_producto_anual') {
                    $aporte->update([
                        'estado_desarrollo' => 'revisado',
                    ]);
                }
            }

            if ($aporte->ejecucion_id) {
                AylluPiBitacoraMesa::create([
                    'ejecucion_id' => $aporte->ejecucion_id,
                    'aporte_curso_id' => $aporte->id,
                    'evidencia_version_id' => $version->id,
                    'ciclo_romano' => $aporte->ciclo_romano,
                    'programa_codigo' => $aporte->agrupacion_programa_codigo,
                    'tipo_entrada' => 'devolucion_formativa',
                    'audiencia' => $version->audiencia === 'borrador_personal' ? 'mesa_docente_ciclo' : $version->audiencia,
                    'titulo' => "Revisión de v{$version->numero_version} («{$aporte->curso_fuente_normalizado}»): " . strtoupper(str_replace('_', ' ', $validated['decision'])),
                    'contenido' => $validated['comentario_revision'],
                    'autor_user_id' => $user->id,
                    'taqe_grupo_id' => $version->taqe_grupo_id,
                ]);
            }
        });

        $this->registrarAuditoria(
            'revisar_o_integrar_version',
            'ayllu_pi_evidencia_versiones',
            $version->id,
            $user->id,
            $aporte->ciclo_romano,
            $aporte->agrupacion_programa_codigo,
            [
                'aporte_curso_id' => $aporte->id,
                'numero_version' => $version->numero_version,
                'decision' => $validated['decision'],
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Revisión registrada en el historial de versiones del aporte.');
    }

    /**
     * Descarga protegida en servidor de un archivo privado asociado a una versión de evidencia.
     * Nunca se sirve por enlace público permanente (Criterio #8 y #10).
     */
    public function descargarArchivoEvidencia(Request $request, AylluPiEvidenciaVersion $version)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeDescargarArchivoEvidencia($user, $version, $ctx),
            403,
            'No tienes autorización para descargar este archivo o su circulación tiene reserva comunitaria/retiro de autorización.'
        );

        abort_unless(
            $version->archivo_privado_path && Storage::disk('local')->exists($version->archivo_privado_path),
            404,
            'El archivo solicitado no se encuentra disponible en el repositorio seguro.'
        );

        $aporte = $version->aporte;
        $this->registrarAuditoria(
            'descargar_archivo_evidencia',
            'ayllu_pi_evidencia_versiones',
            $version->id,
            $user->id,
            $aporte?->ciclo_romano,
            $aporte?->agrupacion_programa_codigo,
            ['numero_version' => $version->numero_version],
            $request->ip()
        );

        return Storage::disk('local')->download(
            $version->archivo_privado_path,
            $version->archivo_nombre_original ?: ('evidencia_v' . $version->numero_version . '.dat')
        );
    }

    /**
     * Permite registrar restricciones de circulación o retiro de autorización comunitaria sobre una evidencia.
     */
    public function actualizarRestriccionEvidencia(Request $request, AylluPiEvidenciaVersion $version)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $aporte = $version->aporte ?? AylluPiAporteCurso::findOrFail($version->aporte_curso_id);

        $esAutorOCoordinador = ((int) $version->autor_user_id === (int) $user->id)
            || $this->authService->esCoordinadorProyectos($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx);

        abort_unless($esAutorOCoordinador, 403, 'Solo la autoría o la coordinación pueden modificar la autorización de circulación.');

        $validated = $request->validate([
            'restriccion_circulacion' => 'required|in:ninguna,uso_interno_ciclo,reserva_comunitaria,autorizacion_retirada',
            'audiencia' => 'nullable|in:borrador_personal,mesa_docente_ciclo,equipo_estudiantes,ciclo_completo',
        ]);

        $version->update([
            'restriccion_circulacion' => $validated['restriccion_circulacion'],
            'audiencia' => $validated['audiencia'] ?? $version->audiencia,
        ]);

        $this->registrarAuditoria(
            'actualizar_restriccion_circulacion',
            'ayllu_pi_evidencia_versiones',
            $version->id,
            $user->id,
            $aporte->ciclo_romano,
            $aporte->agrupacion_programa_codigo,
            [
                'restriccion_circulacion' => $version->restriccion_circulacion,
                'audiencia' => $version->audiencia,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Condiciones de circulación y audiencia actualizadas.');
    }

    /**
     * Registra un acuerdo de articulación entre dos cursos del proyecto anual (qué insumo entrega uno a otro).
     */
    public function storeArticulacion(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $validated = $request->validate([
            'ejecucion_id' => 'required|exists:ayllu_pi_ejecuciones,id',
            'aporte_origen_id' => 'required|exists:ayllu_pi_aportes_curso,id|different:aporte_destino_id',
            'aporte_destino_id' => 'required|exists:ayllu_pi_aportes_curso,id',
            'insumo_requerido' => 'required|string|max:255',
            'criterio_articulacion' => 'nullable|string|max:2000',
            'fecha_acordada' => 'nullable|date',
        ]);

        $aporteOrigen = AylluPiAporteCurso::findOrFail($validated['aporte_origen_id']);
        abort_unless(
            $this->authService->perteneceAMesaDocenteDelCiclo($user, $aporteOrigen->ciclo_romano, (int) $validated['ejecucion_id'], $ctx),
            403,
            'Solo los docentes o coordinadores de la mesa del ciclo pueden acordar dependencias de articulación.'
        );

        $articulacion = AylluPiArticulacion::create([
            'ejecucion_id' => $validated['ejecucion_id'],
            'aporte_origen_id' => $validated['aporte_origen_id'],
            'aporte_destino_id' => $validated['aporte_destino_id'],
            'insumo_requerido' => $validated['insumo_requerido'],
            'criterio_articulacion' => $validated['criterio_articulacion'] ?? null,
            'fecha_acordada' => $validated['fecha_acordada'] ?? null,
            'estado_revision' => 'pendiente',
            'acordado_por_user_id' => $user->id,
        ]);

        $this->registrarAuditoria(
            'acordar_articulacion_cursos',
            'ayllu_pi_articulaciones',
            $articulacion->id,
            $user->id,
            $aporteOrigen->ciclo_romano,
            $aporteOrigen->agrupacion_programa_codigo,
            [
                'aporte_origen_id' => $aporteOrigen->id,
                'aporte_destino_id' => (int) $validated['aporte_destino_id'],
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Dependencia de articulación registrada entre los cursos de la mesa.');
    }

    /**
     * Registra una entrada en la Bitácora cronológica de la Mesa Docente.
     */
    public function storeBitacora(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $validated = $request->validate([
            'ejecucion_id' => 'required|exists:ayllu_pi_ejecuciones,id',
            'aporte_curso_id' => 'nullable|exists:ayllu_pi_aportes_curso,id',
            'ciclo_romano' => 'required|in:I,II,III,IV,V,VI,VII,VIII',
            'programa_codigo' => 'nullable|string|max:40',
            'tipo_entrada' => 'required|in:avance,dificultad,acuerdo_mesa,devolucion_formativa,observacion_calidad',
            'audiencia' => 'required|in:borrador_personal,mesa_docente_ciclo,equipo_estudiantes,ciclo_completo',
            'titulo' => 'required|string|max:220',
            'contenido' => 'required|string|max:5000',
        ]);

        abort_unless(
            $this->authService->puedeComentarEnMesaCiclo($user, $validated['ciclo_romano'], (int) $validated['ejecucion_id'], $ctx),
            403,
            'Solo los docentes y coordinadores asignados a la mesa del ciclo pueden registrar entradas en la bitácora.'
        );

        $entrada = AylluPiBitacoraMesa::create([
            'ejecucion_id' => $validated['ejecucion_id'],
            'aporte_curso_id' => $validated['aporte_curso_id'] ?? null,
            'ciclo_romano' => $validated['ciclo_romano'],
            'programa_codigo' => $validated['programa_codigo'] ?? 'TODOS',
            'tipo_entrada' => $validated['tipo_entrada'],
            'audiencia' => $validated['audiencia'],
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'autor_user_id' => $user->id,
        ]);

        $this->registrarAuditoria(
            'registrar_bitacora_mesa',
            'ayllu_pi_bitacora_mesa',
            $entrada->id,
            $user->id,
            $entrada->ciclo_romano,
            $entrada->programa_codigo,
            [
                'tipo_entrada' => $entrada->tipo_entrada,
                'audiencia' => $entrada->audiencia,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Entrada registrada en la bitácora del proyecto con audiencia controlada.');
    }

    /**
     * Solicita la publicación de una síntesis comunitaria (entra siempre en estado `pendiente_revision_editorial`,
     * nunca se publica automáticamente sin revisión editorial institucional — Criterio #10).
     */
    public function solicitarSintesisComunitaria(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $validated = $request->validate([
            'ejecucion_id' => 'required|exists:ayllu_pi_ejecuciones,id',
            'aporte_curso_id' => 'nullable|exists:ayllu_pi_aportes_curso,id',
            'evidencia_version_id' => 'nullable|exists:ayllu_pi_evidencia_versiones,id',
            'ciclo_romano' => 'required|in:I,II,III,IV,V,VI,VII,VIII',
            'programa_codigo' => 'required|string|max:40',
            'titulo' => 'required|string|max:220',
            'resumen_sintesis' => 'required|string|max:4000',
            'autoria_reconocida' => 'required|string|max:255',
            'audiencia_objetivo' => 'required|in:ciclo_completo,comunidad_ayllu',
        ]);

        abort_unless(
            $this->authService->perteneceAMesaDocenteDelCiclo($user, $validated['ciclo_romano'], (int) $validated['ejecucion_id'], $ctx),
            403,
            'Solo los integrantes de la mesa docente del ciclo pueden proponer síntesis comunitarias.'
        );

        $sintesis = AylluPiSintesisComunitaria::create([
            'ejecucion_id' => $validated['ejecucion_id'],
            'aporte_curso_id' => $validated['aporte_curso_id'] ?? null,
            'evidencia_version_id' => $validated['evidencia_version_id'] ?? null,
            'ciclo_romano' => $validated['ciclo_romano'],
            'programa_codigo' => $validated['programa_codigo'],
            'titulo' => $validated['titulo'],
            'resumen_sintesis' => $validated['resumen_sintesis'],
            'autoria_reconocida' => $validated['autoria_reconocida'],
            'audiencia_objetivo' => $validated['audiencia_objetivo'],
            'estado_editorial' => 'pendiente_revision_editorial',
            'solicitado_por_user_id' => $user->id,
        ]);

        $this->registrarAuditoria(
            'solicitar_sintesis_comunitaria',
            'ayllu_pi_sintesis_comunitarias',
            $sintesis->id,
            $user->id,
            $sintesis->ciclo_romano,
            $sintesis->programa_codigo,
            [
                'audiencia_objetivo' => $sintesis->audiencia_objetivo,
                'evidencia_version_id' => $sintesis->evidencia_version_id,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Síntesis enviada a revisión editorial institucional. Los archivos originales vinculados conservan sus permisos restringidos.');
    }

    /**
     * Revisión editorial institucional de una síntesis comunitaria (aprobar y publicar en el muro o rechazar/retirar).
     * Aprobar la síntesis NO modifica la audiencia ni los permisos de descarga de la evidencia original vinculada.
     */
    public function revisarSintesisComunitaria(Request $request, AylluPiSintesisComunitaria $sintesis)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeAprobarPublicacionSintesis($user, $sintesis->ciclo_romano, $sintesis->ejecucion_id, $ctx),
            403,
            'Se requiere autorización de coordinación o tutoría del ciclo para aprobar la publicación comunitaria.'
        );

        $validated = $request->validate([
            'estado_editorial' => 'required|in:aprobada_publicada,rechazada,retirada',
            'observacion_editorial' => 'nullable|string|max:1500',
        ]);

        DB::transaction(function () use ($sintesis, $user, $validated) {
            $publicacionId = $sintesis->ayllu_publicacion_id;

            if ($validated['estado_editorial'] === 'aprobada_publicada' && !$publicacionId) {
                $pub = AylluPublicacion::create([
                    'user_id' => $sintesis->solicitado_por_user_id,
                    'ambito' => $sintesis->audiencia_objetivo === 'comunidad_ayllu' ? 'institucional' : 'ciclo',
                    'tipo' => 'proyecto_taqe',
                    'titulo' => '[Síntesis Proyecto Integrador · Ciclo ' . $sintesis->ciclo_romano . '] ' . $sintesis->titulo,
                    'contenido' => $sintesis->resumen_sintesis . "\n\nAutoría reconocida: " . $sintesis->autoria_reconocida,
                    'lengua' => 'bilingue',
                    'destacado' => false,
                    'visible' => true,
                ]);
                $publicacionId = $pub->id;
            } elseif ($validated['estado_editorial'] !== 'aprobada_publicada' && $publicacionId) {
                AylluPublicacion::where('id', $publicacionId)->update(['visible' => false]);
            }

            $sintesis->update([
                'estado_editorial' => $validated['estado_editorial'],
                'revisado_por_user_id' => $user->id,
                'revisado_at' => now(),
                'observacion_editorial' => $validated['observacion_editorial'] ?? null,
                'ayllu_publicacion_id' => $publicacionId,
            ]);
        });

        $this->registrarAuditoria(
            'revisar_sintesis_comunitaria',
            'ayllu_pi_sintesis_comunitarias',
            $sintesis->id,
            $user->id,
            $sintesis->ciclo_romano,
            $sintesis->programa_codigo,
            [
                'estado_editorial' => $validated['estado_editorial'],
                'ayllu_publicacion_id' => $sintesis->ayllu_publicacion_id,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Estado editorial de la síntesis actualizado.');
    }

    /**
     * Acción de Coordinación: Ejecutar o verificar importación idempotente con transacción.
     */
    public function ejecutarImportacionWeb(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeAprobarDefinicionCurricular($user, $ctx),
            403,
            'Solo la coordinación institucional puede ejecutar importaciones curriculares.'
        );

        $resultado = $this->importService->ejecutarImportacion($user->id);

        $mensaje = $resultado['idempotente']
            ? 'Conciliación verificada de forma idempotente: las 114 entradas y 10 versiones están íntegras y sin duplicados.'
            : "Importación completada: {$resultado['definiciones_creadas']} definiciones y {$resultado['aportes_creados']} aportes registrados.";

        return back()->with('ayllu_ok', $mensaje);
    }

    /**
     * Acción de Coordinación: Seleccionar / adoptar una versión documental para una ejecución anual,
     * conservando el historial de versiones previas.
     */
    public function adoptarVersionDefinicion(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeAprobarDefinicionCurricular($user, $ctx),
            403,
            'Solo la coordinación institucional puede aprobar o cambiar la versión curricular adoptada.'
        );

        $validated = $request->validate([
            'ejecucion_id' => 'required|exists:ayllu_pi_ejecuciones,id',
            'definicion_id' => 'required|exists:ayllu_pi_definiciones,id',
        ]);

        $ejecucion = AylluPiEjecucion::findOrFail($validated['ejecucion_id']);
        $definicion = AylluPiDefinicion::findOrFail($validated['definicion_id']);

        abort_unless(
            (int) $ejecucion->anio_formativo === (int) $definicion->anio_formativo,
            422,
            'La versión seleccionada no corresponde al mismo año formativo de la ejecución.'
        );

        DB::transaction(function () use ($ejecucion, $definicion, $user) {
            $definicion->update([
                'estado_aprobacion' => 'adoptada_ejecucion',
                'bloqueada_por_uso' => true,
                'aprobado_por_user_id' => $user->id,
                'aprobado_at' => now(),
            ]);

            $ejecucion->update([
                'definicion_id' => $definicion->id,
                'adoptado_por_user_id' => $user->id,
                'adoptado_at' => now(),
            ]);
        });

        $this->registrarAuditoria(
            'adoptar_version_definicion',
            'ayllu_pi_ejecuciones',
            $ejecucion->id,
            $user->id,
            $ejecucion->ciclo_impar_romano . '-' . $ejecucion->ciclo_par_romano,
            'TODOS',
            [
                'definicion_id' => $definicion->id,
                'codigo_fuente' => $definicion->codigo_fuente,
                'version_numero' => $definicion->version_numero,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', "Versión v{$definicion->version_numero} ({$definicion->codigo_fuente}) adoptada para la ejecución {$ejecucion->anio_lectivo}.");
    }

    /**
     * Acción de Coordinación: Calendarizar fechas operativas aprobadas sobre los hitos documentales del Año 1
     * sin alterar los plazos relativos de la fuente.
     */
    public function guardarFechasHitosOperativos(Request $request, AylluPiEjecucion $ejecucion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->esCoordinadorProyectos($user, $ejecucion->ciclo_impar_romano, $ejecucion->id, $ctx),
            403,
            'Solo la coordinación puede aprobar fechas operativas para los hitos del proyecto.'
        );

        $validated = $request->validate([
            'fechas_operativas' => 'required|array',
            'fechas_operativas.*' => 'nullable|date',
        ]);

        $ejecucion->update([
            'fechas_hitos_operativos' => $validated['fechas_operativas'],
        ]);

        $this->registrarAuditoria(
            'calendarizar_hitos_operativos',
            'ayllu_pi_ejecuciones',
            $ejecucion->id,
            $user->id,
            $ejecucion->ciclo_impar_romano . '-' . $ejecucion->ciclo_par_romano,
            'TODOS',
            ['fechas_operativas' => $validated['fechas_operativas']],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Fechas operativas del período guardadas (separadas de los plazos relativos documentales).');
    }

    /**
     * Acción de Coordinación: Asignar docente responsable o coordinador en la mesa de un ciclo/aporte.
     */
    public function asignarDocenteMesa(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeAprobarDefinicionCurricular($user, $ctx),
            403,
            'Solo la coordinación institucional puede gestionar asignaciones de la mesa docente.'
        );

        $validated = $request->validate([
            'ejecucion_id' => 'nullable|exists:ayllu_pi_ejecuciones,id',
            'aporte_curso_id' => 'nullable|exists:ayllu_pi_aportes_curso,id',
            'user_id' => 'required|exists:users,id',
            'rol_mesa' => 'required|in:docente_responsable_aporte,coordinador_ciclo,coordinador_proyecto,acompanante_equipo',
            'ciclo_romano' => 'required|in:I,II,III,IV,V,VI,VII,VIII,TODOS',
            'programa_codigo' => 'nullable|string|max:40',
        ]);

        $docente = Docente::where('user_id', $validated['user_id'])->first();
        $aporte = !empty($validated['aporte_curso_id']) ? AylluPiAporteCurso::find($validated['aporte_curso_id']) : null;

        $asignacion = AylluPiAsignacionMesa::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'aporte_curso_id' => $aporte?->id,
                'rol_mesa' => $validated['rol_mesa'],
                'ciclo_romano' => $aporte ? $aporte->ciclo_romano : $validated['ciclo_romano'],
            ],
            [
                'ejecucion_id' => $aporte ? $aporte->ejecucion_id : ($validated['ejecucion_id'] ?? null),
                'curso_id' => $aporte?->curso_institucional_id,
                'docente_id' => $docente?->id,
                'programa_codigo' => $aporte ? $aporte->agrupacion_programa_codigo : ($validated['programa_codigo'] ?? 'TODOS'),
                'activo' => true,
                'asignado_por_user_id' => $user->id,
                'revocado_por_user_id' => null,
                'revocado_at' => null,
                'motivo_revocacion' => null,
            ]
        );

        $this->registrarAuditoria(
            'asignar_docente_mesa',
            'ayllu_pi_asignaciones_mesa',
            $asignacion->id,
            $user->id,
            $asignacion->ciclo_romano,
            $asignacion->programa_codigo,
            [
                'usuario_asignado_id' => $asignacion->user_id,
                'aporte_curso_id' => $asignacion->aporte_curso_id,
                'rol_mesa' => $asignacion->rol_mesa,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Asignación docente registrada y habilitada en la mesa.');
    }

    /**
     * Acción de Coordinación: Revocar una asignación de la mesa docente.
     * Elimina de inmediato los accesos derivados de edición/mesa conservando la autoría y trazabilidad histórica (Criterio #11).
     */
    public function revocarAsignacionMesa(Request $request, AylluPiAsignacionMesa $asignacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless(
            $this->authService->puedeAprobarDefinicionCurricular($user, $ctx),
            403,
            'Solo la coordinación institucional puede revocar asignaciones de la mesa docente.'
        );

        $validated = $request->validate([
            'motivo_revocacion' => 'nullable|string|max:255',
        ]);

        $asignacion->update([
            'activo' => false,
            'revocado_por_user_id' => $user->id,
            'revocado_at' => now(),
            'motivo_revocacion' => $validated['motivo_revocacion'] ?? 'Revocación administrativa de asignación',
        ]);

        $this->registrarAuditoria(
            'revocar_asignacion_mesa',
            'ayllu_pi_asignaciones_mesa',
            $asignacion->id,
            $user->id,
            $asignacion->ciclo_romano,
            $asignacion->programa_codigo,
            [
                'usuario_revocado_id' => $asignacion->user_id,
                'aporte_curso_id' => $asignacion->aporte_curso_id,
                'motivo' => $asignacion->motivo_revocacion,
            ],
            $request->ip()
        );

        return back()->with('ayllu_ok', 'Asignación revocada: los accesos derivados han sido desactivados conservando la trazabilidad histórica.');
    }

    /**
     * Resuelve Año Formativo (1..4) y Ciclo (I..VIII) garantizando que jamás ocurra una combinación imposible.
     */
    protected function resolverAnioYCicloSinCombinacionImposible(Request $request, array $ctx): array
    {
        $cicloParam = $request->query('ciclo') ? strtoupper(trim((string) $request->query('ciclo'))) : null;
        $anioParam = $request->query('anio') ? (int) $request->query('anio') : null;

        // Si el usuario envió un ciclo válido y no envió año (o el año no coincide con el ciclo cuando cambió el selector de ciclo)
        if ($cicloParam && isset(self::MAPA_CICLO_ANIO[$cicloParam])) {
            $anioDelCiclo = self::MAPA_CICLO_ANIO[$cicloParam];
            if (!$anioParam || $request->query('foco') === 'ciclo') {
                return [$anioDelCiclo, $cicloParam];
            }
            if (in_array($cicloParam, self::MAPA_ANIO_CICLOS[$anioParam] ?? [], true)) {
                return [$anioParam, $cicloParam];
            }
            // Si cambió el año y quedó un ciclo de otro año en la query string, ajustar al primer ciclo del año elegido
            if (isset(self::MAPA_ANIO_CICLOS[$anioParam])) {
                return [$anioParam, self::MAPA_ANIO_CICLOS[$anioParam][0]];
            }
            return [$anioDelCiclo, $cicloParam];
        }

        if ($anioParam && isset(self::MAPA_ANIO_CICLOS[$anioParam])) {
            return [$anioParam, self::MAPA_ANIO_CICLOS[$anioParam][0]];
        }

        // Por defecto, detectar el ciclo del usuario autenticado (o el primer curso asignado al docente)
        $cicloUsuario = !empty($ctx['ciclo']) ? strtoupper(trim((string) $ctx['ciclo']->nombre)) : null;
        if (!$cicloUsuario && isset($ctx['cursos']) && $ctx['cursos']->isNotEmpty()) {
            foreach ($ctx['cursos'] as $curso) {
                $cNom = strtoupper(trim((string) $curso->ciclo?->nombre));
                if (isset(self::MAPA_CICLO_ANIO[$cNom])) {
                    $cicloUsuario = $cNom;
                    break;
                }
            }
        }

        if ($cicloUsuario && isset(self::MAPA_CICLO_ANIO[$cicloUsuario])) {
            return [self::MAPA_CICLO_ANIO[$cicloUsuario], $cicloUsuario];
        }

        return [1, 'I'];
    }

    /**
     * Resuelve el filtro de programa asegurando que Inicial, Primaria y Primaria EIB estén claramente diferenciados.
     */
    protected function resolverProgramaFiltro(Request $request, array $ctx): string
    {
        $progParam = $request->query('programa') ? strtoupper(trim((string) $request->query('programa'))) : null;
        if ($progParam && isset(self::PROGRAMAS_DOCUMENTADOS[$progParam])) {
            return $progParam;
        }

        if (!empty($ctx['esAlumnoFid']) || !empty($ctx['esAlumnoPpd'])) {
            $progUsuario = $ctx['programa'] ?? null;
            if ($progUsuario) {
                if ($progUsuario->esInicial()) {
                    return 'INICIAL';
                }
                if ($progUsuario->esPrimaria() && $progUsuario->eib) {
                    return 'PRIMARIA_EIB';
                }
                if ($progUsuario->esPrimaria() && !$progUsuario->eib) {
                    return 'PRIMARIA';
                }
            }
        }

        return 'TODOS';
    }

    /**
     * Filtra la colección de aportes según el programa seleccionado:
     * - INICIAL -> INICIAL + FORMACION_GENERAL
     * - PRIMARIA -> PRIMARIA + FORMACION_GENERAL (nunca mezcla PRIMARIA_EIB)
     * - PRIMARIA_EIB -> PRIMARIA_EIB + FORMACION_GENERAL (nunca mezcla PRIMARIA)
     * - FORMACION_GENERAL -> solo FORMACION_GENERAL
     * - TODOS -> todos los aportes del ciclo/año
     */
    protected function filtrarAportesPorPrograma($aportes, string $programaFiltro)
    {
        if ($programaFiltro === 'TODOS') {
            return $aportes->values();
        }

        if ($programaFiltro === 'FORMACION_GENERAL') {
            return $aportes->where('agrupacion_programa_codigo', 'FORMACION_GENERAL')->values();
        }

        return $aportes->filter(function (AylluPiAporteCurso $ap) use ($programaFiltro) {
            return $ap->agrupacion_programa_codigo === $programaFiltro
                || $ap->agrupacion_programa_codigo === 'FORMACION_GENERAL';
        })->values();
    }

    protected function registrarAuditoria(
        string $accion,
        string $entidadTipo,
        ?int $entidadId,
        ?int $actorUserId,
        ?string $cicloRomano,
        ?string $programaCodigo,
        array $metadatos,
        ?string $ip
    ): void {
        AylluPiAuditoriaEvento::create([
            'accion' => $accion,
            'entidad_tipo' => $entidadTipo,
            'entidad_id' => $entidadId,
            'actor_user_id' => $actorUserId,
            'ciclo_romano' => $cicloRomano,
            'programa_codigo' => $programaCodigo,
            'metadatos' => $metadatos,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
