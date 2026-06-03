<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CeUnidad;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Http\Request;

class CeContenidoController extends Controller
{
    private function resolveRp(): string
    {
        $name = request()->route()?->getName() ?? '';
        return str_starts_with($name, 'ce.docente.') ? 'ce.docente' : 'ce.cursos';
    }

    private function resolveLayout(): string
    {
        $name = request()->route()?->getName() ?? '';
        return str_starts_with($name, 'ce.docente.') ? 'layouts.docente' : 'layouts.superadmin';
    }

    private function autorizarDocente(CursoEspecial $curso): void
    {
        if ($this->resolveRp() !== 'ce.docente') {
            return;
        }
        $docente = auth()->user()?->docente;
        abort_unless($docente && $curso->docente_id === $docente->id, 403);
    }

    // ── Niveles ───────────────────────────────────────────────────

    public function nivelesCreate(CursoEspecial $curso)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.niveles.create', compact('curso', 'rp', 'layout'));
    }

    public function nivelesStore(Request $request, CursoEspecial $curso)
    {
        $this->autorizarDocente($curso);
        $curso->niveles()->create($request->validate([
            'nombre' => 'required|string|max:255',
            'orden'  => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Nivel agregado.');
    }

    public function nivelesEdit(CursoEspecial $curso, CeNivel $nivel)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.niveles.edit', compact('curso', 'nivel', 'rp', 'layout'));
    }

    public function nivelesUpdate(Request $request, CursoEspecial $curso, CeNivel $nivel)
    {
        $this->autorizarDocente($curso);
        $nivel->update($request->validate([
            'nombre' => 'required|string|max:255',
            'orden'  => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Nivel actualizado.');
    }

    public function nivelesDestroy(CursoEspecial $curso, CeNivel $nivel)
    {
        $this->autorizarDocente($curso);
        $nivel->delete();
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Nivel eliminado.');
    }

    // ── Unidades ──────────────────────────────────────────────────

    public function unidadesCreate(CursoEspecial $curso, CeNivel $nivel)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.unidades.create', compact('curso', 'nivel', 'rp', 'layout'));
    }

    public function unidadesStore(Request $request, CursoEspecial $curso, CeNivel $nivel)
    {
        $this->autorizarDocente($curso);
        $nivel->unidades()->create($request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden'       => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Unidad agregada.');
    }

    public function unidadesEdit(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.unidades.edit', compact('curso', 'nivel', 'unidad', 'rp', 'layout'));
    }

    public function unidadesUpdate(Request $request, CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $unidad->update($request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden'       => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Unidad actualizada.');
    }

    public function unidadesDestroy(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $unidad->delete();
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Unidad eliminada.');
    }

    // ── Lecciones ─────────────────────────────────────────────────

    public function leccionesCreate(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.lecciones.create', compact('curso', 'nivel', 'unidad', 'rp', 'layout'));
    }

    public function leccionesStore(Request $request, CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $unidad->lecciones()->create($request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo'            => 'required|in:texto,audio,video',
            'contenido_texto' => 'nullable|string',
            'archivo_url'     => 'nullable|string|max:500',
            'duracion_min'    => 'nullable|integer|min:1',
            'orden'           => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Lección agregada.');
    }

    public function leccionesEdit(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeLeccion $leccion)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.lecciones.edit', compact('curso', 'nivel', 'unidad', 'leccion', 'rp', 'layout'));
    }

    public function leccionesUpdate(Request $request, CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeLeccion $leccion)
    {
        $this->autorizarDocente($curso);
        $leccion->update($request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo'            => 'required|in:texto,audio,video',
            'contenido_texto' => 'nullable|string',
            'archivo_url'     => 'nullable|string|max:500',
            'duracion_min'    => 'nullable|integer|min:1',
            'orden'           => 'nullable|integer|min:0',
        ]));
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Lección actualizada.');
    }

    public function leccionesDestroy(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeLeccion $leccion)
    {
        $this->autorizarDocente($curso);
        $leccion->delete();
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Lección eliminada.');
    }

    // ── Ejercicios ────────────────────────────────────────────────

    public function ejerciciosCreate(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.ejercicios.create', compact('curso', 'nivel', 'unidad', 'rp', 'layout'));
    }

    public function ejerciciosStore(Request $request, CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad)
    {
        $this->autorizarDocente($curso);
        $data = $request->validate([
            'tipo'               => 'required|in:multiple,completar,emparejar',
            'pregunta'           => 'required|string',
            'opciones_raw'       => 'nullable|string',
            'respuesta_correcta' => 'required|string',
            'puntaje_max'        => 'nullable|integer|min:1',
            'orden'              => 'nullable|integer|min:0',
        ]);
        $data['opciones'] = $this->parsearOpciones($request->input('opciones_raw'));
        unset($data['opciones_raw']);
        $unidad->ejercicios()->create($data);
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Ejercicio agregado.');
    }

    public function ejerciciosEdit(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeEjercicio $ejercicio)
    {
        $this->autorizarDocente($curso);
        $rp = $this->resolveRp();
        $layout = $this->resolveLayout();
        return view('cursos-especiales.admin.ejercicios.edit', compact('curso', 'nivel', 'unidad', 'ejercicio', 'rp', 'layout'));
    }

    public function ejerciciosUpdate(Request $request, CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeEjercicio $ejercicio)
    {
        $this->autorizarDocente($curso);
        $data = $request->validate([
            'tipo'               => 'required|in:multiple,completar,emparejar',
            'pregunta'           => 'required|string',
            'opciones_raw'       => 'nullable|string',
            'respuesta_correcta' => 'required|string',
            'puntaje_max'        => 'nullable|integer|min:1',
            'orden'              => 'nullable|integer|min:0',
        ]);
        $data['opciones'] = $this->parsearOpciones($request->input('opciones_raw'));
        unset($data['opciones_raw']);
        $ejercicio->update($data);
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Ejercicio actualizado.');
    }

    public function ejerciciosDestroy(CursoEspecial $curso, CeNivel $nivel, CeUnidad $unidad, CeEjercicio $ejercicio)
    {
        $this->autorizarDocente($curso);
        $ejercicio->delete();
        return redirect()->route($this->resolveRp() . '.show', $curso)->with('success', 'Ejercicio eliminado.');
    }

    private function parsearOpciones(?string $raw): ?array
    {
        if (blank($raw)) {
            return null;
        }
        return array_values(array_filter(
            array_map('trim', explode("\n", $raw)),
            fn ($linea) => $linea !== ''
        ));
    }
}
