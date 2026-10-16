<?php

namespace App\Http\Controllers;

use App\Enums\Modalidad;
use App\Enums\NivelPrograma;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

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

    /** Reglas comunes de store/update: la clasificación FID/PPD ya no sale del nombre. */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'modalidad' => ['required', new Enum(Modalidad::class)],
            'nivel' => ['nullable', new Enum(NivelPrograma::class)],
        ]);
        $datos['eib'] = $request->boolean('eib');

        return $datos;
    }

    public function store(Request $request)
    {
        Programa::create($this->validar($request));

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
        $programa->update($this->validar($request));

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
