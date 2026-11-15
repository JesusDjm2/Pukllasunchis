<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Promoción (año) de cada estudiante PPD. El programa dura un año, así que la promoción es
 * el año del periodo PPD que cursó ("PPD 2025", "PPD 2026"), en este orden de confianza:
 *  1. el ciclo asignado si contiene año (p. ej. "Egresados 2023", "Egresados 2024");
 *  2. el periodo de su matrícula más reciente (matriculas_ppd);
 *  3. el periodo de sus notas archivadas (periodo_ppds);
 *  4. el año en que llenó su ficha (ppds.created_at) — p. ej. la promoción 2025 tiene sus
 *     notas en un periodo PPD que ya no existe (#12), así que se reconoce por la ficha;
 *  5. sin ficha: el año en que se creó su cuenta (users.created_at).
 * No cambia datos: se calcula al vuelo, en bloque (consultas mínimas para todos).
 */
class PromocionPpd
{
    /** @return array<int,int> user_id => año */
    public static function anios(Collection $usuarios): array
    {
        $anioCiclos = DB::table('ciclos')->get(['id', 'nombre'])
            ->mapWithKeys(fn ($c) => [$c->id => preg_match('/(20\d{2})/', $c->nombre, $m) ? (int) $m[1] : null]);

        // Sin ficha: si su ciclo indica año, lo usamos; si no, el año de creación de la cuenta.
        $anios = $usuarios->reject(fn ($u) => $u->alumnoB)
            ->mapWithKeys(function ($u) use ($anioCiclos) {
                $cId = $u->ciclo_id;
                $anio = ($cId ? ($anioCiclos[$cId] ?? null) : null)
                    ?? (int) optional($u->created_at)->format('Y');
                return [$u->id => $anio];
            })
            ->filter()->all();

        $fichas = $usuarios->filter(fn ($u) => $u->alumnoB)->mapWithKeys(fn ($u) => [$u->alumnoB->id => $u]);
        if ($fichas->isEmpty()) {
            return $anios;
        }
        $ids = $fichas->keys();

        $anioPeriodo = DB::table('periodo_actual_ppds')->get(['id', 'nombre'])
            ->mapWithKeys(fn ($p) => [$p->id => preg_match('/(20\d{2})/', $p->nombre, $m) ? (int) $m[1] : null]);
        $porMatricula = DB::table('matriculas_ppd')->whereIn('ppd_id', $ids)
            ->selectRaw('ppd_id, MAX(periodo_actual_ppd_id) p')->groupBy('ppd_id')->pluck('p', 'ppd_id');
        $porNotas = DB::table('periodo_ppds')->whereIn('alumno_id', $ids)
            ->selectRaw('alumno_id, MAX(periodo_actual_ppd_id) p')->groupBy('alumno_id')->pluck('p', 'alumno_id');

        foreach ($fichas as $fichaId => $u) {
            $cId = $u->ciclo_id ?? $u->alumnoB?->ciclo_id;
            $anioCiclo = $cId ? ($anioCiclos[$cId] ?? null) : null;

            $anios[$u->id] = $anioCiclo
                ?? $anioPeriodo[$porMatricula[$fichaId] ?? 0]
                ?? $anioPeriodo[$porNotas[$fichaId] ?? 0]
                ?? (int) optional($u->alumnoB->created_at)->format('Y');
        }

        return $anios;
    }
}
