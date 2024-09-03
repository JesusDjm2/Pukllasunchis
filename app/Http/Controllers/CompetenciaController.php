<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetenciaController extends Controller
{
    public function index()
    {
        $competencias = Competencia::all();
        return view('docentes.competencias.index', compact('competencias'));
    }
    /* public function create()
    {
        $cursos = Curso::all();
        return view('docentes.competencias.create', compact('cursos'));
    } */
    public function create()
    {
        $programas = Programa::all(); // Asegúrate de tener la relación definida
        return view('docentes.competencias.create', compact('programas'));
    }
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'capacidades' => 'nullable|string',
        ]);

        // Crear la competencia
        Competencia::create($validatedData);

        // Redireccionar después de crear la competencia
        return redirect()->route('competencias.index')->with('success', 'Competencia creada exitosamente.');
    }
    public function edit($id)
    {
        $competencia = Competencia::findOrFail($id);
        return view('docentes.competencias.edit', compact('competencia'));
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'capacidades' => 'nullable|string',
        ]);

        $competencia = Competencia::findOrFail($id);
        $competencia->update($validatedData);

        return redirect()->route('competencias.index')->with('success', 'Competencia actualizada exitosamente.');
    }
    /* public function show($id)
    {
        $competencia = Competencia::findOrFail($id);  
        $docente = Auth::user()->docente;          
        return view('docentes.competencias.show', compact('competencia', 'docente'));
    } */
    public function show($id)
    {
        // Recuperar la competencia por ID o fallar si no se encuentra
        $competencia = Competencia::findOrFail($id);

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener el docente asociado al usuario, si existe
        $docente = $user->docente;

        // Determinar la vista a usar basada en el rol del usuario
        if ($user->hasRole('docente')) {
            return view('docentes.competencias.showDocente', compact('competencia', 'docente'));
        } elseif ($user->hasRole('admin')) {
            return view('docentes.competencias.show', compact('competencia', 'docente'));
        } elseif ($user->hasRole('alumno')) {
            return view('docentes.competencias.showAlumno', compact('competencia', 'docente'));
        } else {
            // Redirigir a una vista por defecto o mostrar un error si el rol no es reconocido
            return abort(403, 'Acceso denegado.');
        }
    }

    public function destroy($id)
    {
        // Encuentra la competencia por ID
        $competencia = Competencia::findOrFail($id);

        // Elimina la competencia
        $competencia->delete();

        // Redirige con un mensaje de éxito
        return redirect()->route('competencias.index')
            ->with('success', 'Competencia eliminada exitosamente.');
    }
}
