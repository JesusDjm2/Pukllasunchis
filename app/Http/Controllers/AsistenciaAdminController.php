<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AsistenciaAdminController extends Controller
{
    /**
     * Panel general de control de asistencias para Dirección y Coordinación Académica.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin', 'adminB']), 403);

        $periodoActual = PeriodoActual::actual();

        // Parámetros de filtrado
        $programaId = $request->query('programa_id');
        $cicloId = $request->query('ciclo_id');
        $cursoId = $request->query('curso_id');
        $docenteId = $request->query('docente_id');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $filtroEspecial = $request->query('filtro_especial', 'todas'); // todas, amantani, pronabec, beca, licencia, alerta

        // Catálogos para filtros
        $programas = Programa::orderBy('nombre')->get();
        $ciclos = Ciclo::with('programa')->orderBy('nombre')->get();
        $docentes = Docente::orderBy('nombre')->get();

        $query = Asistencia::with(['curso.ciclo.programa', 'docente', 'detalles.user', 'detalles.alumno'])
            ->when($periodoActual, fn ($q) => $q->where('periodo_actual_id', $periodoActual->id));

        if ($programaId) {
            $query->whereHas('curso.ciclo', fn ($q) => $q->where('programa_id', $programaId));
        }

        if ($cicloId) {
            $query->whereHas('curso', fn ($q) => $q->where('ciclo_id', $cicloId));
        }

        if ($cursoId) {
            $query->where('curso_id', $cursoId);
        }

        if ($docenteId) {
            $query->where('docente_id', $docenteId);
        }

        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        // Filtro especial a nivel de detalle
        if ($filtroEspecial === 'amantani') {
            $query->whereHas('detalles', fn ($q) => $q->where('tipo_beca', 'LIKE', '%amantani%'));
        } elseif ($filtroEspecial === 'pronabec') {
            $query->whereHas('detalles', fn ($q) => $q->where(fn ($sub) => $sub->where('tipo_beca', 'LIKE', '%pronabec%')->orWhere('tipo_beca', 'LIKE', '%beca 18%')));
        } elseif ($filtroEspecial === 'beca') {
            $query->whereHas('detalles', fn ($q) => $q->where('es_beca', true));
        } elseif ($filtroEspecial === 'licencia') {
            $query->whereHas('detalles', fn ($q) => $q->where('en_licencia', true)->orWhere('estado', 'licencia'));
        } elseif ($filtroEspecial === 'alerta') {
            $query->whereHas('detalles', fn ($q) => $q->where('estado', 'falta'));
        }

        $sesiones = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(25)->withQueryString();

        // ── Métricas y KPIs globales del periodo ──
        $todasSesionesPeriodo = Asistencia::with('detalles')
            ->when($periodoActual, fn ($q) => $q->where('periodo_actual_id', $periodoActual->id))
            ->get();

        $totalSesiones = $todasSesionesPeriodo->count();
        $totalDetalles = $todasSesionesPeriodo->sum(fn ($s) => $s->detalles->count());
        $totalPresentes = $todasSesionesPeriodo->sum(fn ($s) => $s->presentes_count);
        $totalTardanzas = $todasSesionesPeriodo->sum(fn ($s) => $s->tardanzas_count);
        $totalFaltas = $todasSesionesPeriodo->sum(fn ($s) => $s->faltas_count);
        $totalLicencias = $todasSesionesPeriodo->sum(fn ($s) => $s->licencias_count);

        $porcentajeGlobal = $totalDetalles > 0
            ? round((($totalPresentes + $totalTardanzas) / $totalDetalles) * 100, 1)
            : 0;

        // Métricas de becarios (Amantani y Pronabec)
        $detallesBecas = $todasSesionesPeriodo->flatMap->detalles->where('es_beca', true);
        $totalDetallesBecas = $detallesBecas->count();
        $presentesBecas = $detallesBecas->whereIn('estado', ['presente', 'tardanza'])->count();
        $porcentajeBecas = $totalDetallesBecas > 0 ? round(($presentesBecas / $totalDetallesBecas) * 100, 1) : 0;

        $detallesAmantani = $detallesBecas->filter(fn ($d) => stripos($d->tipo_beca ?? '', 'amantani') !== false);
        $detallesPronabec = $detallesBecas->filter(fn ($d) => stripos($d->tipo_beca ?? '', 'pronabec') !== false || stripos($d->tipo_beca ?? '', 'beca 18') !== false);

        // Alumnos con mayor número de faltas (Top alertas)
        $topFaltas = AsistenciaDetalle::with(['user', 'alumno', 'asistencia.curso'])
            ->where('estado', 'falta')
            ->select('user_id', \DB::raw('count(*) as faltas_count'))
            ->groupBy('user_id')
            ->orderByDesc('faltas_count')
            ->take(10)
            ->get();

        return view('admin.asistencias.index', compact(
            'sesiones',
            'programas',
            'ciclos',
            'docentes',
            'periodoActual',
            'totalSesiones',
            'porcentajeGlobal',
            'totalFaltas',
            'totalLicencias',
            'totalDetallesBecas',
            'porcentajeBecas',
            'detallesAmantani',
            'detallesPronabec',
            'topFaltas',
            'programaId',
            'cicloId',
            'cursoId',
            'docenteId',
            'fechaInicio',
            'fechaFin',
            'filtroEspecial'
        ));
    }

    /**
     * Muestra el detalle completo de una sesión de asistencia.
     */
    public function show(Asistencia $asistencia)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin', 'adminB']), 403);

        $asistencia->load(['curso.ciclo.programa', 'docente', 'periodoActual', 'creador', 'detalles.user', 'detalles.alumno']);

        return view('admin.asistencias.show', compact('asistencia'));
    }
}
