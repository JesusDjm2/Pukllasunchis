<?php

namespace App\Console\Commands;

use App\Services\AylluPiImportService;
use Illuminate\Console\Command;

class AylluImportarProyectosCommand extends Command
{
    protected $signature = 'ayllu:importar-proyectos
        {--preview : Mostrar únicamente la vista previa y conciliación documental sin escribir en la base de datos}
        {--lectivo=2026 : Año lectivo para inicializar o verificar las ejecuciones anuales}';

    protected $description = 'Importa y concilia de forma transaccional e idempotente las 4 matrices Excel (114 entradas) y los 2 documentos Word de Proyectos Integradores Anuales.';

    public function handle(AylluPiImportService $importService): int
    {
        $preview = $importService->previsualizarImportacion();

        $this->info('=== CONCILIACIÓN DOCUMENTAL DE PROYECTOS INTEGRADORES (AYLLU PUKLLA) ===');
        $this->line('Código de lote determinístico : ' . $preview['codigo_lote']);
        $this->line('Versión del manifiesto        : ' . $preview['version_manifiesto']);
        $this->line('Total entradas de matriz      : ' . $preview['total_entradas'] . ' (esperadas: 114)');
        $this->line('Total definiciones/versiones  : ' . $preview['total_definiciones']);
        $this->line('Anomalías 5 dígitos (fechas)  : ' . $preview['total_anomalias_competencias'] . ' (esperadas: 9)');
        $this->line('Lote idéntico ya confirmado   : ' . ($preview['ya_importado_identico'] ? 'SÍ (ID ' . $preview['lote_existente_id'] . ')' : 'NO'));

        $filasCiclos = [];
        foreach ($preview['conteo_por_ciclo'] as $ciclo => $cantidad) {
            $filasCiclos[] = [$ciclo, $cantidad];
        }
        $this->table(['Ciclo', 'Entradas en Matriz'], $filasCiclos);

        if ($this->option('preview')) {
            $this->comment('Modo --preview finalizado sin realizar escrituras en la base de datos.');
            return self::SUCCESS;
        }

        $resultado = $importService->ejecutarImportacion(
            null,
            null,
            (string) $this->option('lectivo')
        );

        if ($resultado['idempotente']) {
            $this->info('Importación verificada de forma idempotente: no se duplicaron registros.');
        } else {
            $this->info(sprintf(
                'Importación confirmada en transacción: %d definiciones y %d aportes creados.',
                $resultado['definiciones_creadas'],
                $resultado['aportes_creados']
            ));
        }

        return self::SUCCESS;
    }
}
