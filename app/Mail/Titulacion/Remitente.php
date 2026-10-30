<?php

namespace App\Mail\Titulacion;

/**
 * De parte de quién sale un correo del proceso de titulación a los estudiantes.
 * clave => [nombre visible en el remitente, firma al pie del correo].
 */
class Remitente
{
    public const OPCIONES = [
        'investigacion' => ['Área de Investigación — EESP Pukllasunchis', 'Área de Investigación'],
        'secretaria' => ['Secretaría — EESP Pukllasunchis', 'Secretaría'],
    ];

    public const POR_DEFECTO = 'investigacion';

    public static function nombre(?string $clave): string
    {
        return (self::OPCIONES[$clave] ?? self::OPCIONES[self::POR_DEFECTO])[0];
    }

    public static function firma(?string $clave): string
    {
        return (self::OPCIONES[$clave] ?? self::OPCIONES[self::POR_DEFECTO])[1];
    }

    public static function regla(): string
    {
        return 'nullable|in:'.implode(',', array_keys(self::OPCIONES));
    }

    /** Dirección que se muestra como remitente y a la que llegan las respuestas. */
    public static function direccion(?string $clave): string
    {
        return config('services.titulacion.correo_'.($clave === 'secretaria' ? 'secretaria' : 'investigacion'));
    }

    /**
     * ¿Ya se puede enviar con la cuenta del área? Sin sus datos SMTP (MAIL_SECRETARIA_* o
     * MAIL_INVESTIGACION_* en .env) sale por la cuenta del sistema: Gmail muestra esa cuenta como
     * remitente y solo las respuestas llegan al área.
     */
    public static function cuentaConfigurada(string $clave): bool
    {
        return filled(config("mail.mailers.{$clave}.host")) && filled(config("mail.mailers.{$clave}.password"));
    }

    /** Mailer con el que se envía (null = el del sistema). */
    public static function mailer(?string $clave): ?string
    {
        return $clave && isset(self::OPCIONES[$clave]) && self::cuentaConfigurada($clave) ? $clave : null;
    }
}
