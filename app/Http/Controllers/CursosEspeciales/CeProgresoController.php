<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeInscripcion;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeProgresoAlumno;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Registra progreso: marcar lección completada y resolver ejercicios.
class CeProgresoController extends Controller
{
    public function marcarLeccion(CursoEspecial $curso, CeLeccion $leccion)
    {
        $this->verificarInscripcion($curso);

        CeProgresoAlumno::marcarLeccionCompletada(Auth::id(), $leccion->id);

        return back()->with('success', 'Lección marcada como completada.');
    }

    public function resolverEjercicio(Request $request, CursoEspecial $curso, CeEjercicio $ejercicio)
    {
        $this->verificarInscripcion($curso);

        $request->validate(['respuesta' => 'required|string']);

        $correcta = $ejercicio->esCorrecta($request->respuesta);
        $puntaje  = $correcta ? $ejercicio->puntaje_max : 0;

        CeProgresoAlumno::registrarEjercicio(Auth::id(), $ejercicio->id, $puntaje);

        $mensaje = $correcta ? '¡Correcto! Bien hecho.' : 'Respuesta incorrecta. Inténtalo nuevamente.';

        return back()->with($correcta ? 'success' : 'error', $mensaje);
    }

    private function verificarInscripcion(CursoEspecial $curso): void
    {
        $inscrito = CeInscripcion::where('user_id', Auth::id())
            ->where('curso_especial_id', $curso->id)
            ->exists();

        if (! $inscrito) {
            abort(403);
        }
    }
}
