<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Nombre corto de la clase (p.ej. "ComunicadoPublicado") — el
            // cliente lo usa para elegir ícono/color y a dónde navegar.
            'tipo' => class_basename($this->type),
            'datos' => $this->data,
            'leida' => $this->read_at !== null,
            'leida_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
