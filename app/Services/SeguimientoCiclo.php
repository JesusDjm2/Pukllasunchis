<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\PeriodoActual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seguimiento académico de un ciclo FID en el periodo vigente (panel del tutor): cómo va cada
 * curso, quién está desaprobando y en qué cursos, con qué nota.
 *
 * Notas EN VIVO, las que los docentes están cargando: periodouno (Parcial 1), periodo_dos
 * (Parcial 2) y periodo_tres (nota final). Por curso vale la más avanzada que exista
 * (final › parcial 2 › parcial 1). Mismas reglas que HistorialAcademico: desaprobado = nota
 * vigesimal menor a 11; un 0 (o vacío) es SIN calificar, no desaprobado.
 */
class SeguimientoCiclo
{
    /** @return array{periodo:?PeriodoActual, alumnos:Collection, cursos:Collection, resumen:array} */
    public static function de(Ciclo $ciclo, Collection $alumnos): array
    {
        $periodo = PeriodoActual::actual();
        // Los egresados conservan su Ciclo X real: no son parte del seguimiento del ciclo.
        $alumnos = $alumnos->reject(fn ($a) => $a->user?->condicion === 'Egresado')->values();
        $ids = $alumnos->pluck('id');

        $notas = [];
        foreach ([1 => 'periodouno', 2 => 'periodo_dos', 3 => 'periodo_tres'] as $n => $tabla) {
            foreach (DB::table($tabla)->whereIn('alumno_id', $ids)->get(['alumno_id', 'curso_id', 'calificacion_sistema']) as $f) {
                $nota = is_numeric($f->calificacion_sistema) && (float) $f->calificacion_sistema > 0 ? (float) $f->calificacion_sistema : null;
                if ($nota !== null) {
                    $notas[$f->alumno_id][$f->curso_id][$n] = $nota;
                }
            }
        }

        // Cursos: los del ciclo y los de otros ciclos que algún alumno lleva con nota (p. ej. pendientes).
        $idsCursos = collect($notas)->flatMap(fn ($c) => array_keys($c))->unique();
        $cursos = Curso::with('docentes')
            ->where(fn ($q) => $q->where('ciclo_id', $ciclo->id)->orWhereIn('id', $idsCursos))
            ->orderBy('nombre')->get()->keyBy('id');

        $etiqueta = [1 => 'Parcial 1', 2 => 'Parcial 2', 3 => 'Final'];
        $filas = $alumnos->map(function ($a) use ($notas, $cursos, $etiqueta) {
            $porCurso = collect($notas[$a->id] ?? [])->map(function ($parciales, $cursoId) use ($cursos, $etiqueta) {
                $ultimo = max(array_keys($parciales));

                return (object) [
                    'curso' => $cursos[$cursoId] ?? null,
                    'p1' => $parciales[1] ?? null,
                    'p2' => $parciales[2] ?? null,
                    'final' => $parciales[3] ?? null,
                    'nota' => $parciales[$ultimo],
                    'de' => $etiqueta[$ultimo],
                    'desaprobado' => $parciales[$ultimo] < HistorialAcademico::NOTA_MINIMA,
                ];
            })->filter(fn ($c) => $c->curso);
            $desaprobados = $porCurso->where('desaprobado', true)->sortBy('nota')->values();

            return (object) [
                'alumno' => $a,
                'notas' => $porCurso,
                'desaprobados' => $desaprobados,
                'promedio' => $porCurso->isNotEmpty() ? round($porCurso->avg('nota'), 1) : null,
                'pendientes' => $a->user ? $a->user->cursosPendientes() : [],
            ];
        });

        $porCurso = $cursos->map(function ($curso) use ($filas, $ciclo) {
            $conNota = $filas->map(fn ($f) => $f->notas->get($curso->id))->filter();
            $desaprobados = $conNota->where('desaprobado', true)->count();

            return (object) [
                'curso' => $curso,
                'del_ciclo' => (int) $curso->ciclo_id === (int) $ciclo->id,
                'con_nota' => $conNota->count(),
                'promedio' => $conNota->isNotEmpty() ? round($conNota->avg('nota'), 1) : null,
                'desaprobados' => $desaprobados,
                'porcentaje' => $conNota->isNotEmpty() ? (int) round($desaprobados * 100 / $conNota->count()) : 0,
            ];
        })
            // Fuera de su ciclo solo interesan los cursos donde alguien tiene nota.
            ->filter(fn ($c) => $c->del_ciclo || $c->con_nota > 0)
            ->sortBy([fn ($a, $b) => $b->desaprobados <=> $a->desaprobados, fn ($a, $b) => $a->curso->nombre <=> $b->curso->nombre])
            ->values();

        $enRiesgo = $filas->filter(fn ($f) => $f->desaprobados->isNotEmpty())
            ->sortBy([fn ($a, $b) => $b->desaprobados->count() <=> $a->desaprobados->count(), fn ($a, $b) => ($a->promedio ?? 99) <=> ($b->promedio ?? 99)])
            ->values();
        $todas = $filas->flatMap(fn ($f) => $f->notas);

        return [
            'periodo' => $periodo,
            'alumnos' => $filas->sortBy(fn ($f) => $f->alumno->apellidos)->values(),
            'en_riesgo' => $enRiesgo,
            'cursos' => $porCurso,
            'resumen' => [
                'alumnos' => $filas->count(),
                'con_notas' => $filas->filter(fn ($f) => $f->notas->isNotEmpty())->count(),
                'en_riesgo' => $enRiesgo->count(),
                'desaprobados' => $todas->where('desaprobado', true)->count(),
                'promedio' => $todas->isNotEmpty() ? round($todas->avg('nota'), 1) : null,
                'publicado' => [1 => $periodo?->notasPublicadas(1), 2 => $periodo?->notasPublicadas(2)],
            ],
        ];
    }
}
