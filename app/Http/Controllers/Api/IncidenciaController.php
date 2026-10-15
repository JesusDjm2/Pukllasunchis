<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncidenciaResource;
use App\Models\Incidencia;
use Illuminate\Http\Request;

class IncidenciaController extends Controller
{
    public function index(Request $request)
    {
        $docente = $request->user()->docente;

        if (! $docente) {
            return response()->json(['message' => 'No se encontró un perfil de docente para este usuario.'], 404);
        }

        $incidencias = Incidencia::with(['alumno', 'ciclo.programa'])
            ->where('docente_id', $docente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => IncidenciaResource::collection($incidencias),
            'meta' => [
                'current_page' => $incidencias->currentPage(),
                'last_page' => $incidencias->lastPage(),
                'total' => $incidencias->total(),
            ],
        ]);
    }
}
