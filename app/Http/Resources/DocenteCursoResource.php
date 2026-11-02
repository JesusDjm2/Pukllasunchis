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
        $alumnosCount = $this->area === 'ppd'
            ? $this->alumnosPpdCount()
            : $this->alumnosValidosIds()->count();

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'area' => $this->area,
            'ciclo' => $this->ciclo?->nombre,
            'programa' => $this->ciclo?->programa?->nombre,
            'creditos' => (int) ($this->creditos ?? 0),
            'horas' => (int) ($this->horas ?? 0),
            'alumnos_count' => (int) $alumnosCount,
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

    private function alumnosPpdCount(): int
    {
        $programaId = $this->ciclo?->programa_id;
        if (! $programaId) {
            return 0;
        }

        return \App\Models\User::where('programa_id', $programaId)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->count();
    }
}
