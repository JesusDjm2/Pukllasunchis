<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Periodo;
use App\Models\PeriodoTres;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::select('nombre')
            ->distinct()
            ->get();
        return view('admin.periodos.calificaciones.index', compact('periodos'));
    }
    public function create()
    {
        return view('admin.periodos.calificaciones.create');
    }
    public function store(Request $request)
    {
        $nombrePeriodo = $request->input('nombre');
        if (!$nombrePeriodo) {
            return back()->with('error', 'Debes proporcionar un nombre para el período.');
        }
        // Verificar que no existan registros duplicados con ese nombre
        $yaHayPeriodoConEseNombre = Periodo::where('nombre', $nombrePeriodo)->exists();
        if ($yaHayPeriodoConEseNombre) {
            return back()->with('error', "Ya existe un período con el nombre '$nombrePeriodo'. Elige otro nombre.");
        }
        // ⚠️ Verificamos que exista al menos un registro en PeriodoTres
        $datosPeriodoTres = PeriodoTres::whereHas('alumno.user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'inhabilitado');
            })
                ->whereHas('ciclo', function ($q) {
                    $q->whereRaw("LOWER(TRIM(nombre)) != ?", ['ciclo i']);
                });
        })->get();

        if ($datosPeriodoTres->isEmpty()) {
            return back()->with('error', 'No se encontraron registros válidos en el Periodo de Desempeño, todo esta vacio aún. Quiza ni has llegado al Periodo de desempeño. :/');
        }
        foreach ($datosPeriodoTres as $registro) {
            $alumnoId = $registro->alumno_id;
            $cursoId = $registro->curso_id;

            // Verificar si ya existe un Periodo con ese alumno, curso y nombre
            $existe = Periodo::where('nombre', $nombrePeriodo)
                ->where('alumno_id', $alumnoId)
                ->where('curso_id', $cursoId)
                ->exists();
            if ($existe) {
                continue;
            }
            // Crear el nuevo registro en la tabla Periodo
            Periodo::create([
                'nombre' => $nombrePeriodo,
                'valoracion_curso' => $registro->valoracion_curso,
                'calificacion_curso' => $registro->calificacion_curso,
                'calificacion_sistema' => $registro->calificacion_sistema,
                'alumno_id' => $alumnoId,
                'curso_id' => $cursoId,
            ]);
        }
        $periodos = Periodo::select('nombre')->distinct()->get();
        return view('admin.periodos.calificaciones.index', compact('periodos'))->with('success', 'Períodos generados correctamente.');
    }
    public function show($nombre)
    {
        $periodos = Periodo::where('nombre', $nombre)
            ->whereHas('alumno.user.programa.ciclos', function ($q) {
                $q->whereRaw("LOWER(TRIM(nombre)) != ?", ['ciclo i']);
            })
            ->with([
                'alumno.user.programa.ciclos',
                'curso'
            ])
            ->get()
            ->sortBy([
                fn($a, $b) => ($a->alumno->user->programa->ciclo->nombre ?? '') <=> ($b->alumno->user->programa->ciclo->nombre ?? ''),
                fn($a, $b) => ($a->alumno->apellidos ?? '') <=> ($b->alumno->apellidos ?? '')
            ])
            ->groupBy('alumno_id');
        return view('admin.periodos.calificaciones.show', compact('periodos', 'nombre'));
    }
}
