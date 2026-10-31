<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\PeriodoActual;
use App\Models\TaqeGrupo;
use App\Models\TaqeSolicitudAyuda;
use App\Models\User;
use App\Notifications\TaqeSolicitudAyudaCreada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaqeController extends Controller
{
    private function alumnoActual(Request $request): ?Alumno
    {
        $alumno = $request->user()->alumno;
        if ($alumno && $alumno->user?->hasRole('inhabilitado')) {
            return null;
        }

        return $alumno;
    }

    /**
     * Retorna el espacio Taq'e del alumno:
     * - Proyecto Integrador de su ciclo
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

        $gruposFormateados = $misGrupos->map(function (TaqeGrupo $grupo) use ($alumno) {
            $esOrganizador = $grupo->creador_alumno_id === $alumno->id;

            $integrantes = $grupo->alumnos
                ->reject(fn ($i) => $i->pivot->estado === 'rechazado')
                ->map(fn ($i) => [
                    'id'               => $i->id,
                    'nombre'           => $i->nombres . ' ' . $i->apellidos,
                    'es_organizador'   => $i->id === $grupo->creador_alumno_id,
                    'rol'              => $i->id === $grupo->creador_alumno_id ? 'Organizador' : 'Integrante',
                    'estado_invitacion'=> $i->pivot->estado,
                ])->values()->all();

            $mapearArchivo = fn ($trabajo) => [
                'id'          => $trabajo->id,
                'nombre'      => $trabajo->nombre,
                'tipo'        => $trabajo->tipo, // 'imagen', 'pdf', 'word', 'video', 'enlace', etc.
                'tipo_icono'  => $trabajo->tipo ?? 'otro',
                'url'         => $trabajo->enlace_url ?: ($trabajo->archivo_path ? url('storage/' . $trabajo->archivo_path) : null),
                'es_enlace'   => (bool) $trabajo->enlace_url,
                'subido_por'  => $trabajo->subidoPor?->nombres . ' ' . $trabajo->subidoPor?->apellidos,
                'fecha'       => $trabajo->created_at?->format('d/m/Y H:i'),
            ];

            return [
                'id'             => $grupo->id,
                'nombre'         => $grupo->nombre,
                'curso_id'       => $grupo->curso_id,
                'curso_nombre'   => $grupo->curso?->nombre ?? 'Curso',
                'estado'         => $grupo->estado, // 'en_proceso' | 'finalizado'
                'es_organizador' => $esOrganizador,
                'entregado'      => method_exists($grupo, 'estaEntregado') ? $grupo->estaEntregado() : ($grupo->estado === 'finalizado'),
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
                'mis_grupos'              => $gruposFormateados,
                'invitaciones_pendientes' => $invitacionesFormateadas,
                'investigadores'          => $investigadores,
                'grupos_anteriores_count' => $gruposAnterioresCount,
            ],
        ]);
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

        $grupo->alumnos()->updateExistingPivot($alumno->id, ['estado' => $data['respuesta']]);

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
            'motivo.required' => 'Debes indicar el motivo de la consulta.',
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
