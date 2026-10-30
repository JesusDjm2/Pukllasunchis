<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Acta;
use App\Models\Titulacion\Jurado;
use App\Models\Titulacion\Participante;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;

/**
 * En qué procesos participa cada asesor del catálogo, por rol y agrupado por trabajo (los integrantes
 * de un grupo comparten título). Une las tablas con el criterio de la ficha: el asesor vigente está en
 * «Participantes»; el del PTI, la Tesis o el acta cuenta solo si esa persona aún no tiene ninguno ahí.
 */
class ParticipacionAsesores
{
    /** Rol => [nombre, paso de la ficha que lo muestra, nombre corto para la lista]. */
    public const ROLES = [
        'asesor' => ['Asesor(a)', 'ti_asesores', 'asesor(a)'],
        'jurado_ti' => ['Jurado de exposición del TI', 'ti_jurado', 'jurado del TI'],
        'dictaminante' => ['Dictaminante de tesis', 'tesis_dictaminantes', 'dictaminante'],
        'jurado_sustentacion' => ['Jurado de sustentación', 'tesis_jurado', 'jurado de sustentación'],
    ];

    /**
     * @return array<int, array{roles: array<string, array{personas: int, trabajos: array}>, texto: string}> por id de asesor
     */
    public static function porAsesor(): array
    {
        $vinculos = [];
        $conAsesorVigente = [];
        foreach (Participante::whereNotNull('asesor_externo_id')->get(['asesor_externo_id', 'seguimiento_id', 'etapa', 'funcion', 'cargo']) as $p) {
            $rol = match (true) {
                $p->funcion === 'asesor' => 'asesor',
                $p->etapa === 'ti' => 'jurado_ti',
                PasosProceso::esDictaminante($p->cargo) => 'dictaminante',
                default => 'jurado_sustentacion',
            };
            $vinculos[$p->asesor_externo_id][$rol][$p->seguimiento_id] = true;
        }
        foreach (Participante::where('funcion', 'asesor')->pluck('seguimiento_id') as $id) {
            $conAsesorVigente[$id] = true;
        }
        foreach ([Pti::class, Tesis::class, Acta::class] as $modelo) {
            foreach ($modelo::whereNotNull('asesor_externo_id')->get(['asesor_externo_id', 'seguimiento_id']) as $r) {
                if (! isset($conAsesorVigente[$r->seguimiento_id])) {
                    $vinculos[$r->asesor_externo_id]['asesor'][$r->seguimiento_id] = true;
                }
            }
        }
        foreach (Jurado::with('acta:id,seguimiento_id,tipo')->whereNotNull('asesor_externo_id')->get(['acta_id', 'asesor_externo_id']) as $j) {
            if ($j->acta) {
                $vinculos[$j->asesor_externo_id][$j->acta->tipo === 'expo' ? 'jurado_ti' : 'jurado_sustentacion'][$j->acta->seguimiento_id] = true;
            }
        }

        $ids = collect($vinculos)->flatMap(fn ($roles) => collect($roles)->flatMap(fn ($segs) => array_keys($segs)))->unique();
        $seguimientos = SeguimientoTitulacion::with(['user:id,name,apellidos', 'estadoTitulacion', 'pti', 'tesis'])
            ->whereIn('id', $ids)->get()->filter(fn ($s) => $s->user)->keyBy('id');

        $resultado = [];
        foreach ($vinculos as $asesorId => $roles) {
            $nombres = [];
            foreach (array_keys(self::ROLES) as $rol) {
                $personas = collect(array_keys($roles[$rol] ?? []))->map(fn ($id) => $seguimientos[$id] ?? null)->filter();
                if ($personas->isEmpty()) {
                    continue;
                }
                $nombres = array_merge($nombres, $personas->map(fn ($s) => self::nombre($s))->all());
                $resultado[$asesorId]['roles'][$rol] = [
                    'personas' => $personas->count(),
                    'trabajos' => $personas->groupBy(fn ($s) => self::claveTrabajo($s, $rol))
                        ->map(fn ($grupo) => ['titulo' => self::titulo($grupo->first(), $rol), 'personas' => $grupo->sortBy(fn ($s) => self::nombre($s))->values()])
                        ->sortBy(fn ($t) => self::nombre($t['personas']->first()))->values()->all(),
                ];
            }
            if (isset($resultado[$asesorId])) {
                $resultado[$asesorId]['texto'] = implode(' ', array_unique($nombres));
            }
        }

        return $resultado;
    }

    public static function nombre(SeguimientoTitulacion $s): string
    {
        return mb_convert_case(trim($s->user->apellidos.', '.$s->user->name, ', '), MB_CASE_TITLE);
    }

    private static function titulo(SeguimientoTitulacion $s, string $rol): ?string
    {
        $titulo = in_array($rol, ['dictaminante', 'jurado_sustentacion'], true) ? ($s->tesis?->titulo ?: $s->pti?->titulo) : $s->pti?->titulo;

        return filled($titulo) ? trim($titulo) : null;
    }

    /** Sin título no se puede saber con quién comparte el trabajo: cada persona va aparte. */
    private static function claveTrabajo(SeguimientoTitulacion $s, string $rol): string
    {
        $titulo = self::titulo($s, $rol);

        return $titulo ? mb_strtolower(preg_replace('/\s+/u', ' ', $titulo)) : 'sin-titulo-'.$s->id;
    }
}
