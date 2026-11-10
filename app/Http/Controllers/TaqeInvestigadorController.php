<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ArchivosTaqe;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Models\TaqeGrupo;
use App\Models\TaqeSolicitudAyuda;
use App\Models\TaqeTrabajo;
use App\Notifications\TaqeSolicitudAtendida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaqeInvestigadorController extends Controller
{
    use ArchivosTaqe;

    /**
     * Seguimiento institucional: el Investigador ve y filtra TODOS los
     * grupos de Investigaciones, sin poder editarlos (solo el propio grupo gestiona sus
     * integrantes/trabajos) — pero sí puede ver y descargar sus archivos (uno
     * por uno o en ZIP) y marcar como atendidas las solicitudes de ayuda
     * (ver marcarAtendida()).
     */
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

        return view('taqe.investigador.index', compact(
            'grupos', 'periodos', 'programas', 'proyectos', 'ciclos', 'cursos', 'solicitudesPendientes'
        ));
    }

    /** Ver (o con ?descargar=1, bajar) el archivo de cualquier trabajo. */
    public function verTrabajo(Request $request, TaqeTrabajo $trabajo): \Symfony\Component\HttpFoundation\Response
    {
        return $this->servirArchivo($trabajo, $request->boolean('descargar'));
    }

    /** ZIP del grupo: evidencias | final | todo. */
    public function descargarZip(TaqeGrupo $grupo, string $que)
    {
        return $this->descargarZipGrupo($grupo, $que);
    }

    /** Marca una solicitud de ayuda como atendida y avisa a quien la envió. */
    public function marcarAtendida(TaqeSolicitudAyuda $solicitud)
    {
        $solicitud->estado = 'atendido';
        $solicitud->atendido_por_user_id = auth()->id();
        $solicitud->atendido_at = now();
        $solicitud->save();

        if ($solicitud->alumno->user) {
            try {
                $solicitud->alumno->user->notify(new TaqeSolicitudAtendida($solicitud));
            } catch (\Throwable $e) {
                Log::warning("Notificación de solicitud atendida de Investigaciones a alumno {$solicitud->alumno_id}: ".$e->getMessage());
            }
        }

        return back()->with('success', 'Solicitud marcada como atendida.');
    }
}
