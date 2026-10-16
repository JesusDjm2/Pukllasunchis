<?php

namespace App\Models\Titulacion\Concerns;

/**
 * Para los modelos que alimentan el avance (PTI, TI/Tesis, Actas, Resoluciones):
 * al guardarse o borrarse, el seguimiento recalcula su estado. Así el estado
 * queda al día venga el cambio del panel, del importador o de un comando.
 */
trait RecalculaEstadoSeguimiento
{
    public static function bootRecalculaEstadoSeguimiento(): void
    {
        static::saved(fn ($model) => $model->seguimiento?->recalcularEstado());
        static::deleted(fn ($model) => $model->seguimiento?->recalcularEstado());
    }
}
