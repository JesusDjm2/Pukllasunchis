<?php

namespace App\Http\Controllers;

use App\Models\Capacidades;
use App\Models\Competencia;
use Illuminate\Http\Request;

class CapacidadesController extends Controller
{
    public function index()
    {
        /* $capacidades = Capacidades::with('competencia')->get(); */
        $capacidades = Capacidades::with('competencia')
            ->orderBy('competencia_id', 'asc') // Primero ordena por el número de competencia
            ->orderBy('created_at', 'asc') // Luego ordena por la fecha de creación
            ->get();
        return view('docentes.capacidades.index', compact('capacidades'));
    }

    public function create()
    {
        $competencias = Competencia::all();
        return view('docentes.capacidades.create', compact('competencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias,id',
            'descripcion' => 'required|string|max:500',
        ]);

        Capacidades::create($request->all());

        return redirect()->route('capacidades.index')->with('success', 'Capacidad creada correctamente.');
    }

    public function show(Capacidades $capacidad)
    {
        return view('capacidades.show', compact('capacidad'));
    }

    public function edit(Capacidades $capacidad)
    {
        $competencias = Competencia::all();
        return view('capacidades.edit', compact('capacidad', 'competencias'));
    }

    public function update(Request $request, Capacidades $capacidad)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $capacidad->update($request->all());

        return redirect()->route('capacidades.index')->with('success', 'Capacidad actualizada correctamente.');
    }

    public function destroy(Capacidades $capacidad)
    {
        $capacidad->delete();

        return redirect()->route('capacidades.index')->with('success', 'Capacidades eliminada correctamente.');
    }
}
