<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAlumnoPerfilRequest;
use App\Http\Resources\AlumnoResource;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function perfil(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        return response()->json(['data' => new AlumnoResource($alumno)]);
    }

    public function actualizarPerfil(UpdateAlumnoPerfilRequest $request)
    {
        $alumno = $request->user()->alumno;

        $alumno->update($request->validated());

        return response()->json(['data' => new AlumnoResource($alumno->fresh())]);
    }
}
