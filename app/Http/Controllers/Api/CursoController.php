<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalificacionResource;
use App\Http\Resources\CursoResource;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\PeriodoActual;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        $cursos = $this->cursosDelPeriodoActual($alumno);

        return response()->json(['data' => CursoResource::collection($cursos->sortBy('nombre')->values())]);
    }

    public function calificaciones(Request $request)
    {
        $alumno = $request->user()->alumno;

        if (! $alumno) {
            return response()->json(['message' => 'No se encontró un perfil de alumno para este usuario.'], 404);
        }

        // Misma fuente que AlumnoController@calificaciones (web): el modelo
        // `Periodo` (tabla `periodos`), no `Calificacion` (`calificacions`,
        // que está vacía en producción — nadie escribe ahí). `Periodo` sí
        // trae `periodo_actual_id`, por eso permite agrupar por período.
        $periodos = $alumno->periodo()->with(['curso', 'periodoActual'])->get();

        $anteriores = $periodos
            ->groupBy(fn ($p) => optional($p->periodoActual)->nombre ?? 'Sin periodo')
            ->sortKeys()
            ->map(fn ($grupo) => CalificacionResource::collection($grupo->values()));

        // Período actual: mismos cursos que GET /cursos, cada uno con sus
        // notas de Parcial 1 (`PeriodoUno`), Parcial 2 (`PeriodoDos`) y
        // Promedio (`PeriodoTres`) — tres modelos separados de `Periodo`,
        // igual que `calificaciones.blade.php` en la web. Ninguno de los
        // tres se filtra por `periodo_actual_id` aquí (la web tampoco lo
        // hace para Parcial 1/2/Promedio): se replica ese comportamiento
        // real, no se "corrige" sin que se pida.
        $periodoActual = PeriodoActual::actual();
        $cursosPeriodoActual = $this->cursosDelPeriodoActual($alumno)
            ->sortBy('nombre')
            ->values()
            ->map(function (Curso $curso) use ($alumno) {
                $p1 = $curso->periodos()->where('alumno_id', $alumno->id)->first();
                $p2 = $curso->periododos()->where('alumno_id', $alumno->id)->first();
                $p3 = $curso->periodotres()->where('alumno_id', $alumno->id)->first();

                return [
                    'curso_id' => $curso->id,
                    'curso_nombre' => $curso->nombre,
                    'parcial_1' => $this->notaParcial($p1),
                    'parcial_2' => $this->notaParcial($p2),
                    'promedio' => $this->notaParcial($p3),
                    'observaciones' => $p2?->observaciones ?? $p1?->observaciones,
                ];
            });

        return response()->json([
            'data' => [
                'periodo_actual' => [
                    'periodo_nombre' => optional($periodoActual)->nombre,
                    'cursos' => $cursosPeriodoActual,
                ],
                'anteriores' => $anteriores,
            ],
        ]);
    }

    private function notaParcial($parcial): ?array
    {
        if (! $parcial) {
            return null;
        }

        return [
            'valoracion_curso' => $parcial->valoracion_curso,
            'calificacion_curso' => $parcial->calificacion_curso,
            'calificacion_sistema' => $parcial->calificacion_sistema,
        ];
    }

    /**
     * Misma lógica que `AlumnoController@index`/`@calificaciones` (web):
     * fuente primaria son las asignaciones explícitas en `alumno_cursos`
     * para el período actual; si no hay ninguna, todos los cursos del ciclo
     * propio del alumno.
     */
    private function cursosDelPeriodoActual(Alumno $alumno): Collection
    {
        $periodoActual = PeriodoActual::actual();

        $cursosAsignados = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->with('ciclo')->get()
            : collect();

        return $cursosAsignados->isNotEmpty()
            ? $cursosAsignados
            : ($alumno->ciclo ? $alumno->ciclo->cursos : collect());
    }
}
