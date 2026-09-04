<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'curso_id' => $this->curso_id,
            'curso_nombre' => $this->curso?->nombre,
            'valoracion_1' => $this->valoracion_1,
            'valoracion_2' => $this->valoracion_2,
            'valoracion_3' => $this->valoracion_3,
            'valoracion_curso' => $this->valoracion_curso,
            'calificacion_curso' => $this->calificacion_curso,
            'calificacion_sistema' => $this->calificacion_sistema,
        ];
    }
}
