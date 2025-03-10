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
        $competencia = Competencia::findOrFail($id);
        $user = Auth::user();
        $docente = $user->docente;
        $alumno = $user->alumno;
        $capacidades = $competencia->capacidad()->orderBy('created_at', 'asc')->get();

        if ($user->hasRole('docente')) {
            return view('docentes.competencias.showDocente', compact('competencia', 'docente', 'capacidades'));
        } elseif ($user->hasRole('admin')) {
            return view('docentes.competencias.show', compact('competencia', 'docente', 'capacidades'));
        } elseif ($user->hasRole('alumno')) {
            return view('docentes.competencias.showAlumno', compact('competencia', 'docente', 'alumno', 'capacidades'));
        } else {
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
