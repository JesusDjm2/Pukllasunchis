<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComunicadoResource;
use App\Models\Comunicado;

class ComunicadoController extends Controller
{
    public function index()
    {
        $comunicados = Comunicado::orderByDesc('fecha_publicacion')->paginate(15);

        return response()->json([
            'data' => ComunicadoResource::collection($comunicados),
            'meta' => [
                'current_page' => $comunicados->currentPage(),
                'last_page' => $comunicados->lastPage(),
                'total' => $comunicados->total(),
            ],
        ]);
    }
}
