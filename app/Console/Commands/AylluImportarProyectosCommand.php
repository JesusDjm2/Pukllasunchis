<?php

namespace App\Console\Commands;

use App\Services\AylluPiImportService;
use Illuminate\Console\Command;

class AylluImportarProyectosCommand extends Command
{
    protected $signature = 'ayllu:importar-proyectos
        {--preview : Mostrar únicamente la vista previa y conciliación documental sin escribir en la base de datos}
        {--preview-repair : Mostrar vista previa de registros persistidos con diferencias técnicas de correspondencia/hitos sin escribir en la base de datos}
        {--repair : Aplicar reparación técnica acotada, transaccional e idempotente preservando evidencias, versiones e historial}
        {--lectivo=2026 : Año lectivo para inicializar o verificar las ejecuciones anuales}';

    protected $description = 'Importa, concilia y repara de forma transaccional e idempotente las 4 matrices Excel (114 entradas) y los 2 documentos Word de Proyectos Integradores Anuales.';

    public function handle(AylluPiImportService $importService): int
    {
        if ($this->option('preview-repair') || $this->option('repair')) {
            $previewRepair = $importService->previsualizarReparacionTecnica();
            $this->info('=== DIAGNÓSTICO Y VISTA PREVIA DE REPARACIÓN TÉCNICA (AYLLU PUKLLA) ===');
            $this->line('Versión del manifiesto canónico : ' . $previewRepair['version_manifiesto']);
            $this->line('Definiciones con diferencias    : ' . $previewRepair['total_definiciones_afectadas']);
            $this->line('Aportes de curso con diferencias: ' . $previewRepair['total_aportes_afectados']);

            foreach ($previewRepair['definiciones_afectadas'] as $defItem) {
                $this->warn(sprintf(
                    ' -> [Definición #%d | Año %d | v%d (%s)] Fuente: %s (%s)',
                    $defItem['id'],
                    $defItem['anio_formativo'],
                    $defItem['version_numero'],
                    $defItem['codigo_fuente'],
                    $defItem['archivo_origen'],
                    $defItem['tipo_correccion']
                ));
                $this->line('    Valor anterior : ' . json_encode($defItem['valor_anterior'], JSON_UNESCAPED_UNICODE));
                $this->line('    Valor propuesto: ' . json_encode($defItem['correccion_propuesta'], JSON_UNESCAPED_UNICODE));
            }

            foreach ($previewRepair['aportes_afectados'] as $apItem) {
                $this->warn(sprintf(
                    ' -> [Aporte #%d (Pos #%d) | Año %d Ciclo %s | %s (%s)]',
                    $apItem['id'],
                    $apItem['posicion_global'],
                    $apItem['anio_formativo'],
                    $apItem['ciclo_romano'],
                    $apItem['archivo_fuente'],
                    $apItem['celda_curso']
                ));
                $this->line('    Diferencias    : ' . json_encode($apItem['diferencias'], JSON_UNESCAPED_UNICODE));
            }

            if ($this->option('preview-repair') && !$this->option('repair')) {
                $this->comment('Modo --preview-repair finalizado sin realizar escrituras en la base de datos.');
                return self::SUCCESS;
            }

            $resRepair = $importService->ejecutarReparacionTecnica();
            if ($resRepair['idempotente']) {
                $this->info('Reparación técnica verificada de forma idempotente: 0 registros requerían cambios.');
            } else {
                $this->info(sprintf(
                    'Reparación técnica confirmada en transacción: %d definiciones y %d aportes actualizados preservando historial y evidencias.',
                    $resRepair['definiciones_reparadas'],
                    $resRepair['aportes_reparados']
                ));
            }

            return self::SUCCESS;
        }

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
