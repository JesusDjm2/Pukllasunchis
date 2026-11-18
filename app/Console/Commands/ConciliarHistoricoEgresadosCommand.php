<?php

namespace App\Console\Commands;

use App\Services\SeguimientoEgresado\ConciliacionHistoricoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ConciliarHistoricoEgresadosCommand extends Command
{
    protected $signature = 'puklla:historico:conciliar
        {--source= : Ruta local restringida al ZIP o directorio del paquete histórico}
        {--dry-run : Ejecutar simulación de solo lectura sin escribir en la base de datos (por defecto)}
        {--apply : Persistir lotes y observaciones únicamente en base aislada de ensayo}
        {--integrar-perfiles : Integrar respuestas de Encuesta 2026-I e histórico en perfiles de egresados y Visor Territorial}
        {--database= : Nombre de la base de datos de ensayo autorizada (p. ej. puklla_ensayo)}
        {--confirm-isolated : Confirmación explícita de que la base de destino es un entorno aislado}
        {--revert-lote= : ID de lote de ensayo a revertir de forma auditable}
        {--motivo=Reversión controlada de lote de ensayo : Motivo de la reversión}';

    protected $description = 'Audita, concilia e importa en modo dry-run o en base aislada de ensayo los antecedentes históricos 2026 sin tocar registros académicos oficiales ni exponer PII.';

    public function handle(ConciliacionHistoricoService $servicio): int
    {
        $targetDb = $this->option('database');
        if ($targetDb) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $targetDb);
            DB::reconnect('mysql');
        }

        if ($this->option('revert-lote')) {
            $loteId = (int) $this->option('revert-lote');
            $motivo = (string) $this->option('motivo');
            $resultado = $servicio->revertirLoteEnsayo($loteId, 0, $motivo);
            $this->info("Lote #{$resultado['lote_id']} revertido ({$resultado['registros_revertidos']} registros, {$resultado['observaciones_revertidas']} observaciones).");
            return self::SUCCESS;
        }

        $source = (string) $this->option('source');
        if ($source === '') {
            $this->error('Debe indicar --source=/ruta/privada/al/paquete.');
            return self::FAILURE;
        }

        if ($this->option('integrar-perfiles')) {
            $res = $servicio->integrarPerfilesYVisorTerritorial($source);
            $this->info('Integración de Encuesta 2026-I y perfiles en Visor Territorial completada:');
            $this->table(array_keys($res), [array_values($res)]);
            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply') && !$this->option('dry-run');
        $confirmIsolated = (bool) $this->option('confirm-isolated');

        $reporte = $servicio->ejecutarPaquete(
            $source,
            $apply,
            $confirmIsolated,
            $targetDb ? (string) $targetDb : null,
            null
        );

        $this->info(sprintf(
            'Modo: %s | BD efectiva: %s | Entorno: %s | Archivos SHA-256 verificados: %d',
            strtoupper($reporte['modo']),
            $reporte['base_datos_efectiva'],
            $reporte['entorno'],
            $reporte['hashes_verificados']
        ));

        $filasTabla = [];
        foreach ($reporte['fuentes'] as $f) {
            $filasTabla[] = [
                $f['fuente_codigo'],
                $f['filas_leidas'],
                $f['personas_unicas_en_fuente'],
                $f['duplicados_descartados_en_lote'],
                $f['vinculo_confirmado'],
                $f['identidad_confirmada_trayectoria_pendiente'],
                $f['candidato_revision'],
                $f['candidato_ambiguo'],
                $f['sin_correspondencia'],
                $f['observaciones_importables'],
                $f['observaciones_bloqueadas_cuarentena'],
            ];
        }

        $this->table([
            'Fuente',
            'Filas',
            'Únicos',
            'Duplicados',
            'Vínculo conf.',
            'Id. conf./Tray. pend.',
            'Cand. revisión',
            'Ambiguos',
            'Sin match',
            'Importables',
            'En cuarentena',
        ], $filasTabla);

        return self::SUCCESS;
    }
}
