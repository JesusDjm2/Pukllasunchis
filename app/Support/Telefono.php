<?php

namespace App\Support;

/**
 * Enlaces de contacto a partir de los teléfonos de las fichas, que vienen escritos a
 * mano y de cualquier forma: "952 195 724", "+51 996079932", "984-320-295"…
 */
class Telefono
{
    /** Solo dígitos, con el código de país 51 si es un celular peruano de 9 dígitos. */
    public static function normalizar(?string $numero): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $numero);

        if (strlen($digitos) === 9 && $digitos[0] === '9') {
            return '51'.$digitos;
        }
        if (strlen($digitos) === 11 && str_starts_with($digitos, '519')) {
            return $digitos;
        }
        // Otro país (ya trae su código) — wa.me acepta entre 10 y 15 dígitos.
        if (strlen($digitos) >= 10 && strlen($digitos) <= 15 && ! str_starts_with($digitos, '0')) {
            return $digitos;
        }

        return null;
    }

    /** Enlace a WhatsApp (null si el número no es un celular válido, p. ej. un fijo). */
    public static function whatsapp(?string $numero, ?string $texto = null): ?string
    {
        $normalizado = self::normalizar($numero);
        if (! $normalizado) {
            return null;
        }

        return 'https://wa.me/'.$normalizado.($texto ? '?text='.rawurlencode($texto) : '');
    }

    /** Enlace para llamar (cualquier número con al menos 6 dígitos, fijos incluidos). */
    public static function llamar(?string $numero): ?string
    {
        $normalizado = self::normalizar($numero);
        if ($normalizado) {
            return 'tel:+'.$normalizado;
        }

        $digitos = preg_replace('/\D+/', '', (string) $numero);

        return strlen($digitos) >= 6 ? 'tel:'.$digitos : null;
    }
}
