<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodoActualResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'horario' => $this->horario,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_cierre' => $this->fecha_cierre,
            'formulario_habilitado' => $this->formulario_habilitado,
        ];
    }
}
