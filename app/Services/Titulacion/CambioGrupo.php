<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Corrección manual del grupo de titulación por Secretaría: pasar a una persona a otro grupo o dejarla sin
 * grupo cuando la RD de retiro / incorporación ya se emitió fuera del sistema (datos que vienen del Excel).
 * Hace lo mismo que al emitir esas RD desde el sistema (ResolucionDirectoralController::aplicarCambioDeGrupo):
 *  - Sus RD grupales (PTI, TI, Tesis) dejan de unirla al grupo anterior (agrupa = false); las conserva.
 *  - Queda con el grupo_id del grupo destino (o sin grupo).
 *  - Si adopta el PTI del grupo destino: su PTI actual pasa a su historial (vencido) y recibe el del grupo
 *    (título, RD, asesor y vigencia), con la RD del PTI del grupo.
 *  - Si se indican, registra las RD de retiro / incorporación (sin PDF; se suben luego desde la ficha).
 *  - Deja constancia en las observaciones del expediente (quién, cuándo, de qué grupo a cuál y por qué).
 */
class CambioGrupo
{
    /** Integrantes actuales (sin la persona). */
    public static function companeros(SeguimientoTitulacion $s): Collection
    {
        return GrupoTitulacion::companeros($s->loadMissing(['actas', 'resoluciones']));
    }

    /** Actas que comparte con su grupo actual: por ellas seguirá apareciendo junto a ese grupo. */
    public static function actasCompartidas(SeguimientoTitulacion $s, Collection $companeros): Collection
    {
        $ids = $companeros->map(fn ($u) => $u->seguimientoTitulacion?->id)->filter();
        $numeros = $s->actas->pluck('numero_acta')->filter(fn ($n) => filled($n));

        return $numeros->isEmpty() || $ids->isEmpty() ? collect()
            : \App\Models\Titulacion\Acta::whereIn('seguimiento_id', $ids)->whereIn('numero_acta', $numeros)->pluck('numero_acta')->unique()->values();
    }

    public static function aplicar(SeguimientoTitulacion $s, ?SeguimientoTitulacion $destino, bool $adoptarPti, array $rds, string $motivo, User $por): void
    {
        $antes = self::companeros($s)->map(fn ($u) => trim($u->apellidos.', '.$u->name))->implode('; ');

        DB::transaction(function () use ($s, $destino, $adoptarPti, $rds, $motivo, $por, $antes) {
            $s->resoluciones()->whereIn('tipo', GrupoTitulacion::TIPOS_RD_GRUPALES)->update(['agrupa' => false]);

            $despues = 'sin grupo';
            if ($destino) {
                $miembros = self::companeros($destino);
                $grupoId = $destino->grupo_id ?: $destino->id;
                // Todo el grupo destino queda con el mismo grupo_id (los datos importados no lo traen).
                SeguimientoTitulacion::whereIn('id', $miembros->map(fn ($u) => $u->seguimientoTitulacion?->id)->filter()->push($destino->id))
                    ->where('id', '!=', $s->id)->update(['grupo_id' => $grupoId]);
                $s->grupo_id = $grupoId;
                $despues = $miembros->reject(fn ($u) => $u->id === $s->user_id)
                    ->push($destino->user)->unique('id')->map(fn ($u) => trim($u->apellidos.', '.$u->name))->implode('; ');

                if ($adoptarPti) {
                    $ptiGrupo = $destino->pti()->first();
                    $anterior = $s->pti()->first();
                    if ($anterior && $anterior->estado !== 'vencido') {
                        $anterior->update(['estado' => 'vencido']);
                    }
                    if ($ptiGrupo) {
                        $s->ptis()->create([
                            'numero' => (int) $s->ptis()->withTrashed()->max('numero') + 1,
                            'estado' => 'pendiente',
                        ] + $ptiGrupo->only(['etapa', 'titulo', 'fecha_pti', 'fecha_vencimiento', 'rd_pti', 'asesor_id', 'asesor_externo_id', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado']));
                        $rdGrupo = $destino->resoluciones()->where('tipo', 'PTI')->whereNotNull('numero_rd')->get()
                            ->sortByDesc(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === GrupoTitulacion::claveRd($ptiGrupo->rd_pti))->first();
                        if ($rdGrupo) {
                            $s->resoluciones()->create([
                                'tipo' => 'PTI', 'numero_rd' => $rdGrupo->numero_rd, 'fecha' => $rdGrupo->fecha, 'archivo' => $rdGrupo->archivo,
                                'detalle' => 'RD del PTI del grupo al que se incorporó',
                            ]);
                        }
                    }
                }
            } else {
                $s->grupo_id = null;
                if ($adoptarPti) {
                    // Al retirarse renuncia al PTI del grupo: queda en su historial.
                    $s->pti()->first()?->update(['estado' => 'vencido']);
                }
            }

            foreach (['RetiroGrupo' => 'retiro', 'IncorporacionGrupo' => 'incorporacion'] as $tipo => $clave) {
                if (filled($rds[$clave.'_numero'] ?? null)) {
                    $s->resoluciones()->create([
                        'tipo' => $tipo, 'numero_rd' => trim($rds[$clave.'_numero']), 'fecha' => $rds[$clave.'_fecha'] ?? null,
                        'detalle' => 'Registrada por Secretaría al corregir el grupo. Motivo: '.\Illuminate\Support\Str::limit($motivo, 300),
                    ]);
                }
            }

            $nota = '['.now()->format('d/m/Y H:i').' · '.trim($por->name.' '.$por->apellidos).'] Cambio de grupo. '
                .'Antes: '.($antes ?: 'sin compañeros').'. Ahora: '.$despues.'.'
                .($destino && $adoptarPti ? ' Adoptó el PTI del grupo.' : '').' Motivo: '.$motivo;
            $s->observaciones = trim(($s->observaciones ? $s->observaciones."\n" : '').$nota);
            $s->save();
        });

        // El estado (PTI aprobado, etc.) se calcula de los datos: se recalcula con lo nuevo.
        if (method_exists($s, 'recalcularEstado')) {
            $s->fresh()->recalcularEstado();
        }
    }
}
