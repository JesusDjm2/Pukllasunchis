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
            'id' => (int) $this->id,
            'curso_id' => (int) $this->curso_id,
            'curso_nombre' => $this->curso?->nombre,
            'ciclo_nombre' => $this->curso?->ciclo?->nombre,
            'programa_nombre' => $this->curso?->ciclo?->programa?->nombre,
            'creditos' => is_numeric($this->curso?->creditos) ? (int) $this->curso?->creditos : null,
            'horas' => is_numeric($this->curso?->horas) ? (int) $this->curso?->horas : null,
            'valoracion_curso' => $this->valoracion_curso !== null ? (string) $this->valoracion_curso : null,
            'calificacion_curso' => $this->calificacion_curso !== null ? (string) $this->calificacion_curso : null,
            'calificacion_sistema' => $this->calificacion_sistema !== null ? (string) $this->calificacion_sistema : null,
        ];
    }
}
