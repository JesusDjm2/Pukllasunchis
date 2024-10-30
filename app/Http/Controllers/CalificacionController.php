<?php

namespace App\Http\Controllers;

use App\Exports\CalificacionesExport;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoUno;
use Illuminate\Http\Request;
/* use Maatwebsite\Excel\Excel; */
use Maatwebsite\Excel\Facades\Excel;

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
            'curso_id' => 'required|exists:cursos,id',
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
    public function borrarCalificaciones(Request $request)
    {
        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);

        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);

        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $request->validate([
            'alumnos_ids' => 'required|array',
            'alumnos_ids.*' => 'exists:alumnos,id',
        ]);

        $alumnosIds = $request->input('alumnos_ids');
        Calificacion::whereIn('alumno_id', $alumnosIds)
            ->where('curso_id', $cursoId)
            ->delete();

        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();

        return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'))
            ->with('success', 'Las calificaciones han sido eliminadas para los alumnos seleccionados.');
    }
    public function guardarCalificacionesEnBloque(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'docente_id' => 'required|exists:docentes,id',
            'alumnos' => 'required|array',
            'alumnos.*.valoracion_curso' => 'nullable|string',
            'alumnos.*.calificacion_curso' => 'nullable|string',
            'alumnos.*.calificacion_sistema' => 'nullable|string',
            'alumnos.*.valoracion_1' => 'nullable|string',
            'alumnos.*.valoracion_2' => 'nullable|string',
            'alumnos.*.valoracion_3' => 'nullable|string',
            'alumnos.*.competencias' => 'required|array|min:1|max:3',
        ]);

        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);

        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);

        // Procesar cada alumno y guardar su calificación
        foreach ($request->input('alumnos') as $data) {
            Calificacion::updateOrCreate(
                [
                    'alumno_id' => $data['alumno_id'], // Ahora accederás correctamente al ID del alumno.
                    'curso_id' => $cursoId,
                ],
                [
                    /* 'valoracion_1' => $data['valoracion_1'],
                    'valoracion_2' => $data['valoracion_2'],
                    'valoracion_3' => $data['valoracion_3'],
                    'valoracion_curso' => $data['valoracion_curso'],
                    'calificacion_curso' => $data['calificacion_curso'],
                    'calificacion_sistema' => $data['calificacion_sistema'], */
                    'valoracion_1' => $data['valoracion_1'] ?? null,
                    'valoracion_2' => $data['valoracion_2'] ?? null,
                    'valoracion_3' => $data['valoracion_3'] ?? null,
                    'valoracion_curso' => $data['valoracion_curso'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                ]
            );
        }

        session()->flash('success', 'Calificaciones guardadas exitosamente.');
        $competenciasIds = [];
        foreach ($request->input('alumnos') as $data) {
            if (isset($data['competencias'])) {
                $competenciasIds = array_merge($competenciasIds, $data['competencias']);
            }
        }

        $competenciasIds = array_unique($competenciasIds);

        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    }
    /* public function guardarCalificaciones(Request $request)
    {
        $request->validate([
            'alumnos' => 'required|array',
            'alumnos.*.alumno_id' => 'required|exists:alumnos,id',
            'alumnos.*.curso_id' => 'required|exists:cursos,id',
            'alumnos.*.docente_id' => 'required|exists:docentes,id',
            'alumnos.*.competencias' => 'required|array|min:1|max:3',
            'alumnos.*.valoraciones' => 'required|array',
            'alumnos.*.valoracion_curso' => 'nullable|string',
            'alumnos.*.calificacion_curso' => 'nullable|string',
            'alumnos.*.calificacion_sistema' => 'nullable|string',
        ]);

        // Iteramos sobre cada alumno para guardar sus calificaciones
        foreach ($request->input('alumnos') as $alumno) {
            $cursoId = $alumno['curso_id'];
            $docenteId = $alumno['docente_id'];
            // Actualiza o crea una nueva calificación para cada competencia
            foreach ($alumno['competencias'] as $index => $competenciaId) {
                $valoracion = $alumno['valoraciones'][$index];
                // Guardar o actualizar la calificación de cada competencia
                Calificacion::updateOrCreate(
                    [
                        'alumno_id' => $alumno['alumno_id'],
                        'curso_id' => $cursoId,
                        'competencia_id' => $competenciaId,
                    ],
                    [
                        'valoracion_' . ($index + 1) => $valoracion,
                        'valoracion_curso' => $alumno['valoracion_curso'],
                        'calificacion_curso' => $alumno['calificacion_curso'],
                        'calificacion_sistema' => $alumno['calificacion_sistema'],
                    ]
                );
            }
        }

        // Redireccionamos con un mensaje de éxito
        return redirect()->back()->with('success', 'Todas las calificaciones han sido guardadas correctamente.');
    } */
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

        return view('admin.curso.calificaciones', [
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

        return view('admin.curso.calificaciones', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
        ])->with('success', 'Periodo 1 eliminado correctamente.');
    }


    /* public function exportarCSV($docenteId, $cursoId, Request $request)
    {
        $competenciasSeleccionadas = $request->input('competencias');
        if (is_null($competenciasSeleccionadas) || !is_array($competenciasSeleccionadas)) {
            return redirect()->back()->withErrors(['message' => 'Por favor, selecciona al menos una competencia.']);
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasSeleccionadas)->get();
        return Excel::download(new CalificacionesExport($docenteId, $cursoId, $competenciasSeleccionadas), 'calificaciones.xlsx');
    } */
    public function exportarCSV($docenteId, $cursoId, Request $request)
    {
        // Continuar con la lógica después de la verificación
        $competenciasSeleccionadas = $request->input('competencias');
        if (is_null($competenciasSeleccionadas) || !is_array($competenciasSeleccionadas)) {
            return redirect()->back()->withErrors(['message' => 'Por favor, selecciona al menos una competencia.']);
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasSeleccionadas)->get();
        return Excel::download(new CalificacionesExport($docenteId, $cursoId, $competenciasSeleccionadas), 'calificaciones.xlsx');
    }
}
