<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoUno;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function gestionarCompetencias($cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $competencias = $curso->competencias;
        $competenciasSeleccionadas = $curso->competenciasSeleccionadas()->pluck('competencias.id')->toArray();
        return view('admin.curso.asignar', compact('curso', 'competencias', 'competenciasSeleccionadas'));
    }
    public function guardarCompetenciasSeleccionadas(Request $request, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);

        $validated = $request->validate([
            'competencias' => 'required|array|max:3',
            'competencias.*' => 'exists:competencias,id',
        ]);

        $curso->competenciasSeleccionadas()->sync($validated['competencias']);

        return redirect()->route('curso.gestionar.competencias', $cursoId)->with('success', 'Competencias a calificar guardadas correctamente.');
    }
    public function nuevaCalificacion(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'curso_id' => 'required|exists:cursos,id', //Campo para relacion con curso
            'valoracion_curso' => 'nullable|string',
            'calificacion_curso' => 'nullable|string',
            'calificacion_sistema' => 'nullable|string',
            'valoracion_1' => 'nullable|string',
            'valoracion_2' => 'nullable|string',
            'valoracion_3' => 'nullable|string',
            'docente_id' => 'required|exists:docentes,id',
            'competencias' => 'required|array|min:1|max:3',
        ]);

        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);

        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);

        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();

        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();

        $calificacion = Calificacion::updateOrCreate(
            [
                'alumno_id' => $request->input('alumno_id'),
                'curso_id' => $cursoId,
            ],
            [
                'valoracion_1' => $request->input('valoracion_1'),
                'valoracion_2' => $request->input('valoracion_2'),
                'valoracion_3' => $request->input('valoracion_3'),
                'valoracion_curso' => $request->input('valoracion_curso'),
                'calificacion_curso' => $request->input('calificacion_curso'),
                'calificacion_sistema' => $request->input('calificacion_sistema'),
            ]
        );
        if ($calificacion->wasRecentlyCreated) {
            session()->flash('success', 'Calificación guardada exitosamente.');
        }
        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    }
    //Periodos:
    public function publicarPeriodoUno(Request $request)
    {
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);
        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $alumnosPeriodo = $curso->ciclo->alumnos()->whereHas('calificaciones', function ($query) use ($curso) {
            $query->where('curso_id', $curso->id);
        })->get();

        foreach ($alumnosPeriodo as $alumno) {
            $calificacion = $alumno->calificaciones()->where('curso_id', $curso->id)->first();

            if ($calificacion) {
                PeriodoUno::updateOrCreate(
                    [
                        'calificacion_id' => $calificacion->id,
                        'alumno_id' => $alumno->id,
                        'curso_id' => $curso->id
                    ],
                    [
                        'nombre' => 'Periodo 1',
                        'comp1' => $competenciasSeleccionadas[0]->nombre ?? null,
                        'comp2' => $competenciasSeleccionadas[1]->nombre ?? null,
                        'comp3' => $competenciasSeleccionadas[2]->nombre ?? null,
                        'valoracion_1' => $calificacion->valoracion_1,
                        'valoracion_2' => $calificacion->valoracion_2,
                        'valoracion_3' => $calificacion->valoracion_3,
                        'valoracion_curso' => $calificacion->valoracion_curso,
                        'calificacion_curso' => $calificacion->calificacion_curso,
                        'calificacion_sistema' => $calificacion->calificacion_sistema,
                    ]
                );
            }
        }

        return view('docentes.calificaciones.alumnos', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
        ])->with('success', 'Periodo 1 publicado correctamente.');
    }

    public function eliminarPeriodoUno(Request $request)
    {
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);
        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $alumnosPeriodo = $curso->ciclo->alumnos()->whereHas('calificaciones', function ($query) use ($curso) {
            $query->where('curso_id', $curso->id);
        })->get();
        foreach ($alumnosPeriodo as $alumno) {
            $calificacion = $alumno->calificaciones()->where('curso_id', $curso->id)->first();
            if ($calificacion) {
                PeriodoUno::where('calificacion_id', $calificacion->id)->delete();
            }
        }

        return view('docentes.calificaciones.alumnos', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
        ])->with('success', 'Periodo 1 eliminado correctamente.');
    }
}
