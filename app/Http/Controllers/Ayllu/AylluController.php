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
use App\Services\AylluContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AylluController extends Controller
{
    public function __construct(
        protected AylluContextService $contextService
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
            return $g->miembros()->where('users.id', $user->id)->exists();
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
            $query->where('ambito', 'curso')
                ->when(!empty($cursoIds), fn ($q) => $q->whereIn('curso_id', $cursoIds));
        } elseif ($filtro === 'mi-ciclo') {
            $query->where('ambito', 'ciclo')
                ->when($cicloId, fn ($q) => $q->where('ciclo_id', $cicloId));
        } elseif ($filtro === 'grupos') {
            $query->where('ambito', 'grupo');
        } else {
            // Vista unificada «Inicio»: comunicados/institucionales + destacados + comunidades del usuario
            $query->where(function ($q) use ($cursoIds, $cicloId, $programaId, $misGruposIds) {
                $q->where('ambito', 'institucional')
                    ->orWhere('destacado_inicio', true)
                    ->orWhere('ambito', 'grupo');

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
                    $q->orWhereIn('ayllu_grupo_id', $misGruposIds);
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
     * Publicar en Ayllu Puklla respetando la política aprobada (Decisión A3):
     * - Muro general institucional: reservado a docentes, tutores, investigadores y administración.
     * - Comunidades de Curso, Ciclo y Grupos: abierto a estudiantes y docentes de la comunidad.
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
            'ayllu_grupo_id' => 'nullable|exists:ayllu_grupos,id',
            'enlace_externo' => 'nullable|url|max:500',
            'enlace_titulo' => 'nullable|string|max:180',
            'etiquetas_texto' => 'nullable|string|max:250',
        ]);

        $ambito = $validated['ambito'];
        $grupoFallbackId = $validated['ayllu_grupo_id'] ?? null;

        // Regla A3: Si un estudiante intenta publicar en 'institucional', se redirige su publicación
        // a su Comunidad de Ciclo, Curso o Grupo salvo que tenga permiso explícito de muro general.
        if ($ambito === 'institucional' && !$ctx['puedePublicarMuroGeneral']) {
            if (!empty($validated['curso_id']) || $ctx['cursos']->isNotEmpty()) {
                $ambito = 'curso';
            } elseif (!empty($validated['ayllu_grupo_id'])) {
                $ambito = 'grupo';
            } elseif ($ctx['ciclo']) {
                $ambito = 'ciclo';
            } else {
                $ambito = 'grupo';
                $grupoFallbackId = AylluGrupo::where('activo', true)->value('id');
            }
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
            'ciclo_id' => $ambito === 'ciclo' ? $ctx['ciclo']?->id : null,
            'curso_id' => $ambito === 'curso' ? ($validated['curso_id'] ?? $ctx['cursos']->first()?->id) : null,
            'ayllu_grupo_id' => $ambito === 'grupo' ? $grupoFallbackId : null,
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

    public function storeComentario(Request $request, AylluPublicacion $publicacion)
    {
        $user = Auth::user();
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
        abort_unless($ctx['puedeModerar'], 403);

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
        abort_unless($publicacion->user_id === $user->id || $ctx['puedeModerar'], 403);

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

        // Evaluación y Retroalimentación Formativa (Plano 2: privado estudiante-docente)
        $esDocenteDelCurso = $ctx['esAdmin']
            || $ctx['esTutor']
            || ($ctx['docente'] && $curso->docentes->contains('id', $ctx['docente']->id))
            || $ctx['esDocente'];

        $entregasQuery = AylluEntregaFormativa::with([
            'estudiante',
            'unidad',
            'taqeGrupo',
            'taqeTrabajo',
            'retroalimentaciones.autor',
        ])->where('curso_id', $curso->id);

        if (!$esDocenteDelCurso) {
            // Un estudiante solo ve SUS propias entregas formativas (o las vinculadas a su grupo Taq'e)
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
     * Permite articular el producto con el Proyecto Anual Integrador (Taq'e) y con el Portafolio EIB.
     */
    public function storeEntregaFormativa(Request $request, Curso $curso)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

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
        ]);

        $silabo = $curso->relacionsilabo;
        $proyectoId = $curso->ciclo?->proyecto_id ?? $ctx['proyectoIntegrador']?->id;
        $esAportePi = $request->boolean('es_aporte_proyecto_integrador');
        $enPortafolio = $request->boolean('en_portafolio');

        $taqeGrupoId = $validated['taqe_grupo_id'] ?? null;
        $taqeTrabajoId = null;

        // Si el estudiante marca que este producto tributa a su grupo Taq'e y provee un enlace externo,
        // registramos el vínculo aditivo en taqe_trabajos (categoría 'evidencia') si pertenece al grupo.
        if ($esAportePi && $taqeGrupoId && !empty($validated['enlace_evidencia']) && $ctx['alumno']) {
            $grupo = TaqeGrupo::find($taqeGrupoId);
            if ($grupo) {
                $trabajo = TaqeTrabajo::create([
                    'taqe_grupo_id' => $grupo->id,
                    'nombre' => '[Aporte ' . $curso->nombre . '] ' . $validated['titulo'],
                    'enlace' => $validated['enlace_evidencia'],
                    'tipo_archivo' => 'enlace',
                    'categoria' => 'evidencia',
                    'estado' => 'activo',
                    'subido_por_alumno_id' => $ctx['alumno']->id,
                ]);
                $taqeTrabajoId = $trabajo->id;
            }
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

        return redirect()
            ->route('ayllu.cursos.show', ['curso' => $curso->id, 'tab' => 'formativa'])
            ->with('success', 'Avance formativo registrado correctamente. Recuerda que esta entrega es de acompañamiento formativo y no altera las actas oficiales.');
    }

    /**
     * Retroalimentación formativa dialogada (docente o respuesta reflexiva del estudiante).
     */
    public function storeRetroalimentacion(Request $request, AylluEntregaFormativa $entrega)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $esDocente = $ctx['esDocente'] || $ctx['esTutor'] || $ctx['esAdmin'];
        $esPropietario = ($entrega->user_id === $user->id);

        abort_unless($esDocente || $esPropietario, 403);

        $validated = $request->validate([
            'valoracion_formativa' => 'nullable|in:en_inicio,en_proceso,logrado,destacado',
            'fortalezas' => 'nullable|string|max:2000',
            'sugerencias_mejora' => 'nullable|string|max:2000',
            'comentario_general' => 'required|string|max:3000',
        ]);

        AylluRetroalimentacion::create([
            'ayllu_entrega_formativa_id' => $entrega->id,
            'autor_user_id' => $user->id,
            'tipo' => $esDocente ? 'docente' : 'respuesta_estudiante',
            'valoracion_formativa' => $esDocente ? ($validated['valoracion_formativa'] ?? null) : null,
            'fortalezas' => $esDocente ? ($validated['fortalezas'] ?? null) : null,
            'sugerencias_mejora' => $esDocente ? ($validated['sugerencias_mejora'] ?? null) : null,
            'comentario_general' => $validated['comentario_general'],
        ]);

        if ($esDocente && $entrega->estado === 'enviado') {
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
        abort_unless($entrega->user_id === $user->id, 403);

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

        // Aportes formativos de los cursos hacia el Proyecto Integrador
        $aportesCursos = AylluEntregaFormativa::with(['curso', 'estudiante', 'unidad', 'taqeGrupo', 'retroalimentaciones'])
            ->where('es_aporte_proyecto_integrador', true)
            ->when(!$ctx['esDocente'] && !$ctx['esAdmin'], fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(20)
            ->get();

        // Portafolio EIB curado por el usuario
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

        $publicacionesGrupo = $grupoActual
            ? AylluPublicacion::with(['autor.roles', 'comentarios.autor', 'reacciones'])
                ->where('ambito', 'grupo')
                ->where('ayllu_grupo_id', $grupoActual->id)
                ->where('visible', true)
                ->orderByDesc('fijado')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $misGruposIds = $grupos->filter(fn (AylluGrupo $g) => $g->miembros()->where('users.id', $user->id)->exists())
            ->pluck('id')
            ->all();

        return view('ayllu.grupos', array_merge($ctx, [
            'seccionActiva' => 'grupos',
            'grupos' => $grupos,
            'grupoActual' => $grupoActual,
            'publicacionesGrupo' => $publicacionesGrupo,
            'misGruposIds' => $misGruposIds,
            'catalogoReacciones' => AylluReaccion::catalogo(),
        ]));
    }

    public function toggleMiembroGrupo(AylluGrupo $grupo)
    {
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
     * Calendario Vivo Comunitario y Académico + Chat Académico.
     */
    public function calendario()
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

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

        AylluEventoCalendario::create(array_merge($validated, [
            'creado_por' => $user->id,
        ]));

        return back()->with('success', 'Actividad agregada al Calendario Vivo.');
    }

    /**
     * Chat Académico por Curso o Comunidad Ayllu.
     */
    public function chat(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);

        $grupos = AylluGrupo::where('activo', true)->orderBy('nombre')->get();
        $canalTipo = $request->query('tipo', $ctx['cursos']->isNotEmpty() ? 'curso' : 'grupo');
        $canalId = (int) $request->query('id', $canalTipo === 'curso'
            ? ($ctx['cursos']->first()?->id ?? 0)
            : ($grupos->first()?->id ?? 0));

        $mensajes = AylluMensajeChat::with('emisor')
            ->where('canal_tipo', $canalTipo)
            ->when($canalTipo === 'curso', fn ($q) => $q->where('curso_id', $canalId))
            ->when($canalTipo === 'grupo', fn ($q) => $q->where('ayllu_grupo_id', $canalId))
            ->orderBy('created_at')
            ->take(80)
            ->get();

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
        $validated = $request->validate([
            'canal_tipo' => 'required|in:curso,grupo',
            'canal_id' => 'required|integer',
            'mensaje' => 'required|string|max:1500',
        ]);

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
     * Estrictamente individual y separado de las publicaciones sociales y entregas formativas.
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
     */
    public function perfil(Request $request, ?User $usuario = null)
    {
        $user = Auth::user();
        $ctx = $this->contextService->resolverContexto($user);
        $perfilUser = $usuario ?? $user;

        $perfilAyllu = AylluPerfil::firstOrCreate(
            ['user_id' => $perfilUser->id],
            [
                'saludo_intercultural' => 'Allillanchu · Kuska yachasunchis',
                'lengua_preferida' => 'castellano_quechua',
                'saberes_intereses' => ['Diálogo de saberes', 'Crianza mutua', 'EIB'],
            ]
        );

        $portafolioPublico = AylluEntregaFormativa::with(['curso.ciclo', 'unidad', 'retroalimentaciones.autor'])
            ->where('user_id', $perfilUser->id)
            ->where('en_portafolio', true)
            ->latest()
            ->get();

        return view('ayllu.perfil', array_merge($ctx, [
            'seccionActiva' => 'perfil',
            'perfilUser' => $perfilUser,
            'perfilAyllu' => $perfilAyllu,
            'esMiPerfil' => ($perfilUser->id === $user->id),
            'portafolioItems' => $portafolioPublico,
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
                'mostrar_portafolio_comunidad' => $request->boolean('mostrar_portafolio_comunidad', true),
            ]
        );

        return back()->with('success', 'Tu perfil intercultural de Ayllu Puklla ha sido actualizado.');
    }
}
