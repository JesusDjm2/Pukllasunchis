<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeriodoActualResource;
use App\Models\PeriodoActual;

class PeriodoActualController extends Controller
{
    public function show()
    {
        $periodoActual = PeriodoActual::actual();

        if (! $periodoActual) {
            return response()->json(['message' => 'No hay un periodo activo actualmente.'], 404);
        }

        return response()->json(['data' => new PeriodoActualResource($periodoActual)]);
    }
}
