<?php

namespace App\Enums;

/**
 * Nivel educativo de un programa (`programas.nivel`). Junto con
 * `modalidad` y `eib` reemplaza a las búsquedas por texto del tipo
 * `nombre LIKE '%Inicial%'`.
 */
enum NivelPrograma: string
{
    case INICIAL = 'INICIAL';
    case PRIMARIA = 'PRIMARIA';

    public function etiqueta(): string
    {
        return match ($this) {
            self::INICIAL => 'Inicial',
            self::PRIMARIA => 'Primaria',
        };
    }
}
