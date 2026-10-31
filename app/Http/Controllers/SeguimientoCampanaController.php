<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\User;
use App\Services\SeguimientoEgresado\CampanaAsignacionService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Http\Request;

class SeguimientoCampanaController extends Controller
{
    protected CampanaAsignacionService $asignacionService;
    protected SeguimientoAutorizacionService $autorizacionService;

    public function __construct(
        CampanaAsignacionService $asignacionService,
        SeguimientoAutorizacionService $autorizacionService
    ) {
        $this->asignacionService = $asignacionService;
        $this->autorizacionService = $autorizacionService;
    }

    /**
     * Panel de Coordinación: Gestión de Campaña, selección de docentes y padrón real.
     */
    public function gestion(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esPersonalGestion($user)) {
            abort(403, 'No tienes autorización para acceder a la gestión de campañas.');
        }

        $campanas = Campana::orderBy('id', 'desc')->get();
        $campanaId = $request->query('campana_id', $campanas->first()?->id);
        $campanaActual = $campanas->firstWhere('id', $campanaId) ?: $campanas->first();

        $docentesElegibles = $this->asignacionService->obtenerDocentesElegibles();
        $diagnosticoPadron = $this->asignacionService->diagnosticarPadron();
        $padronEgresados = $this->asignacionService->obtenerPadronEgresadosElegibles($campanaActual?->id);

        $asignacionesActuales = $campanaActual
            ? AsignacionCampana::activos()->where('campana_id', $campanaActual->id)->with(['docente', 'egresado'])->get()
            : collect([]);

        return view('seguimiento_egresados.campanas.gestion', compact(
            'campanas',
            'campanaActual',
            'docentesElegibles',
            'diagnosticoPadron',
            'padronEgresados',
            'asignacionesActuales'
        ));
    }

    /**
     * Vista previa AJAX de la distribución aleatoria equilibrada.
     */
    public function previewDistribucion(Request $request)
    {
        if (!auth()->check() || !$this->autorizacionService->esPersonalGestion(auth()->user())) {
            return response()->json(['success' => false, 'error' => 'No autorizado.'], 403);
        }

        $request->validate([
            'campana_id' => 'required|exists:egresado_campanas,id',
            'docentes' => 'required|array|min:1',
            'docentes.*' => 'exists:users,id',
        ]);

        try {
            $preview = $this->asignacionService->distribuirEquilibrado(
                (int) $request->input('campana_id'),
                $request->input('docentes'),
                auth()->id(),
                $request->boolean('es_ensayo'),
                true
            );

            return response()->json(['success' => true, 'data' => $preview]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Ejecutar y confirmar la distribución aleatoria equilibrada (ACID).
     */
    public function ejecutarDistribucion(Request $request)
    {
        if (!auth()->check() || !$this->autorizacionService->esPersonalGestion(auth()->user())) {
            return response()->json(['success' => false, 'error' => 'No autorizado.'], 403);
        }

        $request->validate([
            'campana_id' => 'required|exists:egresado_campanas,id',
            'docentes' => 'required|array|min:1',
            'docentes.*' => 'exists:users,id',
        ]);

        try {
            $resultado = $this->asignacionService->distribuirEquilibrado(
                (int) $request->input('campana_id'),
                $request->input('docentes'),
                auth()->id(),
                $request->boolean('es_ensayo'),
                false
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $resultado]);
            }

            return redirect()->route('seguimiento.campanas.monitoreo', ['campanaId' => $request->input('campana_id')])
                ->with('success', "Distribución completada: {$resultado['total_egresados']} egresados distribuidos entre {$resultado['total_docentes']} docentes.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->withErrors(['distribucion' => $e->getMessage()]);
        }
    }

    /**
     * Panel de Coordinación: Monitoreo en vivo de avance de campaña por docente.
     */
    public function monitoreo(Request $request, $campanaId)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esPersonalGestion($user)) {
            abort(403, 'No tienes autorización para monitorear campañas.');
        }

        $campana = Campana::findOrFail($campanaId);
        $metricas = $this->asignacionService->obtenerMetricasCampana($campana->id);
        $docentesDisponibles = $this->asignacionService->obtenerDocentesElegibles();
        $campanas = Campana::orderBy('id', 'desc')->get();

        return view('seguimiento_egresados.campanas.monitoreo', compact('campana', 'metricas', 'docentesDisponibles', 'campanas'));
    }

    /**
     * Reasignación individual autorizada de un egresado a otro docente.
     */
    public function reasignar(Request $request)
    {
        if (!auth()->check() || !$this->autorizacionService->esPersonalGestion(auth()->user())) {
            return response()->json(['success' => false, 'error' => 'No autorizado.'], 403);
        }

        $request->validate([
            'asignacion_id' => 'required|exists:egresado_campana_asignaciones,id',
            'nuevo_docente_id' => 'required|exists:users,id',
            'motivo' => 'required|string|min:4',
        ]);

        try {
            $nueva = $this->asignacionService->reasignarEgresado(
                (int) $request->input('asignacion_id'),
                (int) $request->input('nuevo_docente_id'),
                auth()->id(),
                $request->input('motivo')
            );

            return response()->json([
                'success' => true,
                'message' => 'Egresado reasignado exitosamente. Se preservó el historial anterior.',
                'asignacion_id' => $nueva->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Observatorio Territorial: Comparación antes y después de la campaña.
     */
    public function territorio(Request $request, $campanaId)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esPersonalGestion($user) && !$user->can('seguimiento-egresados.ver-analitica')) {
            abort(403, 'No tienes autorización para consultar el observatorio territorial.');
        }

        $campana = Campana::findOrFail($campanaId);
        $metricasTerritoriales = $this->asignacionService->obtenerMetricasTerritoriales($campana->id);
        $campanas = Campana::orderBy('id', 'desc')->get();

        return view('seguimiento_egresados.campanas.territorio', compact('campana', 'metricasTerritoriales', 'campanas'));
    }

    /**
     * Panel Docente: "Mis Egresados Asignados" con lista de trabajo, llamadas y actualización.
     */
    public function misAsignaciones(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esDocenteOEntrevistador($user) && !$this->autorizacionService->esPersonalGestion($user)) {
            abort(403, 'No tienes asignaciones de egresados en este momento.');
        }

        $campanaId = $request->query('campana_id');
        $metricasDocente = $this->asignacionService->obtenerMetricasDocente($user->id, $campanaId ? (int)$campanaId : null);
        $campanas = Campana::orderBy('id', 'desc')->get();

        return view('seguimiento_egresados.docente.mis_asignaciones', compact('metricasDocente', 'campanas', 'campanaId'));
    }
}
