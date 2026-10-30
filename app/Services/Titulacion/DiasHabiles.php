<?php

namespace App\Services\Titulacion;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Días hábiles (lunes a viernes, sin feriados nacionales del Perú) para los plazos del proceso,
 * por ejemplo los 8 días hábiles que tiene el asesor para compartir el informe ITI.
 */
class DiasHabiles
{
    /** Feriados nacionales de fecha fija (mes-día). Jueves y Viernes Santo se calculan cada año. */
    private const FIJOS = [
        '01-01', '05-01', '06-07', '06-29', '07-23', '07-28', '07-29',
        '08-06', '08-30', '10-08', '11-01', '12-08', '12-09', '12-25',
    ];

    public static function esHabil(CarbonInterface $dia): bool
    {
        if ($dia->isWeekend() || in_array($dia->format('m-d'), self::FIJOS, true)) {
            return false;
        }
        $pascua = Carbon::createFromTimestamp(easter_date($dia->year))->startOfDay();

        return ! $dia->isSameDay($pascua->copy()->subDays(3)) && ! $dia->isSameDay($pascua->copy()->subDays(2));
    }

    /** El día hábil número $n contado desde el día siguiente a $desde. */
    public static function sumar(CarbonInterface $desde, int $n): Carbon
    {
        $dia = Carbon::parse($desde)->startOfDay();
        while ($n > 0) {
            $dia->addDay();
            if (self::esHabil($dia)) {
                $n--;
            }
        }

        return $dia;
    }

    /** Días hábiles que faltan desde hoy hasta $limite (0 si vence hoy; negativo si ya pasó). */
    public static function restantes(CarbonInterface $limite, ?CarbonInterface $hoy = null): int
    {
        $hoy = Carbon::parse($hoy ?? now())->startOfDay();
        $limite = Carbon::parse($limite)->startOfDay();
        $signo = $limite->gte($hoy) ? 1 : -1;
        [$a, $b] = $signo > 0 ? [$hoy, $limite] : [$limite, $hoy];
        $cuenta = 0;
        for ($d = $a->copy()->addDay(); $d->lte($b); $d->addDay()) {
            if (self::esHabil($d)) {
                $cuenta++;
            }
        }

        return $signo * $cuenta;
    }
}
