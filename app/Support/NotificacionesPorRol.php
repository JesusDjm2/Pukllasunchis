<?php

namespace App\Support;

use App\Models\User;

/**
 * Qué avisos de la campanita le corresponden a una cuenta de área. Algunas cuentas tienen además el rol
 * «admin» (por ejemplo Secretaría), y les llegaban o quedaban avisos de administración (incidencias,
 * tutorías, sugerencias) que no son suyos. Para esas cuentas la campanita muestra SOLO los tipos de su área.
 * null = sin filtro (todos los avisos del usuario).
 *
 * Secretaría es parte central del proceso de titulación: le llegan TODAS las solicitudes de los trámites de
 * titulación y los avisos de RD por emitir. Un aviso nuevo de titulación se agrega en TITULACION.
 */
class NotificacionesPorRol
{
    /** Avisos de los trámites de titulación (solicitudes y RD). */
    public const TITULACION = [
        \App\Notifications\TitulacionCartaSolicitada::class,
        \App\Notifications\TitulacionPtiSolicitada::class,
        \App\Notifications\TitulacionPtiParaRd::class,
        \App\Notifications\TitulacionOriginalidadSolicitada::class,
    ];

    /** Rol de área con filtro => tipos de aviso que ve. */
    public const POR_ROL = [
        'secretaria' => self::TITULACION,
    ];

    /**
     * Lo que suma otra área si la cuenta también la tiene (una cuenta de Secretaría que además es de
     * Investigación ve también los avisos de Investigación). Estas áreas no filtran por sí solas.
     */
    public const SUMAN = [
        'gestor-titulacion' => [...self::TITULACION, \App\Notifications\TitulacionDatoReportado::class],
    ];

    /** @return array<int, string>|null */
    public static function tipos(?User $user): ?array
    {
        if (! $user || $user->hasRole('super-admin')) {
            return null;
        }
        $tipos = null;
        foreach (self::POR_ROL as $rol => $deRol) {
            if ($user->hasRole($rol)) {
                $tipos = array_merge($tipos ?? [], $deRol);
            }
        }
        if ($tipos === null) {
            return null;
        }
        foreach (self::SUMAN as $rol => $deRol) {
            if ($user->hasRole($rol)) {
                $tipos = array_merge($tipos, $deRol);
            }
        }

        return array_values(array_unique($tipos));
    }

    /** Aplica el filtro a una consulta de notificaciones del usuario. */
    public static function filtrar($consulta, ?User $user)
    {
        $tipos = self::tipos($user);

        return $tipos === null ? $consulta : $consulta->whereIn('type', $tipos);
    }
}
