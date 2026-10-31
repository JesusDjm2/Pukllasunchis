<?php

namespace App\Http\Resources;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Curso visto por el docente que lo tiene asignado, no por el alumno.
 * El área (fid|ppd) y el estado del sílabo se calculan en el controlador,
 * que es donde ya están cargadas las relaciones y el período actual.
 */
class DocenteCursoResource extends JsonResource
{
    public function __construct(
        Curso $curso,
        private readonly string $area,
        private readonly bool $silaboListo,
    ) {
        parent::__construct($curso);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'area' => $this->area,
            'ciclo' => $this->ciclo?->nombre,
            'programa' => $this->ciclo?->programa?->nombre,
            'silabo_listo' => $this->silaboListo,
            'progreso' => $this->area === 'ppd'
                ? ['calificado' => round($this->porcentajePPD(), 1)]
                : [
                    'parcial_1' => round($this->porcentajePeriodo(1), 1),
                    'parcial_2' => round($this->porcentajePeriodo(2), 1),
                    'desempeno' => round($this->porcentajePeriodo(3), 1),
                ],
        ];
    }
}
