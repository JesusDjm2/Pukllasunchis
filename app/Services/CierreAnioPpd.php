<?php

namespace App\Services;

use App\Models\PeriodoActualPpd;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Cierre del año PPD: el programa dura un año, así que al terminarlo la promoción pasa a
 * EGRESADA (mismo término que FID: es la condición previa al título). Arma la vista previa
 * de quiénes egresan y quiénes no (con el motivo), para que administración confirme.
 *
 * Promoción del periodo = estudiantes PPD aún no egresados cuyo año (PromocionPpd) coincide
 * con el del periodo ("PPD 2026" → 2026).
 */
class CierreAnioPpd
{
    public static function anioDe(PeriodoActualPpd $periodo): ?int
    {
        return preg_match('/(20\d{2})/', $periodo->nombre, $m) ? (int) $m[1] : null;
    }

    /** @return Collection<int, object{u: User, egresa: bool, motivos: string[]}> */
    public static function candidatos(PeriodoActualPpd $periodo): Collection
    {
        $anio = self::anioDe($periodo);
        if (! $anio) {
            return collect();
        }

        $usuarios = User::with(['programa', 'ciclo', 'roles', 'alumnoB'])
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['alumnoB', 'inhabilitado']))
            ->whereHas('programa', fn ($q) => $q->ppd())
            ->where(fn ($q) => $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado'))
            ->orderBy('apellidos')->orderBy('name')->get();

        $anios = PromocionPpd::anios($usuarios);
        $usuarios = $usuarios->filter(fn ($u) => ($anios[$u->id] ?? null) === $anio)->values();
        $fichas = $usuarios->pluck('alumnoB.id')->filter();

        // Matrícula del 2.º semestre de este periodo y cursos desaprobados en sus notas archivadas
        $conSegundo = DB::table('matriculas_ppd')->where('periodo_actual_ppd_id', $periodo->id)
            ->where('semestre', 2)->whereIn('ppd_id', $fichas)->pluck('ppd_id')->flip();
        $desaprobados = DB::table('periodo_ppds')->where('periodo_actual_ppd_id', $periodo->id)
            ->whereIn('alumno_id', $fichas)
            ->whereRaw('calificacion_sistema + 0 > 0 AND calificacion_sistema + 0 < ?', [HistorialAcademico::NOTA_MINIMA])
            ->selectRaw('alumno_id, COUNT(*) n')->groupBy('alumno_id')->pluck('n', 'alumno_id');

        return $usuarios->map(function (User $u) use ($conSegundo, $desaprobados) {
            $fichaId = $u->alumnoB?->id;
            $motivos = [];
            if ($u->perfil === 'Retirado') {
                $motivos[] = 'Retirado';
            } elseif ($u->perfil === 'Licencia') {
                $motivos[] = 'Con licencia';
            } elseif ($u->roles->contains('name', 'inhabilitado')) {
                $motivos[] = 'Inhabilitado' . ($u->perfil ? ' (' . $u->perfil . ')' : '');
            }
            if (! $fichaId) {
                $motivos[] = 'Sin ficha de matrícula';
            } elseif (! isset($conSegundo[$fichaId])) {
                $motivos[] = 'Sin matrícula del 2.º semestre';
            }
            if ($fichaId && ($n = $desaprobados[$fichaId] ?? 0)) {
                $motivos[] = $n . ' curso(s) desaprobado(s)';
            }

            return (object) ['u' => $u, 'egresa' => empty($motivos), 'motivos' => $motivos];
        });
    }

    /** ¿Ya se archivaron las notas del año? Sin ellas no se pueden detectar desaprobados. */
    public static function notasArchivadas(PeriodoActualPpd $periodo): bool
    {
        return DB::table('periodo_ppds')->where('periodo_actual_ppd_id', $periodo->id)->exists();
    }
}
