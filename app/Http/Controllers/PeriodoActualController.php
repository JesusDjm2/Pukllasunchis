<?php

namespace App\Http\Controllers;

use App\Exports\RegistrosExport;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\PeriodoActual;
use App\Models\PeriodoTres;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PeriodoActualController extends Controller
{
    public function index()
    {
        /* $periodoactuales = PeriodoActual::all(); */
        $periodoactuales = PeriodoActual::orderBy('nombre', 'asc')->get();

        return view('admin.periodos.index', compact('periodoactuales'));
    }

    public function create()
    {
        return view('admin.periodos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'horario' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'actual' => 'nullable|boolean',
        ]);

        // Si se marca como actual, desmarcamos los demás
        if ($request->has('actual') && $request->actual) {
            PeriodoActual::where('actual', true)->update(['actual' => false]);
        }

        $rutaImagen = null;

        // Guardar imagen si se sube
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreOriginal = $archivo->getClientOriginalName();
            $archivo->move(public_path('img/horarios'), $nombreOriginal);
            $rutaImagen = 'img/horarios/'.$nombreOriginal;
        }

        PeriodoActual::create([
            'nombre' => $request->nombre,
            'horario' => $rutaImagen,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_cierre' => $request->fecha_cierre,
            'actual' => $request->has('actual') ? 1 : 0,
        ]);

        return redirect()->route('periodoactual.index')->with('success', 'Período Actual creado correctamente.');
    }

    public function edit(PeriodoActual $periodoactual)
    {
        return view('admin.periodos.edit', compact('periodoactual'));
    }

    public function update(Request $request, PeriodoActual $periodoactual)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'horario' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5096',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'actual' => 'nullable|boolean',
        ]);

        if ($request->has('actual') && $request->actual) {
            PeriodoActual::where('id', '!=', $periodoactual->id)->update(['actual' => false]);
        }
        $rutaHorario = $periodoactual->horario;
        if ($request->hasFile('horario')) {
            if ($periodoactual->horario && file_exists(public_path($periodoactual->horario))) {
                unlink(public_path($periodoactual->horario)); // elimina anterior
            }

            $archivo = $request->file('horario');
            $nombreOriginal = $archivo->getClientOriginalName();
            $archivo->move(public_path('img/horarios'), $nombreOriginal);
            $rutaHorario = 'img/horarios/'.$nombreOriginal;
        }

        $periodoactual->update([
            'nombre' => $request->nombre,
            'horario' => $rutaHorario,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_cierre' => $request->fecha_cierre,
            'actual' => $request->has('actual') ? 1 : 0,
        ]);

        return redirect()->route('periodoactual.index')->with('success', 'Período Actual actualizado correctamente.');
    }

    public function destroy(PeriodoActual $periodoactual)
    {
        $periodoactual->delete();

        return redirect()->route('periodoactual.index')->with('success', 'Período Actual eliminado correctamente.');
    }

    public function crearCalificaciones(PeriodoActual $periodoactual)
    {
        $datosPeriodoTres = PeriodoTres::whereHas('alumno.user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'inhabilitado');
            })
                ->whereHas('ciclo', function ($q) {
                    $q->whereRaw('LOWER(TRIM(nombre)) != ?', ['ciclo i']);
                });
        })->get();

        if ($datosPeriodoTres->isEmpty()) {
            return back()->with('error', 'No se encontraron registros válidos en el Periodo de Desempeño.');
        }

        foreach ($datosPeriodoTres as $registro) {
            $alumnoId = $registro->alumno_id;
            $cursoId = $registro->curso_id;
            $existe = Periodo::where('periodo_actual_id', $periodoactual->id)
                ->where('alumno_id', $alumnoId)
                ->where('curso_id', $cursoId)
                ->exists();

            if ($existe) {
                continue;
            }

            Periodo::create([
                'valoracion_curso' => $registro->valoracion_curso,
                'calificacion_curso' => $registro->calificacion_curso,
                'calificacion_sistema' => $registro->calificacion_sistema,
                'alumno_id' => $alumnoId,
                'curso_id' => $cursoId,
                'periodo_actual_id' => $periodoactual->id,
            ]);
        }

        return redirect()->route('periodoactual.index')
            ->with('success', 'Calificaciones generadas correctamente para el período: '.$periodoactual->nombre);
    }

    public function showRegistros($id)
    {
        $periodoActual = PeriodoActual::findOrFail($id);
        $periodos = Periodo::with([
            'alumno.programa',
            'alumno.user',
            'alumno.cursos.ciclo',
            'curso.ciclo',
        ])
            ->where('periodo_actual_id', $id)
            ->get()
            ->groupBy('alumno_id');

        // Ciclos
        $ciclos = Periodo::where('periodo_actual_id', $id)
            ->with('curso.ciclo')
            ->get()
            ->pluck('curso.ciclo')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($c) => $c->ordenCiclo() ?? 999);

        // Alumnos con relaciones precargadas
        $alumnoIds = $periodos->keys()->all();
        $alumnosMap = Alumno::with(['cursos.ciclo', 'programa', 'user'])
            ->whereIn('id', $alumnoIds)
            ->get()
            ->keyBy('id');

        $filas = collect();

        foreach ($periodos as $alumnoId => $grupoPeriodos) {
            $alumno = $alumnosMap->get($alumnoId) ?? $grupoPeriodos->first()->alumno;

            // IDs reales en pivot alumno_cursos
            $pivotIds = \DB::table('alumno_cursos')
                ->where('alumno_id', $alumnoId)
                ->pluck('curso_id')
                ->toArray();

            if (! empty($pivotIds)) {
                // Solo cursos en pivot
                $cursosMostrados = $alumno->relationLoaded('cursos')
                    ? $alumno->cursos->whereIn('id', $pivotIds)->values()
                    : Curso::with('ciclo')->whereIn('id', $pivotIds)->get();

                // 🔴 FILTRAMOS → solo los que tengan calificación en periodo
                /* $cursosMostrados = $cursosMostrados->filter(function ($curso) use ($grupoPeriodos) {
                    return $grupoPeriodos->firstWhere('curso_id', $curso->id);
                })->values(); */
                $cursosMostrados = $cursosMostrados
                    ->filter(function ($curso) use ($grupoPeriodos) {
                        return $grupoPeriodos->firstWhere('curso_id', $curso->id)
                            && ! str_contains(strtolower($curso->cc ?? ''), 'extracurricular');
                    })
                    ->values();
            } else {
                // Si no hay pivot, tomamos cursos de los periodos directamente
                /* $cursosMostrados = $grupoPeriodos
                    ->pluck('curso')
                    ->filter(fn ($c) => $c && $c->id)
                    ->unique('id')
                    ->values(); */
                $cursosMostrados = $grupoPeriodos
                    ->pluck('curso')
                    ->filter(fn ($c) => $c && $c->id && ! str_contains(strtolower($c->cc ?? ''), 'extracurricular'))
                    ->unique('id')
                    ->values();
            }

            // Si queda vacío, puedes decidir: mostrar "sin cursos" o saltar al siguiente
            if ($cursosMostrados->isEmpty()) {
                continue; // ⬅️ no mostramos nada del alumno si no hay calificaciones
            }

            $filas->push([
                'alumno' => $alumno,
                'cursos' => $cursosMostrados,
                'periodos' => $grupoPeriodos,
            ]);
        }

        return view('admin.periodos.calificaciones.show', [
            'periodoActual' => $periodoActual,
            'nombre' => $periodoActual->nombre,
            'periodos' => $periodos,
            'ciclos' => $ciclos,
            'filas' => $filas,
        ]);
    }

    public function exportExcel($id)
    {
        $periodoActual = PeriodoActual::findOrFail($id);
        [$ciclos, $filas] = $this->getDataForRegistros($id);
        $nombreArchivo = 'Calificaciones_'.$periodoActual->nombre.'.csv';
        return Excel::download(
            new RegistrosExport($ciclos, $filas),
            $nombreArchivo
        );
    }

    private function getDataForRegistros(int $id): array
    {
        $periodos = Periodo::with([
            'alumno.programa',
            'alumno.user',
            'alumno.cursos.ciclo',
            'curso.ciclo',
        ])
            ->where('periodo_actual_id', $id)
            ->get()
            ->groupBy('alumno_id');

        $ciclos = Periodo::where('periodo_actual_id', $id)
            ->with('curso.ciclo')
            ->get()
            ->pluck('curso.ciclo')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($c) => $c->ordenCiclo() ?? 999);

        $alumnoIds = $periodos->keys()->all();
        $alumnosMap = Alumno::with(['cursos.ciclo', 'programa', 'user'])
            ->whereIn('id', $alumnoIds)
            ->get()
            ->keyBy('id');

        $filas = collect();

        foreach ($periodos as $alumnoId => $grupoPeriodos) {
            $alumno = $alumnosMap->get($alumnoId) ?? $grupoPeriodos->first()->alumno;

            $pivotIds = \DB::table('alumno_cursos')
                ->where('alumno_id', $alumnoId)
                ->pluck('curso_id')
                ->toArray();

            if (! empty($pivotIds)) {
                $cursosMostrados = $alumno->relationLoaded('cursos')
                    ? $alumno->cursos->whereIn('id', $pivotIds)->values()
                    : Curso::with('ciclo')->whereIn('id', $pivotIds)->get();

                $cursosMostrados = $cursosMostrados->filter(function ($curso) use ($grupoPeriodos) {
                    return $grupoPeriodos->firstWhere('curso_id', $curso->id);
                })->values();
            } else {
                $cursosMostrados = $grupoPeriodos
                    ->pluck('curso')
                    ->filter(fn ($c) => $c && $c->id)
                    ->unique('id')
                    ->values();
            }

            if ($cursosMostrados->isEmpty()) {
                continue;
            }

            $filas->push([
                'alumno' => $alumno,
                'cursos' => $cursosMostrados,
                'periodos' => $grupoPeriodos,
            ]);
        }

        return [$ciclos, $filas];
    }
}
