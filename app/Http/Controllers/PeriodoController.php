<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Periodo;
use App\Models\PeriodoTres;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::select('nombre')
            ->distinct()
            ->get();
        return view('admin.periodos.index', compact('periodos'));
    }
    public function create()
    {
        return view('admin.periodos.create');
    }

    public function store(Request $request)
    {
        $existenDatos = PeriodoTres::exists();

        if (!$existenDatos) {
            return back()->with('error', 'No se encontraron registros en PeriodoTres para generar los periodos.');
        }

        $nombrePeriodo = $request->input('nombre');

        if (!$nombrePeriodo) {
            return back()->with('error', 'Debes proporcionar un nombre para el período.');
        }

        $alumnos = Alumno::whereHas('user.roles', function ($query) {
            $query->where('name', '!=', 'inhabilitado');
        })->get();

        foreach ($alumnos as $alumno) {
            foreach ($alumno->ciclo->cursos as $curso) {
                // Obtenemos las competencias asociadas al curso
                $competencias = $curso->competencias;

                if ($competencias->count() < 3) {
                    $comp1 = $competencias->get(0)->nombre ?? null;
                    $comp2 = $competencias->get(1)->nombre ?? null;
                    $comp3 = $competencias->get(2)->nombre ?? null;
                } else {
                    $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                    $comp1 = $competenciasSeleccionadas->get(0)->nombre ?? null;
                    $comp2 = $competenciasSeleccionadas->get(1)->nombre ?? null;
                    $comp3 = $competenciasSeleccionadas->get(2)->nombre ?? null;
                }

                $calificacion = PeriodoTres::where('alumno_id', $alumno->id)
                    ->where('curso_id', $curso->id)
                    ->first();

                if (!$calificacion) {
                    continue;
                }

                Periodo::updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'curso_id' => $curso->id,
                    ],
                    [
                        'nombre' => $nombrePeriodo,
                        'valoracion_curso' => $calificacion->valoracion_curso,
                        'calificacion_curso' => $calificacion->calificacion_curso,
                        'calificacion_sistema' => $calificacion->calificacion_sistema,
                    ]
                );
            }
        }

        return view('admin.periodos.index')->with('success', 'Períodos generados correctamente.');
    }
    public function show($nombre)
    {
        /* $periodos = Periodo::where('nombre', $nombre)->get(); */
        $periodos = Periodo::where('nombre', $nombre)
        ->whereHas('alumno') // Asegura que exista la relación
        ->with('alumno') // Carga la relación para evitar múltiples queries
        ->get()
        ->sortBy(fn($periodo) => $periodo->alumno->apellidos ?? '');
        return view('admin.periodos.show', compact('periodos', 'nombre'));
    }
}
