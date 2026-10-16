<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\PeriodoActual;
use App\Models\SeguimientoAccion;
use App\Models\TrayectoriaEvento;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Panel institucional de seguimiento: TODOS los estudiantes que requieren atención, en una
 * sola pasada. Mismas reglas que el expediente individual (HistorialAcademico / Trayectoria)
 * pero calculadas en bloque, para no recorrer alumno por alumno (≈4 s con ~560 alumnos).
 *
 * Universo: estudiantes (alumno / alumnoB / inhabilitado) que NO son egresados.
 */
class PanelInstitucional
{
    public const LISTAS = [
        'licencias' => ['Licencias activas', 'fa-pause-circle', 'amber'],
        'licencia_limite' => ['Licencia por agotarse o agotada', 'fa-hourglass-end', 'red'],
        'licencia_sin_fecha' => ['Licencias sin fecha de inicio', 'fa-calendar-plus', 'slate'],
        'retirados' => ['Retirados', 'fa-door-open', 'red'],
        'desaprobados' => ['Desaprobaron en el último periodo', 'fa-exclamation-circle', 'red'],
        'repitentes' => ['Repitieron ciclo', 'fa-redo', 'amber'],
        'reincorporados' => ['Reincorporados', 'fa-undo-alt', 'green'],
        'seguimiento_vencido' => ['Seguimiento con revisión vencida', 'fa-calendar-times', 'violet'],
    ];

    /** Días de saldo de licencia por debajo de los cuales se avisa. */
    public const AVISO_LICENCIA_DIAS = 90;

    public static function calcular(array $filtros = []): array
    {
        $usuarios = User::with(['programa', 'ciclo', 'alumno:id,user_id', 'alumnoB:id,user_id'])
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['alumno', 'alumnoB', 'inhabilitado']))
            ->where(fn ($q) => $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado'))
            ->when($filtros['programa_id'] ?? null, fn ($q, $v) => $q->where('programa_id', $v))
            ->when($filtros['ciclo_id'] ?? null, fn ($q, $v) => $q->where('ciclo_id', $v))
            ->orderBy('apellidos')->orderBy('name')
            ->get()->keyBy('id');
        $ids = $usuarios->keys();

        // Una colección NUEVA por lista (array_fill_keys repetiría la misma instancia en todas).
        $listas = [];
        foreach (array_keys(self::LISTAS) as $k) {
            $listas[$k] = collect();
        }
        $item = fn (User $u, string $detalle, $orden = 0) => (object) ['u' => $u, 'detalle' => $detalle, 'orden' => $orden];

        // ── Licencias (acumulado de 730 días, igual que Trayectoria::licenciaAcumulada) ──
        $hoy = now()->startOfDay();
        $licencias = TrayectoriaEvento::where('tipo', 'licencia')->whereIn('user_id', $ids)->get()->groupBy('user_id');
        foreach ($licencias as $userId => $ls) {
            $u = $usuarios[$userId];
            $dias = $ls->filter(fn ($l) => $l->fecha_inicio)
                ->sum(fn ($l) => $l->fecha_inicio->diffInDays(($l->fecha_fin ?? $hoy)->copy()->min($hoy)));
            $restante = max(0, TrayectoriaEvento::LICENCIA_MAX_DIAS - $dias);
            $activa = $ls->first(fn ($l) => $l->fecha_fin === null);

            if ($activa) {
                $listas['licencias']->push($item($u, $activa->fecha_inicio
                    ? 'Desde ' . $activa->fecha_inicio->format('d/m/Y') . " · acumulado {$dias} días"
                    : 'Fecha de inicio por completar', $activa->fecha_inicio?->timestamp ?? 0));
            }
            if ($ls->contains(fn ($l) => ! $l->fecha_inicio)) {
                $listas['licencia_sin_fecha']->push($item($u, 'Completar la fecha en su expediente para controlar el máximo de 2 años'));
            }
            if ($dias >= TrayectoriaEvento::LICENCIA_MAX_DIAS) {
                $listas['licencia_limite']->push($item($u, "Agotada: {$dias} de " . TrayectoriaEvento::LICENCIA_MAX_DIAS . ' días. No puede pedir otra licencia.', -1));
            } elseif ($activa && $restante <= self::AVISO_LICENCIA_DIAS) {
                $listas['licencia_limite']->push($item($u, "Le quedan {$restante} días (agota el " . $hoy->copy()->addDays($restante)->format('d/m/Y') . ')', $restante));
            }
        }

        // ── Retirados (estado actual) ──
        foreach ($usuarios->where('perfil', 'Retirado') as $u) {
            $listas['retirados']->push($item($u, $u->ciclo ? 'Último ciclo: ' . $u->ciclo->nombre : ''));
        }

        // ── Reincorporados: condición «Reincorporación» en su perfil o reincorporación registrada
        // en los últimos 12 meses. Las que se registraron al iniciar la trayectoria no tienen fecha:
        // antes quedaban fuera de la lista aunque el perfil dijera «Reincorporación».
        $eventos = TrayectoriaEvento::where('tipo', 'reincorporacion')->whereIn('user_id', $ids)
            ->orderByRaw('fecha_inicio IS NULL')->orderByDesc('fecha_inicio')->get()->groupBy('user_id');
        $desde = now()->subMonths(12)->startOfDay();
        foreach ($usuarios as $u) {
            $e = optional($eventos->get($u->id))->first();
            $reciente = $e?->fecha_inicio && $e->fecha_inicio->gte($desde);
            if ($u->condicion !== 'Reincorporación' && ! $reciente) {
                continue;
            }
            $detalle = $e?->fecha_inicio
                ? 'El ' . $e->fecha_inicio->format('d/m/Y') . ($e->valor_anterior ? " (venía de {$e->valor_anterior})" : '')
                : 'Fecha de reincorporación por completar en su expediente';
            $listas['reincorporados']->push($item($u, $detalle, $e?->fecha_inicio ? -$e->fecha_inicio->timestamp : 0));
        }

        // ── Seguimiento con revisión vencida ──
        SeguimientoAccion::abiertas()->whereIn('user_id', $ids)
            ->whereDate('proxima_revision', '<', $hoy)->orderBy('proxima_revision')->get()->groupBy('user_id')
            ->each(fn ($as, $userId) => $listas['seguimiento_vencido']->push($item($usuarios[$userId],
                $as->count() . ' acción(es) · revisión vencida desde el ' . $as->first()->proxima_revision->format('d/m/Y'), $as->first()->proxima_revision->timestamp)));

        // ── Desaprobados y repitencia (FID: notas archivadas en periodos) ──
        [$desaprobados, $repitentes] = self::academico($usuarios);
        $listas['desaprobados'] = $desaprobados;
        $listas['repitentes'] = $repitentes;

        foreach ($listas as $k => $l) {
            $listas[$k] = $l->sortBy('orden')->values();
        }

        return ['listas' => $listas, 'universo' => $usuarios->count()];
    }

    /**
     * Desaprobados en el último periodo FID cerrado (con notas finales archivadas) y repitencia
     * (mismo ciclo I–X en dos periodos; ciclo = foto de la matrícula o, si no hay, el más
     * frecuente entre sus cursos con nota). Nota 0 = sin calificar (no cuenta).
     */
    private static function academico(Collection $usuarios): array
    {
        $alumnoAUser = $usuarios->filter(fn ($u) => $u->alumno)->mapWithKeys(fn ($u) => [$u->alumno->id => $u->id]);
        $desaprobados = collect();
        $repitentes = collect();
        if ($alumnoAUser->isEmpty()) {
            return [$desaprobados, $repitentes];
        }

        $ultimo = DB::table('periodos')->max('periodo_actual_id');
        $nombreUltimo = PeriodoActual::find($ultimo)?->nombre;
        DB::table('periodos as p')->join('cursos as c', 'c.id', '=', 'p.curso_id')
            ->where('p.periodo_actual_id', $ultimo)->whereIn('p.alumno_id', $alumnoAUser->keys())
            ->whereRaw('p.calificacion_sistema + 0 > 0 AND p.calificacion_sistema + 0 < ?', [HistorialAcademico::NOTA_MINIMA])
            ->select('p.alumno_id', 'c.nombre')->get()->groupBy('alumno_id')
            ->each(function ($cursos, $alumnoId) use ($desaprobados, $usuarios, $alumnoAUser, $nombreUltimo) {
                $desaprobados->push((object) [
                    'u' => $usuarios[$alumnoAUser[$alumnoId]],
                    'detalle' => $cursos->count() . " curso(s) en {$nombreUltimo}: " . $cursos->pluck('nombre')->implode(', '),
                    'orden' => -$cursos->count(),
                ]);
            });

        // Ciclo de cada alumno en cada periodo
        $porNotas = DB::table('periodos as p')->join('cursos as c', 'c.id', '=', 'p.curso_id')
            ->whereIn('p.alumno_id', $alumnoAUser->keys())
            ->select('p.alumno_id', 'p.periodo_actual_id', 'c.ciclo_id', DB::raw('COUNT(*) n'))
            ->groupBy('p.alumno_id', 'p.periodo_actual_id', 'c.ciclo_id')->get()
            ->groupBy(fn ($r) => $r->alumno_id . '-' . $r->periodo_actual_id)
            // Mismo desempate que la reconstrucción de matrículas: más cursos y, si empatan, ciclo de id mayor.
            ->map(fn ($g) => $g->sortBy([['n', 'desc'], ['ciclo_id', 'desc']])->first()->ciclo_id);
        $porMatricula = DB::table('matriculas')->whereIn('alumno_id', $alumnoAUser->keys())->whereNotNull('ciclo_id')
            ->get(['alumno_id', 'periodo_actual_id', 'ciclo_id'])
            ->mapWithKeys(fn ($m) => [$m->alumno_id . '-' . $m->periodo_actual_id => $m->ciclo_id]);
        $cicloDe = $porNotas->merge($porMatricula); // la matrícula manda sobre lo deducido
        $ciclos = Ciclo::whereIn('id', $cicloDe->values()->unique())->get()->keyBy('id');
        $nombresPeriodo = DB::table('periodo_actual')->pluck('nombre', 'id');

        $porAlumno = [];
        foreach ($cicloDe as $clave => $cicloId) {
            [$alumnoId, $periodoId] = explode('-', $clave);
            $porAlumno[$alumnoId][(int) $periodoId] = $ciclos[$cicloId] ?? null;
        }
        foreach ($porAlumno as $alumnoId => $periodos) {
            ksort($periodos);
            $vistos = [];
            $repeticiones = [];
            foreach ($periodos as $periodoId => $ciclo) {
                if (! $ciclo || $ciclo->ordenCiclo() >= 999) {
                    continue;
                }
                $k = $ciclo->programa_id . '-' . $ciclo->ordenCiclo();
                if (isset($vistos[$k])) {
                    $repeticiones[] = 'Ciclo ' . $ciclo->nombre . ' en ' . ($nombresPeriodo[$periodoId] ?? '?');
                }
                $vistos[$k] = true;
            }
            if ($repeticiones && isset($alumnoAUser[$alumnoId])) {
                $repitentes->push((object) [
                    'u' => $usuarios[$alumnoAUser[$alumnoId]],
                    'detalle' => 'Repitió: ' . implode(', ', $repeticiones),
                    'orden' => -count($repeticiones),
                ]);
            }
        }

        return [$desaprobados, $repitentes];
    }
}
