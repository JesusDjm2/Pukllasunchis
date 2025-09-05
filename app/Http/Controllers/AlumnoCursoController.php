<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Http\Request;

class AlumnoCursoController extends Controller
{
    public function asignar($id)
    {
        $user = User::with(['alumno.cursos.ciclo'])->findOrFail($id);
        $alumno = $user->alumno;

        $cursosCiclo = Curso::where('ciclo_id', $alumno->ciclo_id)
            ->whereHas('ciclo', function ($q) use ($alumno) {
                $q->where('programa_id', $alumno->programa_id);
            })
            ->get();

        // Cursos de otros ciclos del mismo programa
        $cursosOtros = Curso::where('ciclo_id', '!=', $alumno->ciclo_id)
            ->whereHas('ciclo', function ($q) use ($alumno) {
                $q->where('programa_id', $alumno->programa_id);
            })
            ->get();

        // Cursos ya asignados del alumno que NO son del ciclo actual
        $otrosCursosAsignados = $alumno->cursos
            ->filter(fn($curso) => $curso->ciclo_id != $alumno->ciclo_id)
            ->values();

        return view('admin.curso.asignarcursos.asignacion', compact(
            'alumno',
            'cursosCiclo',
            'cursosOtros',
            'otrosCursosAsignados'
        ));
    }

    public function guardarCursos(Request $request, $alumnoId)
    {
        $alumno = Alumno::with('user')->findOrFail($alumnoId);

        $cursoIds = $request->input('cursos', []);

        $validos = Curso::whereIn('id', $cursoIds)
            ->whereHas('ciclo', fn($q) => $q->where('programa_id', $alumno->programa_id))
            ->pluck('id')
            ->toArray();

        $alumno->cursos()->sync($validos);

        return redirect()
            ->route('admin', $alumnoId)
            ->with('success', 'Cursos actualizados correctamente para ' . $alumno->user->name);
    }

}
