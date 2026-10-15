<?php

namespace App\Http\Middleware;

use App\Models\Docente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Evita que un docente opere sobre el registro de otro docente.
 * La regla vive en DocentePolicy@gestionar; aquí solo se resuelve a qué
 * Docente apunta la ruta ({docente}, {docenteId} o {id}) y se consulta el Gate.
 */
class EnsureDocenteOwnProfile
{
    private const PARAMS = ['docente', 'docenteId', 'id'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        $docente = $this->resolveDocente($request);

        if (!$docente) {
            abort(404);
        }

        if (!Gate::allows('gestionar', $docente)) {
            abort(403, 'No puedes gestionar los datos de otro docente.');
        }

        return $next($request);
    }

    private function resolveDocente(Request $request): ?Docente
    {
        foreach (self::PARAMS as $param) {
            $value = $request->route($param);

            if ($value === null) {
                continue;
            }

            if ($value instanceof Docente) {
                return $value;
            }

            if (is_numeric($value)) {
                return Docente::find((int) $value);
            }
        }

        return null;
    }
}
