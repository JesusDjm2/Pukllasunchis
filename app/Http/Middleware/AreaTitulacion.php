<?php

namespace App\Http\Middleware;

use App\Services\Titulacion\AreasTitulacion;
use Closure;
use Illuminate\Http\Request;

/**
 * Titulación: cada área solo modifica lo suyo (ver AreasTitulacion). Va sobre todo el grupo de
 * rutas del módulo. Una acción de escritura sin área asignada queda solo para el super-admin, así
 * una ruta nueva no queda abierta por olvido.
 */
class AreaTitulacion
{
    public function handle(Request $request, Closure $next)
    {
        $ruta = (string) $request->route()?->getName();
        $area = AreasTitulacion::areaDe($ruta) ?? ($request->isMethodSafe() ? null : AreasTitulacion::SOLO_SUPER_ADMIN);

        if ($area === null || AreasTitulacion::puede($area)) {
            return $next($request);
        }

        return redirect()->back(302, [], route('titulacion.index'))
            ->with('error', 'Esta acción le corresponde a '.AreasTitulacion::NOMBRES[$area].'. Puede ver la información, pero no modificarla.');
    }
}
