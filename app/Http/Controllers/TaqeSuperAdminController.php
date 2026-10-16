<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ArchivosTaqe;
use App\Http\Controllers\Concerns\GestionaGruposTaqe;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Models\TaqeGrupo;
use App\Models\TaqeSolicitudAyuda;
use App\Models\TaqeTrabajo;
use App\Notifications\TaqeGrupoEliminado;
use App\Notifications\TaqeIntegranteRetirado;
use App\Notifications\TaqeInvitacionGrupo;
use App\Notifications\TaqeSolicitudAtendida;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gestión institucional de Taq'e para Super Admin: a diferencia del
 * Investigador (solo lectura, ver TaqeInvestigadorController), acá se puede
 * agregar/retirar integrantes, descargar o borrar archivos y eliminar un
 * grupo completo — control total sobre CUALQUIER grupo, no solo el propio
 * (el alumno organizador en TaqeController solo gestiona sus grupos).
 */
class TaqeSuperAdminController extends Controller
{
    use ArchivosTaqe, GestionaGruposTaqe;

    public function index(Request $request)
    {
        $query = TaqeGrupo::with(['curso', 'ciclo', 'programa', 'periodoActual', 'proyecto', 'alumnos', 'evidencias.subidoPor', 'finales.subidoPor']);

        if ($request->filled('periodo_actual_id')) {
            $query->where('periodo_actual_id', $request->input('periodo_actual_id'));
        }
        if ($request->filled('programa_id')) {
            $query->where('programa_id', $request->input('programa_id'));
        }
        if ($request->filled('ciclo_id')) {
            $query->where('ciclo_id', $request->input('ciclo_id'));
        }
        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->input('curso_id'));
        }
        if ($request->filled('proyecto_id')) {
            $query->where('proyecto_id', $request->input('proyecto_id'));
        }

        $grupos = $query->orderByDesc('id')->get();

        // Métricas rápidas para el encabezado — se calculan sobre la colección
        // ya cargada (alumnos/trabajos vienen eager-loaded arriba), sin
        // consultas extra.
        $totalIntegrantesAceptados = $grupos->sum(fn ($g) => $g->alumnos->where('pivot.estado', 'aceptado')->count());
        $totalConTrabajoFinal = $grupos->filter(fn ($g) => $g->estaEntregado())->count(); // entregados

        $periodos = PeriodoActual::orderByDesc('id')->get();
        $programas = Programa::orderBy('nombre')->get();
        $proyectos = \App\Models\Proyecto::orderBy('id')->get(['id', 'nombre', 'producto']);
        $ciclos = $request->filled('programa_id')
            ? Ciclo::where('programa_id', $request->input('programa_id'))->orderBy('id')->get()
            : collect();
        $cursos = $request->filled('ciclo_id')
            ? Curso::where('ciclo_id', $request->input('ciclo_id'))->orderBy('nombre')->get()
            : collect();

        $solicitudesPendientes = TaqeSolicitudAyuda::with('alumno')
            ->where('estado', 'pendiente')
            ->orderByDesc('id')
            ->get();

        return view('taqe.superadmin.index', compact(
            'grupos', 'periodos', 'programas', 'proyectos', 'ciclos', 'cursos',
            'totalIntegrantesAceptados', 'totalConTrabajoFinal', 'solicitudesPendientes'
        ));
    }

    /** Marca una solicitud de ayuda como atendida y avisa a quien la envió. */
    public function marcarAtendidaSolicitud(TaqeSolicitudAyuda $solicitud)
    {
        $solicitud->estado = 'atendido';
        $solicitud->atendido_por_user_id = auth()->id();
        $solicitud->atendido_at = now();
        $solicitud->save();

        if ($solicitud->alumno->user) {
            try {
                $solicitud->alumno->user->notify(new TaqeSolicitudAtendida($solicitud));
            } catch (\Throwable $e) {
                Log::warning("Notificación de solicitud atendida (Super Admin) Taq'e a alumno {$solicitud->alumno_id}: ".$e->getMessage());
            }
        }

        return back()->with('success', 'Solicitud marcada como atendida.');
    }

    /** Compañeros disponibles para sumar a ESTE grupo (mismo curso+periodo), para el modal de gestión. */
    public function alumnosDisponibles(TaqeGrupo $grupo)
    {
        $periodoActual = PeriodoActual::findOrFail($grupo->periodo_actual_id);
        $yaEnGrupo = $grupo->alumnos()->pluck('alumnos.id');

        $disponibles = $this->companerosDisponibles($grupo->curso, $periodoActual, null, $grupo->proyecto_id)
            ->reject(fn ($a) => $yaEnGrupo->contains($a->id))
            ->values();

        return response()->json($disponibles->map(fn ($a) => [
            'id' => $a->id,
            'nombre' => $a->apellidos.', '.$a->nombres.' — DNI '.$a->dni,
        ]));
    }

    /** Invita nuevos integrantes a cualquier grupo (mismas reglas de elegibilidad que el autoservicio). */
    public function agregarIntegrantes(Request $request, TaqeGrupo $grupo)
    {
        $data = $request->validate([
            'integrantes' => 'required|array|min:1',
            'integrantes.*' => 'exists:alumnos,id',
        ]);

        $periodoActual = PeriodoActual::findOrFail($grupo->periodo_actual_id);

        $pivotesActuales = $grupo->alumnos()->pluck('taqe_grupo_alumno.estado', 'alumnos.id');
        $activosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado !== 'rechazado')->keys();
        $rechazadosEnGrupo = $pivotesActuales->filter(fn ($estado) => $estado === 'rechazado')->keys();

        $integrantesIds = collect($data['integrantes'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->diff($activosEnGrupo);

        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $inhabilitados = Alumno::whereIn('id', $integrantesIds)
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'inhabilitado'))
            ->pluck('id');

        // Mismas reglas que el autoservicio, incluido el Proyecto Integrador del ciclo de cada alumno.
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
                $integrante->user->notify(new TaqeInvitacionGrupo($grupo));
            }
        }

        if ($aInvitar->isEmpty()) {
            return back()->with('error', 'No hay nuevos integrantes para invitar (ya están en otro grupo este periodo, están inhabilitados, son de otro Proyecto Integrador o ya tienen relación con este grupo).');
        }

        return back()->with('success', 'Invitación enviada.');
    }

    /** Retira (o cancela la invitación de) un integrante de cualquier grupo. */
    public function retirarIntegrante(TaqeGrupo $grupo, Alumno $integrante)
    {
        $pivot = $grupo->alumnos()->wherePivot('alumno_id', $integrante->id)->first();
        abort_unless($pivot, 404);

        $eraAceptado = $pivot->pivot->estado === 'aceptado';
        $grupo->alumnos()->detach($integrante->id);

        if ($eraAceptado && $integrante->user) {
            try {
                $integrante->user->notify(new TaqeIntegranteRetirado($grupo));
            } catch (\Throwable $e) {
                Log::warning("Notificación de retiro (Super Admin) de grupo Taq'e a alumno {$integrante->id}: ".$e->getMessage());
            }
        }

        $mensaje = $eraAceptado
            ? $integrante->apellidos.', '.$integrante->nombres.' fue retirado del grupo.'
            : 'Invitación a '.$integrante->apellidos.', '.$integrante->nombres.' cancelada.';

        return back()->with('success', $mensaje);
    }

    /** Muestra (streaming) el archivo de un trabajo de CUALQUIER grupo — a diferencia de TaqeController::verTrabajo(), no exige ser integrante. Con ?descargar=1 lo baja como adjunto. */
    public function verTrabajo(Request $request, TaqeTrabajo $trabajo): \Symfony\Component\HttpFoundation\Response
    {
        return $this->servirArchivo($trabajo, $request->boolean('descargar'));
    }

    /** ZIP del grupo: evidencias | final | todo. */
    public function descargarZip(TaqeGrupo $grupo, string $que)
    {
        return $this->descargarZipGrupo($grupo, $que);
    }

    /** Borra un archivo de cualquier grupo (también de Drive); si era el trabajo final, el grupo vuelve a En Proceso. */
    public function destroyTrabajo(TaqeTrabajo $trabajo)
    {
        $grupo = $trabajo->grupo;
        $this->eliminarTrabajo($trabajo);

        // Si era el último archivo del trabajo final de un grupo ya entregado, la entrega se reabre.
        if ($trabajo->esFinal() && $grupo->estaEntregado() && ! $grupo->finales()->exists()) {
            $grupo->update(['estado' => 'en_proceso', 'entregado_at' => null]);

            return back()->with('success', '"'.$trabajo->nombre.'" eliminado. Era lo único del trabajo final: el grupo vuelve a estar En proceso.');
        }

        return back()->with('success', '"'.$trabajo->nombre.'" eliminado.');
    }

    /** Elimina un grupo completo (cascada limpia integrantes y trabajos, sus archivos se borran de Drive) y avisa a los integrantes aceptados. */
    public function destroy(TaqeGrupo $grupo)
    {
        $grupo->loadMissing('curso');
        $nombre = $grupo->nombre;
        $integrantesAceptados = $grupo->alumnosAceptados()->get();

        $this->eliminarArchivosDelGrupo($grupo);
        $grupo->delete();

        foreach ($integrantesAceptados as $integrante) {
            if ($integrante->user) {
                try {
                    $integrante->user->notify(new TaqeGrupoEliminado($grupo));
                } catch (\Throwable $e) {
                    Log::warning("Notificación de eliminación (Super Admin) de grupo Taq'e a alumno {$integrante->id}: ".$e->getMessage());
                }
            }
        }

        return back()->with('success', 'Grupo "'.$nombre.'" eliminado.');
    }
}
