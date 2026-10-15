<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAlumnoPerfilRequest;
use App\Http\Resources\AlumnoResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AlumnoController extends Controller
{
    public function perfil(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $alumno->load('programa', 'ciclo');

        return response()->json(['data' => new AlumnoResource($alumno)]);
    }

    public function actualizarPerfil(UpdateAlumnoPerfilRequest $request)
    {
        $alumno = $request->user()->alumno;

        $alumno->update($request->validated());

        return response()->json(['data' => new AlumnoResource($alumno->fresh())]);
    }

    /**
     * Misma plantilla que `vistasAlumnosController::exportarFichaPDF` (web),
     * pero escaneada al propio alumno autenticado — la ruta web acepta
     * cualquier `{alumno}` porque está detrás de permisos de admin/self ahí;
     * aquí no aceptamos un id arbitrario para evitar exponer fichas ajenas.
     */
    public function fichaPdf(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $alumno->load(['ciclo.cursos', 'cursos', 'programa']);
        $pdf = Pdf::loadView('alumnos.vistasAlumnos.pdf', compact('alumno'));

        return $pdf->download('ficha_'.Str::slug($alumno->apellidos.'_'.$alumno->nombres).'.pdf');
    }
}
