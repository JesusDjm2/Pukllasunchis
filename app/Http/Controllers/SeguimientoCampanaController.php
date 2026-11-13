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
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
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
        $diagnosticoPadron = $this->asignacionService->diagnosticarPadron($grupoFormacion);
        $padronEgresados = $this->asignacionService->obtenerPadronEgresadosElegibles($campanaActual?->id, false, $grupoFormacion);

        $asignacionesActuales = $campanaActual
            ? AsignacionCampana::activos()->whereIn('egresado_user_id', $padronEgresados->pluck('user_id'))->where('campana_id', $campanaActual->id)->with(['docente', 'egresado'])->get()
            : collect([]);

        return view('seguimiento_egresados.campanas.gestion', compact(
            'grupoFormacion', 'campanas',
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
            'grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR',
            'docentes' => 'required|array|min:1',
            'docentes.*' => 'exists:users,id',
        ]);

        try {
            $preview = $this->asignacionService->distribuirEquilibrado(
                (int) $request->input('campana_id'),
                $request->input('docentes'),
                auth()->id(),
                $request->boolean('es_ensayo'),
                true,
                $request->input('grupo_formacion', 'TODOS')
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
            'grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR',
            'docentes' => 'required|array|min:1',
            'docentes.*' => 'exists:users,id',
        ]);

        try {
            $resultado = $this->asignacionService->distribuirEquilibrado(
                (int) $request->input('campana_id'),
                $request->input('docentes'),
                auth()->id(),
                $request->boolean('es_ensayo'),
                false,
                $request->input('grupo_formacion', 'TODOS')
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
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esPersonalGestion($user)) {
            abort(403, 'No tienes autorización para monitorear campañas.');
        }

        $campana = Campana::findOrFail($campanaId);
        $metricas = $this->asignacionService->obtenerMetricasCampana($campana->id, $grupoFormacion);
        $docentesDisponibles = $this->asignacionService->obtenerDocentesElegibles();
        $campanas = Campana::orderBy('id', 'desc')->get();

        return view('seguimiento_egresados.campanas.monitoreo', compact('grupoFormacion', 'campana', 'metricas', 'docentesDisponibles', 'campanas'));
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
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esPersonalGestion($user) && !$user->can('seguimiento-egresados.ver-analitica')) {
            abort(403, 'No tienes autorización para consultar el observatorio territorial.');
        }

        $campana = Campana::findOrFail($campanaId);
        $metricasTerritoriales = $this->asignacionService->obtenerMetricasTerritoriales($campana->id, $grupoFormacion);
        $campanas = Campana::orderBy('id', 'desc')->get();

        return view('seguimiento_egresados.campanas.territorio', compact('grupoFormacion', 'campana', 'metricasTerritoriales', 'campanas'));
    }

    /**
     * Panel Docente: "Mis Egresados Asignados" con lista de trabajo, llamadas y actualización.
     */
    public function misAsignaciones(Request $request)
    {
        $request->validate(['grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR']);
        $grupoFormacion = app(\App\Services\SeguimientoEgresado\GrupoFormacionService::class)->normalizar($request->query('grupo_formacion'));
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        $user = auth()->user();
        if (!$this->autorizacionService->esDocenteOEntrevistador($user) && !$this->autorizacionService->esPersonalGestion($user)) {
            abort(403, 'No tienes asignaciones de egresados en este momento.');
        }

        $campanaId = $request->query('campana_id');
        $metricasDocente = $this->asignacionService->obtenerMetricasDocente($user->id, $campanaId ? (int)$campanaId : null, $grupoFormacion);
        $campanas = Campana::orderBy('id', 'desc')->get();

        $perfilService=app(\App\Services\SeguimientoEgresado\PerfilTerritorialService::class);
        $metricasDocente['lista_trabajo']=$metricasDocente['lista_trabajo']->map(function($fila)use($perfilService){
            $p=$perfilService->actual($fila['user_id']);
            $fila['avance']=$perfilService->avance($p);
            $fila['telefono']=$p['contacto']['telefono'] ?? $fila['telefono'];
            $fila['email']=$p['contacto']['correo'] ?? $fila['email'];
            $fila['historial']=\App\Models\SeguimientoEgresado\Llamada::where('docente_user_id',auth()->id())->where('user_id',$fila['user_id'])->where('campana_id',$fila['campana_id'])->latest('fecha_hora')->limit(20)->get(['fecha_hora','resultado','observaciones']);
            $fila['alterno']=!empty($p['contacto_alterno']['autorizado']) ? $p['contacto_alterno'] : null;
            \App\Models\SeguimientoEgresado\AccesoContacto::create(['user_id_consultante'=>auth()->id(),'rol_consultante'=>'docente entrevistador','user_id_consultado'=>$fila['user_id'],'identificador_consultado'=>(string)$fila['user_id'],'finalidad'=>'Campaña asignada '.$fila['campana_id'],'fecha_hora'=>now()]);
            return $fila;
        });
        $pares=$metricasDocente['lista_trabajo']->map(fn($f)=>[$f['user_id'],$f['campana_id']]);
        $evolucion=\App\Models\SeguimientoEgresado\Llamada::where('docente_user_id',$user->id)->where(function($q)use($pares){
            if($pares->isEmpty()) $q->whereRaw('1=0');
            foreach($pares as [$persona,$campana]) $q->orWhere(fn($sub)=>$sub->where('user_id',$persona)->where('campana_id',$campana));
        })->orderBy('fecha_hora')->get()->groupBy(fn($l)=>$l->fecha_hora->format('Y-m-d'))->map(fn($llamadas)=>['intentos'=>$llamadas->count(),'contactados'=>$llamadas->where('es_contacto_efectivo',true)->pluck('user_id')->unique()->count()]);
        return view('seguimiento_egresados.docente.mis_asignaciones', compact('grupoFormacion', 'metricasDocente', 'campanas', 'campanaId','evolucion'));
    }
}
