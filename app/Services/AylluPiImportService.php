<?php

namespace App\Services;

use App\Models\Ayllu\AylluPiAporteCurso;
use App\Models\Ayllu\AylluPiAuditoriaEvento;
use App\Models\Ayllu\AylluPiDefinicion;
use App\Models\Ayllu\AylluPiEjecucion;
use App\Models\Ayllu\AylluPiLoteImportacion;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\Proyecto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AylluPiImportService
{
    public const MANIFEST_RELATIVE_PATH = 'database/data/fuentes_proyectos_integradores_matriz.json';

    /**
     * Carga el manifiesto canónico extraído de los 6 archivos fuente (4 matrices Excel + 2 documentos Word).
     */
    public function cargarManifiesto(?array $overridePayload = null): array
    {
        if ($overridePayload !== null) {
            return $overridePayload;
        }

        $path = base_path(self::MANIFEST_RELATIVE_PATH);
        if (!is_file($path)) {
            throw new \RuntimeException('No se encontró el manifiesto de fuentes de proyectos integradores en: ' . $path);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('El manifiesto de fuentes de proyectos integradores no tiene formato JSON válido.');
        }

        return $data;
    }

    /**
     * Calcula el hash determinístico del lote documental completo.
     */
    public function calcularCodigoLote(array $payload): string
    {
        $canonical = [
            'definiciones' => $payload['definiciones_proyectos'] ?? [],
            'entradas' => $payload['entradas_matriz'] ?? [],
        ];

        return 'LOTE-PI-' . substr(hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE)), 0, 24);
    }

    /**
     * Genera vista previa y validación de conciliación documental antes de confirmar o verificar una importación.
     */
    public function previsualizarImportacion(?array $overridePayload = null): array
    {
        $payload = $this->cargarManifiesto($overridePayload);
        $codigoLote = $this->calcularCodigoLote($payload);
        $entradas = collect($payload['entradas_matriz'] ?? []);
        $anomalias = collect($payload['anomalias_competencias'] ?? []);
        $definiciones = collect($payload['definiciones_proyectos'] ?? []);

        $conteoPorCiclo = $entradas
            ->groupBy('ciclo_romano')
            ->map(fn (Collection $g) => $g->count())
            ->toArray();

        $conteoPorPrograma = $entradas
            ->groupBy('agrupacion_programa_codigo')
            ->map(fn (Collection $g) => $g->count())
            ->toArray();

        $camposVacios = [
            'competencias_no_consignadas' => $entradas->whereNull('competencias_bruto')->count(),
            'estrategias_no_consignadas' => $entradas->whereNull('estrategias_original')->count(),
            'productos_no_consignados' => $entradas->whereNull('producto_curso_original')->count(),
        ];

        $loteExistente = AylluPiLoteImportacion::where('codigo_lote', $codigoLote)
            ->where('estado', 'confirmado')
            ->first();

        return [
            'codigo_lote' => $codigoLote,
            'version_manifiesto' => $payload['version_manifiesto'] ?? '2026.11.01.1',
            'ya_importado_identico' => $loteExistente !== null,
            'lote_existente_id' => $loteExistente?->id,
            'total_entradas' => $entradas->count(),
            'total_definiciones' => $definiciones->count(),
            'total_anomalias_competencias' => $anomalias->count(),
            'conteo_por_ciclo' => $conteoPorCiclo,
            'conteo_por_programa' => $conteoPorPrograma,
            'campos_vacios' => $camposVacios,
            'archivos_fuente' => $payload['archivos_fuente'] ?? [],
            'anomalias_competencias' => $anomalias->values()->all(),
        ];
    }

    /**
     * Ejecuta la importación de forma transaccional, verificable e idempotente.
     * - Si el lote idéntico ya fue importado y existen sus 114 aportes, no duplica registros.
     * - Si una fuente fue modificada, crea una nueva versión de definición sin alterar la versión adoptada
     *   por las ejecuciones existentes.
     */
    public function ejecutarImportacion(?int $actorUserId = null, ?array $overridePayload = null, string $anioLectivo = '2026'): array
    {
        $payload = $this->cargarManifiesto($overridePayload);
        $preview = $this->previsualizarImportacion($payload);
        $codigoLote = $preview['codigo_lote'];

        return DB::transaction(function () use ($payload, $preview, $codigoLote, $actorUserId, $anioLectivo) {
            $loteExistente = AylluPiLoteImportacion::where('codigo_lote', $codigoLote)
                ->where('estado', 'confirmado')
                ->first();

            if ($loteExistente && $loteExistente->aportes()->count() === $preview['total_entradas']) {
                $this->asegurarEjecucionesAnualesBase($anioLectivo, $actorUserId);

                return [
                    'idempotente' => true,
                    'lote' => $loteExistente,
                    'definiciones_creadas' => 0,
                    'aportes_creados' => 0,
                    'resumen' => $preview,
                ];
            }

            $lote = AylluPiLoteImportacion::updateOrCreate(
                ['codigo_lote' => $codigoLote],
                [
                    'version_manifiesto' => $preview['version_manifiesto'],
                    'estado' => 'confirmado',
                    'total_entradas' => $preview['total_entradas'],
                    'total_anomalias' => $preview['total_anomalias_competencias'],
                    'archivos_fuente' => $preview['archivos_fuente'],
                    'resumen_conciliacion' => [
                        'conteo_por_ciclo' => $preview['conteo_por_ciclo'],
                        'conteo_por_programa' => $preview['conteo_por_programa'],
                        'campos_vacios' => $preview['campos_vacios'],
                    ],
                    'importado_por_user_id' => $actorUserId,
                    'confirmado_at' => now(),
                ]
            );

            // Mapa de proyectos institucionales existentes (por ciclo I..VIII) sin modificar la tabla proyectos
            $proyectosPorAnio = $this->resolverProyectosInstitucionalesPorAnio();

            // Catálogo institucional de cursos cargado en memoria para proponer correspondencias sin N+1
            $catalogoCursos = Curso::with(['ciclo.programa', 'docentes'])->get();

            $definicionesMatrizPorAnio = [];
            $definicionesCreadas = 0;

            foreach ($payload['definiciones_proyectos'] ?? [] as $defData) {
                $anio = (int) $defData['anio_formativo'];
                $codigoFuente = (string) $defData['codigo_fuente'];

                // Incluir las entradas de ese año en el hash cuando es matriz_excel para detectar revisiones de matriz
                $entradasAnio = [];
                if (!empty($defData['es_version_base_matriz'])) {
                    $entradasAnio = array_values(array_filter(
                        $payload['entradas_matriz'] ?? [],
                        fn (array $e) => (int) $e['anio_formativo'] === $anio
                    ));
                }

                $hashContenido = hash('sha256', json_encode([
                    'def' => $defData,
                    'entradas' => $entradasAnio,
                ], JSON_UNESCAPED_UNICODE));

                $definicion = AylluPiDefinicion::where('anio_formativo', $anio)
                    ->where('codigo_fuente', $codigoFuente)
                    ->where('hash_contenido', $hashContenido)
                    ->first();

                if (!$definicion) {
                    $maxVersion = (int) AylluPiDefinicion::where('anio_formativo', $anio)->max('version_numero');
                    $versionNumero = $maxVersion > 0 ? ($maxVersion + 1) : (int) ($defData['version_numero'] ?? 1);

                    // Si ya existe una ejecución usando una definición previa de este año, la nueva entra como 'documental_fuente'
                    // para no alterar retrospectivamente ejecuciones anteriores.
                    $yaTieneEjecucionActiva = AylluPiEjecucion::where('anio_formativo', $anio)->exists();
                    $estadoAprobacion = ($yaTieneEjecucionActiva && !empty($defData['es_version_base_matriz']))
                        ? 'en_revision'
                        : ($defData['estado_aprobacion'] ?? 'documental_fuente');

                    $definicion = AylluPiDefinicion::create([
                        'proyecto_institucional_id' => $proyectosPorAnio[$anio] ?? null,
                        'lote_importacion_id' => $lote->id,
                        'anio_formativo' => $anio,
                        'ciclos_texto' => $defData['ciclos_texto'],
                        'version_numero' => $versionNumero,
                        'codigo_fuente' => $codigoFuente,
                        'es_version_base_matriz' => !empty($defData['es_version_base_matriz']),
                        'estado_aprobacion' => $estadoAprobacion,
                        'denominacion_original' => $defData['denominacion_original'],
                        'denominacion_ciclo_par' => $defData['denominacion_ciclo_par'] ?? null,
                        'proposito_original' => $defData['proposito_original'],
                        'ambito_exploracion' => $defData['ambito_exploracion'] ?? null,
                        'fundamentacion_pedagogica' => $defData['fundamentacion_pedagogica'] ?? null,
                        'producto_integrador' => $defData['producto_integrador'],
                        'competencias_declaradas' => $defData['competencias_declaradas'] ?? null,
                        'desempenos_especificos' => $defData['desempenos_especificos'] ?? [],
                        // Regla estricta: los 5 hitos solo pertenecen al Año 1
                        'hitos_documentados' => $anio === 1 ? ($defData['hitos_documentados'] ?? null) : null,
                        'diferencias_documentales' => $defData['diferencias_documentales'] ?? [],
                        'archivo_origen' => $defData['archivo_origen'] ?? null,
                        'referencia_ubicacion' => $defData['referencia_ubicacion'] ?? null,
                        'hash_contenido' => $hashContenido,
                        'bloqueada_por_uso' => !$yaTieneEjecucionActiva && !empty($defData['es_version_base_matriz']),
                        'aprobado_por_user_id' => $actorUserId,
                        'aprobado_at' => now(),
                    ]);
                    $definicionesCreadas++;
                }

                if (!empty($defData['es_version_base_matriz'])) {
                    $definicionesMatrizPorAnio[$anio] = $definicion;
                }
            }

            // Asegurar las ejecuciones anuales base (sin pisar definiciones ya adoptadas por ejecuciones previas)
            $ejecucionesPorAnio = $this->asegurarEjecucionesAnualesBase($anioLectivo, $actorUserId, $definicionesMatrizPorAnio, $proyectosPorAnio);

            // Importar las 114 entradas de cursos de las matrices vinculadas a su definición de matriz
            $aportesCreados = 0;
            foreach ($payload['entradas_matriz'] ?? [] as $entry) {
                $anio = (int) $entry['anio_formativo'];
                $defMatriz = $definicionesMatrizPorAnio[$anio] ?? null;
                if (!$defMatriz) {
                    continue;
                }

                // Solo vincular ejecucion_id automáticamente si la ejecución de ese año adopta esta misma definición
                $ejecucionAnio = $ejecucionesPorAnio[$anio] ?? null;
                $ejecucionIdParaAporte = ($ejecucionAnio && (int) $ejecucionAnio->definicion_id === (int) $defMatriz->id)
                    ? $ejecucionAnio->id
                    : null;

                $correspondencia = $this->proponerCorrespondenciaCatalogo($entry, $catalogoCursos);

                $aporteExistente = AylluPiAporteCurso::where('definicion_id', $defMatriz->id)
                    ->where('ciclo_romano', $entry['ciclo_romano'])
                    ->where('celda_curso', $entry['celda_curso'])
                    ->first();

                if (!$aporteExistente) {
                    AylluPiAporteCurso::create([
                        'definicion_id' => $defMatriz->id,
                        'ejecucion_id' => $ejecucionIdParaAporte,
                        'lote_importacion_id' => $lote->id,
                        'posicion_global' => (int) $entry['posicion_global'],
                        'posicion_ciclo' => (int) $entry['posicion_ciclo'],
                        'anio_formativo' => $anio,
                        'ciclo_romano' => $entry['ciclo_romano'],
                        'ciclo_orden' => (int) $entry['ciclo_orden'],
                        'agrupacion_programa_original' => $entry['agrupacion_programa_original'],
                        'agrupacion_programa_codigo' => $entry['agrupacion_programa_codigo'],
                        'es_aporte_comun' => $entry['agrupacion_programa_codigo'] === 'FORMACION_GENERAL',
                        'curso_fuente_original' => $entry['curso_original'],
                        'curso_fuente_normalizado' => $entry['curso_normalizado'],
                        'competencias_fuente_bruto' => $entry['competencias_bruto'],
                        'competencias_fuente_mostrar' => $entry['competencias_mostrar'],
                        'competencias_formato_excel' => $entry['competencias_formato_excel'],
                        'es_anomalia_competencia' => !empty($entry['es_anomalia_competencia']),
                        'detalle_anomalia_competencia' => $entry['detalle_anomalia_competencia'],
                        'estrategias_fuente_original' => $entry['estrategias_original'],
                        'estrategias_mostrar' => $entry['estrategias_mostrar'],
                        'producto_previsto_original' => $entry['producto_curso_original'],
                        'producto_previsto_mostrar' => $entry['producto_curso_mostrar'],
                        'producto_integrador_fuente' => $entry['producto_integrador_matriz'],
                        'anotaciones_autor_historico' => $entry['anotaciones_autor_historico'] ?? [],
                        'observaciones_calidad' => $entry['observaciones_calidad'] ?? [],
                        'archivo_fuente' => $entry['archivo_fuente'],
                        'hoja_fuente' => $entry['hoja_fuente'] ?? 'Hoja 1',
                        'celda_curso' => $entry['celda_curso'],
                        'celda_competencias' => $entry['celda_competencias'] ?? null,
                        'celda_estrategias' => $entry['celda_estrategias'] ?? null,
                        'celda_producto' => $entry['celda_producto'] ?? null,
                        'celda_programa' => $entry['celda_programa'] ?? null,
                        'curso_institucional_id' => $correspondencia['curso_institucional_id'],
                        'correspondencia_sugerida_json' => $correspondencia['candidatos'],
                        'estado_correspondencia' => $correspondencia['estado_correspondencia'],
                        // Regla estricta: ningún estado o porcentaje se fabrica al importar
                        'estado_desarrollo' => 'sin_registrar',
                    ]);
                    $aportesCreados++;
                }
            }

            AylluPiAuditoriaEvento::create([
                'accion' => 'importacion_curricular_confirmada',
                'entidad_tipo' => 'ayllu_pi_lotes_importacion',
                'entidad_id' => $lote->id,
                'actor_user_id' => $actorUserId,
                'metadatos' => [
                    'codigo_lote' => $codigoLote,
                    'definiciones_creadas' => $definicionesCreadas,
                    'aportes_creados' => $aportesCreados,
                    'total_entradas' => $preview['total_entradas'],
                    'total_anomalias' => $preview['total_anomalias_competencias'],
                ],
                'created_at' => now(),
            ]);

            return [
                'idempotente' => false,
                'lote' => $lote,
                'definiciones_creadas' => $definicionesCreadas,
                'aportes_creados' => $aportesCreados,
                'resumen' => $preview,
            ];
        });
    }

    /**
     * Asegura que los datos base de las 4 matrices estén importados (se invoca si la tabla está vacía).
     */
    public function asegurarImportacionInicial(?int $actorUserId = null): void
    {
        if (AylluPiAporteCurso::count() === 0) {
            $this->ejecutarImportacion($actorUserId);
        }
    }

    /**
     * Vincula cada año formativo (1..4) con el Proyecto institucional existente en la tabla `proyectos`
     * a través de la relación `ciclos.proyecto_id` sin alterar los datos existentes.
     */
    protected function resolverProyectosInstitucionalesPorAnio(): array
    {
        $mapaCicloAnio = [
            'I' => 1, 'II' => 1,
            'III' => 2, 'IV' => 2,
            'V' => 3, 'VI' => 3,
            'VII' => 4, 'VIII' => 4,
        ];

        $resultado = [];
        $ciclos = Ciclo::whereIn('nombre', array_keys($mapaCicloAnio))
            ->whereNotNull('proyecto_id')
            ->get();

        foreach ($ciclos as $ciclo) {
            $anio = $mapaCicloAnio[strtoupper(trim($ciclo->nombre))] ?? null;
            if ($anio && !isset($resultado[$anio])) {
                $resultado[$anio] = (int) $ciclo->proyecto_id;
            }
        }

        return $resultado;
    }

    /**
     * Crea o recupera las ejecuciones anuales de los 4 años formativos sin alterar ejecuciones ya existentes.
     */
    public function asegurarEjecucionesAnualesBase(
        string $anioLectivo = '2026',
        ?int $actorUserId = null,
        array $definicionesMatrizPorAnio = [],
        array $proyectosPorAnio = []
    ): array {
        $periodoActual = PeriodoActual::actual();
        if (empty($proyectosPorAnio)) {
            $proyectosPorAnio = $this->resolverProyectosInstitucionalesPorAnio();
        }

        $paresCiclos = [
            1 => ['I', 'II'],
            2 => ['III', 'IV'],
            3 => ['V', 'VI'],
            4 => ['VII', 'VIII'],
        ];

        $ejecuciones = [];
        foreach ($paresCiclos as $anio => [$impar, $par]) {
            $ejecucion = AylluPiEjecucion::where('anio_lectivo', $anioLectivo)
                ->where('anio_formativo', $anio)
                ->where('cohorte', 'Cohorte ' . $anioLectivo)
                ->first();

            if (!$ejecucion) {
                $def = $definicionesMatrizPorAnio[$anio]
                    ?? AylluPiDefinicion::where('anio_formativo', $anio)
                        ->where('es_version_base_matriz', true)
                        ->orderBy('version_numero')
                        ->first();

                if ($def) {
                    $def->update(['bloqueada_por_uso' => true]);
                    $ejecucion = AylluPiEjecucion::create([
                        'definicion_id' => $def->id,
                        'proyecto_institucional_id' => $proyectosPorAnio[$anio] ?? $def->proyecto_institucional_id,
                        'anio_lectivo' => $anioLectivo,
                        'periodo_actual_id' => $periodoActual?->id,
                        'cohorte' => 'Cohorte ' . $anioLectivo,
                        'anio_formativo' => $anio,
                        'ciclo_impar_romano' => $impar,
                        'ciclo_par_romano' => $par,
                        'estado' => 'activa',
                        'adoptado_por_user_id' => $actorUserId,
                        'adoptado_at' => now(),
                    ]);
                }
            }

            if ($ejecucion) {
                $ejecuciones[$anio] = $ejecucion;
            }
        }

        return $ejecuciones;
    }

    /**
     * Propone correspondencias entre la posición documental de la matriz y el catálogo `cursos` institucional,
     * sin hacer asociaciones ambiguas automáticas ni renombrar Primaria como Primaria EIB.
     */
    protected function proponerCorrespondenciaCatalogo(array $entry, Collection $catalogoCursos): array
    {
        $cicloRomano = strtoupper(trim((string) $entry['ciclo_romano']));
        $progCodigo = (string) $entry['agrupacion_programa_codigo'];
        $nombreNorm = $this->simplificarTextoComparacion((string) $entry['curso_normalizado']);

        // En el catálogo FID actual existen Programa Inicial (id=1) y Programa Primaria EIB (id=2).
        // Un antecedente de PRIMARIA (no EIB) NO debe renombrarse ni asociarse automáticamente a Primaria EIB.
        if ($progCodigo === 'PRIMARIA') {
            return [
                'curso_institucional_id' => null,
                'estado_correspondencia' => 'sin_equivalente_catalogo',
                'candidatos' => [
                    'nota' => 'Antecedente documental de Primaria (monolingüe). Se conserva íntegro sin asociarlo automáticamente a Primaria EIB.',
                    'cursos_sugeridos' => [],
                ],
            ];
        }

        $cursosDelCiclo = $catalogoCursos->filter(function (Curso $c) use ($cicloRomano) {
            return strtoupper(trim((string) $c->ciclo?->nombre)) === $cicloRomano
                && $c->ciclo?->programa?->esFid();
        });

        $candidatos = [];
        foreach ($cursosDelCiclo as $c) {
            $progCurso = $c->ciclo?->programa;
            if ($progCodigo === 'INICIAL' && (!$progCurso || !$progCurso->esInicial())) {
                continue;
            }
            if ($progCodigo === 'PRIMARIA_EIB' && (!$progCurso || !$progCurso->esPrimaria() || !$progCurso->eib)) {
                continue;
            }

            $cNorm = $this->simplificarTextoComparacion((string) $c->nombre);
            $esExacto = ($cNorm === $nombreNorm);
            $esSimilar = !$esExacto && (str_contains($cNorm, $nombreNorm) || str_contains($nombreNorm, $cNorm));

            if ($esExacto || $esSimilar) {
                $candidatos[] = [
                    'curso_id' => $c->id,
                    'nombre' => $c->nombre,
                    'programa' => $progCurso?->nombre,
                    'ciclo' => $c->ciclo?->nombre,
                    'coincidencia' => $esExacto ? 'exacta' : 'parcial_sugerida',
                    'docentes_actuales' => $c->docentes->map(fn ($d) => trim($d->nombre . ' ' . ($d->apellidos ?? '')))->values()->all(),
                ];
            }
        }

        // Si es FORMACION_GENERAL, existen cursos tanto en Inicial como en Primaria EIB para ese ciclo;
        // se guardan ambos en candidatos y se enlaza como referencia principal si la coincidencia es exacta,
        // pero si solo hay coincidencia parcial queda como 'propuesta_pendiente'.
        $exactos = array_values(array_filter($candidatos, fn ($cand) => $cand['coincidencia'] === 'exacta'));

        if (count($exactos) === 1 && $progCodigo !== 'FORMACION_GENERAL') {
            return [
                'curso_institucional_id' => $exactos[0]['curso_id'],
                'estado_correspondencia' => 'confirmada',
                'candidatos' => [
                    'nota' => 'Correspondencia exacta unívoca en el ciclo y programa.',
                    'cursos_sugeridos' => $candidatos,
                ],
            ];
        }

        if (count($exactos) >= 1 && $progCodigo === 'FORMACION_GENERAL') {
            return [
                'curso_institucional_id' => $exactos[0]['curso_id'],
                'estado_correspondencia' => 'confirmada',
                'candidatos' => [
                    'nota' => 'Curso común de Formación General presente en los programas del ciclo.',
                    'cursos_sugeridos' => $candidatos,
                ],
            ];
        }

        return [
            'curso_institucional_id' => null,
            'estado_correspondencia' => 'propuesta_pendiente',
            'candidatos' => [
                'nota' => count($candidatos) > 0
                    ? 'Formulación con variación respecto al catálogo; requiere confirmación de coordinación.'
                    : 'Sin coincidencia directa en el catálogo del ciclo; requiere revisión de equivalencia.',
                'cursos_sugeridos' => $candidatos,
            ],
        ];
    }

    protected function simplificarTextoComparacion(string $texto): string
    {
        $s = mb_strtolower($texto, 'UTF-8');
        $s = strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        ]);
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));

        return $s;
    }

    protected function normalizarEstructuraJson(mixed $valor): string
    {
        $ordenar = function (mixed $item) use (&$ordenar): mixed {
            if (!is_array($item)) {
                return $item;
            }
            if (!array_is_list($item)) {
                ksort($item);
            }
            foreach ($item as $k => $v) {
                $item[$k] = $ordenar($v);
            }
            return $item;
        };

        return json_encode($ordenar($valor), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Genera una vista previa de los registros persistidos que requieren corrección técnica
     * de correspondencia documental (hitos de Año 1, asociación celda-curso o agrupación de programa),
     * distinguiéndola de modificaciones académicas pendientes de validación institucional.
     */
    public function previsualizarReparacionTecnica(?array $overridePayload = null): array
    {
        $payload = $this->cargarManifiesto($overridePayload);
        $definicionesManifest = collect($payload['definiciones_proyectos'] ?? []);
        $entradasManifest = collect($payload['entradas_matriz'] ?? []);

        $definicionesAfectadas = [];
        foreach (AylluPiDefinicion::orderBy('id')->get() as $def) {
            $defFuente = $definicionesManifest->first(function (array $d) use ($def) {
                return (int) $d['anio_formativo'] === (int) $def->anio_formativo
                    && (string) $d['codigo_fuente'] === (string) $def->codigo_fuente;
            });

            if (!$defFuente) {
                continue;
            }

            $hitosEsperados = (int) $def->anio_formativo === 1
                ? ($defFuente['hitos_documentados'] ?? null)
                : null;

            $actualNorm = $this->normalizarEstructuraJson($def->hitos_documentados);
            $esperadoNorm = $this->normalizarEstructuraJson($hitosEsperados);

            if ($actualNorm !== $esperadoNorm) {
                $definicionesAfectadas[] = [
                    'entidad' => 'ayllu_pi_definiciones',
                    'id' => $def->id,
                    'anio_formativo' => $def->anio_formativo,
                    'version_numero' => $def->version_numero,
                    'codigo_fuente' => $def->codigo_fuente,
                    'archivo_origen' => $def->archivo_origen,
                    'tipo_correccion' => 'correccion_tecnica_hitos_documentados',
                    'valor_anterior' => $def->hitos_documentados,
                    'correccion_propuesta' => $hitosEsperados,
                ];
            }
        }

        $aportesAfectados = [];
        foreach (AylluPiAporteCurso::orderBy('posicion_global')->get() as $aporte) {
            $entry = $entradasManifest->first(function (array $e) use ($aporte) {
                return (int) $e['anio_formativo'] === (int) $aporte->anio_formativo
                    && (string) $e['ciclo_romano'] === (string) $aporte->ciclo_romano
                    && (string) $e['celda_curso'] === (string) $aporte->celda_curso;
            });

            if (!$entry) {
                continue;
            }

            $diferencias = [];
            if ($aporte->curso_fuente_original !== $entry['curso_original']) {
                $diferencias['curso_fuente_original'] = [
                    'anterior' => $aporte->curso_fuente_original,
                    'propuesto' => $entry['curso_original'],
                ];
            }
            if ($aporte->agrupacion_programa_codigo !== $entry['agrupacion_programa_codigo']) {
                $diferencias['agrupacion_programa_codigo'] = [
                    'anterior' => $aporte->agrupacion_programa_codigo,
                    'propuesto' => $entry['agrupacion_programa_codigo'],
                ];
            }
            if ($aporte->celda_competencias !== ($entry['celda_competencias'] ?? null)) {
                $diferencias['celda_competencias'] = [
                    'anterior' => $aporte->celda_competencias,
                    'propuesto' => $entry['celda_competencias'] ?? null,
                ];
            }
            if ($aporte->competencias_fuente_bruto !== $entry['competencias_bruto']) {
                $diferencias['competencias_fuente_bruto'] = [
                    'anterior' => $aporte->competencias_fuente_bruto,
                    'propuesto' => $entry['competencias_bruto'],
                ];
            }

            if (!empty($diferencias)) {
                $aportesAfectados[] = [
                    'entidad' => 'ayllu_pi_aportes_curso',
                    'id' => $aporte->id,
                    'posicion_global' => $aporte->posicion_global,
                    'anio_formativo' => $aporte->anio_formativo,
                    'ciclo_romano' => $aporte->ciclo_romano,
                    'archivo_fuente' => $aporte->archivo_fuente,
                    'celda_curso' => $aporte->celda_curso,
                    'tipo_correccion' => 'correccion_tecnica_correspondencia_celda_programa',
                    'diferencias' => $diferencias,
                ];
            }
        }

        return [
            'version_manifiesto' => $payload['version_manifiesto'] ?? '2026.11.01.2',
            'codigo_lote_canonico' => $this->calcularCodigoLote($payload),
            'total_definiciones_afectadas' => count($definicionesAfectadas),
            'total_aportes_afectados' => count($aportesAfectados),
            'definiciones_afectadas' => $definicionesAfectadas,
            'aportes_afectados' => $aportesAfectados,
        ];
    }

    /**
     * Aplica de forma acotada, transaccional e idempotente las correcciones técnicas sobre
     * los registros persistidos, preservando íntegramente evidencias, versiones, asignaciones,
     * comentarios, fechas operativas e historial.
     */
    public function ejecutarReparacionTecnica(?int $actorUserId = null, ?array $overridePayload = null): array
    {
        $payload = $this->cargarManifiesto($overridePayload);
        $preview = $this->previsualizarReparacionTecnica($payload);

        if ($preview['total_definiciones_afectadas'] === 0 && $preview['total_aportes_afectados'] === 0) {
            return [
                'idempotente' => true,
                'definiciones_reparadas' => 0,
                'aportes_reparados' => 0,
                'preview' => $preview,
            ];
        }

        return DB::transaction(function () use ($payload, $preview, $actorUserId) {
            $definicionesManifest = collect($payload['definiciones_proyectos'] ?? []);
            $entradasManifest = collect($payload['entradas_matriz'] ?? []);

            $definicionesReparadas = 0;
            foreach ($preview['definiciones_afectadas'] as $item) {
                $def = AylluPiDefinicion::find($item['id']);
                if (!$def) {
                    continue;
                }

                $defData = $definicionesManifest->first(function (array $d) use ($def) {
                    return (int) $d['anio_formativo'] === (int) $def->anio_formativo
                        && (string) $d['codigo_fuente'] === (string) $def->codigo_fuente;
                });

                $entradasAnio = [];
                if (!empty($def->es_version_base_matriz)) {
                    $entradasAnio = array_values(array_filter(
                        $payload['entradas_matriz'] ?? [],
                        fn (array $e) => (int) $e['anio_formativo'] === (int) $def->anio_formativo
                    ));
                }

                $nuevoHash = hash('sha256', json_encode([
                    'def' => $defData,
                    'entradas' => $entradasAnio,
                ], JSON_UNESCAPED_UNICODE));

                $def->update([
                    'hitos_documentados' => $item['correccion_propuesta'],
                    'hash_contenido' => $nuevoHash,
                ]);
                $definicionesReparadas++;
            }

            $aportesReparados = 0;
            foreach ($preview['aportes_afectados'] as $item) {
                $aporte = AylluPiAporteCurso::find($item['id']);
                if (!$aporte) {
                    continue;
                }

                $entry = $entradasManifest->first(function (array $e) use ($aporte) {
                    return (int) $e['anio_formativo'] === (int) $aporte->anio_formativo
                        && (string) $e['ciclo_romano'] === (string) $aporte->ciclo_romano
                        && (string) $e['celda_curso'] === (string) $aporte->celda_curso;
                });

                if ($entry) {
                    $aporte->update([
                        'curso_fuente_original' => $entry['curso_original'],
                        'curso_fuente_normalizado' => $entry['curso_normalizado'],
                        'agrupacion_programa_original' => $entry['agrupacion_programa_original'],
                        'agrupacion_programa_codigo' => $entry['agrupacion_programa_codigo'],
                        'es_aporte_comun' => $entry['agrupacion_programa_codigo'] === 'FORMACION_GENERAL',
                        'celda_competencias' => $entry['celda_competencias'] ?? null,
                        'competencias_fuente_bruto' => $entry['competencias_bruto'],
                        'competencias_fuente_mostrar' => $entry['competencias_mostrar'],
                        'competencias_formato_excel' => $entry['competencias_formato_excel'],
                        'es_anomalia_competencia' => !empty($entry['es_anomalia_competencia']),
                        'detalle_anomalia_competencia' => $entry['detalle_anomalia_competencia'],
                    ]);
                    $aportesReparados++;
                }
            }

            // Sincronizar código de lote e información de versión en el lote confirmado activo
            $loteActivo = AylluPiLoteImportacion::where('estado', 'confirmado')->latest('id')->first();
            if ($loteActivo) {
                $loteActivo->update([
                    'codigo_lote' => $preview['codigo_lote_canonico'],
                    'version_manifiesto' => $preview['version_manifiesto'],
                ]);
            }

            AylluPiAuditoriaEvento::create([
                'accion' => 'reparacion_tecnica_correspondencia_aplicada',
                'entidad_tipo' => 'ayllu_pi_definiciones',
                'entidad_id' => $loteActivo?->id,
                'actor_user_id' => $actorUserId,
                'metadatos' => [
                    'version_manifiesto' => $preview['version_manifiesto'],
                    'definiciones_reparadas' => $definicionesReparadas,
                    'aportes_reparados' => $aportesReparados,
                    'detalle_definiciones' => $preview['definiciones_afectadas'],
                    'detalle_aportes' => $preview['aportes_afectados'],
                ],
                'created_at' => now(),
            ]);

            return [
                'idempotente' => false,
                'definiciones_reparadas' => $definicionesReparadas,
                'aportes_reparados' => $aportesReparados,
                'preview' => $preview,
            ];
        });
    }
}
