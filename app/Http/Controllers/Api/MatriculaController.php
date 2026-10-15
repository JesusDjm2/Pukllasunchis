<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatriculaResource;
use Illuminate\Http\Request;

class MatriculaController extends Controller
{
    public function index(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $matriculas = $alumno->matriculas()
            ->with('periodoActual')
            ->latest()
            ->get();

        return response()->json(['data' => MatriculaResource::collection($matriculas)]);
    }
}
