<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Deja la RD del PTI en el formato «088-2021» en los registros importados del Excel (el «número» quedó como
 * nombre de archivo y casi siempre sin año). El número sale del texto; el año, del texto, de la fecha de la
 * RD (seguro) o de la fecha del PTI (estimado, solo si se pide).
 *
 * Cuidado con los grupos: el sistema reconoce a un grupo porque comparten la misma RD del PTI. Antes de
 * aplicar se simula todo dentro de una transacción (que se deshace) y se compara el grupo de cada persona
 * antes y después: si a alguien le cambiarían los compañeros, ese caso NO se toca (queda para revisión).
 * Al aplicar se guarda un respaldo JSON con los valores originales. No cambia PDF, fechas ni estados.
 */
class NormalizarRdPti
{
    public const FUENTES = [
        'texto' => 'Año en el texto',
        'rd' => 'Año por la fecha de la RD',
        'pti' => 'Año estimado por la fecha del PTI',
        'sin_anio' => 'Sin año: completar a mano',
        'ya' => 'Ya está en formato correcto',
        'falta' => 'Sin RD del PTI',
    ];

    /**
     * @return array{filas: Collection, conteo: array, conflictos: int}
     */
    public static function plan(bool $conEstimados): array
    {
        $seguimientos = SeguimientoTitulacion::with(['user:id,name,apellidos,dni', 'pti', 'ptis', 'resoluciones'])->get();

        $filas = $seguimientos->map(function (SeguimientoTitulacion $s) use ($conEstimados) {
            $d = RdPti::de($s);
            $fila = ['seguimiento' => $s, 'texto' => $d['texto'], 'propuesto' => null, 'fuente' => 'falta', 'cambios' => [], 'conflicto' => false];
            if ($d['estado'] === 'falta' || ! $d['numero']) {
                return $fila;
            }
            [$anio, $fuente] = match (true) {
                (bool) $d['anio'] => [$d['anio'], 'texto'],
                (bool) $d['anio_rd'] => [$d['anio_rd'], 'rd'],
                (bool) $d['anio_pti'] => [$d['anio_pti'], 'pti'],
                default => [null, 'sin_anio'],
            };
            $fila['fuente'] = $fuente;
            if (! $anio) {
                return $fila;
            }
            $propuesto = $d['numero'].'-'.$anio;
            $fila['propuesto'] = $propuesto;

            // Se corrigen todos los registros del PTI que se refieren a ese mismo número.
            foreach ($s->ptis as $p) {
                if (filled($p->rd_pti) && RdPti::leer($p->rd_pti)[0] === $d['numero'] && trim($p->rd_pti) !== $propuesto) {
                    $fila['cambios'][] = ['tabla' => 'pti', 'id' => $p->id, 'antes' => $p->rd_pti, 'despues' => $propuesto];
                }
            }
            foreach ($s->resoluciones->where('tipo', 'PTI') as $r) {
                if (filled($r->numero_rd) && RdPti::leer($r->numero_rd)[0] === $d['numero'] && trim($r->numero_rd) !== $propuesto) {
                    $fila['cambios'][] = ['tabla' => 'resolucion', 'id' => $r->id, 'antes' => $r->numero_rd, 'despues' => $propuesto];
                }
            }
            if (empty($fila['cambios'])) {
                $fila['fuente'] = 'ya';
            } elseif ($fuente === 'pti' && ! $conEstimados) {
                $fila['cambios'] = []; // estimado: solo si se pide
            }

            return $fila;
        });

        // ¿A alguien le cambiarían los compañeros? Los grupos por RD se reconocen porque comparten el mismo
        // número de RD del PTI (GrupoTitulacion: claveRd, solo las que agrupan). Se compara, para cada RD, quiénes
        // la comparten antes y después del cambio. Si cambia el grupo de alguien, no se toca a nadie involucrado.
        $nuevo = $filas->flatMap(fn ($f) => $f['cambios'])->where('tabla', 'resolucion')->pluck('despues', 'id');
        $rds = $seguimientos->flatMap(fn ($s) => $s->resoluciones->where('tipo', 'PTI')->filter(fn ($r) => filled($r->numero_rd) && ($r->agrupa ?? true)))
            ->map(fn ($r) => ['seg' => $r->seguimiento_id, 'antes' => GrupoTitulacion::claveRd($r->numero_rd), 'despues' => GrupoTitulacion::claveRd($nuevo[$r->id] ?? $r->numero_rd)]);
        $porAntes = $rds->groupBy('antes')->map(fn ($g) => $g->pluck('seg')->unique()->sort()->values()->all());
        $porDespues = $rds->groupBy('despues')->map(fn ($g) => $g->pluck('seg')->unique()->sort()->values()->all());
        $involucrados = $rds->filter(fn ($r) => $porAntes[$r['antes']] !== $porDespues[$r['despues']])
            ->flatMap(fn ($r) => array_merge($porAntes[$r['antes']], $porDespues[$r['despues']]))->unique()->flip();
        $filas = $filas->map(function ($f) use ($involucrados) {
            if (! empty($f['cambios']) && isset($involucrados[$f['seguimiento']->id])) {
                $f['conflicto'] = true;
                $f['cambios'] = [];
            }

            return $f;
        });

        return [
            'filas' => $filas,
            'conteo' => $filas->countBy('fuente')->all(),
            'conflictos' => $filas->where('conflicto', true)->count(),
        ];
    }

    /** Aplica el plan (recalculado en el servidor) y guarda el respaldo. Devuelve [registros cambiados, archivo de respaldo]. */
    public static function aplicar(bool $conEstimados): array
    {
        $cambios = self::plan($conEstimados)['filas']->flatMap(fn ($f) => $f['cambios'])->values();
        if ($cambios->isEmpty()) {
            return [0, null];
        }
        $archivo = 'titulacion/respaldos/rd-pti-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($archivo, json_encode($cambios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        DB::transaction(fn () => self::escribir($cambios));

        return [$cambios->count(), $archivo];
    }

    /** Escritura directa (sin eventos): no cambia el estado ni la auditoría, solo el texto del número. */
    private static function escribir(Collection $cambios): void
    {
        foreach ($cambios as $c) {
            $c['tabla'] === 'pti'
                ? Pti::whereKey($c['id'])->update(['rd_pti' => $c['despues']])
                : Resolucion::whereKey($c['id'])->update(['numero_rd' => $c['despues']]);
        }
    }

}
