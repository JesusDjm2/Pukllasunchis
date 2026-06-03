<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Models\CursosEspeciales\CeInscripcion;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeProgresoAlumno;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Support\Facades\Auth;

class CeAlumnoController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $cursos = CursoEspecial::where('activo', true)->orderBy('orden')->get();

        $inscritoIds = CeInscripcion::where('user_id', $userId)
            ->pluck('curso_especial_id')
            ->flip();

        $progresoPorCurso = [];
        foreach ($cursos->whereIn('id', $inscritoIds->keys()) as $curso) {
            $progresoPorCurso[$curso->id] = $this->calcularPorcentaje($userId, $curso);
        }

        return view('cursos-especiales.alumno.index', [
            'cursos'           => $cursos,
            'inscrito'         => $inscritoIds,
            'progresoPorCurso' => $progresoPorCurso,
        ]);
    }

    public function inscribir(CursoEspecial $curso)
    {
        CeInscripcion::firstOrCreate(
            ['user_id' => Auth::id(), 'curso_especial_id' => $curso->id],
            ['inscrito_at' => now()]
        );

        return redirect()->route('ce.alumno.show', $curso)
            ->with('success', '¡Te inscribiste correctamente! Bienvenido al curso.');
    }

    public function show(CursoEspecial $curso)
    {
        $this->verificarInscripcion($curso);

        $curso->load('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $userId       = Auth::id();
        $leccionesIds = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->lecciones))->pluck('id');
        $ejerciciosIds = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->ejercicios))->pluck('id');

        $completadas   = CeProgresoAlumno::where('user_id', $userId)
            ->whereIn('ce_leccion_id', $leccionesIds)
            ->where('completado', true)
            ->pluck('ce_leccion_id')
            ->flip();

        $porcentaje = $this->calcularPorcentaje($userId, $curso);

        $totalLecciones  = $leccionesIds->count();
        $completadasCount = $completadas->count();

        return view('cursos-especiales.alumno.show', compact(
            'curso', 'completadas', 'porcentaje', 'totalLecciones', 'completadasCount'
        ));
    }

    public function leccion(CursoEspecial $curso, CeLeccion $leccion)
    {
        $this->verificarInscripcion($curso);

        $leccion->load('unidad.nivel.curso');

        $userId     = Auth::id();
        $completada = CeProgresoAlumno::where('user_id', $userId)
            ->where('ce_leccion_id', $leccion->id)
            ->where('completado', true)
            ->exists();

        $siguiente = $leccion->unidad->lecciones()
            ->where('orden', '>', $leccion->orden)
            ->orderBy('orden')
            ->first();

        $anterior = $leccion->unidad->lecciones()
            ->where('orden', '<', $leccion->orden)
            ->orderBy('orden', 'desc')
            ->first();

        $porcentajeCurso = $this->calcularPorcentaje($userId, $curso);

        return view('cursos-especiales.alumno.leccion', compact(
            'curso', 'leccion', 'completada', 'siguiente', 'anterior', 'porcentajeCurso'
        ));
    }

    public function progreso(CursoEspecial $curso)
    {
        $this->verificarInscripcion($curso);

        $curso->load('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $userId        = Auth::id();
        $leccionesIds  = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->lecciones))->pluck('id');
        $ejerciciosIds = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->ejercicios))->pluck('id');

        $progresoLecciones  = CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_leccion_id', $leccionesIds)->get()->keyBy('ce_leccion_id');
        $progresoEjercicios = CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_ejercicio_id', $ejerciciosIds)->get()->keyBy('ce_ejercicio_id');

        $totalLecciones  = $leccionesIds->count();
        $totalEjercicios = $ejerciciosIds->count();
        $completadasL    = $progresoLecciones->where('completado', true)->count();
        $completadasE    = $progresoEjercicios->where('completado', true)->count();
        $porcentaje      = $totalLecciones + $totalEjercicios > 0
            ? round(($completadasL + $completadasE) / ($totalLecciones + $totalEjercicios) * 100)
            : 0;

        return view('cursos-especiales.alumno.progreso', compact(
            'curso', 'progresoLecciones', 'progresoEjercicios',
            'totalLecciones', 'totalEjercicios', 'completadasL', 'completadasE', 'porcentaje'
        ));
    }

    private function calcularPorcentaje(int $userId, CursoEspecial $curso): int
    {
        $curso->loadMissing('niveles.unidades.lecciones', 'niveles.unidades.ejercicios');

        $leccionesIds  = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->lecciones))->pluck('id');
        $ejerciciosIds = $curso->niveles->flatMap(fn ($n) => $n->unidades->flatMap(fn ($u) => $u->ejercicios))->pluck('id');

        $total = $leccionesIds->count() + $ejerciciosIds->count();
        if ($total === 0) return 0;

        $completadasL = $leccionesIds->isNotEmpty()
            ? CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_leccion_id', $leccionesIds)->where('completado', true)->count()
            : 0;
        $completadasE = $ejerciciosIds->isNotEmpty()
            ? CeProgresoAlumno::where('user_id', $userId)->whereIn('ce_ejercicio_id', $ejerciciosIds)->where('completado', true)->count()
            : 0;

        return (int) round(($completadasL + $completadasE) / $total * 100);
    }

    private function verificarInscripcion(CursoEspecial $curso): void
    {
        if (! CeInscripcion::where('user_id', Auth::id())->where('curso_especial_id', $curso->id)->exists()) {
            abort(403, 'No estás inscrito en este curso.');
        }
    }
}
