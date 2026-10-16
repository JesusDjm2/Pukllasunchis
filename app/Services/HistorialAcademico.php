<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Historial académico de un estudiante, periodo por periodo:
 * en qué ciclo estaba, qué cursos llevó, con qué nota, promedio, desaprobados y si
 * repitió el ciclo. Fuentes:
 *  - notas finales archivadas: `periodos` (FID, alumno_id = alumnos.id) y
 *    `periodo_ppds` (PPD, alumno_id = ppds.id). No hay notas anteriores a 2024-II.
 *  - ciclo del periodo: la foto guardada en la matrícula; si no hay, el ciclo más
 *    frecuente entre sus cursos con nota ese periodo.
 */
class HistorialAcademico
{
    /** Reglamento: desaprobado = nota vigesimal menor a 11. */
    public const NOTA_MINIMA = 11;

    public static function de(User $user): array
    {
        $periodos = collect();

        if ($alumno = $user->alumno) {
            $periodos = $periodos->merge(self::periodosFid($alumno->id));
        }
        if ($ppd = $user->alumnoB) {
            $periodos = $periodos->merge(self::periodosPpd($ppd->id));
        }

        $periodos = self::marcarRepitencia($periodos->values());
        // El resumen solo cuenta notas oficiales (archivadas), no las del periodo en curso.
        $oficiales = $periodos->flatMap(fn ($p) => $p->cursos)->reject(fn ($c) => $c->en_curso);
        $notas = $oficiales->pluck('nota')->filter(fn ($n) => $n !== null);

        return [
            'periodos' => $periodos,
            'resumen' => [
                'periodos' => $periodos->count(),
                'cursos' => $oficiales->count(),
                'promedio' => $notas->isNotEmpty() ? round($notas->avg(), 2) : null,
                'desaprobados' => $oficiales->where('desaprobado', true)->count(),
                'repitencias' => $periodos->where('repite', true)->values(),
            ],
        ];
    }

    private static function periodosFid(int $alumnoId): Collection
    {
        $notas = DB::table('periodos as p')
            ->join('periodo_actual as pa', 'pa.id', '=', 'p.periodo_actual_id')
            ->where('p.alumno_id', $alumnoId)
            ->select('p.periodo_actual_id as periodo_id', 'pa.nombre as periodo', 'p.curso_id',
                'p.calificacion_sistema', 'p.calificacion_curso')
            ->get();

        // Parciales archivados (periodo_parciales) y, del periodo vigente, lo que los docentes
        // están cargando AHORA en periodouno / periodo_dos / periodo_tres (aún sin archivar).
        $parciales = DB::table('periodo_parciales')->where('alumno_id', $alumnoId)
            ->get(['periodo_actual_id', 'curso_id', 'parcial', 'calificacion_sistema']);
        $vigente = \App\Models\PeriodoActual::actual();
        if ($vigente) {
            foreach ([1 => 'periodouno', 2 => 'periodo_dos'] as $n => $tabla) {
                foreach (DB::table($tabla)->where('alumno_id', $alumnoId)->get(['curso_id', 'calificacion_sistema']) as $f) {
                    $parciales->push((object) ['periodo_actual_id' => $vigente->id, 'curso_id' => $f->curso_id, 'parcial' => $n, 'calificacion_sistema' => $f->calificacion_sistema]);
                }
            }
            if (! $notas->contains('periodo_id', $vigente->id)) {
                foreach (DB::table('periodo_tres')->where('alumno_id', $alumnoId)->get(['curso_id', 'calificacion_sistema', 'calificacion_curso']) as $f) {
                    $notas->push((object) ['periodo_id' => $vigente->id, 'periodo' => $vigente->nombre, 'curso_id' => $f->curso_id,
                        'calificacion_sistema' => $f->calificacion_sistema, 'calificacion_curso' => $f->calificacion_curso, 'en_curso' => true]);
                }
            }
        }
        // Cursos que solo tienen parciales (aún sin nota final) también deben aparecer.
        foreach ($parciales->groupBy(fn ($p) => $p->periodo_actual_id . '-' . $p->curso_id) as $grupo) {
            $p = $grupo->first();
            if (! $notas->first(fn ($n) => $n->periodo_id == $p->periodo_actual_id && $n->curso_id == $p->curso_id)) {
                $notas->push((object) ['periodo_id' => $p->periodo_actual_id, 'periodo' => null, 'curso_id' => $p->curso_id,
                    'calificacion_sistema' => null, 'calificacion_curso' => null, 'en_curso' => $vigente && $p->periodo_actual_id == $vigente->id]);
            }
        }
        // Los parciales del periodo vigente en vivo pisan a los archivados (son más recientes).
        $mapaParciales = [];
        foreach ($parciales as $p) {
            $mapaParciales[$p->periodo_actual_id][$p->curso_id][$p->parcial] = self::numero($p->calificacion_sistema);
        }
        foreach ($notas as $n) {
            $n->p1 = $mapaParciales[$n->periodo_id][$n->curso_id][1] ?? null;
            $n->p2 = $mapaParciales[$n->periodo_id][$n->curso_id][2] ?? null;
        }
        $nombres = DB::table('periodo_actual')->pluck('nombre', 'id');
        foreach ($notas as $n) {
            $n->periodo ??= $nombres[$n->periodo_id] ?? null;
        }
        $matriculas = DB::table('matriculas as m')
            ->join('periodo_actual as pa', 'pa.id', '=', 'm.periodo_actual_id')
            ->where('m.alumno_id', $alumnoId)
            ->select('m.periodo_actual_id as periodo_id', 'pa.nombre as periodo', 'm.ciclo_id', 'm.condicion', 'm.estado')
            ->get()->keyBy('periodo_id');

        return self::armar('FID', $notas, $matriculas);
    }

    private static function periodosPpd(int $ppdId): Collection
    {
        $notas = DB::table('periodo_ppds as p')
            ->leftJoin('periodo_actual_ppds as pa', 'pa.id', '=', 'p.periodo_actual_ppd_id')
            ->where('p.alumno_id', $ppdId)
            ->select('p.periodo_actual_ppd_id as periodo_id', 'pa.nombre as periodo', 'p.curso_id',
                'p.calificacion_sistema', 'p.calificacion_curso')
            ->get();
        $matriculas = DB::table('matriculas_ppd as m')
            ->join('periodo_actual_ppds as pa', 'pa.id', '=', 'm.periodo_actual_ppd_id')
            ->where('m.ppd_id', $ppdId)
            ->orderBy('m.semestre') // PPD: dos matrículas por año; queda la del semestre más reciente
            ->select('m.periodo_actual_ppd_id as periodo_id', 'pa.nombre as periodo', 'm.ciclo_id', 'm.condicion', 'm.estado')
            ->get()->keyBy('periodo_id');

        return self::armar('PPD', $notas, $matriculas);
    }

    private static function armar(string $modalidad, Collection $notas, Collection $matriculas): Collection
    {
        $cursos = Curso::with('ciclo')->whereIn('id', $notas->pluck('curso_id')->unique())->get()->keyBy('id');
        $ids = $notas->pluck('periodo_id')->merge($matriculas->keys())->unique()->sort()->values();
        $ciclos = Ciclo::whereIn('id', $matriculas->pluck('ciclo_id')->filter())->get()->keyBy('id');

        return $ids->map(function ($periodoId) use ($modalidad, $notas, $matriculas, $cursos, $ciclos) {
            $filas = $notas->where('periodo_id', $periodoId);
            $matricula = $matriculas->get($periodoId);

            $cursosPeriodo = $filas->map(function ($f) use ($cursos) {
                $nota = self::numero($f->calificacion_sistema);

                return (object) [
                    'curso' => $cursos[$f->curso_id] ?? null,
                    'nota' => $nota,
                    'p1' => $f->p1 ?? null,
                    'p2' => $f->p2 ?? null,
                    'logro' => in_array($f->calificacion_curso, ['', '0', '-', null], true) ? null : $f->calificacion_curso,
                    'desaprobado' => $nota !== null && $nota < self::NOTA_MINIMA,
                    'en_curso' => ! empty($f->en_curso),
                ];
            })->sortBy(fn ($c) => $c->curso?->nombre)->values();

            // Ciclo del periodo: la foto de la matrícula; si no hay, el más frecuente entre sus cursos.
            $ciclo = $matricula?->ciclo_id ? $ciclos[$matricula->ciclo_id] ?? null : null;
            $fuente = $ciclo ? 'matricula' : null;
            if (! $ciclo && $cursosPeriodo->isNotEmpty()) {
                // El más frecuente; si empatan, el de id mayor (igual que la reconstrucción de matrículas).
                $ciclo = $cursosPeriodo->pluck('curso.ciclo')->filter()->groupBy('id')
                    ->sortBy([fn ($a, $b) => $b->count() <=> $a->count(), fn ($a, $b) => $b->first()->id <=> $a->first()->id])
                    ->first()?->first();
                $fuente = $ciclo ? 'notas' : null;
            }

            $conNota = $cursosPeriodo->pluck('nota')->filter(fn ($n) => $n !== null);

            return (object) [
                'modalidad' => $modalidad,
                'periodo_id' => $periodoId,
                'periodo' => $filas->first()->periodo ?? $matricula?->periodo ?? "Periodo {$modalidad} #{$periodoId} (ya no existe)",
                'ciclo' => $ciclo,
                'ciclo_fuente' => $fuente,
                'matriculado' => (bool) $matricula,
                'condicion' => $matricula?->condicion,
                'cursos' => $cursosPeriodo,
                'promedio' => $conNota->isNotEmpty() ? round($conNota->avg(), 2) : null,
                'desaprobados' => $cursosPeriodo->where('desaprobado', true)->count(),
                'en_curso' => $cursosPeriodo->contains('en_curso', true),
                'tiene_parciales' => $cursosPeriodo->contains(fn ($c) => $c->p1 !== null || $c->p2 !== null),
                'repite' => false,
            ];
        });
    }

    /**
     * Nota numérica o null. La escala real va de 1 a 20 (sale de la valoración 1–4); un 0 viene
     * con logro "0", "-" o vacío: es un curso SIN calificar, no un desaprobado.
     */
    private static function numero($valor): ?float
    {
        return is_numeric($valor) && (float) $valor > 0 ? (float) $valor : null;
    }

    /**
     * Repitencia: el mismo ciclo (I–X, del mismo programa) en un periodo posterior.
     * Los ciclos que no son I–X ("Egresados 2025-I", "X - A") no cuentan.
     */
    private static function marcarRepitencia(Collection $periodos): Collection
    {
        $vistos = [];

        return $periodos->each(function ($p) use (&$vistos) {
            if (! $p->ciclo || $p->ciclo->ordenCiclo() >= 999) {
                return;
            }
            $clave = $p->modalidad . '-' . $p->ciclo->programa_id . '-' . $p->ciclo->ordenCiclo();
            $p->repite = isset($vistos[$clave]);
            $vistos[$clave] = true;
        });
    }
}
