<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Enfoques;
use App\Models\Programa;
use Illuminate\Http\Request;

class EnfoquesController extends Controller
{
    public function index()
    {
        /* $capacidades = Capacidades::with('competencia')->get(); */
        $enfoques = Enfoques::all();
        return view('admin.curso.enfoques.index', compact('enfoques'));
    }

    public function create()
    {
        $enfoques = Enfoques::all();

        return view('admin.curso.enfoques.create', compact('enfoques'));
    }

    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'observables' => 'required|string',
            'concretas'   => 'required|string',
        ]);

        // Crear el enfoque
        Enfoques::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'observables' => $request->observables,
            'concretas'   => $request->concretas,
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('enfoques.index')->with('success', 'Enfoque creado correctamente.');
    }

    public function show(Enfoques $enfoque)
    {
        return view('admin.curso.enfoques.show', compact('enfoque'));
    }

    public function edit(Enfoques $enfoque)
    {
        $enfoque = Enfoques::all();
        return view('admin.curso.enfoques.edit', compact('enfoque'));
    }

    public function update(Request $request, Enfoques $enfoque)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:500',
            'observables'=> 'nullable|string|max:500',
            'concretas'   => 'required|string',

        ]);

        $enfoque->update($request->all());

        return redirect()->route('enfoques.index')->with('success', 'Enfoque actualizado correctamente.');
    }

    public function destroy(Enfoques $enfoque)
    {
        $enfoque->delete();

        return redirect()->route('enfoques.index')->with('success', 'Enfoque eliminado correctamente.');
    }
}
