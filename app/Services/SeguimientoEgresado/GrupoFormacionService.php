<?php

namespace App\Services\SeguimientoEgresado;

use App\Enums\Modalidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Dimensión académica, nunca rol ni fuente nueva de identidad. */
class GrupoFormacionService
{
    public const OPCIONES = [
        'TODOS' => 'Todos · FID y PPD',
        'FID' => 'FID · Formación Inicial Docente',
        'PPD' => 'PPD · Programa de Profesionalización Docente',
        'COMPARAR' => 'Comparar FID / PPD',
        'POR_VERIFICAR' => 'Clasificación por verificar',
    ];
    public const RELACIONES = ['programa', 'alumno.programa', 'alumnoB.programa', 'trayectoriaEventos.cicloOrigen.programa', 'trayectoriaEventos.cicloDestino.programa'];

    public function normalizar(?string $grupo): string
    {
        $grupo = strtoupper(trim($grupo ?: 'TODOS'));
        if (!array_key_exists($grupo, self::OPCIONES)) throw new \InvalidArgumentException('Grupo de formación no válido.');
        return $grupo;
    }

    /** Pura y verificable: no deducir FID por ausencia de PPD ni por nombres/roles. */
    public function clasificar(?string $actual, array $fichas, array $egresos): array
    {
        $known = array_column(Modalidad::cases(), 'value');
        $actual = in_array($actual, $known, true) ? $actual : null;
        $fichas = array_values(array_unique(array_intersect($fichas, $known)));
        $egresos = array_values(array_unique(array_intersect($egresos, $known)));
        // Un egreso histórico explícito tiene prioridad: matricularse en otro programa no es egresar.
        $grupos = $egresos ?: ($actual ? [$actual] : (count($fichas) === 1 ? $fichas : []));
        sort($grupos);
        return [
            'grupos' => $grupos,
            'etiqueta' => $grupos ? implode(' y ', $grupos) : 'Por verificar',
            'por_verificar' => !$grupos,
            'conflicto_fichas' => count($fichas) > 1 && !$egresos,
            'fuente' => $egresos ? 'trayectoria_evento_egreso' : ($actual ? 'programa_institucional' : ($grupos ? 'ficha_academica' : 'sin_evidencia_suficiente')),
        ];
    }

    public function resolver(User $user): array
    {
        $mod = fn ($p) => $p?->modalidad instanceof Modalidad ? $p->modalidad->value : null;
        $egresos = []; $detalle = [];
        foreach ($user->trayectoriaEventos as $e) {
            if ($e->tipo !== 'egreso') continue;
            $programa = $e->cicloOrigen?->programa ?? $e->cicloDestino?->programa;
            $grupo = $mod($programa);
            if ($grupo) {
                $egresos[] = $grupo;
                $detalle[] = ['grupo' => $grupo, 'programa_id' => $programa->id, 'programa' => $programa->nombre, 'fecha_egreso' => $e->fecha_inicio?->format('Y-m-d'), 'evento_id' => $e->id];
            }
        }
        $meta = $this->clasificar($mod($user->programa), array_filter([$mod($user->alumno?->programa), $mod($user->alumnoB?->programa)]), $egresos);
        return $meta + ['trayectorias_confirmadas' => $detalle];
    }

    public function coincide(array $grupos, ?string $filtro): bool
    {
        return match ($this->normalizar($filtro)) {
            'FID' => in_array('FID', $grupos, true),
            'PPD' => in_array('PPD', $grupos, true),
            'POR_VERIFICAR' => !$grupos,
            default => true,
        };
    }

    public function ficha(User $user, ?string $filtro = null)
    {
        $filtro = $this->normalizar($filtro);
        if ($filtro === 'FID') return $user->alumno;
        if ($filtro === 'PPD') return $user->alumnoB;
        return $user->programa?->esPpd() ? $user->alumnoB : ($user->alumno ?? $user->alumnoB);
    }

    public function programa(User $user, ?string $filtro = null)
    {
        $filtro = $this->normalizar($filtro);
        if (in_array($filtro, ['FID','PPD'], true)) {
            $ficha = $this->ficha($user, $filtro)?->programa;
            if ($ficha?->modalidad?->value === $filtro) return $ficha;
            foreach ($user->trayectoriaEventos as $e) {
                if ($e->tipo !== 'egreso') continue;
                $p = $e->cicloOrigen?->programa ?? $e->cicloDestino?->programa;
                if ($p?->modalidad?->value === $filtro) return $p;
            }
            return $user->programa?->modalidad?->value === $filtro ? $user->programa : null;
        }
        return $user->programa ?? $this->ficha($user, $filtro)?->programa;
    }

    /** Mantiene filtros/alcances previos: agrega whereIn, jamás los reemplaza. */
    public function aplicar(Builder $query, ?string $grupo): Builder
    {
        $grupo = $this->normalizar($grupo);
        $query->with(self::RELACIONES);
        if (in_array($grupo, ['TODOS', 'COMPARAR'], true)) return $query;
        $ids = (clone $query)->get()->filter(fn ($u) => $this->coincide($this->resolver($u)['grupos'], $grupo))->modelKeys();
        return $query->whereIn('users.id', $ids);
    }

    public function resumen(Collection $usuarios): array
    {
        $sets = ['FID' => [], 'PPD' => [], 'POR_VERIFICAR' => []]; $dual = []; $conflictos = [];
        foreach ($usuarios->unique('id') as $u) {
            $m = $this->resolver($u);
            if (!$m['grupos']) $sets['POR_VERIFICAR'][$u->id] = true;
            foreach ($m['grupos'] as $g) $sets[$g][$u->id] = true;
            if (count($m['grupos']) > 1) $dual[$u->id] = true;
            if ($m['conflicto_fichas']) $conflictos[$u->id] = true;
        }
        return ['personas_unicas' => $usuarios->unique('id')->count(), 'por_grupo' => array_map('count', $sets), 'con_ambos_egresos' => count($dual), 'conflictos_por_revisar' => count($conflictos),
            'nota' => 'FID y PPD pueden incluir a una misma persona con egresos confirmados. No sumar columnas para obtener personas únicas. La comparación no atribuye causalidad y requiere periodos equivalentes.'];
    }
}
