<?php

namespace App\Services;

/**
 * Quién puede ver y registrar notas de un curso. Las rutas ya exigen rol (docente, admin o
 * super-admin); aquí se comprueba además que un docente solo trabaje sobre SUS cursos: antes
 * bastaba cambiar el {docente} o el {curso} de la URL, o el docente_id del formulario.
 * Admin y super-admin pasan siempre.
 */
class AccesoCalificaciones
{
    /** El usuario actual es ese docente y el curso está asignado a él. */
    public static function autorizar($docenteId, $cursoId): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return;
        }

        $docente = $user->docente;
        abort_unless($docente && (int) $docente->id === (int) $docenteId, 403,
            'Solo puedes registrar calificaciones desde tu propia cuenta de docente.');
        abort_unless($docente->cursos()->whereKey($cursoId)->exists(), 403,
            'Este curso no está asignado a tu cuenta.');
    }

    /** Igual que autorizar(), cuando la ruta solo trae el curso (el docente es el usuario actual). */
    public static function autorizarCurso($cursoId): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return;
        }

        self::autorizar($user->docente?->id, $cursoId);
    }
}
