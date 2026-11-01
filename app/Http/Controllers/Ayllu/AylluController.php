<?php

namespace App\Http\Controllers\Ayllu;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Ayllu\AylluComentario;
use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluEventoCalendario;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluMensajeChat;
use App\Models\Ayllu\AylluPerfil;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Ayllu\AylluReaccion;
use App\Models\Ayllu\AylluRetroalimentacion;
use App\Models\Curso;
use App\Models\Proyecto;
use App\Models\TaqeGrupo;
use App\Models\TaqeTrabajo;
use App\Models\User;
use App\Services\AylluAuthorizationService;
use App\Services\AylluContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AylluController extends Controller
{
    public function __construct(
        protected AylluContextService $contextService,
        protected AylluAuthorizationService $authService
    ) {
        $this->middleware('auth');
    }

    /**
     * Menú provisional «Inicio» — Marca: Ayllu Puklla.
     * Muro comunitario cálido, comunicados oficiales, Minkarikuy, cursos y acceso privado.
     */
    public function inicio(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $filtro = $request->query('ambito', 'todos');
        $cursoIds = $ctx['cursos']->pluck('id')->all();
        $cicloId = $ctx['ciclo']?->id;
        $programaId = $ctx['programa']?->id;

        $grupos = AylluGrupo::withCount('miembros')
            ->where('activo', true)
            ->orderByDesc('es_oficial')
            ->orderBy('nombre')
            ->get();

        $misGruposIds = $grupos->filter(function (AylluGrupo $g) use ($user) {
            return $this->authService->esMiembroGrupoAyllu($user, $g);
        })->pluck('id')->all();

        $query = AylluPublicacion::with([
            'autor.roles',
            'autor.docente',
            'autor.alumno',
            'curso',
            'ciclo',
            'grupo',
            'comentarios.autor',
            'reacciones',
        ])->where('visible', true);

        if ($filtro === 'institucional') {
            $query->where('ambito', 'institucional');
        } elseif ($filtro === 'mis-cursos') {
            $query->where('ambito', 'curso');
            if (!empty($cursoIds)) {
                $query->whereIn('curso_id', $cursoIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($filtro === 'mi-ciclo') {
            $query->where('ambito', 'ciclo');
            if ($cicloId) {
                $query->where('ciclo_id', $cicloId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($filtro === 'grupos') {
            $query->where('ambito', 'grupo');
            if (!$this->authService->esAutoridadInstitucional($user, $ctx)) {
                if (!empty($misGruposIds)) {
                    $query->whereIn('ayllu_grupo_id', $misGruposIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        } else {
            // Vista unificada «Inicio»: comunicados/institucionales + destacados + comunidades donde el usuario pertenece
            $query->where(function ($q) use ($cursoIds, $cicloId, $programaId, $misGruposIds) {
                $q->where('ambito', 'institucional')
                    ->orWhere('destacado_inicio', true);

                if (!empty($cursoIds)) {
                    $q->orWhere(function ($q2) use ($cursoIds) {
                        $q2->where('ambito', 'curso')->whereIn('curso_id', $cursoIds);
                    });
                }
                if ($cicloId) {
                    $q->orWhere(function ($q2) use ($cicloId) {
                        $q2->where('ambito', 'ciclo')->where('ciclo_id', $cicloId);
                    });
                }
                if ($programaId) {
                    $q->orWhere(function ($q2) use ($programaId) {
                        $q2->where('ambito', 'programa')->where('programa_id', $programaId);
                    });
                }
                if (!empty($misGruposIds)) {
                    $q->orWhere(function ($q2) use ($misGruposIds) {
                        $q2->where('ambito', 'grupo')->whereIn('ayllu_grupo_id', $misGruposIds);
                    });
                }
            });
        }

        $publicaciones = $query
            ->orderByDesc('fijado')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('ayllu.inicio', array_merge($ctx, [
            'seccionActiva' => 'inicio',
            'filtroAmbito' => $filtro,
            'publicaciones' => $publicaciones,
            'grupos' => $grupos,
            'misGruposIds' => $misGruposIds,
            'catalogoReacciones' => AylluReaccion::catalogo(),
        ]));
    }

    /**
     * Publicar en Ayllu Puklla respetando la política aprobada (Decisión A3) y las reglas de pertenencia.
     */
    public function storePublicacion(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $validated = $request->validate([
            'ambito' => 'required|in:institucional,programa,ciclo,curso,grupo',
            'tipo' => 'required|in:reflexion,recurso,anuncio,pregunta,practica_eib,minka,comunicado,dialogo_saber,avance_formativo,reconocimiento,minkarikuy',
            'titulo' => 'nullable|string|max:180',
            'contenido' => 'required|string|max:5000',
            'resumen_quechua' => 'nullable|string|max:1000',
            'curso_id' => 'nullable|exists:cursos,id',
            'ciclo_id' => 'nullable|exists:ciclos,id',
            'ayllu_grupo_id' => 'nullable|exists:ayllu_grupos,id',
            'enlace_externo' => 'nullable|url|max:500',
            'enlace_titulo' => 'nullable|string|max:180',
            'etiquetas_texto' => 'nullable|string|max:250',
            'confirmacion_politica_menores' => 'nullable|boolean',
            'contiene_imagen_menores_sin_consentimiento' => 'nullable|boolean',
        ]);

        $this->validarProteccionMenores($request, $validated['contenido'], $validated['enlace_externo'] ?? null);

        $ambito = $validated['ambito'];
        $cursoIdDestino = $validated['curso_id'] ?? null;
        $cicloIdDestino = $validated['ciclo_id'] ?? $ctx['ciclo']?->id;
        $grupoIdDestino = $validated['ayllu_grupo_id'] ?? null;

        // Regla A3: Si un estudiante intenta publicar en 'institucional', se redirige su publicación
        // a su Comunidad de Ciclo o Curso salvo que tenga permiso explícito de muro general.
        if ($ambito === 'institucional' && !$ctx['puedePublicarMuroGeneral']) {
            if ($cursoIdDestino || $ctx['cursos']->isNotEmpty()) {
                $ambito = 'curso';
                $cursoIdDestino = $cursoIdDestino ?? $ctx['cursos']->first()?->id;
            } elseif ($grupoIdDestino) {
                $ambito = 'grupo';
            } elseif ($cicloIdDestino) {
                $ambito = 'ciclo';
            } else {
                abort(403, 'En el muro general institucional solo publican docentes, tutores y coordinación.');
            }
        }

        // Validación estricta de autorización sobre la comunidad destino
        if ($ambito === 'curso') {
            $cursoIdDestino = $cursoIdDestino ?? $ctx['cursos']->first()?->id;
            $curso = $cursoIdDestino ? Curso::find($cursoIdDestino) : null;
            abort_unless($curso && $this->authService->puedeAccederCurso($user, $curso, $ctx), 403);
        } elseif ($ambito === 'ciclo') {
            abort_unless($cicloIdDestino && $this->authService->puedeAccederCiclo($user, (int) $cicloIdDestino, $ctx), 403);
        } elseif ($ambito === 'grupo') {
            $grupo = $grupoIdDestino ? AylluGrupo::find($grupoIdDestino) : null;
            abort_unless($grupo && $this->authService->puedeParticiparEnGrupo($user, $grupo, $ctx), 403);
        }

        $etiquetas = [];
        if (!empty($validated['etiquetas_texto'])) {
            $etiquetas = array_values(array_filter(array_map(
                fn ($t) => trim($t, " \t\n\r\0\x0B#"),
                explode(',', $validated['etiquetas_texto'])
            )));
        }

        AylluPublicacion::create([
            'user_id' => $user->id,
            'ambito' => $ambito,
            'programa_id' => $ctx['programa']?->id,
            'ciclo_id' => $ambito === 'ciclo' ? $cicloIdDestino : null,
            'curso_id' => $ambito === 'curso' ? $cursoIdDestino : null,
            'ayllu_grupo_id' => $ambito === 'grupo' ? $grupoIdDestino : null,
            'tipo' => $validated['tipo'],
            'titulo' => $validated['titulo'] ?? null,
            'contenido' => $validated['contenido'],
            'resumen_quechua' => $validated['resumen_quechua'] ?? null,
            'etiquetas' => !empty($etiquetas) ? array_slice($etiquetas, 0, 6) : null,
            'enlace_externo' => $validated['enlace_externo'] ?? null,
            'enlace_titulo' => $validated['enlace_titulo'] ?? null,
            'fijado' => false,
            'destacado_inicio' => $ctx['puedePublicarMuroGeneral'],
            'visible' => true,
        ]);

        return back()->with('success', 'Publicación compartida en Ayllu Puklla.');
    }

    /**
     * Actualizar una publicación existente (solo autor o moderador autorizado en el ámbito).
     */
    public function updatePublicacion(Request $request, AylluPublicacion $publicacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $publicacion->loadMissing(['curso', 'grupo']);

        abort_unless($this->authService->puedeModificarPublicacion($user, $publicacion, $ctx), 403);

        $validated = $request->validate([
            'titulo' => 'nullable|string|max:180',
            'contenido' => 'required|string|max:5000',
            'resumen_quechua' => 'nullable|string|max:1000',
            'enlace_externo' => 'nullable|url|max:500',
        ]);

        $this->validarProteccionMenores($request, $validated['contenido'], $validated['enlace_externo'] ?? null);

        $publicacion->update([
            'titulo' => $validated['titulo'] ?? $publicacion->titulo,
            'contenido' => $validated['contenido'],
            'resumen_quechua' => $validated['resumen_quechua'] ?? $publicacion->resumen_quechua,
            'enlace_externo' => $validated['enlace_externo'] ?? $publicacion->enlace_externo,
        ]);

        return back()->with('success', 'Publicación actualizada correctamente.');
    }

    public function storeComentario(Request $request, AylluPublicacion $publicacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $publicacion->loadMissing(['curso', 'grupo']);

        abort_unless($this->authService->puedeInteractuarConPublicacion($user, $publicacion, $ctx), 403);

        $validated = $request->validate([
            'contenido' => 'required|string|max:2000',
        ]);

        AylluComentario::create([
            'ayllu_publicacion_id' => $publicacion->id,
            'user_id' => $user->id,
            'contenido' => $validated['contenido'],
            'es_aporte_docente' => $user->hasAnyRole(['docente', 'tutor', 'investigador', 'admin', 'super-admin']),
        ]);

        return back()->with('success', 'Comentario añadido al diálogo.');
    }

    public function toggleReaccion(Request $request, AylluPublicacion $publicacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $publicacion->loadMissing(['curso', 'grupo']);

        abort_unless($this->authService->puedeInteractuarConPublicacion($user, $publicacion, $ctx), 403);

        $tipo = $request->input('tipo', 'allinmi');
        if (!array_key_exists($tipo, AylluReaccion::catalogo())) {
            $tipo = 'allinmi';
        }

        $existente = AylluReaccion::where('ayllu_publicacion_id', $publicacion->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existente) {
            if ($existente->tipo === $tipo) {
                $existente->delete();
            } else {
                $existente->update(['tipo' => $tipo]);
            }
        } else {
            AylluReaccion::create([
                'ayllu_publicacion_id' => $publicacion->id,
                'user_id' => $user->id,
                'tipo' => $tipo,
            ]);
        }

        return back();
    }

    public function toggleDestacar(AylluPublicacion $publicacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $publicacion->loadMissing(['curso', 'grupo']);

        abort_unless($ctx['puedeModerar'] && $this->authService->puedeModificarPublicacion($user, $publicacion, $ctx), 403);

        $publicacion->update([
            'destacado_inicio' => !$publicacion->destacado_inicio,
        ]);

        return back()->with('success', $publicacion->destacado_inicio
            ? 'Publicación destacada en el muro de Inicio.'
            : 'Publicación retirada de destacados de Inicio.');
    }

    public function eliminarPublicacion(AylluPublicacion $publicacion)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $publicacion->loadMissing(['curso', 'grupo']);

        abort_unless($this->authService->puedeModificarPublicacion($user, $publicacion, $ctx), 403);

        $publicacion->delete();

        return back()->with('success', 'Publicación eliminada.');
    }

    /**
     * Comunidad de Curso con Sílabo Vivo, Evaluación Formativa, Articulación Taq'e y Conector Google Classroom.
     */
    public function cursoComunidad(Request $request, Curso $curso)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless($this->authService->puedeAccederCurso($user, $curso, $ctx), 403);

        $curso->load([
            'ciclo.programa',
            'ciclo.proyecto.docentes',
            'docentes.user',
            'competencias',
            'competenciasSeleccionadas',
            'relacionsilabo.unidades',
            'relacionsilabo.rubricas',
            'relacionsilabo.enfoques',
            'silabosPdf',
        ]);

        $silabo = $curso->relacionsilabo;
        $proyectoCiclo = $curso->ciclo?->proyecto ?? $ctx['proyectoIntegrador'];
        $tabActiva = $request->query('tab', 'comunidad');

        // Publicaciones de esta comunidad de curso
        $publicacionesCurso = AylluPublicacion::with(['autor.roles', 'comentarios.autor', 'reacciones'])
            ->where('ambito', 'curso')
            ->where('curso_id', $curso->id)
            ->where('visible', true)
            ->orderByDesc('fijado')
            ->orderByDesc('created_at')
            ->get();

        // Evaluación y Retroalimentación Formativa (Plano 2: estrictamente docente asignado al curso o titular)
        $esDocenteDelCurso = $this->authService->puedeEvaluarEnCurso($user, $curso, $ctx);

        $entregasQuery = AylluEntregaFormativa::with([
            'estudiante',
            'unidad',
            'taqeGrupo',
            'taqeTrabajo',
            'retroalimentaciones.autor',
        ])->where('curso_id', $curso->id);

        if (!$esDocenteDelCurso) {
            // Un estudiante solo ve SUS propias entregas formativas (o las vinculadas a su grupo Taq'e validado)
            $misTaqeIds = $ctx['taqeGrupos']->pluck('id')->all();
            $entregasQuery->where(function ($q) use ($user, $misTaqeIds) {
                $q->where('user_id', $user->id);
                if (!empty($misTaqeIds)) {
                    $q->orWhereIn('taqe_grupo_id', $misTaqeIds);
                }
            });
        }

        $entregasFormativas = $entregasQuery->orderByDesc('created_at')->get();

        // Grupos Taq'e vinculados a este curso
        $taqeGruposCurso = Schema::hasTable('taqe_grupos')
            ? TaqeGrupo::with(['alumnosAceptados.user', 'trabajos', 'proyecto'])
                ->where('curso_id', $curso->id)
                ->get()
            : collect();

        return view('ayllu.curso', array_merge($ctx, [
            'seccionActiva' => 'cursos',
            'cursoActual' => $curso,
            'silabo' => $silabo,
            'proyectoCiclo' => $proyectoCiclo,
            'tabActiva' => $tabActiva,
            'publicacionesCurso' => $publicacionesCurso,
            'esDocenteDelCurso' => $esDocenteDelCurso,
            'entregasFormativas' => $entregasFormativas,
            'taqeGruposCurso' => $taqeGruposCurso,
            'nivelesFormativos' => AylluEntregaFormativa::nivelesFormativos(),
            'catalogoReacciones' => AylluReaccion::catalogo(),
        ]));
    }

    /**
     * Registrar una entrega/avance formativo en el curso (Plano 2 — no modifica actas oficiales).
     * Garantiza transacción atómica cuando se vincula simultáneamente una evidencia Taq'e.
     */
    public function storeEntregaFormativa(Request $request, Curso $curso)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        abort_unless($this->authService->puedeAccederCurso($user, $curso, $ctx), 403);

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'unidad_id' => 'nullable|exists:unidades,id',
            'descripcion_reflexion' => 'required|string|max:5000',
            'enlace_evidencia' => 'nullable|url|max:500',
            'autoevaluacion_nivel' => 'nullable|in:en_inicio,en_proceso,logrado,destacado',
            'autoevaluacion_comentario' => 'nullable|string|max:1500',
            'es_aporte_proyecto_integrador' => 'nullable|boolean',
            'taqe_grupo_id' => 'nullable|exists:taqe_grupos,id',
            'en_portafolio' => 'nullable|boolean',
            'reflexion_portafolio' => 'nullable|string|max:1500',
            'confirmacion_politica_menores' => 'nullable|boolean',
            'contiene_imagen_menores_sin_consentimiento' => 'nullable|boolean',
        ]);

        $this->validarProteccionMenores($request, $validated['descripcion_reflexion'], $validated['enlace_evidencia'] ?? null);

        $silabo = $curso->relacionsilabo;

        // Si se especificó unidad_id, verificar que pertenezca al sílabo del curso
        if (!empty($validated['unidad_id'])) {
            $unidadPerteneceAlCurso = $silabo && $silabo->unidades()->where('unidades.id', $validated['unidad_id'])->exists();
            abort_unless($unidadPerteneceAlCurso, 403, 'La unidad seleccionada no pertenece al sílabo de este curso.');
        }

        // Si se especificó un grupo Taq'e, validar que el estudiante pertenezca realmente a dicho grupo y corresponda al curso/proyecto
        $taqeGrupoId = $validated['taqe_grupo_id'] ?? null;
        $grupoTaqe = null;
        if ($taqeGrupoId) {
            $grupoTaqe = TaqeGrupo::find($taqeGrupoId);
            abort_unless($grupoTaqe && $this->authService->puedeVincularGrupoTaqe($user, $grupoTaqe, $curso, $ctx), 403);
        }

        $proyectoId = $curso->ciclo?->proyecto_id ?? $ctx['proyectoIntegrador']?->id;
        $esAportePi = $request->boolean('es_aporte_proyecto_integrador');
        $enPortafolio = $request->boolean('en_portafolio');

        DB::transaction(function () use (
            $curso,
            $silabo,
            $proyectoId,
            $esAportePi,
            $enPortafolio,
            $taqeGrupoId,
            $grupoTaqe,
            $validated,
            $ctx,
            $user
        ) {
            $taqeTrabajoId = null;

            if ($esAportePi && $grupoTaqe && !empty($validated['enlace_evidencia']) && $ctx['alumno']) {
                $trabajo = TaqeTrabajo::create([
                    'taqe_grupo_id' => $grupoTaqe->id,
                    'nombre' => '[Aporte ' . $curso->nombre . '] ' . $validated['titulo'],
                    'enlace' => $validated['enlace_evidencia'],
                    'tipo_archivo' => 'enlace',
                    'categoria' => 'evidencia',
                    'estado' => 'activo',
                    'subido_por_alumno_id' => $ctx['alumno']->id,
                ]);
                $taqeTrabajoId = $trabajo->id;
            }

            AylluEntregaFormativa::create([
                'curso_id' => $curso->id,
                'silabo_id' => $silabo?->id,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'proyecto_id' => $proyectoId,
                'taqe_grupo_id' => $taqeGrupoId,
                'taqe_trabajo_id' => $taqeTrabajoId,
                'user_id' => $user->id,
                'titulo' => $validated['titulo'],
                'descripcion_reflexion' => $validated['descripcion_reflexion'],
                'enlace_evidencia' => $validated['enlace_evidencia'] ?? null,
                'autoevaluacion_nivel' => $validated['autoevaluacion_nivel'] ?? 'en_proceso',
                'autoevaluacion_comentario' => $validated['autoevaluacion_comentario'] ?? null,
                'es_aporte_proyecto_integrador' => $esAportePi,
                'en_portafolio' => $enPortafolio,
                'reflexion_portafolio' => $validated['reflexion_portafolio'] ?? null,
                'estado' => $taqeTrabajoId ? 'vinculado_taqe' : 'enviado',
            ]);
        });

        return redirect()
            ->route('ayllu.cursos.show', ['curso' => $curso->id, 'tab' => 'formativa'])
            ->with('success', 'Avance formativo registrado correctamente. Recuerda que esta entrega es de acompañamiento formativo y no altera las actas oficiales.');
    }

    /**
     * Retroalimentación formativa dialogada (docente asignado al curso o respuesta reflexiva del estudiante titular).
     */
    public function storeRetroalimentacion(Request $request, AylluEntregaFormativa $entrega)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $entrega->loadMissing('curso');

        abort_unless($this->authService->puedeRetroalimentarEntrega($user, $entrega, $ctx), 403);

        $esDocenteEvaluador = $entrega->curso && $this->authService->puedeEvaluarEnCurso($user, $entrega->curso, $ctx) && ((int) $entrega->user_id !== (int) $user->id);

        $validated = $request->validate([
            'valoracion_formativa' => 'nullable|in:en_inicio,en_proceso,logrado,destacado',
            'fortalezas' => 'nullable|string|max:2000',
            'sugerencias_mejora' => 'nullable|string|max:2000',
            'comentario_general' => 'required|string|max:3000',
        ]);

        AylluRetroalimentacion::create([
            'ayllu_entrega_formativa_id' => $entrega->id,
            'autor_user_id' => $user->id,
            'tipo' => $esDocenteEvaluador ? 'docente' : 'respuesta_estudiante',
            'valoracion_formativa' => $esDocenteEvaluador ? ($validated['valoracion_formativa'] ?? null) : null,
            'fortalezas' => $esDocenteEvaluador ? ($validated['fortalezas'] ?? null) : null,
            'sugerencias_mejora' => $esDocenteEvaluador ? ($validated['sugerencias_mejora'] ?? null) : null,
            'comentario_general' => $validated['comentario_general'],
        ]);

        if ($esDocenteEvaluador && $entrega->estado === 'enviado') {
            $entrega->update(['estado' => 'retroalimentado']);
        }

        return back()->with('success', 'Retroalimentación formativa guardada.');
    }

    /**
     * Alternar curaduría de una evidencia en el Portafolio Docente EIB del estudiante.
     */
    public function togglePortafolio(Request $request, AylluEntregaFormativa $entrega)
    {
        $user = Auth::user();
        abort_unless((int) $entrega->user_id === (int) $user->id, 403);

        $entrega->update([
            'en_portafolio' => !$entrega->en_portafolio,
            'reflexion_portafolio' => $request->input('reflexion_portafolio', $entrega->reflexion_portafolio),
        ]);

        return back()->with('success', $entrega->en_portafolio
            ? 'Evidencia incorporada a tu Portafolio Docente EIB.'
            : 'Evidencia retirada de tu Portafolio.');
    }

    /**
     * Vista articuladora del Proyecto Anual Integrador (Taq'e) + Portafolio Docente EIB.
     */
    public function proyectoIntegrador()
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $todosProyectos = Proyecto::with(['ciclos.programa', 'docentes'])->orderBy('id')->get();
        $proyectoActual = $ctx['proyectoIntegrador'] ?? $todosProyectos->first();

        $cursoIdsAutorizados = $ctx['cursos']->pluck('id')->all();
        $misTaqeIds = $ctx['taqeGrupos']->pluck('id')->all();

        // Aportes formativos hacia el Proyecto Integrador (sin exponer retroalimentaciones privadas de terceros)
        $aportesCursos = AylluEntregaFormativa::with(['curso', 'estudiante', 'unidad', 'taqeGrupo'])
            ->where('es_aporte_proyecto_integrador', true)
            ->when(!$this->authService->esAutoridadInstitucional($user, $ctx), function ($q) use ($user, $ctx, $cursoIdsAutorizados, $misTaqeIds) {
                if ($ctx['esDocente']) {
                    $q->whereIn('curso_id', !empty($cursoIdsAutorizados) ? $cursoIdsAutorizados : [0]);
                } else {
                    $q->where(function ($sub) use ($user, $misTaqeIds) {
                        $sub->where('user_id', $user->id);
                        if (!empty($misTaqeIds)) {
                            $sub->orWhereIn('taqe_grupo_id', $misTaqeIds);
                        }
                    });
                }
            })
            ->latest()
            ->take(20)
            ->get();

        // Portafolio EIB curado por el propio usuario autenticado
        $miPortafolio = AylluEntregaFormativa::with(['curso.ciclo', 'unidad', 'retroalimentaciones.autor'])
            ->where('user_id', $user->id)
            ->where('en_portafolio', true)
            ->latest()
            ->get();

        return view('ayllu.proyecto_portafolio', array_merge($ctx, [
            'seccionActiva' => 'proyecto',
            'proyectoActual' => $proyectoActual,
            'todosProyectos' => $todosProyectos,
            'aportesCursos' => $aportesCursos,
            'miPortafolio' => $miPortafolio,
            'nivelesFormativos' => AylluEntregaFormativa::nivelesFormativos(),
        ]));
    }

    /**
     * Comunidades y Grupos Interculturales (Ayllus).
     */
    public function grupos(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $grupos = AylluGrupo::withCount(['miembros', 'publicaciones'])
            ->where('activo', true)
            ->orderByDesc('es_oficial')
            ->orderBy('nombre')
            ->get();

        $grupoSeleccionadoId = $request->query('grupo');
        $grupoActual = $grupoSeleccionadoId
            ? $grupos->firstWhere('id', (int) $grupoSeleccionadoId)
            : $grupos->first();

        $misGruposIds = $grupos->filter(fn (AylluGrupo $g) => $this->authService->esMiembroGrupoAyllu($user, $g))
            ->pluck('id')
            ->all();

        $puedeParticiparEnGrupoActual = $grupoActual
            ? $this->authService->puedeParticiparEnGrupo($user, $grupoActual, $ctx)
            : false;

        $publicacionesGrupo = $grupoActual
            ? AylluPublicacion::with(['autor.roles', 'comentarios.autor', 'reacciones'])
                ->where('ambito', 'grupo')
                ->where('ayllu_grupo_id', $grupoActual->id)
                ->where('visible', true)
                ->orderByDesc('fijado')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('ayllu.grupos', array_merge($ctx, [
            'seccionActiva' => 'grupos',
            'grupos' => $grupos,
            'grupoActual' => $grupoActual,
            'publicacionesGrupo' => $publicacionesGrupo,
            'misGruposIds' => $misGruposIds,
            'puedeParticiparEnGrupoActual' => $puedeParticiparEnGrupoActual,
            'catalogoReacciones' => AylluReaccion::catalogo(),
        ]));
    }

    public function toggleMiembroGrupo(AylluGrupo $grupo)
    {
        abort_unless($grupo->activo, 404);
        $user = Auth::user();
        $existe = $grupo->miembros()->where('users.id', $user->id)->exists();

        if ($existe) {
            $grupo->miembros()->detach($user->id);
            $msg = 'Saliste de la comunidad «' . $grupo->nombre . '».';
        } else {
            $grupo->miembros()->attach($user->id, ['rol' => 'miembro']);
            $msg = 'Te uniste a la comunidad «' . $grupo->nombre . '».';
        }

        return back()->with('success', $msg);
    }

    public function storeGrupo(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'nombre' => 'required|string|max:140',
            'nombre_quechua' => 'nullable|string|max:140',
            'descripcion' => 'required|string|max:1000',
            'tipo' => 'required|in:intercultural,taller,circulo_estudio,investigacion,tutoria',
        ]);

        $slugBase = Str::slug($validated['nombre']);
        $slug = $slugBase . '-' . Str::lower(Str::random(4));

        $grupo = AylluGrupo::create([
            'nombre' => $validated['nombre'],
            'nombre_quechua' => $validated['nombre_quechua'] ?? null,
            'slug' => $slug,
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'icono' => 'fas fa-users',
            'color' => '#33445a',
            'creado_por' => $user->id,
            'es_oficial' => $user->hasAnyRole(['docente', 'tutor', 'admin', 'super-admin']),
            'activo' => true,
        ]);

        $grupo->miembros()->attach($user->id, ['rol' => 'moderador']);

        return redirect()->route('ayllu.grupos', ['grupo' => $grupo->id])
            ->with('success', 'Comunidad creada en Ayllu Puklla.');
    }

    /**
     * Calendario Vivo Comunitario y Académico.
     */
    public function calendario()
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $cursoIdsAutorizados = $ctx['cursos']->pluck('id')->all();

        $hitos = collect();
        $pa = $ctx['periodoActual'];
        if ($pa) {
            if ($pa->fecha_inicio) {
                $hitos->push([
                    'fecha' => $pa->fecha_inicio->format('Y-m-d'),
                    'titulo' => 'Inicio del Período Académico ' . $pa->nombre,
                    'tipo' => 'academico',
                    'detalle' => 'Apertura oficial de matrículas y comunidades de curso.',
                ]);
            }
            if ($pa->calificaciones_parcial1_inicio) {
                $hitos->push([
                    'fecha' => $pa->calificaciones_parcial1_inicio->format('Y-m-d'),
                    'titulo' => 'Ventana Parcial 1 (Inicio)',
                    'tipo' => 'academico',
                    'detalle' => 'Consolidación del primer corte oficial.',
                ]);
            }
            if ($pa->calificaciones_parcial1_cierre) {
                $hitos->push([
                    'fecha' => $pa->calificaciones_parcial1_cierre->format('Y-m-d'),
                    'titulo' => 'Publicación Parcial 1',
                    'tipo' => 'academico',
                    'detalle' => 'Cierre de ventana y visibilidad de notas del Parcial 1.',
                ]);
            }
            if ($pa->calificaciones_parcial2_inicio) {
                $hitos->push([
                    'fecha' => $pa->calificaciones_parcial2_inicio->format('Y-m-d'),
                    'titulo' => 'Ventana Parcial 2 y Desempeño',
                    'tipo' => 'academico',
                    'detalle' => 'Inicio de valoración final del período.',
                ]);
            }
            if ($pa->fecha_cierre) {
                $hitos->push([
                    'fecha' => $pa->fecha_cierre->format('Y-m-d'),
                    'titulo' => 'Cierre del Período Académico ' . $pa->nombre,
                    'tipo' => 'academico',
                    'detalle' => 'Cierre de actas y archivo histórico del semestre.',
                ]);
            }
        }

        if ($ctx['minkarikuy'] && $ctx['minkarikuy']->fecha) {
            $hitos->push([
                'fecha' => $ctx['minkarikuy']->fecha->format('Y-m-d'),
                'titulo' => 'Minkarikuy: ' . $ctx['minkarikuy']->nombre,
                'tipo' => 'comunitario',
                'detalle' => 'Hora: ' . $ctx['minkarikuy']->hora_texto,
            ]);
        }

        $eventosAditivos = AylluEventoCalendario::with('curso')
            ->where(function ($q) use ($cursoIdsAutorizados) {
                $q->whereNull('curso_id');
                if (!empty($cursoIdsAutorizados)) {
                    $q->orWhereIn('curso_id', $cursoIdsAutorizados);
                }
            })
            ->orderBy('fecha_inicio')
            ->get();

        foreach ($eventosAditivos as $ev) {
            $hitos->push([
                'fecha' => $ev->fecha_inicio->format('Y-m-d'),
                'titulo' => $ev->titulo,
                'tipo' => $ev->tipo,
                'detalle' => trim(($ev->hora ? $ev->hora . ' · ' : '') . ($ev->curso ? $ev->curso->nombre . ' · ' : '') . $ev->descripcion),
            ]);
        }

        $hitosOrdenados = $hitos->sortBy('fecha')->values();

        return view('ayllu.calendario', array_merge($ctx, [
            'seccionActiva' => 'calendario',
            'hitos' => $hitosOrdenados,
        ]));
    }

    public function storeEventoCalendario(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        abort_unless($ctx['puedePublicarMuroGeneral'], 403);

        $validated = $request->validate([
            'titulo' => 'required|string|max:180',
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|in:academico,comunitario,entrega_formativa,taqe,tutoria',
            'fecha_inicio' => 'required|date',
            'hora' => 'nullable|string|max:20',
            'curso_id' => 'nullable|exists:cursos,id',
        ]);

        if (!empty($validated['curso_id'])) {
            $curso = Curso::findOrFail($validated['curso_id']);
            abort_unless($this->authService->puedeEvaluarEnCurso($user, $curso, $ctx), 403);
        }

        AylluEventoCalendario::create(array_merge($validated, [
            'creado_por' => $user->id,
        ]));

        return back()->with('success', 'Actividad agregada al Calendario Vivo.');
    }

    /**
     * Chat Académico por Curso o Comunidad Ayllu (validando pertenencia al canal).
     */
    public function chat(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $todosGrupos = AylluGrupo::where('activo', true)->orderBy('nombre')->get();
        $grupos = $this->authService->esAutoridadInstitucional($user, $ctx)
            ? $todosGrupos
            : $todosGrupos->filter(fn (AylluGrupo $g) => $this->authService->esMiembroGrupoAyllu($user, $g))->values();

        $canalTipo = $request->query('tipo', $ctx['cursos']->isNotEmpty() ? 'curso' : 'grupo');
        $tieneIdExplicito = $request->has('id');
        $canalId = (int) $request->query('id', $canalTipo === 'curso'
            ? ($ctx['cursos']->first()?->id ?? 0)
            : ($grupos->first()?->id ?? 0));

        if ($canalTipo === 'curso' && $canalId > 0) {
            $cursoCanal = Curso::find($canalId);
            abort_unless($cursoCanal && $this->authService->puedeAccederCurso($user, $cursoCanal, $ctx), 403);
        } elseif ($canalTipo === 'grupo' && $canalId > 0) {
            $grupoCanal = AylluGrupo::find($canalId);
            if ($tieneIdExplicito) {
                abort_unless($grupoCanal && $this->authService->puedeParticiparEnGrupo($user, $grupoCanal, $ctx), 403);
            } elseif (!$grupoCanal || !$this->authService->puedeParticiparEnGrupo($user, $grupoCanal, $ctx)) {
                $canalId = 0;
            }
        }

        $mensajes = $canalId > 0
            ? AylluMensajeChat::with('emisor')
                ->where('canal_tipo', $canalTipo)
                ->when($canalTipo === 'curso', fn ($q) => $q->where('curso_id', $canalId))
                ->when($canalTipo === 'grupo', fn ($q) => $q->where('ayllu_grupo_id', $canalId))
                ->orderBy('created_at')
                ->take(80)
                ->get()
            : collect();

        return view('ayllu.chat', array_merge($ctx, [
            'seccionActiva' => 'chat',
            'grupos' => $grupos,
            'canalTipo' => $canalTipo,
            'canalId' => $canalId,
            'mensajes' => $mensajes,
        ]));
    }

    public function storeMensajeChat(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $validated = $request->validate([
            'canal_tipo' => 'required|in:curso,grupo',
            'canal_id' => 'required|integer',
            'mensaje' => 'required|string|max:1500',
        ]);

        if ($validated['canal_tipo'] === 'curso') {
            $curso = Curso::find($validated['canal_id']);
            abort_unless($curso && $this->authService->puedeAccederCurso($user, $curso, $ctx), 403);
        } else {
            $grupo = AylluGrupo::find($validated['canal_id']);
            abort_unless($grupo && $this->authService->puedeParticiparEnGrupo($user, $grupo, $ctx), 403);
        }

        AylluMensajeChat::create([
            'canal_tipo' => $validated['canal_tipo'],
            'curso_id' => $validated['canal_tipo'] === 'curso' ? $validated['canal_id'] : null,
            'ayllu_grupo_id' => $validated['canal_tipo'] === 'grupo' ? $validated['canal_id'] : null,
            'emisor_user_id' => $user->id,
            'mensaje' => $validated['mensaje'],
        ]);

        return redirect()->route('ayllu.chat', [
            'tipo' => $validated['canal_tipo'],
            'id' => $validated['canal_id'],
        ]);
    }

    /**
     * Mi Espacio Privado (Plano 3: Notas Oficiales, Asistencia, Matrícula y Trámites).
     */
    public function miEspacioPrivado()
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $misAsistencias = Schema::hasTable('asistencia_detalles')
            ? AsistenciaDetalle::with(['asistencia.curso', 'asistencia.docente'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(25)
                ->get()
            : collect();

        $sesionesDocente = ($ctx['docente'] && Schema::hasTable('asistencias'))
            ? Asistencia::with(['curso', 'detalles'])
                ->where('docente_id', $ctx['docente']->id)
                ->orderByDesc('fecha')
                ->take(15)
                ->get()
            : collect();

        return view('ayllu.privado', array_merge($ctx, [
            'seccionActiva' => 'privado',
            'misAsistencias' => $misAsistencias,
            'sesionesDocente' => $sesionesDocente,
        ]));
    }

    /**
     * Perfil Intercultural y Portafolio Docente EIB.
     * - Cumple mostrar_portafolio_comunidad en el controlador y en la consulta SQL.
     * - Nunca expone retroalimentaciones formativas individuales ni autoevaluaciones privadas a visitantes.
     */
    public function perfil(Request $request, ?User $usuario = null)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $perfilUser = $usuario ?? $user;
        $esMiPerfil = ((int) $perfilUser->id === (int) $user->id);

        $perfilAyllu = AylluPerfil::firstOrCreate(
            ['user_id' => $perfilUser->id],
            [
                'saludo_intercultural' => 'Allillanchu · Kuska yachasunchis',
                'lengua_preferida' => 'castellano_quechua',
                'saberes_intereses' => ['Diálogo de saberes', 'Crianza mutua', 'EIB'],
                'mostrar_portafolio_comunidad' => true,
            ]
        );

        if ($esMiPerfil) {
            $portafolioItems = AylluEntregaFormativa::with(['curso.ciclo', 'unidad', 'retroalimentaciones.autor'])
                ->where('user_id', $perfilUser->id)
                ->where('en_portafolio', true)
                ->latest()
                ->get();
        } elseif ($perfilAyllu->mostrar_portafolio_comunidad) {
            // Para visitantes de la comunidad: solo evidencias y reflexión de portafolio compartidas,
            // excluyendo estrictamente retroalimentaciones docentes confidenciales y autoevaluaciones privadas.
            $portafolioItems = AylluEntregaFormativa::with(['curso.ciclo', 'unidad'])
                ->select([
                    'id',
                    'curso_id',
                    'unidad_id',
                    'user_id',
                    'titulo',
                    'descripcion_reflexion',
                    'enlace_evidencia',
                    'reflexion_portafolio',
                    'en_portafolio',
                    'created_at',
                ])
                ->where('user_id', $perfilUser->id)
                ->where('en_portafolio', true)
                ->latest()
                ->get()
                ->each(fn (AylluEntregaFormativa $item) => $item->setRelation('retroalimentaciones', collect()));
        } else {
            $portafolioItems = collect();
        }

        return view('ayllu.perfil', array_merge($ctx, [
            'seccionActiva' => 'perfil',
            'perfilUser' => $perfilUser,
            'perfilAyllu' => $perfilAyllu,
            'esMiPerfil' => $esMiPerfil,
            'portafolioItems' => $portafolioItems,
        ]));
    }

    public function actualizarPerfil(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'saludo_intercultural' => 'nullable|string|max:180',
            'biografia' => 'nullable|string|max:2000',
            'lengua_preferida' => 'required|in:castellano_quechua,quechua_castellano,castellano',
            'saberes_texto' => 'nullable|string|max:300',
            'mostrar_portafolio_comunidad' => 'nullable|boolean',
        ]);

        $saberes = [];
        if (!empty($validated['saberes_texto'])) {
            $saberes = array_values(array_filter(array_map('trim', explode(',', $validated['saberes_texto']))));
        }

        AylluPerfil::updateOrCreate(
            ['user_id' => $user->id],
            [
                'saludo_intercultural' => $validated['saludo_intercultural'] ?? null,
                'biografia' => $validated['biografia'] ?? null,
                'lengua_preferida' => $validated['lengua_preferida'],
                'saberes_intereses' => !empty($saberes) ? array_slice($saberes, 0, 8) : ['Diálogo de saberes', 'EIB'],
                'mostrar_portafolio_comunidad' => $request->boolean('mostrar_portafolio_comunidad', false),
            ]
        );

        return back()->with('success', 'Tu perfil intercultural de Ayllu Puklla ha sido actualizado.');
    }

    /**
     * Protege las evidencias de prácticas docentes que involucren datos o imágenes de menores de edad.
     */
    protected function validarProteccionMenores(Request $request, string $texto, ?string $enlace): void
    {
        if ($request->boolean('contiene_imagen_menores_sin_consentimiento')) {
            throw ValidationException::withMessages([
                'confirmacion_politica_menores' => 'Por política institucional de protección de menores, no está permitido publicar fotografías ni datos identificables de niñas, niños o adolescentes sin autorización expresa.',
            ]);
        }

        if ($request->has('confirmacion_politica_menores') && !$request->boolean('confirmacion_politica_menores')) {
            throw ValidationException::withMessages([
                'confirmacion_politica_menores' => 'Debes confirmar el cumplimiento de la política institucional de protección de identidad e imagen de menores.',
            ]);
        }
    }
}
