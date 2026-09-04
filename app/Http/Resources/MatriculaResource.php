<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatriculaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_actual_id' => $this->periodo_actual_id,
            'periodo_nombre' => $this->periodoActual?->nombre,
            'estado' => $this->estado,
            'comprobante' => $this->comprobante,
            'fecha_completado' => $this->fecha_completado,
            'created_at' => $this->created_at,
        ];
    }
}
