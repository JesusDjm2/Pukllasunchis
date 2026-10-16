<?php

namespace App\Console\Commands;

use App\Models\Titulacion\Importacion;
use App\Services\Titulacion\NombresImportados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Arreglo de lo ya importado antes de que el importador separara grupos:
 * las filas cuya celda de nombre traía varios integrantes (uno por línea)
 * se parten en una fila por persona y cada una se busca por separado.
 *
 * La fila grupal original NO se borra: queda con estado_match='dividido'
 * (fuera de todos los listados) por si hubiera que revisar o deshacer.
 * También completa apellidos/nombres de filas individuales que vienen como
 * "Apellidos, Nombres". Se puede correr varias veces sin duplicar.
 */
class TitulacionSepararGruposCommand extends Command
{
    protected $signature = 'titulacion:separar-grupos {--dry-run : Solo muestra el resultado, sin guardar}';

    protected $description = 'Parte en una fila por persona las filas de staging de exposiciones grupales';

    public function handle(TitulacionImportarCommand $importador): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = ['grupos' => 0, 'personas' => 0, 'vinculado' => 0, 'ambiguo' => 0, 'sin_match' => 0, 'nombres_separados' => 0];

        DB::transaction(function () use ($importador, $dryRun, &$stats) {
            $filas = Importacion::whereIn('estado_match', ['sin_match', 'ambiguo', 'vinculado'])
                ->whereNotNull('nombre_estudiante')
                ->get();

            foreach ($filas as $fila) {
                $datos = $fila->datos_normalizados ?? [];
                $integrantes = NombresImportados::integrantes($fila->nombre_estudiante);

                if (count($integrantes) <= 1) {
                    // Individual: solo completar apellidos/nombres si vienen con coma.
                    $conNombres = $importador->filasPorIntegrante($datos)[0];
                    if (($conNombres['apellidos'] ?? null) !== ($datos['apellidos'] ?? null)) {
                        $stats['nombres_separados']++;
                        if (! $dryRun) {
                            $fila->update(['datos_normalizados' => $conNombres]);
                        }
                    }
                    continue;
                }

                $stats['grupos']++;
                foreach ($importador->filasPorIntegrante($datos + ['nombre_estudiante' => $fila->nombre_estudiante]) as $datosPersona) {
                    $normalizado = $importador->normalizarNombre($datosPersona['nombre_estudiante']);
                    [$userId, $estado] = $importador->matchPorNombre(
                        $normalizado,
                        null,
                        $fila->origen === 'expo-sust-marilyn' ? ($datosPersona['tipo_k'] ?? '') : null
                    );
                    $stats['personas']++;
                    $stats[$estado]++;

                    if (! $dryRun) {
                        Importacion::create([
                            'lote_importacion' => $fila->lote_importacion,
                            'origen' => $fila->origen,
                            'archivo_nombre' => $fila->archivo_nombre,
                            'fila_num' => $fila->fila_num,
                            'dni' => null,
                            'nombre_estudiante' => $datosPersona['nombre_estudiante'],
                            'nombre_normalizado' => $normalizado ?: null,
                            'user_id' => $userId,
                            'estado_match' => $estado,
                            'datos_originales' => $fila->datos_originales,
                            'datos_normalizados' => $datosPersona,
                            'importado_por' => $fila->importado_por,
                        ]);
                    }
                }

                if (! $dryRun) {
                    $fila->update(['estado_match' => 'dividido']);
                }
            }
        });

        $this->table(['Resultado', 'Cantidad'], [
            ['Filas grupales partidas', $stats['grupos']],
            ['Personas resultantes', $stats['personas']],
            ['  vinculadas a una cuenta existente', $stats['vinculado']],
            ['  ambiguas (más de un candidato)', $stats['ambiguo']],
            ['  sin cuenta todavía', $stats['sin_match']],
            ['Filas individuales con apellidos/nombres separados por coma', $stats['nombres_separados']],
        ]);
        $this->info($dryRun ? '[dry-run] No se guardó nada.' : 'Listo. Las filas grupales originales quedaron como estado_match=dividido.');

        return self::SUCCESS;
    }
}
