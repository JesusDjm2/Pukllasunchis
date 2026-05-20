<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use Illuminate\Console\Command;

class ImportarMatriculadosExcel extends Command
{
    protected $signature = 'matriculas:importar
                            {archivo : Ruta al archivo CSV}
                            {--periodo= : ID del PeriodoActual. Si se omite, usa el periodo con actual=true}
                            {--columna=DNI : Nombre de la columna que contiene el DNI}
                            {--separador=; : Separador de columnas del CSV (por defecto punto y coma)}';

    protected $description = 'Importa matriculados al periodo actual desde un archivo CSV con columna DNI';

    public function handle(): int
    {
        $rutaArchivo = $this->argument('archivo');

        if (! file_exists($rutaArchivo)) {
            $this->error("El archivo no existe: {$rutaArchivo}");
            return self::FAILURE;
        }

        // Resolver período
        $periodoId = $this->option('periodo');
        if ($periodoId) {
            $periodo = PeriodoActual::find((int) $periodoId);
        } else {
            $periodo = PeriodoActual::where('actual', true)->first();
        }

        if (! $periodo) {
            $this->error('No se encontró un período válido. Usa --periodo=ID o asegúrate de tener un período con actual=true.');
            return self::FAILURE;
        }

        $this->info("Período destino: [{$periodo->id}] {$periodo->nombre}");

        $separador = $this->option('separador');
        $columnaKey = strtolower(trim($this->option('columna')));

        // Leer archivo completo
        $contenido = file_get_contents($rutaArchivo);
        // Quitar BOM UTF-8 si existe
        $contenido = str_replace("\xEF\xBB\xBF", '', $contenido);
        $lineas = explode("\n", str_replace("\r\n", "\n", str_replace("\r", "\n", $contenido)));

        // Encontrar la línea de encabezados (la que contiene "DNI")
        $headerIndex = null;
        $headers = [];
        foreach ($lineas as $i => $linea) {
            $cols = str_getcsv($linea, $separador);
            $colsLower = array_map(fn($c) => strtolower(trim($c)), $cols);
            if (in_array($columnaKey, $colsLower)) {
                $headerIndex = $i;
                $headers = $colsLower;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->error("No se encontró la columna '{$columnaKey}' en el archivo. Revisa el nombre de columna con --columna=NOMBRE");
            return self::FAILURE;
        }

        $colIndex = array_search($columnaKey, $headers);
        $this->info("Encabezados encontrados en fila " . ($headerIndex + 1) . ". Columna DNI en posición " . ($colIndex + 1) . ".");

        $matriculados = 0;
        $noEncontrados = [];
        $yaExistian = 0;
        $omitidos = 0;

        // Procesar filas de datos (después del header)
        $filasDatos = array_slice($lineas, $headerIndex + 1);

        foreach ($filasDatos as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }

            $cols = str_getcsv($linea, $separador);
            $dni = trim($cols[$colIndex] ?? '');

            // Ignorar filas sin DNI o con DNI no numérico (títulos de sección)
            if ($dni === '' || ! ctype_digit($dni)) {
                $omitidos++;
                continue;
            }

            $alumno = Alumno::where('dni', $dni)->first();

            if (! $alumno) {
                $noEncontrados[] = $dni . ' — ' . trim($cols[$colIndex + 1] ?? '');
                continue;
            }

            $resultado = Matricula::updateOrCreate(
                ['alumno_id' => $alumno->id, 'periodo_actual_id' => $periodo->id],
                ['fecha_completado' => now(), 'estado' => 'matriculado']
            );

            if ($resultado->wasRecentlyCreated) {
                $matriculados++;
            } else {
                $yaExistian++;
            }
        }

        $this->newLine();
        $this->info("Importación completada para el período: {$periodo->nombre}");
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Matriculados nuevos registrados', $matriculados],
                ['Ya existían (sin cambios)', $yaExistian],
                ['Filas de sección omitidas', $omitidos],
                ['DNIs no encontrados en BD', count($noEncontrados)],
            ]
        );

        if (! empty($noEncontrados)) {
            $this->warn('DNIs no encontrados en la base de datos:');
            foreach ($noEncontrados as $entrada) {
                $this->line("  - {$entrada}");
            }
            $this->line('');
            $this->line('Estos alumnos pueden no tener cuenta en el sistema todavía.');
        }

        return self::SUCCESS;
    }
}
