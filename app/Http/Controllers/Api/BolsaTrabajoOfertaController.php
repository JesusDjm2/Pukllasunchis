<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BolsaTrabajoOfertaResource;
use App\Models\BolsaTrabajoOferta;
use Illuminate\Http\Request;

class BolsaTrabajoOfertaController extends Controller
{
    public function index(Request $request)
    {
        $query = BolsaTrabajoOferta::query();

        if ($request->filled('anio')) {
            $query->where('anio', (int) $request->anio);
        }
        if ($request->filled('mes')) {
            $query->where('mes', (int) $request->mes);
        }

        $ofertas = $query
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json(['data' => BolsaTrabajoOfertaResource::collection($ofertas)]);
    }
}
