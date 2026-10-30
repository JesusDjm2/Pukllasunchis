<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Http\Controllers\Controller;
use App\Models\RicardoPalma\RpEstudiante;
use App\Models\RicardoPalma\RpPeriodo;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RpEstudianteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim($request->get('buscar', ''));
        $programaId = $request->get('programa_id');

        $query = RpEstudiante::with('programa');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_matricula', 'like', "%{$buscar}%")
                  ->orWhere('dni', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('nombres', 'like', "%{$buscar}%");
            });
        }

        if ($programaId) {
            $query->where('programa_id', $programaId);
        }

        // Período: alumnos que figuran en alguna acta de ese año académico
        $periodoId = $request->get('periodo_id');
        if ($periodoId) {
            $query->whereHas('calificaciones.acta', fn ($q) => $q->where('periodo_id', $periodoId));
        }
        // Solo los períodos que tienen actas cargadas
        $periodos = RpPeriodo::whereHas('actas')->orderByDesc('anio')->orderByDesc('semestre')->get();

        $estudiantes = $query->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(25)
            ->withQueryString();

        $programas = RpPrograma::where('activo', true)->orderBy('nombre')->get();

        // Períodos (año académico) de cada alumno de la página, sacados de sus actas, del más antiguo al más reciente
        $periodosPorEstudiante = DB::table('rp_calificaciones as c')
            ->join('rp_actas as a', 'a.id', '=', 'c.acta_id')
            ->join('rp_periodos as p', 'p.id', '=', 'a.periodo_id')
            ->whereIn('c.estudiante_id', $estudiantes->pluck('id'))
            ->select('c.estudiante_id', 'p.codigo', 'p.anio', 'p.semestre', 'a.numero_semestre')
            ->distinct()
            ->orderBy('p.anio')->orderByRaw("p.semestre = 'II'")
            ->get()
            ->groupBy('estudiante_id');

        return view('admin.ricardo-palma.estudiantes.index', compact('estudiantes', 'programas', 'buscar', 'programaId', 'periodosPorEstudiante', 'periodos', 'periodoId'));
    }

    public function show(RpEstudiante $estudiante): View
    {
        $estudiante->load([
            'programa',
            'calificaciones.acta.periodo',
            'calificaciones.curso',
        ]);

        // Agrupar calificaciones por semestre o acta para mostrar el récord histórico
        $calificacionesPorActa = $estudiante->calificaciones
            ->sortBy(fn ($c) => ($c->acta?->periodo?->anio ?? 0) * 10 + ($c->acta?->numero_semestre ?? 0))
            ->groupBy('acta_id');

        return view('admin.ricardo-palma.estudiantes.show', compact('estudiante', 'calificacionesPorActa'));
    }

    public const ESTADOS = ['REGULAR', 'EGRESADO', 'RETIRADO', 'TRASLADADO'];

    public function editar(RpEstudiante $estudiante): View
    {
        $programas = RpPrograma::orderBy('nombre')->get();
        $estados = self::ESTADOS;

        return view('admin.ricardo-palma.estudiantes.editar', compact('estudiante', 'programas', 'estados'));
    }

    public function actualizar(Request $request, RpEstudiante $estudiante): RedirectResponse
    {
        $datos = $request->validate([
            'programa_id' => 'nullable|exists:rp_programas,id',
            'numero_matricula' => 'nullable|string|max:50',
            'dni' => 'nullable|string|max:15',
            'apellidos' => 'required|string|max:150',
            'nombres' => 'required|string|max:150',
            'sexo' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|max:50',
            'anio_ingreso' => 'nullable|integer|min:1950|max:2030',
            'estado' => 'required|in:'.implode(',', self::ESTADOS),
            'observaciones' => 'nullable|string|max:2000',
        ]);
        $datos['apellidos'] = mb_strtoupper(trim($datos['apellidos']));
        $estudiante->update($datos);

        return redirect()->route('rp.estudiantes.show', $estudiante)->with('success', 'Datos del estudiante actualizados.');
    }

    /** Elimina al estudiante y todas sus notas (de todas las actas). */
    public function eliminar(RpEstudiante $estudiante): RedirectResponse
    {
        $nombre = trim($estudiante->apellidos.', '.$estudiante->nombres);
        $estudiante->delete();

        return redirect()->route('rp.estudiantes.index')->with('success', "Se eliminó a {$nombre} con todas sus notas.");
    }
}
