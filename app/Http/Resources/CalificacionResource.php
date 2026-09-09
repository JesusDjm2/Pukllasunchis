<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Espeja el modelo `Periodo` (tabla `periodos`) — un registro de nota de un
 * alumno en un curso, dentro de un período específico. No confundir con el
 * modelo `Calificacion` (tabla `calificacions`), que existe pero está vacía
 * en producción y no la usa ningún flujo real de la web.
 */
class CalificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'curso_id' => $this->curso_id,
            'curso_nombre' => $this->curso?->nombre,
            'valoracion_curso' => $this->valoracion_curso,
            'calificacion_curso' => $this->calificacion_curso,
            'calificacion_sistema' => $this->calificacion_sistema,
        ];
    }
}
