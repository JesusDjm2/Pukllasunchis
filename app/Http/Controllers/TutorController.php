<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\User;

class TutorController extends Controller
{
    public function index()
    {
        $docente = auth()->user()->docente;
        $ciclos  = auth()->user()->tutorCiclos()->with('programa')->get();

        return view('tutor.index', compact('docente', 'ciclos'));
    }

    public function ciclo($cicloId)
    {
        $user  = auth()->user();
        $ciclo = Ciclo::with(['programa', 'alumnos.user', 'incidencias.docente', 'incidencias.alumno'])
            ->findOrFail($cicloId);

        abort_if(!$user->tutorCiclos->contains($cicloId), 403);

        $alumnos    = $ciclo->alumnos()->with('user')->orderBy('apellidos')->get();
        $incidencias = $ciclo->incidencias()
            ->with(['docente', 'alumno.ciclo.programa'])
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp(
                    optional(optional($a->alumno->ciclo)->programa)->nombre ?? '',
                    optional(optional($b->alumno->ciclo)->programa)->nombre ?? ''
                ),
                fn ($a, $b) => strcmp(
                    optional($a->alumno->ciclo)->nombre ?? '',
                    optional($b->alumno->ciclo)->nombre ?? ''
                ),
                fn ($a, $b) => strcmp($a->fecha, $b->fecha) * -1,
            ])
            ->values();

        return view('tutor.ciclo', compact('ciclo', 'alumnos', 'incidencias'));
    }
}
