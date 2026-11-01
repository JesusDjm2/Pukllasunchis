<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ArchivosTaqe;
use App\Http\Controllers\Concerns\GestionaGruposTaqe;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\TaqeGrupo;
use App\Models\TaqeSolicitudAyuda;
use App\Models\TaqeTrabajo;
use App\Models\User;
use App\Notifications\TaqeIntegranteRetirado;
use App\Notifications\TaqeInvitacionGrupo;
use App\Notifications\TaqeMiembroAcepto;
use App\Notifications\TaqeMiembroSalio;
use App\Notifications\TaqeSolicitudAyudaCreada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaqeController extends Controller
{
    use ArchivosTaqe, GestionaGruposTaqe;

    private function alumnoActual(Request $request): ?Alumno
    {
        $alumno = $request->user()->alumno;
        if ($alumno && $alumno->user?->hasRole('inhabilitado')) {
            return null;
        }

        return $alumno;
    }

    private const LIMITES_KB = ['imagen' => 10240, 'pdf' => 25600, 'word' => 10240, 'audio' => 10240, 'video' => 51200];
    private const EXTENSIONES = 'jpg,jpeg,png,webp,pdf,doc,docx,mp3,wav,m4a,ogg,mp4,webm';

    private function tipoArchivo(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) => 'imagen',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['doc', 'docx']) => 'word',
            in_array($ext, ['mp3', 'wav', 'm4a', 'ogg']) => 'audio',
            in_array($ext, ['mp4', 'webm', 'mov', 'avi']) => 'video',
            default => 'otro',
        };
    }

    private function nombreArchivoDrive(TaqeGrupo $grupo, string $autor, string $nombreTrabajo, string $ext): string
    {
        $limpiar = fn (string $s) => trim(preg_replace('/[\\/:*?"<>|]+/', '-', $s));

        $partes = [
            $limpiar($grupo->nombre),
            $limpiar($autor),
            $limpiar($nombreTrabajo),
        ];

        return implode(' - ', $partes).'.'.$ext;
    }

    /**
     * Retorna el espacio Taq'e del alumno:
     * - Proyecto Integrador de su ciclo
     * - Cursos disponibles para crear grupo (si no tiene grupo en el periodo)
     * - Grupos activos en el periodo actual con evidencias y trabajo final
     * - Invitaciones pendientes
     * - Lista de investigadores de apoyo
     */
    public function index(Request $request)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json([
                'message' => 'No se encontró un perfil de alumno activo.',
            ], 403);
        }

        $periodoActual = PeriodoActual::where('actual', true)->first();
        $alumno->loadMissing('ciclo.proyecto', 'ciclo.programa');

        $proyecto = $alumno->ciclo?->proyecto;
        $proyectoData = $proyecto ? [
            'id'          => $proyecto->id,
            'nombre'      => $proyecto->nombre,
            'producto'    => $proyecto->producto,
            'descripcion' => $proyecto->descripcion,
            'ciclo'       => $alumno->ciclo ? 'Ciclo ' . $alumno->ciclo->nombre : null,
        ] : null;

        $misGrupos = $periodoActual
            ? $alumno->taqeGrupos()
                ->wherePivot('estado', 'aceptado')
                ->where('periodo_actual_id', $periodoActual->id)
                ->with(['curso', 'proyecto', 'alumnos', 'evidencias.subidoPor', 'finales.subidoPor'])
                ->get()
            : collect();

        $invitacionesPendientes = $periodoActual && $misGrupos->isEmpty()
            ? $alumno->taqeGrupos()
                ->wherePivot('estado', 'pendiente')
                ->where('periodo_actual_id', $periodoActual->id)
                ->with(['curso', 'creador', 'proyecto'])
                ->get()
            : collect();

        // Cursos sin grupo en el periodo actual que correspondan al proyecto del alumno
        $cursosAsignados = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->with('ciclo')->get()
            : collect();
        $cursos = $cursosAsignados->isNotEmpty()
            ? $cursosAsignados
            : ($alumno->ciclo ? $alumno->ciclo->cursos : collect());

        $cursosSinGrupo = $misGrupos->isNotEmpty() ? collect() : $cursos
            ->filter(fn ($curso) => $proyecto && (int) $curso->ciclo?->proyecto_id === (int) $proyecto->id)
            ->values()
            ->map(fn ($c) => [
                'id'     => $c->id,
                'nombre' => $c->nombre,
                'ciclo'  => $c->ciclo ? 'Ciclo ' . $c->ciclo->nombre : '',
            ]);

        $gruposFormateados = $misGrupos->map(function (TaqeGrupo $grupo) use ($alumno) {
            $esOrganizador = $grupo->creador_alumno_id === $alumno->id;
            $entregado = $grupo->estaEntregado();

            $integrantes = $grupo->alumnos
                ->reject(fn ($i) => $i->pivot->estado === 'rechazado')
                ->map(fn ($i) => [
                    'id'                => $i->id,
                    'nombre'            => trim($i->nombres . ' ' . $i->apellidos),
                    'es_organizador'    => $i->id === $grupo->creador_alumno_id,
                    'rol'               => $i->id === $grupo->creador_alumno_id ? 'Organizador' : 'Integrante',
                    'estado_invitacion' => $i->pivot->estado,
                ])->values()->all();

            $mapearArchivo = fn (TaqeTrabajo $trabajo) => [
                'id'            => $trabajo->id,
                'nombre'        => $trabajo->nombre,
                'tipo'          => $trabajo->tipo_archivo ?? 'otro',
                'tipo_icono'    => $trabajo->tipo_archivo ?? 'otro',
                'url'           => $trabajo->esEnlace()
                    ? $trabajo->enlace
                    : ($trabajo->archivo ? url('/taqe/trabajos/' . $trabajo->id . '/ver') : null),
                'es_enlace'     => $trabajo->esEnlace(),
                'subido_por'    => trim(($trabajo->subidoPor?->nombres ?? '') . ' ' . ($trabajo->subidoPor?->apellidos ?? '')),
                'subido_por_id' => $trabajo->subido_por_alumno_id,
                'fecha'         => $trabajo->created_at?->format('d/m/Y H:i'),
                'puede_eliminar'=> ! $entregado && ($esOrganizador || $trabajo->subido_por_alumno_id === $alumno->id),
                'puede_mover'   => ! $entregado,
            ];

            return [
                'id'             => $grupo->id,
                'nombre'         => $grupo->nombre,
                'curso_id'       => $grupo->curso_id,
                'curso_nombre'   => $grupo->curso?->nombre ?? 'Curso',
                'estado'         => $grupo->estado, // 'en_proceso' | 'finalizado'
                'es_organizador' => $esOrganizador,
                'entregado'      => $entregado,
                'entregado_at'   => $grupo->entregado_at?->format('d/m/Y H:i'),
                'puede_entregar' => $esOrganizador && ! $entregado && $grupo->finales->isNotEmpty(),
                'puede_reabrir'  => $esOrganizador && $entregado,
                'integrantes'    => $integrantes,
                'evidencias'     => $grupo->evidencias->map($mapearArchivo)->values()->all(),
                'finales'        => $grupo->finales->map($mapearArchivo)->values()->all(),
            ];
        })->values()->all();

        $invitacionesFormateadas = $invitacionesPendientes->map(fn ($inv) => [
            'id'             => $inv->id,
            'grupo_nombre'   => $inv->nombre,
            'curso_nombre'   => $inv->curso?->nombre ?? 'Curso',
            'creador_nombre' => trim(($inv->creador?->nombres ?? '') . ' ' . ($inv->creador?->apellidos ?? '')),
            'fecha'          => $inv->created_at?->format('d/m/Y'),
        ])->values()->all();

        $investigadores = User::role('investigador')->with('docente')->get()->map(fn ($u) => [
            'id'       => $u->id,
            'nombre'   => trim((string) $u->docente?->nombre) ?: trim($u->name . ' ' . $u->apellidos),
            'email'    => $u->email,
            'telefono' => $u->docente?->telefono ?? $u->telefono ?? null,
        ])->values()->all();

        $gruposAnterioresCount = $alumno->taqeGrupos()
            ->wherePivot('estado', 'aceptado')
            ->when($periodoActual, fn ($q) => $q->where('periodo_actual_id', '!=', $periodoActual->id))
            ->count();

        return response()->json([
            'data' => [
                'periodo_nombre'          => $periodoActual?->nombre ?? 'Periodo activo',
                'proyecto'                => $proyectoData,
                'cursos_sin_grupo'        => $cursosSinGrupo,
                'mis_grupos'              => $gruposFormateados,
                'invitaciones_pendientes' => $invitacionesFormateadas,
                'investigadores'          => $investigadores,
                'grupos_anteriores_count' => $gruposAnterioresCount,
            ],
        ]);
    }

    /**
     * Retorna los compañeros disponibles para invitar en un curso.
     */
    public function alumnosDelCurso(Request $request, int $cursoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $periodoActual = PeriodoActual::where('actual', true)->first();
        if (! $periodoActual) {
            return response()->json(['data' => []]);
        }

        $curso = Curso::with('ciclo')->findOrFail($cursoId);
        $proyectoId = $this->proyectoDelAlumno($alumno);

        $companeros = $this->companerosDisponibles($curso, $periodoActual, $alumno->id, $proyectoId);

        return response()->json([
            'data' => $companeros->map(fn ($a) => [
                'id'     => $a->id,
                'nombre' => trim($a->apellidos . ', ' . $a->nombres . ' — DNI ' . $a->dni),
            ])->values()->all(),
        ]);
    }

    /**
     * Crear un grupo Taq'e desde la app móvil.
     */
    public function crearGrupo(Request $request)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'nombre'        => 'required|string|max:255',
            'curso_id'      => 'required|exists:cursos,id',
            'integrantes'   => 'nullable|array',
            'integrantes.*' => 'exists:alumnos,id',
        ], [
            'nombre.required'   => 'El nombre del grupo es obligatorio.',
            'curso_id.required' => 'Debes seleccionar un curso.',
        ]);

        $periodoActual = PeriodoActual::where('actual', true)->first();
        if (! $periodoActual) {
            return response()->json(['message' => 'No hay un periodo activo configurado.'], 422);
        }

        $curso = Curso::with('ciclo')->findOrFail($data['curso_id']);

        $matriculado = $alumno->cursosDelPeriodo($periodoActual->id)->where('cursos.id', $curso->id)->exists()
            || ($alumno->ciclo && $alumno->ciclo->id === $curso->ciclo_id);
        if (! $matriculado) {
            return response()->json(['message' => 'No estás matriculado en ese curso este periodo.'], 422);
        }

        $proyectoId = $curso->ciclo?->proyecto_id;
        if (! $proyectoId || (int) $this->proyectoDelAlumno($alumno) !== (int) $proyectoId) {
            return response()->json(['message' => 'Ese curso pertenece al Proyecto Integrador de otro ciclo.'], 422);
        }

        $yaTieneGrupo = $alumno->taqeGrupos()
            ->wherePivot('estado', 'aceptado')
            ->where('periodo_actual_id', $periodoActual->id)
            ->exists();
        if ($yaTieneGrupo) {
            return response()->json(['message' => "Ya perteneces a un grupo Taq'e este periodo."], 422);
        }

        $grupo = TaqeGrupo::create([
            'nombre'            => $data['nombre'],
            'curso_id'          => $curso->id,
            'ciclo_id'          => $curso->ciclo_id,
            'programa_id'       => $curso->ciclo->programa_id,
            'proyecto_id'       => $proyectoId,
            'periodo_actual_id' => $periodoActual->id,
            'creador_alumno_id' => $alumno->id,
            'estado'            => 'en_proceso',
        ]);

        $grupo->alumnos()->attach($alumno->id, ['estado' => 'aceptado', 'respondido_at' => now()]);
        $this->rechazarInvitacionesDelPeriodo($alumno, $periodoActual->id, $grupo->id);

        $integrantesIds = collect($data['integrantes'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $alumno->id)
            ->unique();

        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $inhabilitados = Alumno::whereIn('id', $integrantesIds)
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'inhabilitado'))
            ->pluck('id');
        $deOtroProyecto = $this->alumnoIdsDeOtroProyecto($integrantesIds, $proyectoId);

        $aInvitar = $integrantesIds->diff($comprometidos)->diff($inhabilitados)->diff($deOtroProyecto);

        foreach ($aInvitar as $integranteId) {
            $grupo->alumnos()->attach($integranteId, ['estado' => 'pendiente']);
            $integrante = Alumno::find($integranteId);
            if ($integrante?->user) {
                try {
                    $integrante->user->notify(new TaqeInvitacionGrupo($grupo));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de invitación Taq'e a alumno {$integrante->id}: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'message' => 'Grupo "' . $grupo->nombre . '" creado correctamente con ' . $aInvitar->count() . ' invitación(es) enviada(s).',
            'data'    => ['id' => $grupo->id, 'nombre' => $grupo->nombre],
        ], 201);
    }

    /**
     * Subir evidencia o entregable al trabajo final (archivo o enlace externo).
     */
    public function subirTrabajo(Request $request, int $grupoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        $esMiembroAceptado = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();
        if (! $esMiembroAceptado) {
            return response()->json(['message' => 'No perteneces a este grupo.'], 403);
        }

        if ($grupo->estaEntregado()) {
            return response()->json(['message' => 'El grupo ya entregó su trabajo final: no se pueden agregar archivos ni enlaces.'], 422);
        }

        $esEnlace = $request->input('modo') === 'enlace';
        $data = $request->validate([
            'nombre'  => 'required|string|max:255',
            'destino' => 'required|in:evidencia,final',
            'modo'    => 'required|in:archivo,enlace',
            'enlace'  => [$esEnlace ? 'required' : 'nullable', 'url:http,https', 'max:2048'],
            'archivo' => [
                $esEnlace ? 'nullable' : 'required',
                'file',
                'mimes:' . self::EXTENSIONES,
                function ($attribute, $value, $fail) {
                    if (! $value) return;
                    $tipo = $this->tipoArchivo(strtolower((string) $value->getClientOriginalExtension()));
                    $limiteKB = self::LIMITES_KB[$tipo] ?? 10240;
                    if ($value->getSize() > $limiteKB * 1024) {
                        $fail('El archivo supera el límite de ' . ($limiteKB / 1024) . ' MB.');
                    }
                },
            ],
        ], [
            'enlace.required' => 'Pega el enlace web (debe empezar con http:// o https://).',
            'enlace.url'      => 'El enlace debe ser una URL válida.',
            'archivo.required'=> 'Elige el archivo que deseas subir.',
        ]);

        $destino = $data['destino'];

        if ($esEnlace) {
            $trabajo = TaqeTrabajo::create([
                'taqe_grupo_id'        => $grupo->id,
                'nombre'               => $data['nombre'],
                'enlace'               => $data['enlace'],
                'tipo_archivo'         => 'enlace',
                'categoria'            => $destino,
                'subido_por_alumno_id' => $alumno->id,
            ]);
        } else {
            $archivo = $request->file('archivo');
            $ext = strtolower((string) $archivo->getClientOriginalExtension());
            try {
                $driveFileId = $this->drive()->upload(
                    config('services.google_drive.folders.taqe'),
                    $archivo,
                    $this->nombreArchivoDrive($grupo, $destino === 'final' ? 'TRABAJO FINAL' : $alumno->apellidos . ' ' . $alumno->nombres, $data['nombre'], $ext)
                );
            } catch (\Throwable $e) {
                Log::warning("Drive upload failed in mobile Taq'e, falling back to local storage: " . $e->getMessage());
                $path = $archivo->store('taqe_archivos', 'public');
                $driveFileId = $path;
            }

            $trabajo = TaqeTrabajo::create([
                'taqe_grupo_id'        => $grupo->id,
                'nombre'               => $data['nombre'],
                'archivo'              => $driveFileId,
                'tipo_archivo'         => $this->tipoArchivo($ext),
                'categoria'            => $destino,
                'subido_por_alumno_id' => $alumno->id,
            ]);
        }

        $que = $esEnlace ? 'Enlace' : 'Archivo';
        $donde = $destino === 'final' ? 'al trabajo final' : 'a las evidencias';

        return response()->json([
            'message' => $que . ' "' . $trabajo->nombre . '" agregado ' . $donde . ' correctamente.',
            'data'    => ['id' => $trabajo->id, 'nombre' => $trabajo->nombre],
        ], 201);
    }

    /**
     * Entregar el trabajo final (solo el organizador).
     */
    public function entregar(Request $request, int $grupoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        if ($grupo->creador_alumno_id !== $alumno->id) {
            return response()->json(['message' => 'Solo el organizador del grupo puede entregar el trabajo final.'], 403);
        }

        if ($grupo->estaEntregado()) {
            return response()->json(['message' => 'Este trabajo final ya fue entregado.'], 422);
        }

        if ($grupo->finales()->count() === 0) {
            return response()->json(['message' => 'Antes de entregar, agrega al menos un archivo o enlace al trabajo final.'], 422);
        }

        $grupo->update(['estado' => 'finalizado', 'entregado_at' => now()]);

        return response()->json([
            'message' => '¡Felicitaciones! Trabajo final entregado exitosamente. El grupo queda Finalizado.',
        ]);
    }

    /**
     * Reabrir la entrega (solo el organizador).
     */
    public function reabrir(Request $request, int $grupoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        if ($grupo->creador_alumno_id !== $alumno->id) {
            return response()->json(['message' => 'Solo el organizador del grupo puede reabrir la entrega.'], 403);
        }

        if (! $grupo->estaEntregado()) {
            return response()->json(['message' => 'El trabajo final todavía no se ha entregado.'], 422);
        }

        $grupo->update(['estado' => 'en_proceso', 'entregado_at' => null]);

        return response()->json([
            'message' => 'Entrega reabierta. El grupo vuelve a estar En proceso para corregir o agregar elementos.',
        ]);
    }

    /**
     * Invitar nuevos integrantes a un grupo ya creado (solo organizador).
     */
    public function agregarIntegrantes(Request $request, int $grupoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        if ($grupo->creador_alumno_id !== $alumno->id) {
            return response()->json(['message' => 'Solo el organizador puede invitar integrantes.'], 403);
        }

        $data = $request->validate([
            'integrantes'   => 'required|array|min:1',
            'integrantes.*' => 'exists:alumnos,id',
        ]);

        $periodoActual = PeriodoActual::findOrFail($grupo->periodo_actual_id);
        $pivotesActuales = $grupo->alumnos()->pluck('taqe_grupo_alumno.estado', 'alumnos.id');
        $rechazadosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado === 'rechazado')->keys();
        $activosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado !== 'rechazado')->keys();

        $integrantesIds = collect($data['integrantes'])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $alumno->id)
            ->unique()
            ->diff($activosEnGrupo);

        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $inhabilitados = Alumno::whereIn('id', $integrantesIds)
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'inhabilitado'))
            ->pluck('id');
        $deOtroProyecto = $this->alumnoIdsDeOtroProyecto($integrantesIds, $grupo->proyecto_id);

        $aInvitar = $integrantesIds->diff($comprometidos)->diff($inhabilitados)->diff($deOtroProyecto);

        foreach ($aInvitar as $integranteId) {
            if ($rechazadosEnGrupo->contains($integranteId)) {
                $grupo->alumnos()->updateExistingPivot($integranteId, ['estado' => 'pendiente', 'respondido_at' => null]);
            } else {
                $grupo->alumnos()->attach($integranteId, ['estado' => 'pendiente']);
            }
            $integrante = Alumno::find($integranteId);
            if ($integrante?->user) {
                try {
                    $integrante->user->notify(new TaqeInvitacionGrupo($grupo));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de invitación Taq'e a alumno {$integrante->id}: " . $e->getMessage());
                }
            }
        }

        if ($aInvitar->isEmpty()) {
            return response()->json(['message' => 'No se pudo invitar a nadie nuevo (ya pertenecen a otro grupo o ciclo).'], 422);
        }

        return response()->json([
            'message' => 'Se enviaron ' . $aInvitar->count() . ' invitación(es) con éxito.',
        ]);
    }

    /**
     * Retirar a un integrante o cancelar invitación pendiente (solo organizador).
     */
    public function retirarIntegrante(Request $request, int $grupoId, int $integranteId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        if ($grupo->creador_alumno_id !== $alumno->id) {
            return response()->json(['message' => 'Solo el organizador puede retirar integrantes.'], 403);
        }

        if ($integranteId === $alumno->id) {
            return response()->json(['message' => 'Para salirte tú mismo usa la opción Salir del grupo.'], 422);
        }

        $pivot = $grupo->alumnos()->wherePivot('alumno_id', $integranteId)->first();
        if (! $pivot) {
            return response()->json(['message' => 'El alumno no pertenece a este grupo.'], 404);
        }

        $eraAceptado = $pivot->pivot->estado === 'aceptado';
        $grupo->alumnos()->detach($integranteId);

        if ($eraAceptado) {
            $integrante = Alumno::find($integranteId);
            if ($integrante?->user) {
                try {
                    $integrante->user->notify(new TaqeIntegranteRetirado($grupo));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de retiro Taq'e: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'message' => $eraAceptado ? 'Integrante retirado del grupo.' : 'Invitación cancelada.',
        ]);
    }

    /**
     * Salir del grupo (cualquier integrante).
     */
    public function salir(Request $request, int $grupoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $grupo = TaqeGrupo::findOrFail($grupoId);
        $esAceptado = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();
        if (! $esAceptado) {
            return response()->json(['message' => 'No perteneces activamente a este grupo.'], 403);
        }

        $otrosAceptados = $grupo->alumnosAceptados()
            ->where('alumnos.id', '!=', $alumno->id)
            ->orderBy('taqe_grupo_alumno.respondido_at')
            ->get();

        $huboCambioDeOrganizador = false;

        if ($otrosAceptados->isEmpty()) {
            $this->eliminarArchivosDelGrupo($grupo);
            $grupo->delete();

            return response()->json(['message' => 'Saliste del grupo. Como eras el único integrante, el grupo se eliminó.']);
        }

        if ($grupo->creador_alumno_id === $alumno->id) {
            $grupo->creador_alumno_id = $otrosAceptados->first()->id;
            $grupo->save();
            $huboCambioDeOrganizador = true;
        }

        $grupo->alumnos()->detach($alumno->id);

        foreach ($otrosAceptados as $integrante) {
            if ($integrante->user) {
                try {
                    $integrante->user->notify(new TaqeMiembroSalio($grupo, $alumno, $huboCambioDeOrganizador));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de salida Taq'e: " . $e->getMessage());
                }
            }
        }

        return response()->json(['message' => 'Saliste del grupo "' . $grupo->nombre . '".']);
    }

    /**
     * Mover trabajo entre evidencia y trabajo final.
     */
    public function moverTrabajo(Request $request, int $trabajoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $trabajo = TaqeTrabajo::with('grupo')->findOrFail($trabajoId);
        $grupo = $trabajo->grupo;

        $esMiembro = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();
        if (! $esMiembro) {
            return response()->json(['message' => 'No perteneces a este grupo.'], 403);
        }

        if ($grupo->estaEntregado()) {
            return response()->json(['message' => 'El grupo ya entregó su trabajo final: no se puede modificar.'], 422);
        }

        $trabajo->categoria = $trabajo->esFinal() ? 'evidencia' : 'final';
        $trabajo->save();

        return response()->json([
            'message' => $trabajo->esFinal()
                ? '"' . $trabajo->nombre . '" ahora es parte del trabajo final.'
                : '"' . $trabajo->nombre . '" volvió a las evidencias.',
        ]);
    }

    /**
     * Eliminar un archivo o enlace del grupo.
     */
    public function eliminarTrabajo(Request $request, int $trabajoId)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $trabajo = TaqeTrabajo::with('grupo')->findOrFail($trabajoId);
        $grupo = $trabajo->grupo;

        $esMiembro = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();
        if (! $esMiembro) {
            return response()->json(['message' => 'No perteneces a este grupo.'], 403);
        }

        $esOrganizador = $grupo->creador_alumno_id === $alumno->id;
        if (! $esOrganizador && $trabajo->subido_por_alumno_id !== $alumno->id) {
            return response()->json(['message' => 'Solo quien lo subió o el organizador pueden eliminarlo.'], 403);
        }

        if ($grupo->estaEntregado()) {
            return response()->json(['message' => 'El grupo ya entregó su trabajo final: no se puede eliminar nada.'], 422);
        }

        $this->eliminarTrabajoModel($trabajo);

        return response()->json(['message' => '"' . $trabajo->nombre . '" eliminado.']);
    }

    private function eliminarTrabajoModel(TaqeTrabajo $trabajo): void
    {
        if ($trabajo->archivo) {
            try {
                $this->drive()->delete($trabajo->archivo);
            } catch (\Throwable $e) {
                Log::warning("No se pudo borrar archivo {$trabajo->archivo} en Drive: " . $e->getMessage());
            }
        }
        $trabajo->delete();
    }

    /**
     * Responder a una invitación (aceptar o rechazar).
     */
    public function responder(Request $request)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'grupo_id'  => 'required|exists:taqe_grupos,id',
            'respuesta' => 'required|in:aceptado,rechazado',
        ]);

        $grupo = TaqeGrupo::findOrFail($data['grupo_id']);

        $pivotExiste = $grupo->alumnos()->wherePivot('alumno_id', $alumno->id)->exists();
        if (! $pivotExiste) {
            return response()->json(['message' => 'No tienes una invitación para este grupo.'], 403);
        }

        if ($data['respuesta'] === 'aceptado') {
            $yaTieneOtroGrupo = $alumno->taqeGrupos()
                ->where('periodo_actual_id', $grupo->periodo_actual_id)
                ->wherePivot('estado', 'aceptado')
                ->where('taqe_grupos.id', '!=', $grupo->id)
                ->exists();

            if ($yaTieneOtroGrupo) {
                return response()->json([
                    'message' => 'Ya perteneces a otro grupo en este periodo. No puedes pertenecer a dos grupos a la vez.',
                ], 422);
            }
        }

        $grupo->alumnos()->updateExistingPivot($alumno->id, [
            'estado'         => $data['respuesta'],
            'respondido_at'  => now(),
        ]);

        if ($data['respuesta'] === 'aceptado') {
            $this->rechazarInvitacionesDelPeriodo($alumno, $grupo->periodo_actual_id, $grupo->id);

            $grupo->alumnosAceptados()
                ->where('alumnos.id', '!=', $alumno->id)
                ->get()
                ->each(function (Alumno $integrante) use ($grupo, $alumno) {
                    if ($integrante->user) {
                        try {
                            $integrante->user->notify(new TaqeMiembroAcepto($grupo, $alumno));
                        } catch (\Throwable $e) {
                            Log::warning("Notificación de nuevo integrante Taq'e: " . $e->getMessage());
                        }
                    }
                });
        }

        $mensaje = $data['respuesta'] === 'aceptado'
            ? '¡Invitación aceptada! Ahora formas parte del grupo.'
            : 'Invitación rechazada.';

        return response()->json(['message' => $mensaje]);
    }

    /**
     * Solicitar ayuda metodológica al docente investigador.
     */
    public function solicitarAyuda(Request $request)
    {
        $alumno = $this->alumnoActual($request);
        if (! $alumno) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'grupo_id' => 'required|exists:taqe_grupos,id',
            'motivo'   => 'required|in:cambiar_organizador,disolver_grupo,otro',
            'mensaje'  => 'nullable|string|max:1000|required_if:motivo,otro',
        ], [
            'motivo.required'     => 'Debes indicar el motivo de la consulta.',
            'mensaje.required_if' => 'Por favor describe la consulta para el docente investigador.',
        ]);

        $grupo = TaqeGrupo::findOrFail($data['grupo_id']);

        $esMiembro = $grupo->alumnos()
            ->wherePivot('alumno_id', $alumno->id)
            ->wherePivot('estado', 'aceptado')
            ->exists();

        if (! $esMiembro) {
            return response()->json(['message' => 'Debes pertenecer a este grupo para solicitar asesoría.'], 403);
        }

        $solicitud = TaqeSolicitudAyuda::create([
            'taqe_grupo_id' => $grupo->id,
            'grupo_nombre'  => $grupo->nombre,
            'alumno_id'     => $alumno->id,
            'motivo'        => $data['motivo'],
            'mensaje'       => $data['mensaje'] ?? null,
            'estado'        => 'pendiente',
        ]);

        try {
            $investigadores = User::role('investigador')->get();
            foreach ($investigadores as $inv) {
                $inv->notify(new TaqeSolicitudAyudaCreada($solicitud));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a investigadores: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Solicitud enviada a los docentes investigadores. Se comunicarán pronto con tu equipo.',
            'data'    => $solicitud,
        ], 201);
    }
}
