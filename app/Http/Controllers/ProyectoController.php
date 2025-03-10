<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::all();
        return view('admin.curso.silabos.proyectos.index', compact('proyectos'));
    }
    /* public function create()
    {
        return view('admin.curso.silabos.proyectos.create');
    } */
    public function create()
    {
        $ciclos = Ciclo::with('programa')->get()->groupBy('programa.nombre');
        return view('admin.curso.silabos.proyectos.create', compact('ciclos'));
    }


    /* public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'producto' => 'nullable|string',
            'descripcion' => 'required|string|max:4000',
        ]);

        Proyecto::create($request->all());

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente.');
    } */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'producto' => 'nullable|string|max:255',
            'descripcion' => 'required|string',
            'ciclos' => 'array',
            'ciclos.*' => 'exists:ciclos,id'
        ]);

        $proyecto = Proyecto::create([
            'nombre' => $request->nombre,
            'producto' => $request->producto,
            'descripcion' => $request->descripcion,
        ]);

        if ($request->has('ciclos')) {
            Ciclo::whereIn('id', $request->ciclos)->update(['proyecto_id' => $proyecto->id]);
        }

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado correctamente.');
    }

    public function show(Proyecto $proyecto)
    {
        return view('admin.curso.silabos.proyectos.show', compact('proyecto'));
    }
    public function edit(Proyecto $proyecto)
    {
        $ciclos = Ciclo::with('programa')->get()->groupBy('programa.nombre');
        return view('admin.curso.silabos.proyectos.edit', compact('proyecto', 'ciclos'));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'producto' => 'nullable|string|max:255',
            'descripcion' => 'required|string',
            'ciclos' => 'array',
            'ciclos.*' => 'exists:ciclos,id'
        ]);

        $proyecto->update([
            'nombre' => $request->nombre,
            'producto' => $request->producto,
            'descripcion' => $request->descripcion,
        ]);

        if ($request->has('ciclos')) {
            Ciclo::whereIn('id', $request->ciclos)->update(['proyecto_id' => $proyecto->id]);
            Ciclo::whereNotIn('id', $request->ciclos)->where('proyecto_id', $proyecto->id)->update(['proyecto_id' => null]);
        }

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado correctamente.');
    }
    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();
        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado.');
    }
}
