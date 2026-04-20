<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class DocenteCOntroller extends Controller
{
    public function index()
    {
        $docentes = Docente::all();
        $totalDocentes = $docentes->count();

        return view('docentes.index', compact('docentes', 'totalDocentes'));
    }

    public function vistaDocente($docenteId)
    {
        $periodoActual = PeriodoActual::where('actual', true)->first();
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

        $alumnos = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->get();

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
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();

        $alumnosRelacionados = $curso->alumnos()
            ->whereHas('user', function ($q) {
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                    ->orWhere(function ($q2) {
                        $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                            ->where('perfil', '!=', 'Sin matrícula');
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
                            })->where('perfil', '!=', 'Sin matrícula');
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

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados'));
    }

    public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::with('ciclo.programa')->findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $programaId = $curso->ciclo->programa->id;

        // Obtener alumnos que:
        // 1. Tienen rol 'alumnoB' o 'inhabilitado'
        // 2. Están en el curso específico
        // 3. NO tienen el campo 'guardado' = true (es decir, aún no han guardado sus calificaciones)
        $alumnos = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['alumnoB', 'inhabilitado']);
        })
            ->whereHas('programa.ciclos.cursos', function ($query) use ($cursoId) {
                $query->where('id', $cursoId);
            })
            ->whereDoesntHave('alumnoB', function ($query) {
                // 🔥 SOLO TRAER ALUMNOS QUE NO TIENEN PPD GUARDADO O TIENEN guardado = false
                $query->where('guardado', true);
            })
            ->with(['programa.ciclos.cursos', 'roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnos->map(function ($alumno) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
            $alumno->tiene_ppd = $alumno->alumnoB !== null;

            // Verificar si ya tiene calificaciones guardadas
            $alumno->tiene_calificacion_guardada = $alumno->alumnoB && $alumno->alumnoB->guardado == true;

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

    public function alumnos($id)
    {
        $docente = Docente::with([
            'cursos.ciclo.alumnos.user.roles',
            'cursos.ciclo.programa',
            'cursos.alumnos.user.roles',
            'cursos.alumnos.ciclo',
        ])->findOrFail($id);

        $cursos = $docente->cursos;
        $alumnosPorCurso = [];

        foreach ($cursos as $curso) {
            if ($curso->ciclo && in_array($curso->ciclo->programa_id, [1, 2, 3, 4])) {

                $alumnosCiclo = $curso->ciclo->alumnos->filter(function ($alumno) {
                    $user = $alumno->user;

                    if (! $user) {
                        return false;
                    }

                    // Excluir si el usuario tiene rol inhabilitado Y perfil sin_matricula o reserva
                    if (
                        $user->hasRole('inhabilitado') &&
                        in_array($user->perfil, ['sin_matricula', 'reserva'])
                    ) {
                        return false;
                    }

                    return true;
                });

                $alumnosRelacionados = $curso->alumnos->filter(function ($alumno) {
                    $user = $alumno->user;

                    if (! $user) {
                        return false;
                    }

                    // Mismo filtro que en alumnosCiclo
                    if (
                        $user->hasRole('inhabilitado') &&
                        in_array($user->perfil, ['sin_matricula', 'reserva'])
                    ) {
                        return false;
                    }

                    return true;
                });

                $alumnosUnificados = $alumnosCiclo
                    ->merge($alumnosRelacionados)
                    ->unique('id')
                    ->sortBy('apellidos');

                $alumnosPorCurso[$curso->id] = $alumnosUnificados;
            }
        }

        return view('docentes.alumnos.index', compact('docente', 'alumnosPorCurso', 'curso'));
    }

    public function alumnosppd($id)
    {
        $docente = Docente::with(['cursos.ciclo.programa'])->findOrFail($id);
        $cursos = $docente->cursos;
        $alumnosPorCurso = [];
        foreach ($cursos as $curso) {
            $alumnos = User::with(['ciclo.programa', 'alumnoB'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'alumnoB');
                })
                ->whereHas('ciclo.cursos', function ($query) use ($curso) {
                    $query->where('cursos.id', $curso->id);
                })
                ->orderBy('apellidos')
                ->get();
            $alumnosPorCurso[$curso->id] = $alumnos;
        }

        return view('docentes.alumnos.alumnos-ppd', compact('docente', 'alumnosPorCurso'));
    }

    public function repositorio($docente)
    {
        $docente = Docente::findOrFail($docente);
        $cursos = Curso::all();

        return view('docentes.silabos', compact('docente', 'cursos'));
    }
}
