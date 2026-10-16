<?php

namespace App\Console\Commands;

use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Recupera los enlaces a los PDF de las RD que en el "Cuadro de exposiciones"
 * viven como HIPERVÍNCULOS dentro de las celdas (se ve "RD 23-2021 PTI.pdf",
 * el enlace a Drive va por detrás). Al exportar a CSV esos enlaces se pierden,
 * por eso este comando lee el .xlsx.
 *
 * Cruce: DNI de la fila (columna "DNI"; puede traer varios si es un grupo) →
 * seguimiento → resolución del tipo de la columna. Si la persona tiene varias
 * RD de ese tipo, se elige por el número que aparece en el texto de la celda.
 * Si la RD todavía no está registrada (persona sin cuenta, hoja sin importar),
 * el enlace NO se guarda en otro lado: se reporta, y al volver a correr el
 * comando tras importarla se adjunta. Idempotente: nunca pisa un enlace ya
 * asignado.
 */
class TitulacionImportarEnlacesCommand extends Command
{
    protected $signature = 'titulacion:importar-enlaces
        {archivo : Ruta del .xlsx (Cuadro de exposiciones - Titulacion.xlsx)}
        {--hoja=* : Hojas a leer (por defecto "EXP (FID)" y "EXP. (PPD) 1")}
        {--dry-run : Solo mostrar qué se haría}';

    protected $description = 'Adjunta a las resoluciones de titulación los enlaces (hipervínculos) de los PDF de RD del Excel';

    /** Tipo de RD según el encabezado de la columna (primer patrón que calce). */
    private const TIPO_POR_ENCABEZADO = [
        '/cambio/i' => 'CambioAsesor',
        '/extensi/i' => 'Extension',
        '/tesis/i' => 'Tesis',
        '/\bTI\b|expo/i' => 'Exposicion',
        '/PTI/i' => 'PTI',
    ];

    public function handle(): int
    {
        $archivo = $this->argument('archivo');
        if (! is_file($archivo)) {
            $this->error("No existe el archivo: {$archivo}");

            return self::FAILURE;
        }

        $hojas = $this->option('hoja') ?: ['EXP (FID)', 'EXP. (PPD) 1'];
        $libro = IOFactory::createReader('Xlsx')->load($archivo);
        $seguimientoPorDni = $this->seguimientosPorDni();
        $dryRun = (bool) $this->option('dry-run');

        $conteo = ['resolucion' => 0, 'ya_tenia' => 0, 'otro_archivo' => 0, 'sin_rd' => 0, 'sin_persona' => 0, 'ambiguo' => 0, 'columna_ignorada' => 0];
        $sinPersona = [];
        $ambiguos = [];

        DB::beginTransaction();
        try {
            foreach ($hojas as $nombreHoja) {
                $hoja = $libro->getSheetByName($nombreHoja);
                if (! $hoja) {
                    $this->warn("No existe la hoja \"{$nombreHoja}\"; se omite.");
                    continue;
                }

                $encabezados = [];
                foreach ($hoja->getRowIterator(1, 1)->current()->getCellIterator() as $celda) {
                    $encabezados[$celda->getColumn()] = trim(preg_replace('/\s+/', ' ', (string) $celda->getValue()));
                }
                $colDni = array_search('DNI', array_map('strtoupper', $encabezados), true);

                foreach ($hoja->getHyperlinkCollection() as $coord => $hipervinculo) {
                    [$col, $fila] = Coordinate::coordinateFromString($coord);
                    $url = trim((string) $hipervinculo->getUrl());
                    $tipo = $this->tipoDeColumna($encabezados[$col] ?? '');
                    if (! $tipo || ! preg_match('~^https?://~i', $url)) {
                        $conteo['columna_ignorada']++;
                        continue;
                    }

                    $texto = trim((string) $hoja->getCell($coord)->getValue());
                    preg_match_all('/\d{7,8}/', (string) $hoja->getCell($colDni.$fila)->getValue(), $m);
                    $dnis = array_unique($m[0]);
                    $nombreFila = trim((string) $hoja->getCell('D'.$fila)->getValue());

                    $seguimientos = collect($dnis)->map(fn ($dni) => $seguimientoPorDni[$dni] ?? null)->filter();
                    if ($seguimientos->isEmpty()) {
                        $conteo['sin_persona']++;
                        $sinPersona[] = [$nombreHoja, $fila, implode(' ', $dnis) ?: '—', mb_strimwidth($nombreFila, 0, 40, '…'), $tipo];
                        continue;
                    }

                    // Una RD de grupo sirve para todos los integrantes de la fila.
                    foreach ($seguimientos as $seguimientoId) {
                        $resultado = $this->adjuntar($seguimientoId, $tipo, $url, $texto, $dryRun);
                        $conteo[$resultado]++;
                        if ($resultado === 'ambiguo') {
                            $ambiguos[] = [$nombreHoja, $fila, mb_strimwidth($nombreFila, 0, 40, '…'), $tipo, mb_strimwidth($texto, 0, 40, '…')];
                        }
                    }
                }
            }

            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->table(['Resultado', 'Enlaces'], [
            ['Adjuntado a su resolución', $conteo['resolucion']],
            ['La resolución ya tenía ese enlace', $conteo['ya_tenia']],
            ['La resolución ya tiene OTRO enlace (no se pisa)', $conteo['otro_archivo']],
            ['La RD todavía no está registrada (volver a correr tras importarla)', $conteo['sin_rd']],
            ['Persona sin proceso de titulación en el sistema', $conteo['sin_persona']],
            ['Varias RD del mismo tipo y el número no las distingue', $conteo['ambiguo']],
            ['Celda fuera de las columnas de RD', $conteo['columna_ignorada']],
        ]);

        if ($ambiguos) {
            $this->warn('Revisar a mano (no se adjuntó):');
            $this->table(['Hoja', 'Fila', 'Nombre', 'Tipo', 'Texto celda'], $ambiguos);
        }
        if ($sinPersona && $this->output->isVerbose()) {
            $this->table(['Hoja', 'Fila', 'DNI', 'Nombre', 'Tipo'], $sinPersona);
        }

        $this->line($dryRun ? '<comment>Dry-run: no se guardó nada.</comment>' : '<info>Listo.</info>');

        return self::SUCCESS;
    }

    private function tipoDeColumna(string $encabezado): ?string
    {
        foreach (self::TIPO_POR_ENCABEZADO as $patron => $tipo) {
            if (preg_match($patron, $encabezado)) {
                return $tipo;
            }
        }

        return null;
    }

    /** DNI → seguimiento_id, buscando el DNI en users, alumnos y ppds. */
    private function seguimientosPorDni(): array
    {
        $porUsuario = SeguimientoTitulacion::pluck('id', 'user_id');
        $mapa = [];
        foreach ([['users', 'id'], ['alumnos', 'user_id'], ['ppds', 'user_id']] as [$tabla, $colUser]) {
            foreach (DB::table($tabla)->whereNotNull('dni')->where('dni', '!=', '')->get(['dni', $colUser.' as uid']) as $fila) {
                $dni = preg_replace('/\D/', '', (string) $fila->dni);
                if ($dni !== '' && isset($porUsuario[$fila->uid])) {
                    $mapa[$dni] ??= $porUsuario[$fila->uid];
                }
            }
        }

        return $mapa;
    }

    /** Número principal de una RD o de un nombre de archivo ("RD 0261- Aprob..." → 261). */
    private function numero(?string $texto): ?int
    {
        return preg_match('/(\d{1,4})/', (string) $texto, $m) ? (int) $m[1] : null;
    }

    private function adjuntar(int $seguimientoId, string $tipo, string $url, string $texto, bool $dryRun): string
    {
        $todas = Resolucion::where('seguimiento_id', $seguimientoId)->get();
        if ($todas->contains('archivo', $url)) {
            return 'ya_tenia';
        }

        $numero = $this->numero($texto);
        $mismoNumero = fn ($r) => $numero !== null && $this->numero($r->numero_rd) === $numero;

        $candidatas = $todas->where('tipo', $tipo);
        if ($candidatas->isEmpty()) {
            // A veces el PDF está en otra columna (p. ej. la RD de PTI puesta en
            // "Cambio de asesor"): se busca entre TODAS sus RD la del mismo
            // texto o, si es única, la del mismo número.
            $candidatas = $todas->filter(fn ($r) => trim((string) $r->numero_rd) === $texto);
            if ($candidatas->isEmpty()) {
                $candidatas = $todas->filter($mismoNumero);
            }
            if ($candidatas->isEmpty()) {
                // La RD aún no está registrada (p. ej. hoja PPD sin importar): no se
                // inventa; al volver a correr el comando tras importarla, se adjunta.
                return 'sin_rd';
            }
        }

        $elegida = $candidatas->count() === 1 ? $candidatas->first() : $candidatas->first($mismoNumero);

        // Varias sin forma de distinguir, o la única tiene OTRO número: a mano.
        if (! $elegida || ($numero !== null && $this->numero($elegida->numero_rd) !== null && ! $mismoNumero($elegida))) {
            return 'ambiguo';
        }
        if (filled($elegida->archivo)) {
            return 'otro_archivo';
        }

        $dryRun || $elegida->update(['archivo' => $url]);

        return 'resolucion';
    }
}
