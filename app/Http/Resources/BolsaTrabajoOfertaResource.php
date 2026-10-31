<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BolsaTrabajoOfertaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'detalles' => $this->detalles,
            'imagen_url' => $this->imagen ? asset($this->imagen) : null,
            'fecha_publicacion' => $this->fecha_publicacion,
            'vigente' => $this->vigente,
            'nombre_publicador' => $this->nombre_publicador,
            'telefono_publicador' => $this->telefono_publicador,
            'relacion_publicador' => $this->relacion_publicador,
        ];
    }
}
