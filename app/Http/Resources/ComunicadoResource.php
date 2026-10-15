<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComunicadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'archivo_url' => asset($this->archivo),
            'archivo_tipo' => $this->archivo_tipo,
            'fecha_publicacion' => $this->fecha_publicacion,
        ];
    }
}
