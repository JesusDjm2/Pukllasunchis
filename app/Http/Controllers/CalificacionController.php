<?php

namespace App\Http\Controllers;

use App\Exports\CalificacionesExport;
use App\Exports\CalificacionesPPDExport;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\PeriodoDos;
use App\Models\PeriodoTres;
use App\Models\PeriodoUno;
use App\Models\ppd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {
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
            /* $mostrarBotonDesempeno = false;
            $porcentaje = $curso->porcentajePeriodo(2, ['calificacion_curso']);
            $mostrarBotonDesempeno = $porcentaje >= 50; */

            if ($calificacion->wasRecentlyCreated) {
                session()->flash('success', 'Calificación guardada exitosamente.');
            }

            return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        } catch (\Throwable $e) {
            Log::error('Error al guardar calificación', [
                'curso_id' => $request->input('curso_id'),
                'docente_id' => $request->input('docente_id'),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar la calificación por un problema técnico. Tus datos no se perdieron: corrige e inténtalo de nuevo, o contacta a soporte si el problema continúa.');
        }
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

    public function borrarTodasLasCalificaciones()
    {
        Calificacion::query()->delete();

        return redirect()->back()->with('success', 'Todas las calificaciones han sido eliminadas exitosamente.');
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
            'alumnos.*.observaciones' => 'nullable|string|max:1000',
        ]);

        try {
        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);
        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);

        foreach ($request->input('alumnos') as $data) {
            PeriodoDos::updateOrCreate(
                [
                    'alumno_id' => $data['alumno_id'],
                    'curso_id' => $cursoId,
                ],
                [
                    'valoracion_1' => $data['valoracion_1'] ?? null,
                    'valoracion_2' => $data['valoracion_2'] ?? null,
                    'valoracion_3' => $data['valoracion_3'] ?? null,
                    'valoracion_curso' => $data['valoracion_curso'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ]
            );
        }

        session()->flash('success', 'Calificaciones Parcial 2 guardadas exitosamente');
        $competenciasIds = [];
        foreach ($request->input('alumnos') as $data) {
            if (isset($data['competencias'])) {
                $competenciasIds = array_merge($competenciasIds, $data['competencias']);
            }
        }

        $competenciasIds = array_unique($competenciasIds);
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        $alumnosRelacionados = $curso->alumnos()
            ->whereHas('user', function ($q) {
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                    ->orWhere(function ($q2) {
                        $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                            ->whereNotIn('perfil', ['Sin matrícula', 'Retirado']);
                    });
            })
            ->orderBy('apellidos')
            ->get();

        $alumnosCiclo = $curso->ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->where(function ($q) {
                    $q->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'inhabilitado');
                    })
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'inhabilitado');
                            })->whereNotIn('perfil', ['Sin matrícula', 'Retirado']);
                        });
                });
            })
            ->orderBy('apellidos')
            ->get();
        $alumnos = $alumnosRelacionados
            ->merge($alumnosCiclo)
            ->unique('id')
            ->values();
        $alumnos = $alumnos->filter(function ($alumno) use ($cursoId) {
            $cursoRelacionIds = $alumno->cursos()->pluck('curso_id');
            if ($cursoRelacionIds->isNotEmpty() && ! $cursoRelacionIds->contains($cursoId)) {
                return false;
            }

            return true;
        })->values();

        $mostrarBotonDesempeno = false;
        $porcentaje = $curso->porcentajePeriodo(2, ['calificacion_curso']);
        $mostrarBotonDesempeno = $porcentaje >= 50;

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno'));
        } catch (\Throwable $e) {
            Log::error('Error al guardar calificaciones de Parcial 2', [
                'curso_id' => $request->input('curso_id'),
                'docente_id' => $request->input('docente_id'),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudieron guardar las notas de Parcial 2 por un problema técnico. Tus datos no se perdieron: corrige e inténtalo de nuevo, o contacta a soporte si el problema continúa.');
        }
    }

    public function guardarPeriodoTres(Request $request)
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

        try {
        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);
        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);
        foreach ($request->input('alumnos') as $data) {
            PeriodoTres::updateOrCreate(
                [
                    'alumno_id' => $data['alumno_id'],
                    'curso_id' => $cursoId,
                ],
                [
                    'valoracion_1' => $data['valoracion_1'] ?? null,
                    'valoracion_2' => $data['valoracion_2'] ?? null,
                    'valoracion_3' => $data['valoracion_3'] ?? null,
                    'valoracion_curso' => $data['valoracion_curso'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                ]
            );
        }

        session()->flash('success', 'Calificaciones periodo de desempeño guardadas exitosamente');
        $competenciasIds = [];
        foreach ($request->input('alumnos') as $data) {
            if (isset($data['competencias'])) {
                $competenciasIds = array_merge($competenciasIds, $data['competencias']);
            }
        }

        $competenciasIds = array_unique($competenciasIds);
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();
        $alumnosRelacionados = $curso->alumnos()
            ->whereHas('user', function ($q) {
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                    ->orWhere(function ($q2) {
                        $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                            ->whereNotIn('perfil', ['Sin matrícula', 'Retirado']);
                    });
            })
            ->orderBy('apellidos')
            ->get();

        $alumnosCiclo = $curso->ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->where(function ($q) {
                    $q->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'inhabilitado');
                    })
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'inhabilitado');
                            })->whereNotIn('perfil', ['Sin matrícula', 'Retirado']);
                        });
                });
            })
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnosRelacionados
            ->merge($alumnosCiclo)
            ->unique('id')
            ->values();

        $alumnos = $alumnos->filter(function ($alumno) use ($cursoId) {
            $cursoRelacionIds = $alumno->cursos()->pluck('curso_id');
            if ($cursoRelacionIds->isNotEmpty() && ! $cursoRelacionIds->contains($cursoId)) {
                return false;
            }

            return true;
        })->values();
        $mostrarBotonDesempeno = false;

        foreach ($alumnos as $alumno) {
            $periodoDos = $alumno->periododos()->where('curso_id', $cursoId)->first();

            if ($periodoDos) {
                foreach ($competenciasSeleccionadas as $index => $competencia) {
                    $campoValoracion = 'valoracion_'.($index + 1);
                    if ($periodoDos->$campoValoracion > 0) {
                        $mostrarBotonDesempeno = true;
                        break 2;
                    }
                }
            }
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno'));
        } catch (\Throwable $e) {
            Log::error('Error al guardar calificaciones de Desempeño', [
                'curso_id' => $request->input('curso_id'),
                'docente_id' => $request->input('docente_id'),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudieron guardar las notas de Desempeño por un problema técnico. Tus datos no se perdieron: corrige e inténtalo de nuevo, o contacta a soporte si el problema continúa.');
        }
    }

    public function guardarPeriodo2yDesempenoEnBloque(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'docente_id' => 'required|exists:docentes,id',
            'alumnos' => 'required|array',
            'alumnos.*.competencias' => 'required|array|min:1|max:3',
            'alumnos.*.periodo2.valoracion_curso' => 'nullable|string',
            'alumnos.*.periodo2.calificacion_curso' => 'nullable|string',
            'alumnos.*.periodo2.calificacion_sistema' => 'nullable|string',
            'alumnos.*.periodo2.valoracion_1' => 'nullable|string',
            'alumnos.*.periodo2.valoracion_2' => 'nullable|string',
            'alumnos.*.periodo2.valoracion_3' => 'nullable|string',
            'alumnos.*.periodo2.observaciones' => 'nullable|string|max:1000',
            'alumnos.*.periodo3.valoracion_curso' => 'nullable|string',
            'alumnos.*.periodo3.calificacion_curso' => 'nullable|string',
            'alumnos.*.periodo3.calificacion_sistema' => 'nullable|string',
            'alumnos.*.periodo3.valoracion_1' => 'nullable|string',
            'alumnos.*.periodo3.valoracion_2' => 'nullable|string',
            'alumnos.*.periodo3.valoracion_3' => 'nullable|string',
        ]);

        try {
            $docenteId = $request->input('docente_id');
            $docente = Docente::findOrFail($docenteId);
            $cursoId = $request->input('curso_id');
            $curso = Curso::findOrFail($cursoId);

            foreach ($request->input('alumnos') as $data) {
                if (! empty($data['periodo2'])) {
                    PeriodoDos::updateOrCreate(
                        [
                            'alumno_id' => $data['alumno_id'],
                            'curso_id' => $cursoId,
                        ],
                        [
                            'valoracion_1' => $data['periodo2']['valoracion_1'] ?? null,
                            'valoracion_2' => $data['periodo2']['valoracion_2'] ?? null,
                            'valoracion_3' => $data['periodo2']['valoracion_3'] ?? null,
                            'valoracion_curso' => $data['periodo2']['valoracion_curso'] ?? null,
                            'calificacion_curso' => $data['periodo2']['calificacion_curso'] ?? null,
                            'calificacion_sistema' => $data['periodo2']['calificacion_sistema'] ?? null,
                            'observaciones' => $data['periodo2']['observaciones'] ?? null,
                        ]
                    );
                }

                if (! empty($data['periodo3'])) {
                    PeriodoTres::updateOrCreate(
                        [
                            'alumno_id' => $data['alumno_id'],
                            'curso_id' => $cursoId,
                        ],
                        [
                            'valoracion_1' => $data['periodo3']['valoracion_1'] ?? null,
                            'valoracion_2' => $data['periodo3']['valoracion_2'] ?? null,
                            'valoracion_3' => $data['periodo3']['valoracion_3'] ?? null,
                            'valoracion_curso' => $data['periodo3']['valoracion_curso'] ?? null,
                            'calificacion_curso' => $data['periodo3']['calificacion_curso'] ?? null,
                            'calificacion_sistema' => $data['periodo3']['calificacion_sistema'] ?? null,
                        ]
                    );
                }
            }

            $competenciasIds = [];
            foreach ($request->input('alumnos') as $data) {
                if (isset($data['competencias'])) {
                    $competenciasIds = array_merge($competenciasIds, $data['competencias']);
                }
            }
            $competenciasIds = array_unique($competenciasIds);

            return redirect()
                ->route('competencias.calificar', ['docente' => $docenteId, 'curso' => $cursoId, 'competencias' => $competenciasIds])
                ->with('success', 'Calificaciones de Parcial 2 y Desempeño guardadas exitosamente');
        } catch (\Throwable $e) {
            Log::error('Error al guardar calificaciones de Parcial 2 y Desempeño', [
                'curso_id' => $request->input('curso_id'),
                'docente_id' => $request->input('docente_id'),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudieron guardar las notas de Parcial 2 y Desempeño por un problema técnico. Tus datos no se perdieron: corrige e inténtalo de nuevo, o contacta a soporte si el problema continúa.');
        }
    }

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
                        'curso_id' => $curso->id,
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
        $mostrarBotonDesempeno = false;
        $porcentaje = $curso->porcentajePeriodo(2, ['calificacion_curso']);
        $mostrarBotonDesempeno = $porcentaje >= 50;

        return view('admin.curso.calificaciones', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
            'mostrarBotonDesempeno' => $mostrarBotonDesempeno,
        ])->with('success', 'Periodo 1 publicado correctamente.');
    }

    public function storePeriodoEnBloque(Request $request)
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
            'alumnos.*.observaciones' => 'nullable|string|max:1000',
        ]);

        try {
        $docenteId = $request->input('docente_id');
        $docente = Docente::findOrFail($docenteId);
        $cursoId = $request->input('curso_id');
        $curso = Curso::findOrFail($cursoId);

        foreach ($request->input('alumnos') as $data) {
            PeriodoUno::updateOrCreate(
                [
                    'alumno_id' => $data['alumno_id'],
                    'curso_id' => $cursoId,
                ],
                [
                    'valoracion_1' => $data['valoracion_1'] ?? null,
                    'valoracion_2' => $data['valoracion_2'] ?? null,
                    'valoracion_3' => $data['valoracion_3'] ?? null,
                    'valoracion_curso' => $data['valoracion_curso'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ]
            );
        }
        $competenciasIds = [];
        foreach ($request->input('alumnos') as $data) {
            if (isset($data['competencias'])) {
                $competenciasIds = array_merge($competenciasIds, $data['competencias']);
            }
        }
        $competenciasIds = array_unique($competenciasIds);

        return redirect()
            ->route('competencias.calificar', ['docente' => $docenteId, 'curso' => $cursoId, 'competencias' => $competenciasIds])
            ->with('success', 'Calificaciones de Parcial 1 guardado correctamente!');
        } catch (\Throwable $e) {
            Log::error('Error al guardar calificaciones de Parcial 1', [
                'curso_id' => $request->input('curso_id'),
                'docente_id' => $request->input('docente_id'),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudieron guardar las notas de Parcial 1 por un problema técnico. Tus datos no se perdieron: corrige e inténtalo de nuevo, o contacta a soporte si el problema continúa.');
        }
    }

    public function eliminarPeriodoUno()
    {
        \DB::table('periodouno')->truncate();

        return back()->with('success', 'Todos los datos de Periodo 1 han sido eliminados correctamente.');
    }

    public function storePeriodoDos(Request $request)
    {
        $alumnos = Alumno::whereHas('user.roles', function ($query) {
            $query->where('name', '!=', 'inhabilitado');
        })->get();

        foreach ($alumnos as $alumno) {
            foreach ($alumno->ciclo->cursos as $curso) {
                $competencias = $curso->competencias;
                if ($competencias->count() < 3) {
                    $comp1 = $competencias->get(0)->nombre ?? null;
                    $comp2 = $competencias->get(1)->nombre ?? null;
                    $comp3 = $competencias->get(2)->nombre ?? null;
                } else {
                    $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                    $comp1 = $competenciasSeleccionadas->get(0)->nombre ?? null;
                    $comp2 = $competenciasSeleccionadas->get(1)->nombre ?? null;
                    $comp3 = $competenciasSeleccionadas->get(2)->nombre ?? null;
                }

                $calificacion = $alumno->calificaciones()->where('curso_id', $curso->id)->first();

                if ($calificacion) {
                    PeriodoDos::updateOrCreate(
                        [
                            'alumno_id' => $alumno->id,
                            'curso_id' => $curso->id,
                        ],
                        [
                            'comp1' => $comp1,
                            'comp2' => $comp2,
                            'comp3' => $comp3,
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
        }

        return back()->with('success', 'Calificaciones de Periodo 2 guardados correctamente.');
    }

    public function eliminarPeriodoDos()
    {
        \DB::table('periodo_dos')->truncate();

        return back()->with('success', 'Todos los datos de Periodo 2 han sido eliminados correctamente.');
    }

    public function storePeriodoTres(Request $request)
    {
        $alumnos = Alumno::whereHas('user.roles', function ($query) {
            $query->where('name', '!=', 'inhabilitado');
        })->get();

        foreach ($alumnos as $alumno) {
            foreach ($alumno->ciclo->cursos as $curso) {
                $competencias = $curso->competencias;
                if ($competencias->count() < 3) {
                    $comp1 = $competencias->get(0)->nombre ?? null;
                    $comp2 = $competencias->get(1)->nombre ?? null;
                    $comp3 = $competencias->get(2)->nombre ?? null;
                } else {
                    $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                    $comp1 = $competenciasSeleccionadas->get(0)->nombre ?? null;
                    $comp2 = $competenciasSeleccionadas->get(1)->nombre ?? null;
                    $comp3 = $competenciasSeleccionadas->get(2)->nombre ?? null;
                }

                $calificacion = $alumno->calificaciones()->where('curso_id', $curso->id)->first();

                if ($calificacion) {
                    PeriodoTres::updateOrCreate(
                        [
                            'alumno_id' => $alumno->id,
                            'curso_id' => $curso->id,
                        ],
                        [
                            'comp1' => $comp1,
                            'comp2' => $comp2,
                            'comp3' => $comp3,
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
        }

        return back()->with('success', 'Calificaciones de Periodo de Desempeño guardados correctamente.');
    }

    public function eliminarPeriodoTres()
    {
        \DB::table('periodo_tres')->truncate();

        return back()->with('success', 'Todos los datos de Periodo 3 han sido eliminados correctamente.');
    }

    public function exportarCSV($docenteId, $cursoId, Request $request)
    {
        $competenciasSeleccionadas = $request->input('competencias');

        if (is_null($competenciasSeleccionadas) || ! is_array($competenciasSeleccionadas)) {
            return redirect()->back()->withErrors(['message' => 'Por favor, selecciona al menos una competencia.']);
        }

        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasSeleccionadas)->get();

        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);

        // 👉 Armamos solo el nombre del archivo
        $nombreArchivo = "{$curso->nombre}-{$docente->nombre}.xlsx";

        return Excel::download(
            new CalificacionesExport($docenteId, $cursoId, $competenciasSeleccionadas),
            $nombreArchivo
        );
    }

    public function exportarCSVppd(Request $request, $docenteId, $cursoId)
    {
        $competenciasSeleccionadas = $request->input('competencias');

        if (is_null($competenciasSeleccionadas) || ! is_array($competenciasSeleccionadas)) {
            return redirect()->back()->withErrors(['message' => 'Por favor, selecciona al menos una competencia.']);
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasSeleccionadas)->get();

        $curso = Curso::findOrFail($cursoId);
        $programa = $curso->ciclo->programa;

        $alumnos = ppd::whereHas('ciclo', function ($query) use ($programa) {
            $query->where('programa_id', $programa->id);
        })
            ->with([
                'user.alumnoB.calificaciones' => function ($query) use ($cursoId) {
                    $query->where('curso_id', $cursoId);
                },
            ])
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->get();
        // 👉 Armamos el nombre dinámico del archivo
        /* $nombreArchivo = "$curso->nombre.xlsx"; */
        $docente = Docente::findOrFail($docenteId);

        $nombreCurso = preg_replace('/[\/\\\\]/', '-', $curso->nombre);
        $nombreDocente = preg_replace('/[\/\\\\]/', '-', $docente->nombre);

        $nombreArchivo = "{$nombreCurso}_{$nombreDocente}.xlsx";

        return Excel::download(
            new CalificacionesPPDExport($docenteId, $cursoId, $competenciasSeleccionadas, $alumnos),
            $nombreArchivo
        );
    }

    /**
     * Exporta las calificaciones de TODOS los cursos FID del periodo actual
     * activo: un archivo .xlsx por curso (mismo formato/contenido que
     * exportarCSV(), el botón "Exportar Excel" de la vista de calificaciones
     * de un curso), empaquetados en un único .zip para descargar de una vez.
     */
    public function exportarPeriodoActualFID()
    {
        $cursoIdsActuales = \DB::table('curso_docente')->distinct()->pluck('curso_id');

        $cursos = Curso::with(['ciclo.programa', 'competencias', 'competenciasSeleccionadas'])
            ->whereIn('id', $cursoIdsActuales)
            ->get()
            ->reject(fn ($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'))
            ->reject(fn ($curso) => str_contains(strtolower($curso->cc ?? ''), 'extracurricular'))
            ->sortBy([
                fn ($a, $b) => ($a->ciclo->ordenCiclo() ?? 999) <=> ($b->ciclo->ordenCiclo() ?? 999),
                fn ($a, $b) => $a->nombre <=> $b->nombre,
            ]);

        $archivosPorNombre = [];

        foreach ($cursos as $curso) {
            $docenteId = \DB::table('curso_docente')->where('curso_id', $curso->id)->value('docente_id');
            $docente = $docenteId ? Docente::find($docenteId) : null;

            if (! $docente) {
                continue;
            }

            $competencias = $curso->competencias->count() <= 3
                ? $curso->competencias
                : $curso->competenciasSeleccionadas;

            if ($competencias->isEmpty()) {
                continue;
            }

            $alumnosCiclo = $curso->ciclo->alumnos()
                ->orderBy('apellidos')
                ->get();

            $alumnosRelacionados = $curso->alumnos()
                ->orderBy('apellidos')
                ->get();

            $alumnos = $alumnosCiclo->merge($alumnosRelacionados)->unique('id')->values();

            if ($alumnos->isEmpty()) {
                continue;
            }

            $sanear = fn ($texto) => preg_replace('/[\/\\\\]/', '-', $texto ?? '');
            $nombreCurso = $sanear($curso->nombre);
            $nombrePrograma = $sanear($curso->ciclo->programa->nombre ?? 'Sin programa');
            $nombreCiclo = $sanear($curso->ciclo->nombre ?? '-');
            $nombreDocente = $sanear($docente->nombre);
            $nombreArchivo = "{$nombreCurso} - {$nombrePrograma} - Ciclo {$nombreCiclo} - {$nombreDocente} ({$curso->id}).xlsx";

            $archivosPorNombre[$nombreArchivo] = Excel::raw(
                new CalificacionesExport($docenteId, $curso->id, $competencias),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }

        if (empty($archivosPorNombre)) {
            return back()->with('error', 'No hay cursos con calificaciones para exportar en el periodo actual.');
        }

        $periodoActual = PeriodoActual::where('actual', true)->first();
        $nombreZip = 'Calificaciones_FID_'.($periodoActual->nombre ?? 'PeriodoActual').'.zip';
        $rutaZip = storage_path('app/'.uniqid('export_periodo_actual_').'.zip');

        $zip = new \ZipArchive();
        $zip->open($rutaZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($archivosPorNombre as $nombreArchivo => $contenido) {
            $zip->addFromString($nombreArchivo, $contenido);
        }
        $zip->close();

        return response()->download($rutaZip, $nombreZip)->deleteFileAfterSend(true);
    }

    /* public function eliminarTodosCursosGlobal()
    {
        \DB::table('curso_docente')->truncate();
        return redirect()->back()->with('success', 'Se eliminaron todos los cursos de todos los docentes.');
    } */
    public function eliminarTodosCursosGlobal()
    {
        // Obtener los IDs de cursos que NO son de programas PPD
        $cursosNoPPD = Curso::whereHas('ciclo.programa', function ($query) {
            $query->where('nombre', 'NOT LIKE', '%PPD%');
        })->pluck('id');

        // Eliminar solo las asignaciones de cursos que NO son PPD
        \DB::table('curso_docente')
            ->whereIn('curso_id', $cursosNoPPD)
            ->delete();

        return redirect()->back()->with('success', 'Se eliminaron todos los cursos de todos los docentes (excluyendo cursos de programas PPD).');
    }

    public function eliminarTodosCursosGlobalPPD()
    {
        // Obtener los IDs de cursos que SÍ son de programas PPD
        $cursosPPD = Curso::whereHas('ciclo.programa', function ($query) {
            $query->where('nombre', 'LIKE', '%PPD%');
        })->pluck('id');

        // Eliminar solo las asignaciones de cursos PPD
        \DB::table('curso_docente')
            ->whereIn('curso_id', $cursosPPD)
            ->delete();

        return redirect()->back()->with('success', 'Se eliminaron todas las asignaciones de cursos PPD a todos los docentes.');
    }
}
