<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CicloController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::withCount('alumnos')
            ->whereIn('programa_id', [1, 2])
            ->get();
        return view('admin.ciclo.index', compact('ciclos'));
    }

    public function create()
    {
        $programas = Programa::all();
        return view('admin.ciclo.create', compact('programas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ciclos')->where('programa_id', $request->programa_id),
            ],
            'programa_id' => 'required|exists:programas,id',
        ]);

        Ciclo::create($request->all());

        return redirect()->route('ciclo.index')->with('success', 'Ciclo creado exitosamente');
    }
    public function show(Ciclo $ciclo)
    {
        $alumnos = $ciclo->alumnos()->orderBy('apellidos')->get();
        $ciclosDisponibles = Ciclo::where('programa_id', $ciclo->programa_id)
            ->get();

        $cantidadAlumnos = $alumnos->count();

        return view('admin.ciclo.show', compact('ciclo', 'alumnos', 'cantidadAlumnos', 'ciclosDisponibles'));
    }

    public function updateCicloAlumnos(Request $request)
    {
        $request->validate([
            'nuevo_ciclo_id' => 'required|exists:ciclos,id',
            'alumnos' => 'required|array',
            'alumnos.*' => 'exists:alumnos,id', // Validar contra la tabla 'alumnos'
        ]);

        $nuevoCicloId = $request->input('nuevo_ciclo_id');
        $alumnosIds = $request->input('alumnos');

        // Actualizar la tabla 'alumnos'
        Alumno::whereIn('id', $alumnosIds)->update(['ciclo_id' => $nuevoCicloId]);
        $usuariosIds = Alumno::whereIn('id', $alumnosIds)->pluck('user_id')->toArray();
        User::whereIn('id', $usuariosIds)->update(['ciclo_id' => $nuevoCicloId]);

        return redirect()->back()->with('success', 'Ciclo actualizado exitosamente para los alumnos seleccionados.');
    }


    public function edit(Ciclo $ciclo)
    {
        $ciclo->load('programa');
        $programas = Programa::all();
        return view('admin.ciclo.edit', compact('ciclo', 'programas'));
    }

    public function update(Request $request, Ciclo $ciclo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $ciclo->update($request->all());

        return redirect()->route('ciclo.index')->with('success', 'Ciclo actualizado exitosamente');
    }

    public function destroy(Ciclo $ciclo)
    {
        $ciclo->delete();

        return redirect()->route('ciclo.index')->with('success', 'Ciclo eliminado exitosamente');
    }
}
