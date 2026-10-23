<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Acta;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Compañeros de grupo de titulación. Desde el 19/10 quienes inician juntos el PTI quedan
 * con el mismo seguimiento_titulacion.grupo_id. En los datos anteriores no hay grupo
 * guardado: quienes presentaron juntos comparten el mismo N° de acta o la misma RD de
 * PTI/TI/Tesis (un solo documento para todo el grupo). Se suman ambas fuentes.
 */
class GrupoTitulacion
{
    /** Tipos de RD que se emiten por grupo. */
    public const TIPOS_RD_GRUPALES = ['PTI', 'Exposicion', 'Tesis'];

    /** Normaliza un N° de RD para comparar ("RD  012-2024" = "rd 012-2024"). */
    public static function claveRd(?string $rd): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $rd)));
    }

    /** @return Collection<int, User> los demás integrantes (sin la propia persona) */
    public static function companeros(SeguimientoTitulacion $seguimiento): Collection
    {
        $actas = $seguimiento->actas->pluck('numero_acta')->filter(fn ($n) => filled($n))->unique();
        $porActa = $actas->isEmpty() ? collect() : Acta::whereIn('numero_acta', $actas)
            ->where('seguimiento_id', '!=', $seguimiento->id)
            ->pluck('seguimiento_id');

        $misRds = $seguimiento->resoluciones
            ->whereIn('tipo', self::TIPOS_RD_GRUPALES)
            ->filter(fn ($r) => filled($r->numero_rd))
            ->map(fn ($r) => $r->tipo.'|'.self::claveRd($r->numero_rd))
            ->unique();
        $porRd = $misRds->isEmpty() ? collect() : Resolucion::whereIn('tipo', self::TIPOS_RD_GRUPALES)
            ->where('seguimiento_id', '!=', $seguimiento->id)
            ->whereNotNull('numero_rd')
            ->get(['seguimiento_id', 'tipo', 'numero_rd'])
            ->filter(fn ($r) => $misRds->contains($r->tipo.'|'.self::claveRd($r->numero_rd)))
            ->pluck('seguimiento_id');

        $porGrupo = $seguimiento->grupo_id
            ? SeguimientoTitulacion::where('grupo_id', $seguimiento->grupo_id)->where('id', '!=', $seguimiento->id)->pluck('id')
            : collect();

        $ids = $porGrupo->merge($porActa)->merge($porRd)->unique();

        return $ids->isEmpty() ? collect() : User::whereHas('seguimientoTitulacion', fn ($q) => $q->whereIn('id', $ids))
            ->with([
                'seguimientoTitulacion.estadoTitulacion',
                // Contacto para la pestaña "Mi grupo" (celular de la ficha FID o PPD).
                'alumno:id,user_id,numero,numero_referencia', 'alumnoB:id,user_id,numero,numero_referencia',
                'programa:id,nombre',
            ])
            ->orderBy('apellidos')->get(['id', 'name', 'apellidos', 'email', 'dni', 'telefono', 'foto', 'programa_id']);
    }
}
