<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Storage;

class DocenteCOntroller extends Controller
{
    public function index()
    {
        $docentes = Docente::all();
        $totalDocentes = $docentes->count(); // Contar el número total de docentes
        return view('docentes.index', compact('docentes', 'totalDocentes'));
    }
    public function vistaDocente($docenteId)
    {
        $docente = Docente::findOrFail($docenteId);
        $user = $docente->user;
        return view('docentes.show', compact('user', 'docente'));
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
            'password_confirmation' => 'nullable|same:password'
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
            $rutaFoto = public_path("docentes/fotos/");
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
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->get();

        $alumnosCiclo = $curso->ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->with(['periodos', 'periododos', 'periodotres'])
            ->get();

        $alumnos = $alumnosCiclo->merge($alumnosRelacionados)->unique('id')->values();


        $mostrarBotonDesempeno = $alumnos->contains(function ($alumno) {
            return $alumno->periodos->isNotEmpty() && $alumno->calificaciones->isNotEmpty();
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados'));
    }
    /* public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();

        $programa = $curso->ciclo->programa;

        $alumnos = ppd::whereHas('ciclo', function ($query) use ($programa) {
            $query->where('programa_id', $programa->id);
        })
            ->with([
                'user.alumnoB.calificaciones',
                'user.roles' // 👈 importante para identificar si es inhabilitado
            ])
            ->orderBy('apellidos')
            ->get()
            ->map(function ($alumno) {
                $alumno->es_inhabilitado = $alumno->user->roles->contains('name', 'inhabilitado');
                return $alumno;
            });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    } */
    public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
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
            ->with(['programa.ciclos.cursos', 'roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get();

        // Seteamos banderas para la vista
        $alumnos = $alumnos->map(function ($alumno) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
            $alumno->tiene_ppd = $alumno->alumnoB !== null;
            return $alumno;
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    }



    public function updateBlog(Request $request, $id)
    {
        // Validar la entrada
        $request->validate([
            'blog' => 'nullable|string',
            'curso_id' => 'sometimes|exists:cursos,id',
            'docente_id' => 'sometimes|exists:docentes,id',
            'competencias' => 'sometimes|array',
            'competencias.*' => 'exists:competencias,id',
        ]);

        // Encontrar el docente por ID
        $docente = Docente::findOrFail($id);
        // Actualizar solo el campo blog
        $docente->blog = $request->input('blog');
        $docente->save();
        // Recoger las variables del formulario
        $cursoId = $request->input('curso_id');
        $competenciasSeleccionadas = $request->input('competencias', []);

        // Redirigir a la vista con las variables solo si están presentes
        $response = redirect()->back()->with('success', 'Blog actualizado correctamente');

        // Verificar si las variables están presentes
        if ($request->has('curso_id') && $request->has('competencias')) {
            $curso = Curso::findOrFail($cursoId);
            $docente = Docente::findOrFail($id);
            $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();

            // Filtrar alumnos excluyendo aquellos que tengan el rol "inhabilitado"
            $alumnos = $curso->ciclo->alumnos()
                ->whereHas('user', function ($query) {
                    $query->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'inhabilitado');
                    });
                })
                ->orderBy('apellidos')
                ->get();
            $mostrarBotonDesempeno = $alumnos->contains(function ($alumno) {
                return $alumno->periodos->isNotEmpty() && $alumno->calificaciones->isNotEmpty();
            });


            return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno'));
        }

        return $response;
    }
    /* public function alumnos($id)
    {
        $docente = Docente::with([
            'cursos.ciclo.alumnos.user.roles',
            'cursos.ciclo.programa',
            'cursos.alumnos.user.roles',
            'cursos.alumnos.ciclo'
        ])->findOrFail($id);
        $cursos = $docente->cursos;

        foreach ($cursos as $curso) {
            if ($curso->ciclo && in_array($curso->ciclo->programa_id, [1, 2])) {
                $alumnosCiclo = $curso->ciclo->alumnos->filter(function ($alumno) {
                    $user = $alumno->user;

                    if (!$user || !$user->hasRole('alumno') || $user->hasRole('alumnoB')) {
                        return false;
                    }
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
                    if (!$user || !$user->hasRole('alumno') || $user->hasRole('alumnoB')) {
                        return false;
                    }

                    if (
                        $user->hasRole('inhabilitado') &&
                        in_array($user->perfil, ['sin_matricula', 'reserva'])
                    ) {
                        return false;
                    }

                    return true;
                });


                $alumnosCiclo = $curso->ciclo->alumnos->filter(function ($alumno) {
                    return $alumno->user
                        && $alumno->user->hasRole('alumno')
                        && !$alumno->user->hasRole('alumnoB')
                        && !$alumno->user->hasRole('inhabilitado');
                });

                $alumnosRelacionados = $curso->alumnos->filter(function ($alumno) {
                    return $alumno->user
                        && $alumno->user->hasRole('alumno')
                        && !$alumno->user->hasRole('alumnoB')
                        && !$alumno->user->hasRole('inhabilitado');
                });

                $alumnosUnificados = $alumnosCiclo
                    ->merge($alumnosRelacionados)
                    ->unique('id')
                    ->sortBy('apellidos');

                $alumnosPorCurso[$curso->id] = $alumnosUnificados;
            }
        }
        return view('docentes.alumnos.index', compact('docente', 'alumnosPorCurso', 'curso'));
    } */
    public function alumnos($id)
    {
        $docente = Docente::with([
            'cursos.ciclo.alumnos.user.roles',
            'cursos.ciclo.programa',
            'cursos.alumnos.user.roles',
            'cursos.alumnos.ciclo'
        ])->findOrFail($id);

        $cursos = $docente->cursos;
        $alumnosPorCurso = [];

        foreach ($cursos as $curso) {
            if ($curso->ciclo && in_array($curso->ciclo->programa_id, [1, 2, 3, 4])) {

                $alumnosCiclo = $curso->ciclo->alumnos->filter(function ($alumno) {
                    $user = $alumno->user;

                    if (!$user) {
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

                    if (!$user) {
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
        $docente = Docente::with('cursos')->findOrFail($id);
        // Traer los cursos del docente
        $cursos = $docente->cursos;
        // Inicializar un array vacío para almacenar los alumnos por curso
        $alumnosPorCurso = [];
        foreach ($cursos as $curso) {
            // Buscar alumnos que tengan el rol alumnoB y pertenezcan al ciclo del curso actual
            $alumnos = User::with(['ciclo', 'programa'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'alumnoB');
                })
                ->whereHas('ciclo.cursos', function ($query) use ($curso) {
                    $query->where('cursos.id', $curso->id);
                })
                ->get();
            // Agrupar por el ID del curso
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
