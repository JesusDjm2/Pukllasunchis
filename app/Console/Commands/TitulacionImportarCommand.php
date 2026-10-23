<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use App\Models\Titulacion\Importacion;
use App\Models\ppd;
use App\Models\User;
use App\Services\Titulacion\NombresImportados;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Importa los CSV de seguimiento de titulación de Carlos y Marilyn a la
 * tabla de staging `titulacion_importaciones` — NUNCA escribe directo en
 * seguimiento_titulacion/titulacion_ptis/etc. Cada archivo tiene su propia
 * estructura de columnas y su propia forma de identificar al egresado
 * (DNI en los archivos de Marilyn, solo nombre en los de Carlos y en
 * EXPO-SUST), así que cada uno se procesa con su propio mapeo de columnas
 * y su propia estrategia de vinculación — nunca se adivina "a ojo" cuando
 * hay ambigüedad: si no hay match único y confiable, la fila queda
 * `sin_match`/`ambiguo` para revisión manual en el panel de sincronización
 * (Fase 6), en vez de fusionar por error a dos personas distintas.
 */
class TitulacionImportarCommand extends Command
{
    protected $signature = 'titulacion:importar
                            {archivo : Ruta al archivo CSV}
                            {origen : ppd-carlos|fid-carlos|exp-ppd-marilyn|exp-fid-marilyn|expo-sust-marilyn}
                            {--delimiter=, : Separador de columnas del CSV}
                            {--dry-run : Solo muestra el resultado, no escribe nada en la BD}';

    protected $description = 'Importa un CSV de seguimiento de titulación (Carlos/Marilyn) a la zona de staging titulacion_importaciones';

    /** @var array<string, array{label: string, match_por: 'dni'|'nombre', modelo: string, campos: array<string, string[]>}> */
    private array $origenes;

    public function __construct()
    {
        parent::__construct();

        $this->origenes = [
            'ppd-carlos' => [
                'label' => 'PPD — Carlos (seguimiento PTI/Tesis)',
                'match_por' => 'nombre',
                'modelo' => ppd::class,
                'campos' => [
                    'anio_ingreso' => ['Año de Ingreso'],
                    'programa' => ['Programa'],
                    'situacion' => ['SITUACIÓN'],
                    'nombre_estudiante' => ['Estudiante'],
                    'celular' => ['Núm. celular'],
                    'titulo_tesis' => ['Título de Tesis'],
                    'asesor' => ['Asesor/a'],
                    'rd_pti' => ['RD PTI'],
                    'link_iti' => ['Link ITI'],
                    'fecha_exposicion' => ['Fecha de Exp. TI'],
                    'jurado_exposicion' => ['Jurado-Exp. TI'],
                    'grupo_exposicion' => ['Grupo de Exposición de TI'],
                    'grado_bachiller' => ['Grado Bachiller'],
                    'url_ti_repositorio' => ['URL TI Repositorio'],
                    'dictaminante_1' => ['Dictaminante 1'],
                    'link_dictamen_1' => ['Link Dictamen 1'],
                    'dictaminante_2' => ['Dictaminante 2'],
                    'link_dictamen_2' => ['Link Dictaminante 2'],
                    'fecha_sustentacion' => ['Fecha-Sus.Tesis'],
                    'jurado_sustentacion' => ['Jurado Sus.Tesis'],
                    'grupo_sustentacion' => ['Grupo de Sustentación Tesis'],
                    'enlace_tesis' => ['Enlace de tesis (Licentura Ok)'],
                    'situacion_tesis' => ['Situación del Tesis'],
                    'url_repositorio' => ['URL Repositorio'],
                    'grado_licenciado' => ['Grado Licenciado'],
                    'licenciado_sunedu' => ['Licenciado en Sunedu'],
                ],
            ],
            'fid-carlos' => [
                'label' => 'FID — Carlos (seguimiento PTI/Tesis)',
                'match_por' => 'nombre',
                'modelo' => Alumno::class,
                'campos' => [
                    'num' => ['No.'],
                    'anio_ingreso' => ['AÑO INGRESO'],
                    'programa' => ['Programa Estudios'],
                    'situacion' => ['SITUACIÓN'],
                    'nombre_estudiante' => ['Estudiante'],
                    'celular' => ['Núm. celular'],
                    'titulo_ti' => ['Título TI'],
                    'asesor' => ['Asesor/a'],
                    'titulo_tesis' => ['Título de Tesis'],
                    'observaciones' => ['Observaciones'],
                    'rd_pti' => ['Rd PTI'],
                    'link_iti' => ['Link ITI'],
                    'fecha_exposicion' => ['Fecha Exp. TI'],
                    'jurado_exposicion' => ['Jurado-Exp. TI', 'Jurado - Exp. TI'],
                    'grado_bachiller' => ['Grado Bachiller'],
                    'url_ti_repositorio' => ['URL TI Repositorio'],
                    'grupo_exposicion' => ['Grupo de Exposición TI'],
                    'dictaminante_1' => ['Dictaminante 1'],
                    'link_dictamen_1' => ['Link Dictamen 1'],
                    'dictaminante_2' => ['Dictaminante 2'],
                    'link_dictamen_2' => ['Link Dictamen 2'],
                    'fecha_sustentacion' => ['Fecha-Sus.Tesis'],
                    'jurado_sustentacion' => ['Jurado Sus.Tesis'],
                    'grupo_sustentacion' => ['Grupo de Sustentación Tesis'],
                    'enlace_tesis' => ['Enlace de tesis (Licenciatura Ok)'],
                    'situacion_tesis' => ['Situación del Tesis'],
                    'url_repositorio' => ['URL Repositorio'],
                    'grado_licenciado' => ['Grado Licenciado'],
                    'registro_sunedu' => ['Registro SUNEDU'],
                ],
            ],
            'exp-ppd-marilyn' => [
                'label' => 'EXP. PPD — Marilyn (expedientes)',
                'match_por' => 'dni',
                'modelo' => ppd::class,
                'campos' => [
                    'periodo' => ['PERIODO'],
                    'programa' => ['PROGRAMA'],
                    'dni' => ['DNI'],
                    'nombre_estudiante' => ['APELLIDOS Y NOMBRES'],
                    'num_expediente' => ['Nº EXPEDIENTE'],
                    'correo' => ['CORREOS'],
                    'fecha_pti' => ['FECHA DE PTI'],
                    'rd_pti' => ['PTI'],
                    'fecha_exposicion' => ['FECHA DE EXPO'],
                    'rd_ti' => ['RD TI'],
                    'bachiller_sunedu' => ['Bachiller SUNEDU'],
                    'rd_extension' => ['RD EXTENSION'],
                    'rd_tesis' => ['RD TESIS'],
                ],
            ],
            'exp-fid-marilyn' => [
                'label' => 'EXP. FID — Marilyn (expedientes)',
                'match_por' => 'dni',
                'modelo' => Alumno::class,
                'campos' => [
                    'periodo' => ['PERIODO'],
                    'programa' => ['PRO. ESTUDIOS'],
                    'dni' => ['DNI'],
                    'nombre_estudiante' => ['APELLIDOS Y NOMBRES'],
                    'num_expediente' => ['Nº EXPEDIENTE'],
                    'correo' => ['CORREOS'],
                    'fecha_pti' => ['FECHA DE PTI'],
                    'rd_pti' => ['RD .PTI'],
                    'fecha_exposicion' => ['FECHA DE EXPO'],
                    'rd_ti' => ['RD TI'],
                    'cambio_asesor' => ['CAMBIO DE ASESOR/TEMA'],
                    'rd_extension' => ['RD EXTENSION'],
                    'fecha_sustentacion' => ['FECHA SUSTENTACIÓN'],
                    'rd_tesis' => ['RD TESIS'],
                ],
            ],
            'expo-sust-marilyn' => [
                'label' => 'EXPO-SUST — Marilyn (actas)',
                'match_por' => 'nombre',
                'modelo' => null, // mezcla FID/PPD por fila (columna "k"), se busca en ambas tablas
                'campos' => [
                    // "EXPO FID", "TESIS PPD"…: en la hoja vieja la columna se llamaba "k";
                    // en la actual la primera columna no tiene título ('').
                    'tipo_k' => ['k', ''],
                    'ingreso' => ['INGRESO'],
                    'fecha_acta' => ['fecha de exposicion / sustentacion'],
                    'expediente' => ['EXPEDIENTE'],
                    'carrera' => ['CARRERA'],
                    'nombre_estudiante' => ['APELLIDOS Y NOMBRES'],
                    'promedio' => ['PROMEDIO GENERAL'],
                    'titulo_trabajo' => ['Titulo trabajo de investigacion'],
                    'num_pag_acta' => ['Nº de pag. Acta'],
                    'asesor' => ['asesor/a'],
                    'jurado_1' => ['jurado 1'],
                    'jurado_2' => ['jurado 2'],
                    'jurado_3' => ['jurado 3'],
                    // La hoja real de secretaría trae este encabezado con un
                    // salto de línea Y un typo ("juerado" en vez de "jurado")
                    // dentro de la celda: "jurado 4\njuerado invitado". Se
                    // deja tal cual como alias (la normalización ya colapsa
                    // saltos de línea a espacios) en vez de "corregir" el
                    // typo del origen.
                    'jurado_4' => ['jurado 4 (jurado invitado)', 'jurado 4 juerado invitado'],
                ],
            ],
            'ppd-unificado' => [
                'label' => 'PPD — CSV unificado (Carlos+Marilyn ya fusionado a mano por el usuario)',
                'match_por' => 'dni',
                'modelo' => ppd::class,
                'campos' => [
                    'dni' => ['dni'],
                    'nombres' => ['nombres'],
                    'apellidos' => ['apellidos'],
                    'email' => ['email'],
                    'celular' => ['celular'],
                    'condicion' => ['condicion'],
                    'programa' => ['programa'],
                    'anio_ingreso' => ['anio_ingreso'],
                    'numero_expediente' => ['numero_expediente'],
                    'sunedu' => ['sunedu'],
                    'titulo_pti' => ['titulo_pti'],
                    'asesor_pti' => ['asesor_pti'],
                    'fecha_pti' => ['fecha_pti'],
                    'rd_pti' => ['rd_pti'],
                    'link_iti' => ['link_iti'],
                    'estado_pti' => ['estado_pti'],
                    'fecha_exposicion' => ['fecha_exposicion'],
                    'rd_exposicion' => ['rd_exposicion'],
                    'bachiller_sunedu' => ['bachiller_sunedu'],
                    'grupo_exposicion' => ['grupo_exposicion'],
                    'titulo_tesis' => ['titulo_tesis'],
                    'asesor_tesis' => ['asesor_tesis'],
                    'fecha_sustentacion' => ['fecha_sustentacion'],
                    'rd_tesis' => ['rd_tesis'],
                    'url_repositorio' => ['url_repositorio'],
                    'situacion_tesis' => ['situacion_tesis'],
                    'estado_tesis' => ['estado_tesis'],
                    'numero_acta' => ['numero_acta'],
                    'promedio' => ['promedio'],
                    'jurado_1' => ['jurado_1'],
                    'jurado_2' => ['jurado_2'],
                    'jurado_3' => ['jurado_3'],
                    'jurado_4' => ['jurado_4'],
                    'dictaminante_1' => ['dictaminante_1'],
                    'link_dictamen_1' => ['link_dictamen_1'],
                    'dictaminante_2' => ['dictaminante_2'],
                    'link_dictamen_2' => ['link_dictamen_2'],
                    'observaciones' => ['observaciones'],
                ],
            ],
        ];
    }

    public function handle(): int
    {
        $rutaArchivo = $this->argument('archivo');
        $origenKey = $this->argument('origen');
        $delimiter = $this->option('delimiter');
        $dryRun = (bool) $this->option('dry-run');

        if (! file_exists($rutaArchivo)) {
            $this->error("El archivo no existe: {$rutaArchivo}");
            return self::FAILURE;
        }

        if (! isset($this->origenes[$origenKey])) {
            $this->error("Origen desconocido: {$origenKey}. Válidos: ".implode(', ', array_keys($this->origenes)));
            return self::FAILURE;
        }

        $config = $this->origenes[$origenKey];
        $this->info('Origen: '.$config['label'].($dryRun ? ' [DRY-RUN — no se escribe nada]' : ''));

        [$headers, $filasDatos] = $this->leerCsv($rutaArchivo, $delimiter);
        if ($headers === null) {
            $this->error('No se pudo leer el encabezado del archivo.');
            return self::FAILURE;
        }

        $indicesPorCampo = $this->resolverIndices($headers, $config['campos']);
        // Hojas con una fila de títulos agrupadores arriba ("Grado de Bachiller | Título de
        // Licenciatura"): el encabezado real es la primera fila que trae la columna del nombre.
        if (! isset($indicesPorCampo['nombre_estudiante'])) {
            foreach (array_slice($filasDatos, 0, 5) as $i => $fila) {
                $candidato = array_map(fn ($h) => trim((string) $h), $fila);
                if (isset($this->resolverIndices($candidato, $config['campos'])['nombre_estudiante'])) {
                    $headers = $candidato;
                    $filasDatos = array_slice($filasDatos, $i + 1);
                    $indicesPorCampo = $this->resolverIndices($headers, $config['campos']);
                    break;
                }
            }
        }
        $this->reportarColumnasNoMapeadas($headers, $indicesPorCampo);

        // Columnas sin título (o repetidas) compartían la misma clave en
        // datos_originales y se pisaban: la última vacía borraba a la
        // anterior (así se perdía el "enviado" de la hoja de expedientes).
        $vistos = [];
        foreach ($headers as $i => $h) {
            if (trim($h) === '' || isset($vistos[$h])) {
                $headers[$i] = '(columna '.($i + 1).')';
            }
            $vistos[$headers[$i]] = true;
        }
        $indicesSinTitulo = array_keys(array_filter($headers, fn ($h) => str_starts_with($h, '(columna ')));

        $lote = (string) Str::uuid();
        $usuarioActual = auth()->id() ?? $this->resolverUsuarioConsola();

        $stats = ['vinculado' => 0, 'ambiguo' => 0, 'sin_match' => 0, 'vacias' => 0];
        $filaNum = 1; // 1 = primera fila de datos (después del header)

        foreach ($filasDatos as $cols) {
            $filaNum++;

            $datosOriginales = [];
            foreach ($headers as $i => $headerOriginal) {
                $datosOriginales[$headerOriginal] = trim((string) ($cols[$i] ?? ''));
            }

            if (implode('', $datosOriginales) === '') {
                $stats['vacias']++;
                continue;
            }

            $datosNormalizados = [];
            foreach ($indicesPorCampo as $campo => $idx) {
                $datosNormalizados[$campo] = trim((string) ($cols[$idx] ?? ''));
            }
            $sinTitulo = [];
            foreach ($indicesSinTitulo as $idx) {
                if (($valor = trim((string) ($cols[$idx] ?? ''))) !== '') {
                    $sinTitulo[] = $headers[$idx].': '.$valor;
                }
            }
            if ($sinTitulo) {
                $datosNormalizados['columnas_sin_titulo'] = implode('; ', $sinTitulo);
            }
            foreach (['fecha_exposicion', 'fecha_sustentacion', 'fecha_pti', 'fecha_acta'] as $campoFecha) {
                if (! empty($datosNormalizados[$campoFecha] ?? null)) {
                    $datosNormalizados[$campoFecha.'_parseada'] = $this->parsearFecha($datosNormalizados[$campoFecha]);
                }
            }

            $dniCrudo = $datosNormalizados['dni'] ?? null;

            if (NombresImportados::esFilaDeEncabezado($datosNormalizados['nombre_estudiante'] ?? null, $dniCrudo)) {
                $stats['vacias']++;
                continue;
            }

            // Una fila de exposición grupal trae a varios integrantes en la
            // misma celda: se registra una fila de staging por persona.
            foreach ($this->filasPorIntegrante($datosNormalizados) as $datosPersona) {
                $nombreCrudo = $datosPersona['nombre_estudiante'] ?? '';
                $nombreNormalizado = $this->normalizarNombre($nombreCrudo);

                [$userId, $estadoMatch] = $config['match_por'] === 'dni'
                    ? $this->matchPorDni($dniCrudo, $config['modelo'])
                    : $this->matchPorNombre($nombreNormalizado, $config['modelo'], $origenKey === 'expo-sust-marilyn' ? ($datosPersona['tipo_k'] ?? '') : null);

                $stats[$estadoMatch]++;

                if (! $dryRun) {
                    Importacion::create([
                        'lote_importacion' => $lote,
                        'origen' => $origenKey,
                        'archivo_nombre' => basename($rutaArchivo),
                        'fila_num' => $filaNum,
                        'dni' => $dniCrudo !== '' ? $dniCrudo : null,
                        'nombre_estudiante' => $nombreCrudo !== '' ? $nombreCrudo : null,
                        'nombre_normalizado' => $nombreNormalizado !== '' ? $nombreNormalizado : null,
                        'user_id' => $userId,
                        'estado_match' => $estadoMatch,
                        'datos_originales' => $datosOriginales,
                        'datos_normalizados' => $datosPersona,
                        'importado_por' => $usuarioActual,
                    ]);
                }
            }
        }

        $this->newLine();
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Vinculadas a un usuario (match único)', $stats['vinculado']],
                ['Ambiguas (más de un candidato — revisar a mano)', $stats['ambiguo']],
                ['Sin match (revisar a mano)', $stats['sin_match']],
                ['Filas vacías omitidas', $stats['vacias']],
            ]
        );

        if (! $dryRun) {
            $this->info("Lote de importación: {$lote}");
            $this->line('Nada de esto tocó seguimiento_titulacion/titulacion_ptis/etc. — quedó en titulacion_importaciones para revisión/fusión manual.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: ?string[], 1: array<int, string[]>}
     */
    /**
     * Usa fgetcsv() sobre un stream (no partir el archivo por saltos de
     * línea a mano) a propósito: un campo entre comillas puede traer un
     * salto de línea adentro (pasó de verdad con un encabezado real de
     * Google Sheets, "jurado 4\njurado invitado") — partir primero por
     * línea y recién ahí parsear cada una como CSV corrompe esa fila y
     * desalinea todas las siguientes. fgetcsv() sí sabe seguir leyendo
     * hasta que las comillas cierran.
     */
    private function leerCsv(string $ruta, string $delimiter): array
    {
        $contenido = file_get_contents($ruta);
        $contenido = str_replace("\xEF\xBB\xBF", '', $contenido); // BOM UTF-8

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contenido);
        rewind($stream);

        $filas = [];
        while (($fila = fgetcsv($stream, 0, $delimiter)) !== false) {
            if (count($fila) === 1 && $fila[0] === null) {
                continue; // línea vacía
            }
            $filas[] = $fila;
        }
        fclose($stream);

        if (empty($filas)) {
            return [null, []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($filas));

        return [$headers, $filas];
    }

    /**
     * Empareja los encabezados reales del CSV contra los alias esperados,
     * normalizando acentos/mayúsculas/espacios/puntos para tolerar
     * variaciones menores sin adivinar a ciegas.
     *
     * @param  array<string, string[]>  $camposEsperados
     * @return array<string, int>
     */
    private function resolverIndices(array $headers, array $camposEsperados): array
    {
        $headersNormalizados = array_map(fn ($h) => $this->normalizarTextoComparable($h), $headers);

        $indices = [];
        foreach ($camposEsperados as $campo => $alias) {
            foreach ($alias as $a) {
                $idx = array_search($this->normalizarTextoComparable($a), $headersNormalizados, true);
                if ($idx !== false) {
                    $indices[$campo] = $idx;
                    break;
                }
            }
        }

        return $indices;
    }

    private function reportarColumnasNoMapeadas(array $headers, array $indicesPorCampo): void
    {
        $noMapeadas = array_diff(array_keys($headers), array_values($indicesPorCampo));
        if (! empty($noMapeadas)) {
            $nombres = array_map(fn ($i) => $headers[$i], $noMapeadas);
            $this->warn('Columnas del archivo sin mapear a un campo conocido (quedan igual en datos_originales, no se pierden): '.implode(' | ', $nombres));
        }
    }

    private function normalizarTextoComparable(string $texto): string
    {
        $texto = Str::ascii($texto); // quita acentos
        $texto = strtolower($texto);
        $texto = str_replace(['.', 'º', 'nº', 'n°'], '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = trim($texto);
        // Encabezados reales de hojas de cálculo a veces traen una coma
        // colgada al final de la celda (ej. "CAMBIO DE ASESOR/TEMA, ") —
        // se ignora al comparar en vez de exigir un alias por cada variante.
        return rtrim($texto, ', ');
    }

    /**
     * Datos normalizados de una fila -> uno por integrante. Si la celda de
     * nombre trae varias personas (una por línea), cada una recibe una
     * copia de los datos del grupo con su propio nombre, más la lista
     * completa en "integrantes_grupo". Si el nombre viene como "Apellidos,
     * Nombres", se guardan también separados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function filasPorIntegrante(array $datos): array
    {
        $integrantes = NombresImportados::integrantes($datos['nombre_estudiante'] ?? '');
        if (count($integrantes) <= 1) {
            return [$this->conNombreSeparado($datos)];
        }

        return array_map(fn (string $nombre) => $this->conNombreSeparado(array_merge($datos, [
            'nombre_estudiante' => $nombre,
            'integrantes_grupo' => implode('; ', $integrantes),
        ])), $integrantes);
    }

    private function conNombreSeparado(array $datos): array
    {
        if (empty($datos['apellidos']) && empty($datos['nombres'])
            && ($partes = NombresImportados::separarApellidosNombres($datos['nombre_estudiante'] ?? null))) {
            $datos['apellidos'] = $partes['apellidos'];
            $datos['nombres'] = $partes['nombres'];
        }

        return $datos;
    }

    public function normalizarNombre(string $nombre): string
    {
        $nombre = Str::ascii($nombre);
        $nombre = strtoupper($nombre);
        $nombre = preg_replace('/[^A-Z0-9\s]/', ' ', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);
        return trim($nombre);
    }

    /**
     * @return array{0: ?int, 1: 'vinculado'|'ambiguo'|'sin_match'}
     */
    /**
     * Busca primero en alumnos/ppds (la ficha "de estudiante"). Si no hay
     * ficha ahí, cae a users.dni directo: hay personas (ej. un egresado que
     * hoy es docente/tutor) que SÍ tienen cuenta en el sistema pero nunca
     * tuvieron fila en alumnos/ppds — sin este segundo intento se reportaban
     * como sin_match aunque la persona sí existe. No se exige condicion
     * = 'Egresado' aquí: el DNI ya es un identificador lo bastante preciso
     * por sí solo (a diferencia del match por nombre, que sí necesita esa
     * restricción para no ambigüar).
     */
    private function matchPorDni(?string $dni, string $modelo): array
    {
        $dni = preg_replace('/\D/', '', (string) $dni);
        if ($dni === '') {
            return [null, 'sin_match'];
        }

        $registro = $modelo::where('dni', $dni)->first();
        if ($registro && $registro->user_id) {
            return [$registro->user_id, 'vinculado'];
        }

        $user = User::where('dni', $dni)->first();
        if ($user) {
            return [$user->id, 'vinculado'];
        }

        return [null, 'sin_match'];
    }

    /**
     * Empareja por conjunto de palabras del nombre (no por igualdad exacta
     * de texto): todas las palabras del nombre importado deben aparecer
     * como palabra completa en "apellidos + nombres" del candidato. Esto
     * tolera que un archivo traiga el apellido materno y otro no (p. ej.
     * "Avella Bedford, Natasha Luisa" vs "Avella, Natasha Luisa"), pero
     * SOLO vincula si hay exactamente un candidato — si hay más de uno o
     * ninguno, la fila queda para revisión manual, nunca se adivina.
     *
     * @return array{0: ?int, 1: 'vinculado'|'ambiguo'|'sin_match'}
     */
    public function matchPorNombre(string $nombreNormalizado, ?string $modelo, ?string $tipoK): array
    {
        if ($nombreNormalizado === '') {
            return [null, 'sin_match'];
        }

        $palabras = array_filter(explode(' ', $nombreNormalizado), fn ($p) => mb_strlen($p) > 1);
        if (empty($palabras)) {
            return [null, 'sin_match'];
        }

        $modelos = $modelo ? [$modelo] : $this->modelosSegunTipoK($tipoK);

        $candidatos = collect();
        foreach ($modelos as $m) {
            // "condicion" vive en users, no en alumnos/ppds — hay que pasar por la relación.
            $registros = $m::whereNotNull('user_id')
                ->whereHas('user', fn ($q) => $q->where('condicion', 'Egresado'))
                ->get(['id', 'user_id', 'nombres', 'apellidos']);
            $candidatos = $candidatos->merge($registros);
        }

        $coincidencias = $candidatos->filter(function ($registro) use ($palabras) {
            $textoCompleto = $this->normalizarNombre($registro->apellidos.' '.$registro->nombres);
            foreach ($palabras as $palabra) {
                if (! preg_match('/\b'.preg_quote($palabra, '/').'\b/', $textoCompleto)) {
                    return false;
                }
            }
            return true;
        })->unique('user_id');

        if ($coincidencias->count() === 1) {
            return [$coincidencias->first()->user_id, 'vinculado'];
        }

        return [null, $coincidencias->count() > 1 ? 'ambiguo' : 'sin_match'];
    }

    /**
     * El archivo EXPO-SUST mezcla filas FID y PPD identificadas por la
     * columna "k" (ej. "EXPO FID"). Si no se puede determinar, busca en
     * ambas tablas (más lento pero no descarta al candidato correcto).
     */
    private function modelosSegunTipoK(?string $tipoK): array
    {
        $tipoK = strtoupper((string) $tipoK);
        if (str_contains($tipoK, 'PPD')) {
            return [ppd::class];
        }
        if (str_contains($tipoK, 'FID')) {
            return [Alumno::class];
        }
        return [Alumno::class, ppd::class];
    }

    /**
     * Nunca lanza excepción ni "adivina": si el formato no calza con
     * ninguno de los patrones conocidos (incluye rangos de periodo tipo
     * "2016-I al 2021-II", que no son una fecha real), devuelve null y el
     * texto original se conserva intacto en datos_normalizados.
     */
    private function parsearFecha(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || preg_match('/\bal\b/i', $raw) || preg_match('/[IVX]{1,4}\s*$/', $raw)) {
            return null; // rango de periodo, no una fecha puntual
        }

        // Y-m-d (ISO, ya lista para MySQL tal cual — formato del CSV unificado)
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $raw, $m)) {
            return $raw;
        }

        // Marca de tiempo de Google Forms ("12/15/2022 22:24:16"): viene en
        // m/d/Y o d/m/Y según la fila. Solo se acepta si el orden es
        // inequívoco (una parte > 12, o día = mes); si no, se deja null.
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})\s+\d{1,2}:\d{2}(:\d{2})?$#', $raw, $m)) {
            [$a, $b] = [(int) $m[1], (int) $m[2]];
            if ($a > 12 || $a === $b) {
                return sprintf('%04d-%02d-%02d', $m[3], $b, $a);
            }
            return $b > 12 ? sprintf('%04d-%02d-%02d', $m[3], $a, $b) : null;
        }

        // d/m/Y o d-m-Y (también d/m/yy: "10/04/26")
        if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4}|\d{2})$#', $raw, $m)) {
            $anio = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
            return sprintf('%04d-%02d-%02d', $anio, $m[2], $m[1]);
        }

        // m/Y -> primer día del mes
        if (preg_match('#^(\d{1,2})[/-](\d{4})$#', $raw, $m)) {
            return sprintf('%04d-%02d-01', $m[2], $m[1]);
        }

        // "22 de noviembre de 2024" / "22 de diciembre del 2021" / "15 de nov. 2023"
        $meses = [
            'ene' => 1, 'enero' => 1, 'feb' => 2, 'febrero' => 2, 'mar' => 3, 'marzo' => 3,
            'abr' => 4, 'abril' => 4, 'may' => 5, 'mayo' => 5, 'jun' => 6, 'junio' => 6,
            'jul' => 7, 'julio' => 7, 'ago' => 8, 'agosto' => 8, 'sep' => 9, 'set' => 9, 'septiembre' => 9, 'setiembre' => 9,
            'oct' => 10, 'octubre' => 10, 'nov' => 11, 'noviembre' => 11, 'dic' => 12, 'diciembre' => 12,
        ];
        $textoNorm = strtolower(Str::ascii($raw));
        // También sin los "de" ("23 agosto del 2024", "20 de dic. 2024",
        // "14 setiembre 2026") y con año de 2 dígitos ("07 de abril 25").
        if (preg_match('/(\d{1,2})\s+(?:de\s+)?([a-z]+)\.?\s+(?:del?\s+)?(\d{4}|\d{2})\b/', $textoNorm, $m)) {
            if (isset($meses[$m[2]])) {
                $anio = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
                return sprintf('%04d-%02d-%02d', $anio, $meses[$m[2]], $m[1]);
            }
        }

        return null;
    }

    private function resolverUsuarioConsola(): ?int
    {
        // Corrida por CLI sin sesión web (php artisan directo) — no hay
        // "quién" real; se deja null en vez de inventar un usuario.
        return null;
    }
}
