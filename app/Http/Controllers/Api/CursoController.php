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

        // Misma fuente que AlumnoController@calificaciones (web): el modelo
        // `Periodo` (tabla `periodos`), no `Calificacion` (`calificacions`,
        // que está vacía en producción — nadie escribe ahí). `Periodo` sí
        // trae `periodo_actual_id`, por eso permite agrupar por período.
        $periodos = $alumno->periodo()->with(['curso', 'periodoActual'])->get();

        $agrupado = $periodos
            ->groupBy(fn ($p) => optional($p->periodoActual)->nombre ?? 'Sin periodo')
            ->sortKeys()
            ->map(fn ($grupo) => CalificacionResource::collection($grupo->values()));

        return response()->json(['data' => $agrupado]);
    }
}
