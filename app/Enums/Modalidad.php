<?php

namespace App\Enums;

/**
 * Modalidad a la que pertenece un programa de estudios. Antes se deducía
 * buscando "PPD" en `programas.nombre`; ahora es un campo propio
 * (`programas.modalidad`) y el nombre queda libre para renombrarse.
 */
enum Modalidad: string
{
    case FID = 'FID';
    case PPD = 'PPD';

    public function etiqueta(): string
    {
        return match ($this) {
            self::FID => 'Formación Inicial Docente',
            self::PPD => 'Profesionalización Docente',
        };
    }
}
