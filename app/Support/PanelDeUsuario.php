<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * A dónde vuelve cada usuario («Volver a mi panel»): el mismo criterio que LoginController::authenticated().
 * Si cambia el destino de un rol al iniciar sesión, cambiarlo también aquí.
 */
class PanelDeUsuario
{
    public static function url(User $u): string
    {
        $ruta = fn (string $nombre, array $p = []) => Route::has($nombre) ? route($nombre, $p) : url('/');

        return match (true) {
            $u->hasAnyRole(['super-admin', 'admin']) => $ruta('admin'),
            $u->hasRole('docente') && (bool) $u->docente => $ruta('vistaDocente', ['docente' => $u->docente->id]),
            $u->hasRole('tutor') => $ruta('tutor.dashboard'),
            $u->hasRole('adminB') => $ruta('trabajo.index'),
            $u->hasAnyRole(['gestor-titulacion', 'secretaria']) => $ruta('titulacion.index'),
            $u->condicion === 'Egresado' => $ruta('egresado.intranet'),
            $u->hasRole('alumnoB') => $ruta('ppd.index'),
            $u->hasRole('alumno') => $ruta('alumnos.index'),
            $u->hasRole('inhabilitado') => $ruta('inhabilitado'),
            default => url('/'),
        };
    }

    /** ¿Es personal de la institución (no estudiante ni egresado)? */
    public static function esPersonal(User $u): bool
    {
        return $u->hasAnyRole(['super-admin', 'admin', 'docente', 'tutor', 'adminB', 'gestor-titulacion', 'secretaria', 'investigador']);
    }
}
