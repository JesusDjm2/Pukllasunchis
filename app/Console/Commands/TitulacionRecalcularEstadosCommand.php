<?php

namespace App\Console\Commands;

use App\Models\Titulacion\SeguimientoTitulacion;
use App\Services\Titulacion\AvanceTitulacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recalcula el estado de TODOS los seguimientos a partir de sus datos (ver
 * AvanceTitulacion) y alinea pti.estado con su RD. Necesario una vez tras la
 * migración 2026_09_30_000001 (antes el estado se elegía a mano y no coincidía
 * con los datos); después, el estado se mantiene solo al guardar.
 */
class TitulacionRecalcularEstadosCommand extends Command
{
    protected $signature = 'titulacion:recalcular-estados {--dry-run : Solo mostrar qué cambiaría}';

    protected $description = 'Recalcula el estado de titulación de cada seguimiento según sus PTI/TI/Tesis/RD';

    public function handle(): int
    {
        $seguimientos = SeguimientoTitulacion::with(['estadoTitulacion', 'pti', 'tesis', 'actas', 'resoluciones'])->get();

        $cambios = [];
        $resumen = [];
        foreach ($seguimientos as $seguimiento) {
            $avance = new AvanceTitulacion($seguimiento);
            $nuevo = $avance->estadoNombre();
            $anterior = $seguimiento->estadoTitulacion?->nombre ?? '—';
            $resumen[$nuevo] = ($resumen[$nuevo] ?? 0) + 1;

            if ($anterior !== $nuevo) {
                $cambios["{$anterior} → {$nuevo}"] = ($cambios["{$anterior} → {$nuevo}"] ?? 0) + 1;
            }
        }

        $this->info('Estado calculado de '.$seguimientos->count().' seguimientos:');
        $this->table(['Estado', 'Personas'], collect(AvanceTitulacion::ESTADOS)
            ->map(fn ($nombre) => [$nombre, $resumen[$nombre] ?? 0])->values()->all());

        if ($cambios) {
            arsort($cambios);
            $this->info('Cambios respecto al estado guardado:');
            $this->table(['Antes → Ahora', 'Personas'], collect($cambios)->map(fn ($n, $k) => [$k, $n])->values()->all());
        }

        $ptisPorAprobar = $seguimientos->filter(fn ($s) => $s->pti && $s->pti->estado === 'pendiente'
            && (new AvanceTitulacion($s))->hitos['pti_aprobado']['logrado'])->count();
        $this->line("PTI con RD que pasan a \"aprobado\": {$ptisPorAprobar}");

        if ($this->option('dry-run')) {
            $this->warn('Dry-run: no se guardó nada.');

            return self::SUCCESS;
        }

        DB::transaction(fn () => $seguimientos->each->recalcularEstado());
        $this->info('Listo: estados recalculados.');

        return self::SUCCESS;
    }
}
