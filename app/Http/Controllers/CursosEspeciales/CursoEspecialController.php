<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Models\CursosEspeciales\CursoEspecial;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CursoEspecialController extends Controller
{
    public function index()
    {
        $cursos = CursoEspecial::with('niveles.unidades')->orderBy('orden')->orderBy('nombre')->get();

        return view('cursos-especiales.admin.index', compact('cursos'));
    }

    public function create()
    {
        $docentes = Docente::orderBy('nombre')->get();
        return view('cursos-especiales.admin.create', compact('docentes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo'      => 'boolean',
            'orden'       => 'nullable|integer|min:0',
            'docente_id'  => 'nullable|exists:docentes,id',
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('cursos-especiales', 'public');
        }

        $data['activo'] = $request->boolean('activo', true);
        CursoEspecial::create($data);
        return redirect()->route('ce.cursos.index')->with('success', 'Curso creado correctamente.');
    }

    public function show(CursoEspecial $curso)
    {
        $curso->load('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        return view('cursos-especiales.admin.show', compact('curso'));
    }

    public function edit(CursoEspecial $curso)
    {
        $docentes = Docente::orderBy('nombre')->get();
        return view('cursos-especiales.admin.edit', compact('curso', 'docentes'));
    }

    public function update(Request $request, CursoEspecial $curso)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo'      => 'boolean',
            'orden'       => 'nullable|integer|min:0',
            'docente_id'  => 'nullable|exists:docentes,id',
        ]);

        if ($request->hasFile('imagen')) {
            if ($curso->imagen) {
                Storage::disk('public')->delete($curso->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('cursos-especiales', 'public');
        }

        $data['activo'] = $request->boolean('activo');

        $curso->update($data);

        return redirect()->route('ce.cursos.index')->with('success', 'Curso actualizado.');
    }

    public function destroy(CursoEspecial $curso)
    {
        if ($curso->imagen) {
            Storage::disk('public')->delete($curso->imagen);
        }

        $curso->delete();

        return redirect()->route('ce.cursos.index')->with('success', 'Curso eliminado.');
    }
}
