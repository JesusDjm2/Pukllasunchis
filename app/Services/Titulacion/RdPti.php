<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use Carbon\CarbonInterface;

/**
 * La RD que aprueba el PTI: da inicio a todo el proceso, por eso se muestra siempre (cabecera de la ficha,
 * pantalla de generar RD, intranet del estudiante). Muchos registros vienen del Excel con el «número»
 * guardado como nombre de archivo («RD- 088 PTI APAZA PINEDO VERONICA.pdf»), sin año. Aquí se lee:
 *  - completa:   número y año («088-2021»).
 *  - incompleta: hay número pero no año (se muestra «088 · falta año»).
 *  - falta:      no hay RD del PTI.
 * El año que falta se puede sacar de la fecha de la RD (seguro) o de la fecha del PTI (estimado).
 */
class RdPti
{
    /**
     * @return array{estado:string, texto:?string, numero:?string, anio:?int, mostrar:?string, fecha:?CarbonInterface,
     *               enlace:?string, anio_rd:?int, anio_pti:?int, resolucion:?Resolucion}
     */
    public static function de(SeguimientoTitulacion $s): array
    {
        $pti = $s->pti;
        $resoluciones = $s->resoluciones->where('tipo', 'PTI')->filter(fn ($r) => filled($r->numero_rd));
        $texto = trim((string) ($pti?->rd_pti ?: $resoluciones->sortByDesc(fn ($r) => filled($r->archivo))->first()?->numero_rd));
        [$numero, $anio] = self::leer($texto);
        // La resolución que corresponde a ese número (para su fecha y su PDF).
        $res = $resoluciones->first(fn ($r) => $numero && self::leer($r->numero_rd)[0] === $numero)
            ?? $resoluciones->sortByDesc(fn ($r) => filled($r->archivo))->first();

        $estado = $texto === '' ? 'falta' : ($numero && $anio ? 'completa' : 'incompleta');

        return [
            'estado' => $estado,
            'texto' => $texto !== '' ? $texto : null,
            'numero' => $numero,
            'anio' => $anio,
            'mostrar' => match ($estado) {
                'completa' => $numero.'-'.$anio,
                'incompleta' => $numero ?: $texto,
                default => null,
            },
            'fecha' => $res?->fecha,
            'enlace' => filled($res?->archivo) ? $res->archivo : null,
            'anio_rd' => $res?->fecha?->year,
            'anio_pti' => $pti?->fecha_pti?->year,
            'resolucion' => $res,
        ];
    }

    /**
     * «RD- 106-2021 PTI …pdf» → ['106', 2021]; «RD- 088 PTI APAZA….pdf» → ['088', null]; sin número → [null, null].
     *
     * @return array{0:?string, 1:?int}
     */
    public static function leer(?string $texto): array
    {
        // El primer número es el de la RD; el año, solo si va pegado a él («029-2021», «29 - 2021»).
        // Así «RD - 003 APROB. Modificar la RD N° 0246-2024» es la 003 (sin año), no la 0246.
        if (! preg_match('/(?<!\d)(\d{1,4})(?!\d)(?:\s*-\s*((?:19|20)\d{2})(?!\d))?/', (string) $texto, $m)) {
            return [null, null];
        }

        return [self::rellenar($m[1]), isset($m[2]) && $m[2] !== '' ? (int) $m[2] : null];
    }

    /** Tres cifras, como se numeran las RD: 88 → «088», 1234 → «1234». */
    public static function rellenar(string $numero): string
    {
        return str_pad(ltrim($numero, '0') ?: '0', 3, '0', STR_PAD_LEFT);
    }
}
