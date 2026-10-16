<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Pti;
use Illuminate\Support\Carbon;

/**
 * Vigencia del PTI: 2 años desde su inscripción (Reglamento de
 * Investigación, Art. 6°). Si la hoja de secretaría trae el vencimiento
 * ("FV julio 2026") manda ese; si no, se calcula desde la fecha del PTI.
 */
class VigenciaPti
{
    public const ANIOS_VIGENCIA = 2;

    /** Con esta cantidad de meses o menos, el aviso sugerido es el urgente. */
    public const MESES_AVISO_URGENTE = 4;

    public readonly ?Carbon $inscripcion;

    public readonly ?Carbon $vencimiento;

    /** 'ampliacion' (RD de ampliación) | 'archivo' (vencimiento registrado) | 'calculado' (fecha PTI + 2 años) | null */
    public readonly ?string $origen;

    public function __construct(?Pti $pti)
    {
        $this->inscripcion = $pti?->fecha_pti?->copy();

        if ($pti?->vencimiento_ampliado) {
            // Ampliación con RD: extiende el mismo PTI.
            $this->vencimiento = $pti->vencimiento_ampliado->copy();
            $this->origen = 'ampliacion';
        } elseif ($pti?->fecha_vencimiento) {
            $this->vencimiento = $pti->fecha_vencimiento->copy();
            $this->origen = 'archivo';
        } elseif ($this->inscripcion) {
            $this->vencimiento = $this->inscripcion->copy()->addYears(self::ANIOS_VIGENCIA)->endOfMonth();
            $this->origen = 'calculado';
        } else {
            $this->vencimiento = null;
            $this->origen = null;
        }
    }

    public function conocida(): bool
    {
        return $this->vencimiento !== null;
    }

    public function vencido(): bool
    {
        return $this->vencimiento !== null && $this->vencimiento->endOfDay()->isPast();
    }

    /** Meses completos que faltan (0 si vence este mes o ya venció). */
    public function mesesRestantes(): ?int
    {
        return $this->vencimiento ? max(0, (int) now()->startOfDay()->diffInMonths($this->vencimiento, false)) : null;
    }

    /** "1 año", "1 año y 3 meses", "7 meses", "menos de un mes". */
    public function tiempoRestanteTexto(): ?string
    {
        $meses = $this->mesesRestantes();
        if ($meses === null) {
            return null;
        }
        if ($meses === 0) {
            return 'menos de un mes';
        }
        $anios = intdiv($meses, 12);
        $resto = $meses % 12;

        return trim(
            ($anios ? $anios.' '.($anios === 1 ? 'año' : 'años') : '')
            .($anios && $resto ? ' y ' : '')
            .($resto ? $resto.' '.($resto === 1 ? 'mes' : 'meses') : '')
        );
    }

    /** 'urgente' | 'recordatorio' | null (sin fecha o ya vencido). */
    public function avisoSugerido(): ?string
    {
        if (! $this->conocida() || $this->vencido()) {
            return null;
        }

        return $this->mesesRestantes() <= self::MESES_AVISO_URGENTE ? 'urgente' : 'recordatorio';
    }

    /** "julio de 2026" */
    public static function mesAnio(?Carbon $fecha): ?string
    {
        return $fecha ? $fecha->locale('es')->translatedFormat('F \d\e Y') : null;
    }
}
