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

        $cursos = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->orderBy('nombre')->get()
            : collect();

        return response()->json(['data' => CursoResource::collection($cursos)]);
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
