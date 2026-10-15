<?php

namespace App\Http\Resources\CursosEspeciales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoEspecialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'imagen_url' => $this->imagen_url,
            'inscrito' => (bool) ($this->inscrito ?? false),
            'porcentaje' => $this->porcentaje ?? null,
        ];
    }
}
