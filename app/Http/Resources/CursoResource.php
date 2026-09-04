<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'sumilla' => $this->sumilla,
            'cc' => $this->cc,
            'horas' => $this->horas,
            'creditos' => $this->creditos,
            'classroom' => $this->classroom,
        ];
    }
}
