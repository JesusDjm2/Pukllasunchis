<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalificacionResource;
use App\Http\Resources\CursoResource;
use App\Models\PeriodoActual;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $periodoActual = PeriodoActual::actual();

        // Misma lógica que AlumnoController@index (web): fuente primaria son
        // las asignaciones explícitas en alumno_cursos para el período
        // actual; si no hay ninguna, se muestran todos los cursos del ciclo
        // propio del alumno.
        $cursosAsignados = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->with('ciclo')->get()
            : collect();

        $cursos = $cursosAsignados->isNotEmpty()
            ? $cursosAsignados
            : ($alumno->ciclo ? $alumno->ciclo->cursos : collect());

        return response()->json(['data' => CursoResource::collection($cursos->sortBy('nombre')->values())]);
    }

    public function calificaciones(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $calificaciones = $alumno->calificaciones()->with('curso')->get();

        return response()->json(['data' => CalificacionResource::collection($calificaciones)]);
    }
}
