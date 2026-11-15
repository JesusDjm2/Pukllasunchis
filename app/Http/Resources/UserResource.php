<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fotoNombre = $this->foto ?: $this->alumno?->user?->foto;
        $fotoUrl = null;
        if ($fotoNombre) {
            if (str_starts_with($fotoNombre, 'http://') || str_starts_with($fotoNombre, 'https://')) {
                $fotoUrl = $fotoNombre;
            } elseif ($this->docente && file_exists(public_path('docentes/fotos/' . $fotoNombre))) {
                $fotoUrl = $request->getSchemeAndHttpHost() . '/api/v1/foto/docente/' . ltrim($fotoNombre, '/');
            } else {
                $fotoUrl = $request->getSchemeAndHttpHost() . '/api/v1/foto/estudiante/' . ltrim($fotoNombre, '/');
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'foto' => $fotoUrl,
            'roles' => $this->getRoleNames(),
            'beca' => (bool) $this->beca,
            // Egresado: la web le da su propia intranet (sin matrícula, cursos ni tutoría).
            'condicion' => $this->condicion,
            'es_egresado' => $this->condicion === 'Egresado',
            'alumno_id' => $this->alumno?->id,
            'docente_id' => $this->docente?->id,
        ];
    }
}
