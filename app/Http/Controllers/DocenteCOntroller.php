<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
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
        return view('docentes.index', compact('docentes'));
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
    public function asignarCurso(Request $request, $docenteId)
    {
        $request->validate([
            'programa_id' => 'required',
            'ciclo_id' => 'required',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::findOrFail($request->curso_id);
        $docente->cursos()->attach($curso->id);

        return redirect()->route('docente.index')->with('success', 'Curso asignado exitosamente.');
    }
    public function eliminarCurso(Docente $docente, Curso $curso)
    {
        // Eliminar la relación entre el docente y el curso
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
    /* public function showAlumnos(Curso $curso, Docente $docente)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;

        $alumnos = $programa->alumnos() 
            ->where('ciclo_id', $ciclo->id)
            ->orderBy('apellidos')
            ->get();
        $cantidadAlumnos = $alumnos->count();

        return view('docentes.alumnos', compact('curso', 'alumnos', 'cantidadAlumnos', 'ciclo', 'docente'));
    } */

    public function showAlumnos(Curso $curso, Docente $docente)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;

        // Filtrar los alumnos del programa excluyendo los que tienen el rol "inhabilitado"
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
        $alumno = auth()->user()->alumno;
        if (auth()->user()->hasRole('alumno')) {
            return view('alumnos.vistasAlumnos.docente', compact('docente', 'alumno'));
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
        return view('docentes.calificaciones.index', compact('docente'));
    }
    /* public function calificarCurso(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $alumnos = $curso->ciclo->alumnos()->orderBy('apellidos')->get();

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    } */
    public function calificarCurso(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
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

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
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

            return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return $response;
    }

    public function alumnos($id)
    {
        $docente = Docente::findOrFail($id);
        $cursos = $docente->cursos;
        $alumnosPorCurso = [];
        foreach ($cursos as $curso) {
            /* $alumnosOrdenados = $curso->ciclo->alumnos->sortBy('apellidos'); */
            $alumnosOrdenados = $curso->ciclo->alumnos
                ->filter(function ($alumno) {
                    return $alumno->user && !$alumno->user->hasRole('inhabilitado');
                })
                ->sortBy('apellidos');
            $alumnosPorCurso[$curso->id] = $alumnosOrdenados;
        }
        return view('docentes.alumnos.index', compact('docente', 'alumnosPorCurso'));
    }
}
