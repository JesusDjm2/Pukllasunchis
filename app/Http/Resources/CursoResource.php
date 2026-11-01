<?php

namespace App\Http\Resources;

use App\Models\PeriodoActual;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $periodoActual = PeriodoActual::actual();

        // Resolver URL del sílabo oficial (estructurado o PDF subido)
        $silaboUrl = null;
        $silaboEstructurado = $this->silabos?->firstWhere('periodo_actual_id', $periodoActual?->id)
            ?? $this->silabos?->firstWhere('periodo', $periodoActual?->nombre);
        $silaboPdf = $this->silabosPdf?->where('periodo_actual_id', $periodoActual?->id)->first();

        if ($silaboEstructurado) {
            $silaboUrl = url("/silabo/{$silaboEstructurado->id}/pdf");
        } elseif ($silaboPdf && $silaboPdf->pdf) {
            $silaboUrl = url('/api/v1/silabo/archivo/' . ltrim($silaboPdf->pdf, '/'));
        } elseif ($this->silabo) {
            $silaboUrl = url('/api/v1/silabo/archivo/' . ltrim($this->silabo, '/'));
        }

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'sumilla' => $this->sumilla,
            'cc' => $this->cc,
            'horas' => $this->horas,
            'creditos' => $this->creditos,
            'ciclo_nombre' => $this->ciclo?->nombre,
            'programa_nombre' => $this->ciclo?->programa?->nombre,
            'classroom' => $this->classroom,
            'clave' => $this->clave,
            'silabo_url' => $silaboUrl,
            'docentes' => $this->docentes ? $this->docentes->map(function ($d) {
                $fotoUrl = null;
                if ($d->foto) {
                    $fotoUrl = (str_starts_with($d->foto, 'http://') || str_starts_with($d->foto, 'https://'))
                        ? $d->foto
                        : url('/api/v1/foto/docente/' . ltrim($d->foto, '/'));
                }
                return [
                    'id' => $d->id,
                    'nombre' => $d->nombre,
                    'email' => $d->email,
                    'telefono' => $d->telefono,
                    'foto_url' => $fotoUrl,
                ];
            })->values() : [],
            'competencias' => $this->competencias ? $this->competencias->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'descripcion' => $c->descripcion,
                    'capacidades' => $c->capacidades,
                ];
            })->values() : [],
        ];
    }
}
