<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
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
        $curso->docente_id = $docente->id;
        $curso->save();

        return redirect()->route('docente.index')->with('success', 'Curso asignado exitosamente.');
    }

    public function eliminarCurso(Docente $docente, Curso $curso)
    {
        // Establecer el campo docente_id a null en el curso
        $curso->docente_id = null;
        $curso->save();

        return redirect()->back()->with('success', 'Curso eliminado correctamente.');
    }

    //Prueba para editar Cursos asignados
    /* public function editCursos($id)
    {
        $user = User::findOrFail($id);

        return view('docentes.editcursos', compact('user'));
    }

    // Método para actualizar los cursos asignados
    public function updateCursos(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $cursosIds = $request->input('cursos', []);

        // Aquí puedes añadir lógica para asignar los cursos al docente
        // Por ejemplo:
        $docente->cursos()->sync($cursosIds);

        return redirect()->route('docente.index')->with('success', 'Cursos actualizados exitosamente.');
    } */

    public function edit($id)
    {
        $docente = Docente::findOrFail($id);
        return view('docentes.edit', compact('docente'));
    }

    // Update the specified resource in storage.
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
    public function showAlumnos(Curso $curso, Docente $docente)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;

        $alumnos = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->orderBy('apellidos')
            ->get();
        $cantidadAlumnos = $alumnos->count();

        return view('docentes.alumnos', compact('curso', 'alumnos', 'cantidadAlumnos', 'ciclo', 'docente'));
    }
    public function show($id)
    {
        $docente = Docente::findOrFail($id);
        return view('docentes.perfil', compact('docente'));
    }
}
