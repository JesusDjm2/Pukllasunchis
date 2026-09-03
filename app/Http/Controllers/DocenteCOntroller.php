<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\Silabo;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class DocenteCOntroller extends Controller
{
    public function index()
    {
        $docentes = Docente::with(['user.roles', 'user.tutorCiclos.incidencias', 'user.tutorCiclos.programa', 'incidencias'])->get();
        $totalDocentes = $docentes->count();
        $totalIncidencias = $docentes->sum(fn($d) => $d->incidencias->count());
        $totalSilabos = Silabo::count();

        $periodoActual = PeriodoActual::actual();
        $ciclos = Ciclo::all()->sortBy(fn ($c) => $c->ordenCiclo() ?? 999);

        $conteosZonaRiesgo = [
            'parcial1' => \DB::table('periodouno')->count(),
            'parcial2' => \DB::table('periodo_dos')->count(),
            'desempeno' => \DB::table('periodo_tres')->count(),
            'asignacionesFid' => \DB::table('curso_docente')
                ->whereIn('curso_id', Curso::whereHas('ciclo.programa', fn ($q) => $q->where('nombre', 'NOT LIKE', '%PPD%'))->pluck('id'))
                ->count(),
            'asignacionesPpd' => \DB::table('curso_docente')
                ->whereIn('curso_id', Curso::whereHas('ciclo.programa', fn ($q) => $q->where('nombre', 'LIKE', '%PPD%'))->pluck('id'))
                ->count(),
        ];

        return view('docentes.index', compact('docentes', 'totalDocentes', 'totalIncidencias', 'totalSilabos', 'periodoActual', 'ciclos', 'conteosZonaRiesgo'));
    }

    public function vistaDocente($docenteId)
    {
        $periodoActual = PeriodoActual::actual();
        $nombrePeriodoActual = $periodoActual->nombre ?? null;
        $docente = Docente::findOrFail($docenteId);
        $user = $docente->user;

        return view('docentes.show', compact('user', 'docente', 'periodoActual'));
    }

    public function asignar($id)
    {
        $user = User::findOrFail($id);

        $docente = $user->docente;

        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();

        return view('docentes.asignar', compact('docente', 'programas', 'ciclos', 'cursos'));
    }

    /* public function asignarCurso(Request $request, $docenteId)
    {
        $request->validate([
            'programa_id' => 'required',
            'ciclo_id' => 'required',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::findOrFail($request->curso_id);
        $docente->cursos()->attach($curso->id);
        if ($docente->cursos()->where('curso_id', $curso->id)->exists()) {
            return redirect()
                ->route('docente.index')
                ->with('error', "El curso {$curso->nombre} ya está asignado a {$docente->nombre}.");
        }
        $docente->cursos()->attach($curso->id);

        return redirect()->route('docente.index')->with('success', 'Curso asignado a ' . $docente->nombre . ' correctamente.');
    } */
    public function asignarCurso(Request $request, $docenteId)
    {
        $request->validate([
            'programa_id' => 'required',
            'ciclo_id' => 'required',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::findOrFail($request->curso_id);

        // Usamos syncWithoutDetaching
        $result = $docente->cursos()->syncWithoutDetaching([$curso->id]);

        if (empty($result['attached'])) {
            return redirect()
                ->route('docente.index')
                ->with('error', "El curso {$curso->nombre} ya está asignado a {$docente->nombre}.");
        }

        return redirect()
            ->route('docente.index')
            ->with('success', "Curso {$curso->nombre} asignado a {$docente->nombre} correctamente.");
    }

    public function eliminarCurso(Docente $docente, Curso $curso)
    {
        $docente->cursos()->detach($curso->id);

        return redirect()->back()->with('success', 'Curso eliminado correctamente.');
    }

    public function edit($id)
    {
        $docente = Docente::findOrFail($id);

        return view('docentes.edit', compact('docente'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'password' => 'nullable|confirmed|min:8',
            'password_confirmation' => 'nullable|same:password',
        ]);

        // Buscar docente
        $docente = Docente::findOrFail($id);

        // Actualizar datos
        $docente->nombre = $request->input('nombre');
        $docente->dni = $request->input('dni');
        $docente->email = $request->input('email');
        $docente->descripcion = $request->input('descripcion');

        // Manejar foto
        if ($request->hasFile('foto')) {
            // Eliminar la foto anterior si existe
            if ($docente->foto) {
                $oldPath = public_path("docentes/fotos/{$docente->foto}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Subir la nueva foto
            $foto = $request->file('foto');
            $nombreFoto = $foto->getClientOriginalName();
            $rutaFoto = public_path('docentes/fotos/');
            $foto->move($rutaFoto, $nombreFoto);

            // Guardar el nombre de la nueva foto en la base de datos
            $docente->foto = $nombreFoto;
        }

        // Manejar contraseña
        if ($request->filled('password')) {
            $docente->user->password = Hash::make($request->input('password'));
            $docente->user->save();
        }

        $docente->save();

        return redirect()->route('docente.show', $docente->id)
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function showBlog($docenteId)
    {
        $docente = Docente::findOrFail($docenteId);
        $blog = $docente->blog;

        return view('docentes.blog', compact('docente', 'blog'));
    }

    public function showAlumnos(Curso $curso, Docente $docente)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;
        $periodoActual = \App\Models\PeriodoActual::where('actual', true)->first();

        $query = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->whereHas('user', fn ($q) => $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
                ->where(function ($q) {
                    $q->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                      ->orWhere(function ($q2) {
                          $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                              ->where('perfil', '!=', 'Retirado');
                      });
                }));

        if ($periodoActual) {
            $query->where(function ($q) use ($periodoActual) {
                $q->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoActual->id))
                  ->orWhereHas('user', fn ($u) => $u->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado')));
            });
        }

        $alumnos = $query->orderBy('apellidos')->get();
        $cantidadAlumnos = $alumnos->count();

        return view('docentes.alumnos', compact('curso', 'alumnos', 'cantidadAlumnos', 'ciclo', 'docente'));
    }

    public function show($id)
    {
        $docente = Docente::findOrFail($id);
        $alumno = auth()->user()->alumnoB;

        if (auth()->user()->hasRole('alumno')) {
            return view('alumnos.vistasAlumnos.docente', compact('docente', 'alumno'));
        } elseif (auth()->user()->hasRole('alumnoB')) {
            return view('alumnos.ppd.docente', compact('docente', 'alumno'));
        }

        return view('docentes.perfil', compact('docente'));
    }

    public function destroy($id)
    {
        $docente = Docente::findOrFail($id);
        // Eliminar el usuario relacionado
        if ($docente->user) {
            $docente->user->delete();
        }
        // Eliminar el docente
        $docente->delete();

        return redirect()->route('docente.index')->with('success', 'Docente eliminado correctamente.');
    }

    public function calificar($id)
    {
        $docente = Docente::findOrFail($id);
        $fechaLimite = \Carbon\Carbon::createFromTimeString('16:16')->toDateTimeString();

        return view('docentes.calificaciones.index', compact('docente', 'fechaLimite'));
    }

    public function calificarCurso(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);

        $competenciasIds = $request->input('competencias');
        if (empty($competenciasIds)) {
            $competenciasIds = $curso->competencias->count() <= 3
                ? $curso->competencias->pluck('id')
                : $curso->competenciasSeleccionadas->pluck('id');
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        $periodoActual = \App\Models\PeriodoActual::where('actual', true)->first();

        // Solo alumnos activos (no inhabilitados) entran por pertenecer al ciclo del curso.
        // Un inhabilitado solo aparece si tiene asignación explícita a ESTE curso (vía "Asignar cursos") —
        // así, quitarle todos los cursos a un inhabilitado lo saca de las listas de calificar.
        $filtroFidActivo = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'alumno'));
        };

        $filtroFidInhabilitado = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
              ->where('perfil', '!=', 'Retirado');
        };

        // Asignación directa de alumnos activos: sin cambios, incluye filas heredadas sin período
        // (hay ~300 asignaciones de "cursos adicionales" que predatan la columna periodo_actual_id).
        $alumnosRelacionadosActivos = $curso->alumnos()
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->get();

        // Asignación directa de inhabilitados: solo cuenta si es del período actual. Las filas de otros
        // períodos o sin período (datos heredados) no deben mantenerlo visible indefinidamente.
        $alumnosRelacionadosInhabilitados = collect();
        if ($periodoActual) {
            $alumnosRelacionadosInhabilitados = $curso->alumnos()
                ->with('user.roles')
                ->whereHas('user', $filtroFidInhabilitado)
                ->wherePivot('periodo_actual_id', $periodoActual->id)
                ->get();
        }

        $alumnosRelacionados = $alumnosRelacionadosActivos
            ->merge($alumnosRelacionadosInhabilitados)
            ->sortBy('apellidos')
            ->values();

        $alumnosCiclo = $curso->ciclo->alumnos()
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnosCiclo
            ->merge($alumnosRelacionados)
            ->unique('id')
            ->sortBy('apellidos')
            ->values();

        $alumnos->each(function ($alumno) use ($periodoActual) {
            $alumno->es_inhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
            $alumno->no_matriculado = $periodoActual && ! $alumno->es_inhabilitado && ! $alumno->matriculaEnPeriodo($periodoActual->id);
        });

        $mostrarBotonDesempeno = true;

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados', 'periodoActual'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados', 'periodoActual'));
    }

    public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::with('ciclo.programa', 'competencias', 'calificacionesppd')->findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);

        $competenciasIds = $request->input('competencias');
        if (empty($competenciasIds)) {
            $competenciasIds = $curso->competencias->count() <= 3
                ? $curso->competencias->pluck('id')
                : $curso->competenciasSeleccionadas->pluck('id');
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        // Alumnos que YA tienen TODOS los campos de Proceso/Final llenos para ESTE curso (no el
        // flag global ppd.guardado, ni el valor derivado calificacion_curso: la pantalla recalcula
        // ese campo cada 200ms para toda fila visible, incluso las que el docente nunca tocó, y
        // puede dejar una nota parcial que oculta de esta lista a alumnos que en realidad no
        // fueron calificados).
        $userIdsCalificados = $curso->alumnosPpdCalificadosCompletos();

        // Obtener alumnos que:
        // 1. Tienen rol 'alumnoB' o 'inhabilitado'
        // 2. Pertenecen al MISMO PROGRAMA que el curso. En PPD un alumno cursa Ciclo I y
        //    Ciclo II en paralelo dentro del mismo año (no es secuencial como en FID), así
        //    que a qué Ciclo esté asignado no debe limitar en qué cursos puede calificarse.
        // 3. No están marcados como "Egresado": cada promoción que termina el programa
        //    conserva su Ciclo II real (para no perder el vínculo con sus cursos y
        //    calificaciones), pero no forma parte de la cohorte que está cursando ahora
        //    y no debe aparecer mezclada en la pantalla de calificar del periodo actual.
        // 4. NO tienen calificacion_curso guardada para este curso
        $query = User::where(function ($q) {
            $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->orWhere(function ($q2) {
                  $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                      ->where('perfil', '!=', 'Retirado');
              });
        })
            ->where('programa_id', $curso->ciclo?->programa_id)
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->with(['roles', 'alumnoB'])
            ->orderBy('apellidos');

        // El docente solo ve quienes aún no tienen calificaciones guardadas para este curso;
        // el admin ve todos para tener visibilidad completa.
        if (!auth()->user()->hasRole('admin')) {
            $query->whereNotIn('id', $userIdsCalificados);
        }

        $alumnos = $query->get();

        $alumnos = $alumnos->map(function ($alumno) use ($userIdsCalificados) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');

            // Si no hay alumnoB vinculado por user_id, intentar encontrar su registro PPD por email
            if ($alumno->alumnoB === null && $alumno->email) {
                $ppdPorEmail = ppd::where('email', $alumno->email)->first();
                if ($ppdPorEmail) {
                    // Vincular automáticamente para que futuras consultas funcionen
                    if (! $ppdPorEmail->user_id) {
                        $ppdPorEmail->user_id = $alumno->id;
                        $ppdPorEmail->save();
                    }
                    // Recargar la relación con el registro encontrado
                    $alumno->setRelation('alumnoB', $ppdPorEmail);
                }
            }

            $alumno->tiene_ppd = $alumno->alumnoB !== null;

            // Verificar si ya tiene calificacion_curso guardada para ESTE curso
            $alumno->tiene_calificacion_guardada = $userIdsCalificados->contains($alumno->id);

            return $alumno;
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    }

    /* public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::with('ciclo.programa')->findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $programaId = $curso->ciclo->programa->id;
        $alumnos = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['alumnoB', 'inhabilitado']);
        })
            ->whereHas('programa.ciclos.cursos', function ($query) use ($cursoId) {
                $query->where('id', $cursoId);
            })
            ->whereHas('alumnoB', function ($query) {
                $query->where('guardado', true);
            })

            ->with(['programa.ciclos.cursos', 'roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnos->map(function ($alumno) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
            $alumno->tiene_ppd = $alumno->alumnoB !== null;

            return $alumno;
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    } */

    public function alumnosPanel($id)
    {
        $docente = Docente::with(['cursos.ciclo.programa'])->findOrFail($id);
        $cursos = $docente->cursos;

        $mostrarFid = $cursos->contains(function ($curso) {
            return !str_contains(strtoupper(optional(optional($curso)->ciclo?->programa)->nombre ?? ''), 'PPD');
        });
        $mostrarPpd = $cursos->contains(function ($curso) {
            return str_contains(strtoupper(optional(optional($curso)->ciclo?->programa)->nombre ?? ''), 'PPD');
        });

        $alumnosPorCursoFid = $mostrarFid ? $this->alumnosFidPorCurso($docente) : [];
        $alumnosPorCursoPpd = $mostrarPpd ? $this->alumnosPpdPorCurso($docente) : [];

        return view('docentes.alumnos.panel', compact(
            'docente', 'mostrarFid', 'mostrarPpd', 'alumnosPorCursoFid', 'alumnosPorCursoPpd'
        ));
    }

    private function alumnosFidPorCurso(Docente $docente): array
    {
        $cursos = $docente->cursos;
        $periodoActual = \App\Models\PeriodoActual::where('actual', true)->first();
        $alumnosPorCurso = [];

        foreach ($cursos as $curso) {
            if (! $curso->ciclo || ! in_array($curso->ciclo->programa_id, [1, 2, 3, 4])) {
                continue;
            }

            $filtroFid = fn ($q) => $q
                ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
                ->where(function ($q) {
                    $q->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                      ->orWhere(function ($q2) {
                          $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                              ->where('perfil', '!=', 'Retirado');
                      });
                });

            $aplicarPeriodo = function ($builder) use ($periodoActual) {
                if ($periodoActual) {
                    $builder->where(function ($q) use ($periodoActual) {
                        $q->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoActual->id))
                          ->orWhereHas('user', fn ($u) => $u->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado')));
                    });
                }
            };

            $qCiclo = $curso->ciclo->alumnos()->with(['user', 'user.roles'])->whereHas('user', $filtroFid);
            $aplicarPeriodo($qCiclo);
            $alumnosCiclo = $qCiclo->orderBy('apellidos')->get();

            $qRelacionados = $curso->alumnos()->with(['user', 'user.roles'])->whereHas('user', $filtroFid);
            $aplicarPeriodo($qRelacionados);
            $alumnosRelacionados = $qRelacionados->orderBy('apellidos')->get();

            $alumnosUnificados = $alumnosCiclo
                ->merge($alumnosRelacionados)
                ->unique('id')
                ->filter(function ($alumno) use ($curso) {
                    $cursoRelacionIds = $alumno->cursos()->pluck('curso_id');

                    return $cursoRelacionIds->isEmpty() || $cursoRelacionIds->contains($curso->id);
                })
                ->sortBy('apellidos')
                ->values();

            $alumnosUnificados->each(function ($alumno) {
                $alumno->es_inhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
            });

            $alumnosPorCurso[$curso->id] = $alumnosUnificados;
        }

        return $alumnosPorCurso;
    }

    private function alumnosPpdPorCurso(Docente $docente): array
    {
        $cursos = $docente->cursos;
        $alumnosPorCurso = [];

        foreach ($cursos as $curso) {
            $alumnos = User::with(['ciclo.programa', 'alumnoB', 'roles'])
                ->where(function ($q) {
                    $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                      ->orWhere(function ($q2) {
                          $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                              ->where('perfil', '!=', 'Retirado');
                      });
                })
                // Mismo PROGRAMA que el curso, no mismo Ciclo exacto: en PPD un alumno cursa
                // Ciclo I y Ciclo II en paralelo dentro del mismo año.
                ->where('programa_id', $curso->ciclo?->programa_id)
                // Excluir egresados: conservan su Ciclo II real pero no son parte de la
                // cohorte del periodo actual (ver nota en alumnosPpdPorCurso/calificarCursoPPD).
                ->where(function ($q) {
                    $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
                })
                ->orderBy('apellidos')
                ->get();

            $alumnos->each(function ($alumno) {
                $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
            });

            $alumnosPorCurso[$curso->id] = $alumnos;
        }

        return $alumnosPorCurso;
    }

    public function repositorio($docente)
    {
        $docente = Docente::findOrFail($docente);
        $periodoActual = PeriodoActual::actual();
        $nombrePeriodoActual = $periodoActual->nombre ?? null;

        $cursos = Curso::with(['silabos' => fn ($q) => $q->orderByDesc('periodo')])
            ->orderBy('nombre')
            ->get();

        // Un curso puede tener un sílabo estructurado por cada periodo académico.
        // Se genera una fila por (curso, periodo) para no ocultar periodos recientes
        // detrás de uno antiguo, como pasaba al usar la relación relacionsilabo() (hasOne sin orden).
        $filas = collect();
        foreach ($cursos as $curso) {
            $silabosCurso = $curso->silabos;

            if ($silabosCurso->isEmpty()) {
                if ($curso->silabo) {
                    $filas->push((object) [
                        'curso' => $curso,
                        'periodo' => 'Sílabo sin periodo',
                        'silabo' => null,
                        'mostrarPdfLegacy' => true,
                    ]);
                }

                continue;
            }

            foreach ($silabosCurso->values() as $index => $silabo) {
                $filas->push((object) [
                    'curso' => $curso,
                    'periodo' => $silabo->periodo,
                    'silabo' => $silabo,
                    // El PDF legacy (campo cursos.silabo) no tiene periodo propio;
                    // se muestra junto al sílabo estructurado más reciente del curso.
                    'mostrarPdfLegacy' => $index === 0 && (bool) $curso->silabo,
                ]);
            }
        }

        $conPeriodo = $filas->where('periodo', '!=', 'Sílabo sin periodo')
            ->groupBy('periodo')
            ->sortKeysDesc();
        $sinPeriodo = $filas->where('periodo', 'Sílabo sin periodo')->groupBy('periodo');

        $cursosAgrupados = $conPeriodo->merge($sinPeriodo);

        return view('docentes.silabos', compact('docente', 'cursosAgrupados', 'periodoActual', 'nombrePeriodoActual'));
    }
}
