<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'reporte' => $this->reporte,
            'imagen_url' => $this->imagen ? asset('img/incidencias/'.$this->imagen) : null,
            'alumno' => $this->whenLoaded('alumno', fn () => [
                'id' => $this->alumno->id,
                'nombres' => $this->alumno->nombres,
                'apellidos' => $this->alumno->apellidos,
            ]),
            'ciclo' => $this->whenLoaded('ciclo', fn () => [
                'id' => $this->ciclo->id,
                'nombre' => $this->ciclo->nombre,
                'programa' => $this->ciclo->programa?->nombre,
            ]),
        ];
    }
}
