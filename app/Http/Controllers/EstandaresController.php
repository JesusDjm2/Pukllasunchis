<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Estandares;
use DB;
use Illuminate\Http\Request;

class EstandaresController extends Controller
{
    public function index()
    {
        $estandares = Estandares::with(['ciclos', 'competencias'])->get();
        return view('admin.ciclo.estandares.index', compact('estandares'));
    }

    public function create()
    {
        $ciclos = Ciclo::with('programa')->get();
        $competencias = Competencia::all();
        return view('admin.ciclo.estandares.create', compact('ciclos', 'competencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'ciclos' => 'required|array',
            'ciclos.*' => 'exists:ciclos,id',
            'competencias' => 'required|array',
            'competencias.*' => 'exists:competencias,id',
        ]);

        $estandar = Estandares::create([
            'descripcion' => $request->descripcion
        ]);

        // Relacionar el estándar con los ciclos seleccionados
        foreach ($request->ciclos as $ciclo_id) {
            DB::table('ciclo_competencia_estandar')->insert([
                'estandar_id' => $estandar->id,
                'ciclo_id' => $ciclo_id,
                'competencia_id' => null, // Ahora puede ser NULL
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Relacionar el estándar con las competencias seleccionadas
        foreach ($request->competencias as $competencia_id) {
            DB::table('ciclo_competencia_estandar')->insert([
                'estandar_id' => $estandar->id,
                'ciclo_id' => null, // Ahora puede ser NULL
                'competencia_id' => $competencia_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('estandares.index')->with('success', 'Estándar creado correctamente.');
    }

    public function edit($id)
    {
        $estandar = Estandares::findOrFail($id);
        $ciclos = Ciclo::all();
        $competencias = Competencia::all();
        return view('admin.ciclo.estandares.edit', compact('estandar', 'ciclos', 'competencias'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'ciclos' => 'nullable|array',
            'ciclos.*' => 'exists:ciclos,id',
            'competencias' => 'nullable|array',
            'competencias.*' => 'exists:competencias,id',
        ]);

        $estandar = Estandares::findOrFail($id);
        $estandar->update([
            'descripcion' => $request->descripcion
        ]);

        // **Eliminar relaciones antiguas antes de insertar nuevas**
        DB::table('ciclo_competencia_estandar')->where('estandar_id', $estandar->id)->delete();

        // **Insertar los nuevos ciclos seleccionados**
        if ($request->has('ciclos')) {
            foreach ($request->ciclos as $ciclo_id) {
                DB::table('ciclo_competencia_estandar')->insert([
                    'estandar_id' => $estandar->id,
                    'ciclo_id' => $ciclo_id,
                    'competencia_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // **Insertar las nuevas competencias seleccionadas**
        if ($request->has('competencias')) {
            foreach ($request->competencias as $competencia_id) {
                DB::table('ciclo_competencia_estandar')->insert([
                    'estandar_id' => $estandar->id,
                    'ciclo_id' => null,
                    'competencia_id' => $competencia_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('estandares.index')->with('success', 'Estándar actualizado correctamente.');
    }

    public function destroy($id)
    {
        $estandar = Estandares::findOrFail($id);
        DB::table('ciclo_competencia_estandar')->where('estandar_id', $estandar->id)->delete();
        $estandar->delete();
        return redirect()->route('estandares.index')->with('success', 'Estándar eliminado correctamente.');
    }


}
