<?php

namespace App\Http\Middleware;

use App\Models\Ayllu\AylluPublicacion;
use App\Services\RedPuklla\AccesoRed;
use Closure;
use Illuminate\Http\Request;

class ArchivoRedVisible
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        abort_unless($usuario, 401);

        $publicacionId = $request->route('publicacionId');

        abort_unless(
            is_scalar($publicacionId)
                && ctype_digit((string) $publicacionId),
            404
        );

        $publicacion = AylluPublicacion::deRedPuklla()
            ->findOrFail((int) $publicacionId);

        $acceso = app(AccesoRed::class);

        abort_unless($acceso->entrar($usuario), 403);

        if (!$acceso->moderar($usuario)) {
            // El autor tampoco puede distribuir archivos retirados
            // mediante una URL directa.
            abort_unless(
                $publicacion->visible
                    && $publicacion->estado_red === 'visible'
                    && $acceso->publicacion($usuario, $publicacion),
                404
            );
        }

        $request->attributes->set(
            'publicacion_red_autorizada',
            $publicacion
        );

        $respuesta = $next($request);

        $respuesta->headers->set(
            'Cache-Control',
            'private, no-store, max-age=0'
        );

        return $respuesta;
    }
}
