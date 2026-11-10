<?php

namespace App\Support;

use App\Models\User;

/**
 * Qué avisos de la campanita le corresponden a una cuenta de área. Algunas cuentas tienen además el rol
 * «admin» (por ejemplo Secretaría), y les llegaban o quedaban avisos de administración (incidencias,
 * tutorías, sugerencias) que no son suyos. Para esas cuentas la campanita muestra SOLO los tipos de su área.
 * null = sin filtro (todos los avisos del usuario). Un aviso nuevo de Secretaría se agrega aquí.
 */
class NotificacionesPorRol
{
    /** Rol de área => tipos de aviso que ve. */
    public const POR_ROL = [
        'secretaria' => [
            \App\Notifications\TitulacionCartaSolicitada::class,
        ],
    ];

    /** @return array<int, string>|null */
    public static function tipos(?User $user): ?array
    {
        if (! $user || $user->hasRole('super-admin')) {
            return null;
        }
        foreach (self::POR_ROL as $rol => $tipos) {
            if ($user->hasRole($rol)) {
                return $tipos;
            }
        }

        return null;
    }

    /** Aplica el filtro a una consulta de notificaciones del usuario. */
    public static function filtrar($consulta, ?User $user)
    {
        $tipos = self::tipos($user);

        return $tipos === null ? $consulta : $consulta->whereIn('type', $tipos);
    }
}
