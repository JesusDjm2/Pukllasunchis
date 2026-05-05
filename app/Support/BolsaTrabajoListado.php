<?php

namespace App\Support;

use App\Models\BolsaTrabajoOferta;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Datos para la vista pública de bolsa de trabajo y el perfil alumno (mismos filtros y registros).
 */
class BolsaTrabajoListado
{
    /**
     * @return array{ofertas: Collection, aniosOfertas: Collection, mesesNombres: array<int, string>}
     */
    public static function datos(Request $request): array
    {
        $ofertasQuery = BolsaTrabajoOferta::query();
        if ($request->filled('anio')) {
            $ofertasQuery->where('anio', (int) $request->anio);
        }
        if ($request->filled('mes')) {
            $ofertasQuery->where('mes', (int) $request->mes);
        }

        $ofertas = $ofertasQuery
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $aniosOfertas = BolsaTrabajoOferta::query()->select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return compact('ofertas', 'aniosOfertas', 'mesesNombres');
    }
}
