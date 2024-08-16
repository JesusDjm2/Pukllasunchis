<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::all();
        $cant = Curso::count();
        $cursosInicial = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 1);
        })->get();
        $inicial = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 1);
        })->count();

        $cursosEib = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 2);
        })->get();
        $EIB = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 2);
        })->count();
        return view('admin.curso.index', compact('cursos', 'cant', 'inicial', 'cursosInicial', 'EIB', 'cursosEib'));
    }

    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();
        return view('admin.curso.create', compact('programas', 'ciclos', 'cursos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            'cc' => 'required|string',
            'horas' => 'required|string',
            'creditos' => 'required|string',
        ]);

        $curso = Curso::create([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas'),
            'creditos' => $request->input('creditos'),
        ]);

        return redirect()->route('curso.index')->with('success', 'Curso creado exitosamente');
    }

    public function edit(Curso $curso)
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        return view('admin.curso.edit', compact('programas', 'curso', 'ciclos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            'cc' => 'required|string',
            'horas' => 'required|string',
            'creditos' => 'required|string',
        ]);

        $curso = Curso::findOrFail($id);

        $curso->update([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas'),
            'creditos' => $request->input('creditos'),
        ]);
        return redirect()->route('curso.index')->with('success', 'Curso actualizado exitosamente');
    }
    public function show(Curso $curso)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;
        
        $alumnos = $programa->alumnos()
        ->where('ciclo_id', $ciclo->id)
        ->orderBy('apellidos')
        ->get();
        $cantidadAlumnos = $alumnos->count();
        return view('admin.curso.show', compact('curso', 'alumnos', 'cantidadAlumnos'));
    }

    public function destroy(Curso $curso)
    {
        $curso->delete();

        return redirect()->route('curso.index')->with('success', 'Curso eliminado exitosamente');
    }
}
