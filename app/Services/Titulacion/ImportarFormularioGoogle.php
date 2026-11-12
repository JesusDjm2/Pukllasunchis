<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\Solicitud;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;

/**
 * Base para importar las respuestas de un formulario de Google de titulación (libro «Formularios de Titulación
 * (Respuestas)», una pestaña por formulario, o un CSV de una pestaña) como solicitudes (titulacion_solicitudes),
 * con su fecha original y sin enviar correos. Todas las respuestas (también las repetidas) quedan como historial;
 * una ya importada no se vuelve a importar (clave_importacion).
 *
 * Cada formulario (una subclase) dice qué pestaña es la suya (esHoja), qué columnas tiene (COLUMNAS: se
 * reconocen por su encabezado, no por el orden) y cómo se arma su solicitud (fila).
 */
abstract class ImportarFormularioGoogle
{
    /** Tipo de solicitud (Solicitud::PTI, Solicitud::ORIGINALIDAD…). */
    public const TIPO = '';

    /** Columna => opciones; cada opción, palabras (sin tildes, minúsculas) que debe tener el encabezado. La primera que calce gana. */
    protected const COLUMNAS = [];

    /** Columnas que se avisan en la vista previa si no se encuentran. */
    protected const IMPORTANTES = ['fecha', 'archivo', 'comprobante'];

    /** ¿Esta pestaña es la del formulario? Se decide por la fila de encabezados. */
    abstract protected static function esHoja(array $cabecera): bool;

    /**
     * Arma la solicitud de una respuesta. $txt($columna) da el texto de esa columna (vacío si no está).
     * Debe devolver al menos: usuario, metodo, motivo, integrantes, correo, celulares, programa, ciclo, titulo,
     * datos, archivo, comprobante, declaracion, seguimiento_id, estado y clave.
     */
    abstract protected static function fila(string $hoja, int $numFila, \Closure $txt, Carbon $fecha, string $crudoFecha, ReconocerPersonas $p): array;

    /**
     * Lee el archivo y reconoce a cada persona. No guarda nada.
     *
     * @return array{filas: Collection, hojas: array, faltan: array} una fila por respuesta
     */
    public static function leer(string $ruta): array
    {
        $libro = static::cargar($ruta);
        $personas = new ReconocerPersonas;
        $filas = collect();
        $hojas = [];
        $faltan = [];
        foreach ($libro->getWorksheetIterator() as $hoja) {
            $datos = array_values(array_filter($hoja->toArray(null, true, false, false),
                fn ($f) => trim(implode('', array_map('strval', $f))) !== ''));
            if (! $datos || ! static::esHoja($datos[0])) {
                continue;
            }
            $col = static::columnas($datos[0]);
            $hojas[] = $hoja->getTitle();
            $faltan = array_merge($faltan, array_keys(array_diff_key(array_flip(static::IMPORTANTES), $col)));
            foreach (array_slice($datos, 1) as $i => $f) {
                $txt = fn ($k) => isset($col[$k]) ? trim(preg_replace('/\s+/', ' ', (string) ($f[$col[$k]] ?? ''))) : '';
                $crudo = isset($col['fecha']) ? ($f[$col['fecha']] ?? null) : null;
                $fecha = is_numeric($crudo) ? Carbon::instance(FechaExcel::excelToDateTimeObject($crudo)) : (self::fecha((string) $crudo) ?? now());
                $filas->push(static::fila($hoja->getTitle(), $i + 2, $txt, $fecha, (string) $crudo, $personas)
                    + ['hoja' => $hoja->getTitle(), 'fila' => $i + 2, 'fecha' => $fecha]);
            }
        }

        $ya = Solicitud::whereIn('clave_importacion', $filas->pluck('clave'))->pluck('clave_importacion')->flip();

        return [
            'filas' => $filas->map(fn ($f) => $f + ['ya_importada' => isset($ya[$f['clave']])])->sortBy('fecha')->values(),
            'hojas' => $hojas,
            'faltan' => array_values(array_unique($faltan)),
        ];
    }

    /** Importa las filas reconocidas que no estaban. Devuelve [importadas, y cuántas quedaron en cada estado]. */
    public static function importar(Collection $filas): array
    {
        $cuenta = ['importadas' => 0, 'atendida' => 0, 'recibida' => 0];
        DB::transaction(function () use ($filas, &$cuenta) {
            foreach ($filas->filter(fn ($f) => $f['usuario'] && ! $f['ya_importada'])->sortBy('fecha') as $f) {
                $s = Solicitud::make([
                    'tipo' => static::TIPO,
                    'solicitante_id' => $f['usuario']->id,
                    'seguimiento_id' => $f['seguimiento_id'],
                    'correo' => Str::limit($f['correo'], 150, ''),
                    'integrantes' => $f['integrantes'],
                    'celulares' => Str::limit($f['celulares'], 200, ''),
                    'programa_estudios' => Str::limit($f['programa'], 100, ''),
                    'ciclo' => Str::limit($f['ciclo'], 30, ''),
                    'titulo' => trim($f['titulo'], " \"“”"),
                    'datos' => $f['datos'],
                    'archivo' => $f['archivo'],
                    'comprobante' => $f['comprobante'],
                    'declaracion' => $f['declaracion'],
                    'estado' => $f['estado'],
                    'origen' => 'formulario',
                    'clave_importacion' => $f['clave'],
                    'atendido_en' => $f['estado'] === 'atendida' ? $f['fecha'] : null,
                ]);
                $s->created_at = $f['fecha'];
                $s->updated_at = now();
                $s->save();
                $cuenta['importadas']++;
                $cuenta[$f['estado']] = ($cuenta[$f['estado']] ?? 0) + 1;
            }
        });

        return $cuenta;
    }

    // ── Auxiliares para las subclases ───────────────────────────────────

    /**
     * Reconoce al grupo y a quien envió.
     *  - Cada integrante: por su DNI o su correo si los escribió; si no, por su nombre (cuando es único).
     *  - Quien envió: por el correo de envío → el integrante 1 → otro integrante reconocido → un celular.
     *  - Quien envió queda siempre entre los integrantes.
     *
     * @param  array<int, array{nombre:string, dni?:string, correo?:string}>  $escritos
     * @return array{0: array, 1: ?User, 2: ?string} [integrantes [{nombre, user_id}], quien envió, cómo se reconoció]
     */
    protected static function reconocerGrupo(ReconocerPersonas $p, array $escritos, string $correoEnvio, string $celulares): array
    {
        $integrantes = [];
        $metodo1 = null;
        foreach (array_values($escritos) as $n => $e) {
            $nombre = trim($e['nombre'] ?? '');
            if ($nombre === '') {
                continue;
            }
            [$u, $como] = [null, null];
            if ($x = $p->porDni((string) ($e['dni'] ?? ''))) {
                [$u, $como] = [$x, 'dni'];
            } elseif (filled($e['correo'] ?? null) && ($x = $p->porCorreo($e['correo']))) {
                [$u, $como] = [$x, 'correo'];
            } elseif ($x = $p->porNombre($nombre)[0]) {
                [$u, $como] = [$x, 'nombre'];
            }
            if ($n === 0 && $u) {
                $metodo1 = $como;
            }
            $integrantes[] = ['nombre' => $nombre, 'user_id' => $u?->id];
        }

        [$usuario, $metodo] = [null, null];
        if ($correoEnvio !== '' && ($u = $p->porCorreo($correoEnvio))) {
            [$usuario, $metodo] = [$u, 'correo'];
        } elseif ($metodo1 && $integrantes) {
            [$usuario, $metodo] = [User::find($integrantes[0]['user_id']), $metodo1];
        } elseif ($id = collect($integrantes)->pluck('user_id')->filter()->first()) {
            [$usuario, $metodo] = [User::find($id), 'nombre'];
        } elseif ($u = $p->porCelular($celulares)) {
            [$usuario, $metodo] = [$u, 'celular'];
        }

        if ($usuario) {
            foreach ($integrantes as &$i) {
                if (! $i['user_id'] && $p->esLaMisma($i['nombre'], $usuario)) {
                    $i['user_id'] = $usuario->id;
                }
            }
            unset($i);
            if (! in_array($usuario->id, array_column($integrantes, 'user_id'), true)) {
                array_unshift($integrantes, ['nombre' => trim($usuario->apellidos.', '.$usuario->name), 'user_id' => $usuario->id]);
            }
        }

        return [$integrantes, $usuario, $metodo];
    }

    /** ¿Algún encabezado tiene todas estas palabras (sin tildes, minúsculas)? */
    protected static function tieneEncabezado(array $cabecera, array $palabras): bool
    {
        return collect($cabecera)->contains(function ($c) use ($palabras) {
            $t = Str::lower(Str::ascii((string) $c));

            return collect($palabras)->every(fn ($p) => str_contains($t, $p));
        });
    }

    protected static function clave(string $hoja, string $crudoFecha, string ...$partes): string
    {
        return sha1(implode('|', [$hoja, $crudoFecha, ...$partes]));
    }

    // ── Lectura ─────────────────────────────────────────────────────────

    /**
     * Un libro con muchas pestañas: primero se leen solo los encabezados de cada una y luego se carga completa
     * únicamente la del formulario (menos memoria en el servidor). Un CSV se lee entero (es una sola pestaña).
     */
    private static function cargar(string $ruta)
    {
        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(true);
        if ($lector instanceof \PhpOffice\PhpSpreadsheet\Reader\Csv) {
            return $lector->load($ruta);
        }
        $lector->setReadFilter(new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row === 1;
            }
        });
        $cabeceras = $lector->load($ruta);
        $suyas = [];
        foreach ($cabeceras->getWorksheetIterator() as $h) {
            if (static::esHoja($h->toArray(null, true, false, false)[0] ?? [])) {
                $suyas[] = $h->getTitle();
            }
        }
        $cabeceras->disconnectWorksheets();
        unset($cabeceras);

        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(true);
        $lector->setLoadSheetsOnly($suyas ?: ['__ninguna__']);

        return $lector->load($ruta);
    }

    /** Encabezados → índice de cada columna conocida. */
    private static function columnas(array $cabecera): array
    {
        $norm = array_map(fn ($c) => preg_replace('/\s+/', ' ', Str::lower(Str::ascii(trim((string) $c)))), $cabecera);
        $col = [];
        foreach (static::COLUMNAS as $clave => $opciones) {
            foreach ($opciones as $palabras) {
                foreach ($norm as $i => $t) {
                    if ($t !== '' && ! in_array($i, $col, true) && collect($palabras)->every(fn ($p) => str_contains($t, $p))) {
                        $col[$clave] = $i;
                        break 2;
                    }
                }
            }
        }

        return $col;
    }

    private static function fecha(string $t): ?Carbon
    {
        try {
            return trim($t) !== '' ? Carbon::parse(str_replace('/', '-', $t)) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
