<?php

namespace App\Http\Controllers;

use App\Exports\RegistrosExport;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\PeriodoTres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PeriodoActualController extends Controller
{
    public function index()
    {
        $periodoactuales = PeriodoActual::orderBy('nombre', 'desc')->get();
        $periodosppd = PeriodoActualPpd::orderBy('id', 'desc')->get();

        return view('admin.periodos.index', compact('periodoactuales', 'periodosppd'));
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

        if ($request->has('actual') && $request->actual) {
            PeriodoActual::where('actual', true)->update(['actual' => false]);
        }

        $rutaImagen = null;

        if ($request->hasFile('horario')) {
            $carpeta = public_path('img/horarios');
            if (! file_exists($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            $archivo = $request->file('horario');
            $nombreOriginal = $archivo->getClientOriginalName();
            $archivo->move($carpeta, $nombreOriginal);
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

            $carpeta = public_path('img/horarios');
            if (! file_exists($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            $archivo = $request->file('horario');
            $nombreOriginal = $archivo->getClientOriginalName();
            $archivo->move($carpeta, $nombreOriginal);
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

        $creados = 0;
        $actualizados = 0;
        $sinCambios = 0;

        foreach ($datosPeriodoTres as $registro) {
            $alumnoId = $registro->alumno_id;
            $cursoId = $registro->curso_id;

            // BUSCAR si ya existe un registro para este período, alumno y curso
            $periodoExistente = Periodo::where('periodo_actual_id', $periodoactual->id)
                ->where('alumno_id', $alumnoId)
                ->where('curso_id', $cursoId)
                ->first();

            $datos = [
                'valoracion_curso' => $registro->valoracion_curso,
                'calificacion_curso' => $registro->calificacion_curso,
                'calificacion_sistema' => $registro->calificacion_sistema,
                'alumno_id' => $alumnoId,
                'curso_id' => $cursoId,
                'periodo_actual_id' => $periodoactual->id,
            ];

            if ($periodoExistente) {
                // ✅ ACTUALIZAR si ya existe
                $cambios = [];
                if ($periodoExistente->valoracion_curso != $registro->valoracion_curso) {
                    $cambios['valoracion_curso'] = $registro->valoracion_curso;
                }
                if ($periodoExistente->calificacion_curso != $registro->calificacion_curso) {
                    $cambios['calificacion_curso'] = $registro->calificacion_curso;
                }
                if ($periodoExistente->calificacion_sistema != $registro->calificacion_sistema) {
                    $cambios['calificacion_sistema'] = $registro->calificacion_sistema;
                }

                if (! empty($cambios)) {
                    $periodoExistente->update($cambios);
                    $actualizados++;
                } else {
                    $sinCambios++;
                }
            } else {
                // ✅ CREAR si no existe
                Periodo::create($datos);
                $creados++;
            }
        }

        // ELIMINAR registros huérfanos (los que están en PeriodoActual pero ya no en PeriodoTres)
        $periodosExistentes = Periodo::where('periodo_actual_id', $periodoactual->id)->get();
        $eliminados = 0;

        foreach ($periodosExistentes as $periodoExistente) {
            $existeEnPeriodoTres = $datosPeriodoTres->first(function ($registro) use ($periodoExistente) {
                return $registro->alumno_id == $periodoExistente->alumno_id
                    && $registro->curso_id == $periodoExistente->curso_id;
            });

            if (! $existeEnPeriodoTres) {
                $periodoExistente->delete();
                $eliminados++;
            }
        }

        $mensaje = "Calificaciones procesadas para el período: {$periodoactual->nombre}. ";
        $parts = [];
        if ($creados > 0) {
            $parts[] = "{$creados} creados";
        }
        if ($actualizados > 0) {
            $parts[] = "{$actualizados} actualizados";
        }
        if ($sinCambios > 0) {
            $parts[] = "{$sinCambios} sin cambios";
        }
        if ($eliminados > 0) {
            $parts[] = "{$eliminados} eliminados";
        }
        $mensaje .= implode(', ', $parts);

        return redirect()->route('periodoactual.index')
            ->with('success', $mensaje);
    }

    public function showRegistros($id)
    {
        $periodoActual = PeriodoActual::findOrFail($id);

        // Obtener periodos con relaciones necesarias
        $periodos = Periodo::with([
            'alumno.programa',
            'alumno.user',
            'curso.ciclo',
        ])
            ->where('periodo_actual_id', $id)
            ->get();

        // Agrupar por alumno
        $periodosAgrupados = $periodos->groupBy('alumno_id');

        // Filtrar solo los cursos de este periodo
        $filas = collect();

        foreach ($periodosAgrupados as $alumnoId => $periodosAlumno) {
            $alumno = $periodosAlumno->first()->alumno;

            // Solo cursos que tienen registros en este periodo
            $cursosMostrados = $periodosAlumno
                ->pluck('curso')
                ->filter(fn ($curso) => $curso && ! str_contains(strtolower($curso->cc ?? ''), 'extracurricular'))
                ->unique('id')
                ->values();

            if ($cursosMostrados->isNotEmpty()) {
                $filas->push([
                    'alumno' => $alumno,
                    'cursos' => $cursosMostrados,
                    'periodos' => $periodosAlumno->keyBy('curso_id'),
                ]);
            }
        }

        // Ciclos únicos de los cursos mostrados
        $ciclos = $filas->flatMap(fn ($fila) => $fila['cursos'])
            ->pluck('ciclo')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($c) => $c->ordenCiclo() ?? 999);

        return view('admin.periodos.calificaciones.show', [
            'periodoActual' => $periodoActual,
            'nombre' => $periodoActual->nombre,
            'filas' => $filas,
            'ciclos' => $ciclos,
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

            $pivotIds = DB::table('alumno_cursos')
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

    public function periodos()
    {
        return view('alumnos.postulantes.periodos');
    }
}
