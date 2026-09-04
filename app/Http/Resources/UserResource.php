<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),
            'alumno_id' => $this->alumno?->id,
            'docente_id' => $this->docente?->id,
        ];
    }
}
