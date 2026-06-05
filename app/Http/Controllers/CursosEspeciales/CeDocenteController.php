<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CursosEspeciales\CeCursoEstadisticasTrait;
use App\Models\CursosEspeciales\CursoEspecial;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CeDocenteController extends Controller
{
    use CeCursoEstadisticasTrait;

    private function docenteAutenticado(): Docente
    {
        $docente = auth()->user()?->docente;
        abort_unless($docente !== null, 403, 'No tienes un perfil de docente asociado.');

        return $docente;
    }

    private function autorizarCurso(CursoEspecial $curso, Docente $docente): void
    {
        abort_unless($curso->docente_id === $docente->id, 403, 'No tienes acceso a este curso.');
    }

    public function index()
    {
        $docente = $this->docenteAutenticado();
        $cursos = $docente->cursosEspeciales()
            ->with([
                'niveles.unidades.lecciones',
                'niveles.unidades.ejercicios',
                'inscripciones.user.programa',
                'inscripciones.user.ciclo',
            ])
            ->withCount('inscripciones')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        foreach ($cursos as $curso) {
            $curso->promedio_avance = $this->calcularPromedioAvance($curso);
        }

        return view('cursos-especiales.docente.index', compact('cursos', 'docente'));
    }

    public function show(CursoEspecial $curso)
    {
        $docente = $this->docenteAutenticado();
        $this->autorizarCurso($curso, $docente);

        $curso->load([
            'niveles.unidades.lecciones',
            'niveles.unidades.ejercicios',
            'inscripciones.user.programa',
            'inscripciones.user.ciclo',
        ]);

        $estadisticas = $this->generarEstadisticasPorEstudiante($curso);

        $rp = 'ce.docente';
        $layout = 'layouts.docente';

        return view('cursos-especiales.admin.show', array_merge(['curso' => $curso, 'rp' => $rp, 'layout' => $layout, 'docente' => $docente], $estadisticas));
    }

    public function edit(CursoEspecial $curso)
    {
        $docente = $this->docenteAutenticado();
        $this->autorizarCurso($curso, $docente);

        return view('cursos-especiales.docente.edit', compact('curso', 'docente'));
    }

    public function update(Request $request, CursoEspecial $curso)
    {
        $docente = $this->docenteAutenticado();
        $this->autorizarCurso($curso, $docente);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo' => 'boolean',
            'orden' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('imagen')) {
            if ($curso->imagen) {
                Storage::disk('public')->delete($curso->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('cursos-especiales', 'public');
        }

        $data['activo'] = $request->boolean('activo');
        $curso->update($data);

        return redirect()->route('ce.docente.show', $curso)->with('success', 'Curso actualizado correctamente.');
    }
}
