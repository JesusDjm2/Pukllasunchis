<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use Illuminate\Http\Request;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::all();
        return view('admin.programa.index', compact('programas'));
    }

    public function create()
    {
        return view('admin.programa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Programa::create($request->all());

        return redirect()->route('programa.index')
            ->with('success', 'Programa creado exitosamente');
    }

    public function show(Programa $programa)
    {
        $ciclos = $programa->ciclos;
        return view('admin.programa.show', compact('programa', 'ciclos'));
    }

    public function edit(Programa $programa)
    {
        return view('admin.programa.edit', compact('programa'));
    }

    public function update(Request $request, Programa $programa)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $programa->update($request->all());

        return redirect()->route('programa.index')
            ->with('success', 'Programa actualizado exitosamente');
    }

    public function destroy(Programa $programa)
    {
        $programa->delete();

        return redirect()->route('programa.index')
            ->with('success', 'Programa eliminado exitosamente');
    }
}
