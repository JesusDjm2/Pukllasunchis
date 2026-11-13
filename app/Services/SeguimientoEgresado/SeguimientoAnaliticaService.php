<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\InvitacionCampana;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeguimientoAnaliticaService
{
    protected EstadisticaSeguraService $seguridadService;
    protected ObservatorioTerritorialService $territorialService;

    /**
     * Catálogo estricto de variables permitidas para evitar inyecciones.
     */
    protected array $variablesPermitidas = [
        'grupo_formacion' => 'Grupo de formación (FID / PPD)',
        'programa' => 'Programa de estudios',
        'cohorte' => 'Promoción / Cohorte de egreso',
        'estado_titulacion' => 'Estado de titulación',
        'situacion_laboral' => 'Situación laboral declarada',
        'sector' => 'Sector de empleo',
        'contrato' => 'Tipo de contrato / Régimen',
        'docencia_directa' => 'Docencia directa en aula',
        'trabajo_educativo' => 'Empleo en el sector educativo',
        'eib' => 'Docencia Intercultural Bilingüe (EIB)',
        'departamento_residencia' => 'Departamento de residencia',
        'departamento_trabajo' => 'Departamento de trabajo',
        'provincia_residencia' => 'Provincia de residencia',
        'provincia_trabajo' => 'Provincia de trabajo',
        'tiempo_egreso' => 'Años transcurridos desde el egreso',
        'campana' => 'Campaña de seguimiento',
        'necesidades_formacion' => 'Temas de capacitación priorizados',
    ];

    public function __construct(
        EstadisticaSeguraService $seguridadService,
        ObservatorioTerritorialService $territorialService
    ) {
        $this->seguridadService = $seguridadService;
        $this->territorialService = $territorialService;
    }

    /**
     * Retorna las variables permitidas para filas y columnas.
     */
    public function getVariablesPermitidas(): array
    {
        return $this->variablesPermitidas;
    }

    /**
     * 1. CÁLCULO RIGUROSO DE UNIVERSOS ANALÍTICOS SEPARADOS
     */
    public function calcularUniversos(array $filtros = []): array
    {
        // Mismo conjunto filtrado para todos los numeradores y denominadores.
        $ids = $this->obtenerDatasetAnalitico($filtros)->pluck('user_id')->unique()->values();
        $padronTotal = $ids->count();
        $elegiblesIds = User::whereIn('id', $ids)->whereDoesntHave('roles', fn ($q) => $q->where('name', 'inhabilitado'))->pluck('id');
        $elegiblesTotal = $elegiblesIds->count();
        $contactadosIds = Llamada::whereIn('user_id', $elegiblesIds)->where('es_contacto_efectivo', true)->pluck('user_id')->unique();
        $contactadosTotal = $contactadosIds->count();
        $respondientesIds = Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->pluck('user_id')->unique();
        $respondientesTotal = $respondientesIds->count();
        $conLaboralIds = EpisodioLaboral::whereIn('user_id', $ids)->where('es_actual', true)->pluck('user_id')
            ->concat(Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->whereIn('trabaja', ['Trabaja','No trabaja','Trabaja en gestión','Trabaja otro sector'])->pluck('user_id'))->unique();
        $conLaboralTotal = $conLaboralIds->count();
        $conTerritorialTotal = Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')
            ->where(fn ($q) => $q->whereNotNull('res_departamento')->orWhereNotNull('trab_departamento'))->distinct()->count('user_id');
        $multiCampana = Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->groupBy('user_id')->havingRaw('COUNT(DISTINCT periodo_campana) >= 2')->pluck('user_id');
        $multiEmpleo = EpisodioLaboral::whereIn('user_id', $ids)->groupBy('user_id')->havingRaw('COUNT(id) >= 2')->pluck('user_id');
        $longitudinalesTotal = $multiCampana->concat($multiEmpleo)->unique()->count();
        $docenciaDirectaCount = EpisodioLaboral::whereIn('user_id', $conLaboralIds)->where('es_actual', true)->where('es_docencia_directa', true)->distinct()->count('user_id');
        $tituladosCount = User::whereIn('id', $ids)->whereHas('seguimientoTitulacion.estadoTitulacion', fn ($q) => $q->whereIn('clave', ['licenciado','tesis_aprobada','ti_aprobado']))->count();
        $pct = fn ($n, $d) => $d ? round(100 * $n / $d, 1) : null;
        $tasaCobertura = $pct($contactadosTotal, $elegiblesTotal);
        $tasaRespuesta = $pct($respondientesTotal, $padronTotal);
        return [
            'padron_total' => $padronTotal, 'total_padron' => $padronTotal,
            'elegibles' => $elegiblesTotal, 'elegibles_seguimiento' => $elegiblesTotal,
            'contactados' => $contactadosTotal, 'respondientes' => $respondientesTotal,
            'invitaciones_enviadas' => InvitacionCampana::whereIn('user_id', $ids)->distinct()->count('user_id'),
            'no_respondientes' => $padronTotal - $respondientesTotal,
            'con_info_laboral' => $conLaboralTotal, 'con_info_laboral_valida' => $conLaboralTotal,
            'con_info_territorial' => $conTerritorialTotal,
            'longitudinales' => $longitudinalesTotal, 'longitudinales_multiperiodo' => $longitudinalesTotal,
            'tasa_respuesta_pct' => $tasaRespuesta, 'cobertura_padron_pct' => $pct($contactadosTotal, $padronTotal),
            'grupo_formacion' => app(GrupoFormacionService::class)->normalizar($filtros['grupo_formacion'] ?? null),
            'denominadores' => ['cobertura' => $elegiblesTotal, 'respuesta' => $padronTotal, 'docencia_directa' => $conLaboralTotal, 'titulacion' => $padronTotal],
            'advertencias_metodologicas' => ['Las invitaciones e intentos no son contactos efectivos.', 'Respuesta sobre padrón filtrado; docencia sobre información laboral disponible. Sin denominador se informa dato no calculable.', 'La ausencia de respuesta no equivale a desempleo.'],
            'indicadores' => ['tasa_cobertura' => $tasaCobertura, 'tasa_respuesta' => $tasaRespuesta,
                'tasa_docencia_directa' => $pct($docenciaDirectaCount, $conLaboralTotal), 'tasa_titulacion' => $pct($tituladosCount, $padronTotal),
                'docencia_directa_absoluta' => $docenciaDirectaCount, 'titulados_absoluto' => $tituladosCount],
            'advertencias' => ['no_respondio_no_es_desempleo' => 'Sin respuesta no equivale a desempleo.', 'cobertura_cuestion' => !$contactadosTotal ? 'No se han registrado contactos efectivos en este conjunto.' : null],
        ];
    }

    /**
     * 2. MOTOR DE MATRICES CRUZADAS BIDIMENSIONALES (FILAS × COLUMNAS)
     */
    public function generarMatrizCruzada(string $varFila, string $varColumna, array $filtros = []): array
    {
        // Validar lista permitida
        if (!isset($this->variablesPermitidas[$varFila]) || !isset($this->variablesPermitidas[$varColumna])) {
            throw new \InvalidArgumentException('Una o ambas dimensiones seleccionadas no están en la lista permitida.');
        }

        // Obtener el universo consolidado de egresados con sus atributos normalizados
        $dataset = $this->obtenerDatasetAnalitico($filtros);

        // Extraer valores únicos de filas y columnas presentes en el dataset
        $filasValores = [];
        $columnasValores = [];

        foreach ($dataset as $fila) {
            $valF = $fila[$varFila] ?? 'Sin información';
            $valC = $fila[$varColumna] ?? 'Sin información';

            $filasValores[$valF] = true;
            $columnasValores[$valC] = true;
        }

        $filasClaves = array_keys($filasValores);
        $columnasClaves = array_keys($columnasValores);
        sort($filasClaves);
        sort($columnasClaves);

        // Inicializar matriz
        $matriz = [];
        $totalesFilas = array_fill_keys($filasClaves, 0);
        $totalesColumnas = array_fill_keys($columnasClaves, 0);
        $totalGeneral = 0;

        foreach ($filasClaves as $f) {
            $matriz[$f] = array_fill_keys($columnasClaves, 0);
        }

        // Conteo por PERSONAS ÚNICAS
        foreach ($dataset as $item) {
            $f = $item[$varFila] ?? 'Sin información';
            $c = $item[$varColumna] ?? 'Sin información';

            $matriz[$f][$c]++;
            $totalesFilas[$f]++;
            $totalesColumnas[$c]++;
            $totalGeneral++;
        }

        // Aplicar Privacidad Estadística (Supresión Primaria y Complementaria R08)
        $resultadoSeguro = $this->seguridadService->procesarMatrizSegura(
            $matriz,
            $totalesFilas,
            $totalesColumnas,
            $totalGeneral
        );

        return [
            'fila_key' => $varFila,
            'fila_label' => $this->variablesPermitidas[$varFila],
            'columna_key' => $varColumna,
            'columna_label' => $this->variablesPermitidas[$varColumna],
            'valores_fila' => $filasClaves,
            'valores_columna' => $columnasClaves,
            'celdas' => $resultadoSeguro['matriz'],
            'totales_fila' => $resultadoSeguro['totales_filas'],
            'totales_columna' => $resultadoSeguro['totales_columnas'],
            'dimension_fila' => [
                'clave' => $varFila,
                'nombre' => $this->variablesPermitidas[$varFila],
                'categorias' => $filasClaves,
            ],
            'dimension_columna' => [
                'clave' => $varColumna,
                'nombre' => $this->variablesPermitidas[$varColumna],
                'categorias' => $columnasClaves,
            ],
            'filtros_aplicados' => $filtros,
            'denominador_personas' => $totalGeneral,
            'matriz_cruda' => $matriz, // Solo visible para agregaciones internas
            'matriz_segura' => $resultadoSeguro['matriz'],
            'totales_filas' => $resultadoSeguro['totales_filas'],
            'totales_columnas' => $resultadoSeguro['totales_columnas'],
            'total_general' => is_array($resultadoSeguro['total_general']) ? ($resultadoSeguro['total_general']['valor'] ?? $totalGeneral) : $resultadoSeguro['total_general'],
            'suprimido_general' => is_array($resultadoSeguro['total_general']) ? ($resultadoSeguro['total_general']['suprimido'] ?? false) : false,
            'reglas_privacidad' => $resultadoSeguro['reglas_aplicadas'],
            'fecha_corte' => now()->toDateString(),
            'nota_metodologica' => 'Conteo de personas únicas (users.id). Las celdas entre 1 y 4 están protegidas mediante supresión estadística para salvaguardar la identidad de los egresados.',
            'nota_privacidad' => 'Conteo de personas únicas (users.id). Las celdas entre 1 y 4 están protegidas mediante supresión estadística para salvaguardar la identidad de los egresados.',
        ];
    }

    /**
     * 3. INTELIGENCIA LONGITUDINAL: EVOLUCIÓN INTERCOHORTES Y TRANSICIONES
     */
    public function generarAnalisisLongitudinal(array $filtros = []): array
    {
        $dataset = $this->obtenerDatasetAnalitico($filtros);

        // Agrupación por cohorte de egreso
        $cohortes = [];

        foreach ($dataset as $item) {
            $cohorte = $item['cohorte'] ?: 'Por verificar';
            if (!isset($cohortes[$cohorte])) {
                $cohortes[$cohorte] = [
                    'cohorte' => $cohorte,
                    'total_egresados' => 0,
                    'trabajan' => 0,
                    'docencia_directa' => 0,
                    'eib' => 0,
                    'nombrados' => 0,
                    'contratados' => 0,
                    'titulados' => 0,
                    'programas' => [
                        'Inicial' => 0,
                        'Primaria EIB' => 0,
                        'Otros' => 0,
                    ],
                ];
            }

            $cohortes[$cohorte]['total_egresados']++;

            if ($item['situacion_laboral'] === 'Trabaja' || str_contains($item['situacion_laboral'], 'docencia') || str_contains($item['situacion_laboral'], 'Trabaja')) {
                $cohortes[$cohorte]['trabajan']++;
            }
            if ($item['docencia_directa'] === 'Docencia directa en aula') {
                $cohortes[$cohorte]['docencia_directa']++;
            }
            if ($item['eib'] === 'Docencia Intercultural Bilingüe (EIB)') {
                $cohortes[$cohorte]['eib']++;
            }
            if ($item['contrato'] === 'Nombrado') {
                $cohortes[$cohorte]['nombrados']++;
            } elseif (str_contains($item['contrato'], 'Contrato')) {
                $cohortes[$cohorte]['contratados']++;
            }
            if (in_array($item['estado_titulacion'], ['Licenciado', 'Bachiller', 'Tesis aprobada'])) {
                $cohortes[$cohorte]['titulados']++;
            }

            if (str_contains($item['programa'], 'Inicial')) {
                $cohortes[$cohorte]['programas']['Inicial']++;
            } elseif (str_contains($item['programa'], 'EIB') || str_contains($item['programa'], 'Primaria')) {
                $cohortes[$cohorte]['programas']['Primaria EIB']++;
            } else {
                $cohortes[$cohorte]['programas']['Otros']++;
            }
        }

        // Ordenar cohortes cronológicamente
        ksort($cohortes);

        // Calcular porcentajes seguros
        $cohortesReporte = [];
        foreach ($cohortes as $key => $c) {
            $tot = $c['total_egresados'];
            $cohortesReporte[] = [
                'cohorte' => $c['cohorte'],
                'total_egresados' => $tot,
                'tasa_empleo' => $tot > 0 ? round(($c['trabajan'] / $tot) * 100, 1) : 0,
                'tasa_docencia_directa' => $tot > 0 ? round(($c['docencia_directa'] / $tot) * 100, 1) : 0,
                'tasa_eib' => $tot > 0 ? round(($c['eib'] / $tot) * 100, 1) : 0,
                'tasa_titulacion' => $tot > 0 ? round(($c['titulados'] / $tot) * 100, 1) : 0,
                'nombrados' => $c['nombrados'],
                'contratados' => $c['contratados'],
                'programas' => $c['programas'],
            ];
        }

        return [
            'cohortes' => $cohortesReporte,
            'comparativa_grupos' => $this->compararGrupos($dataset),
            'comparativa_programas' => $this->generarComparativaProgramas($dataset),
            'advertencia_cobertura' => 'Las variaciones entre cohortes recientes y antiguas pueden reflejar diferencias en la tasa de respuesta y no necesariamente menor éxito laboral.',
        ];
    }

    /**
     * Comparación específica entre programas Inicial vs Primaria EIB.
     */
    public function compararGrupos(Collection $dataset): array
    {
        $result = [];
        foreach (['FID','PPD','POR_VERIFICAR'] as $grupo) {
            $rows = $dataset->filter(fn ($r) => app(GrupoFormacionService::class)->coincide($r['grupos_formacion'] ?? [], $grupo))->unique('user_id');
            $valid = $rows->whereIn('situacion_laboral', ['Trabaja','No trabaja']);
            $n = $valid->count(); $empleados = $valid->where('situacion_laboral','Trabaja')->count();
            $safeN = $this->seguridadService->protegerConteo($n);
            $safeEmpleo = $this->seguridadService->protegerConteo($empleados);
            $cohortes = [];
            foreach ($rows as $row) {
                $dates = collect($row['trayectorias_confirmadas'] ?? [])->where('grupo',$grupo)->pluck('fecha_egreso')->filter()->sort()->values();
                $cohorte = $dates->isNotEmpty() ? substr($dates->last(),0,4) : (count($row['grupos_formacion'] ?? []) === 1 ? $row['cohorte'] : 'Por verificar');
                $cohortes[$cohorte] = ($cohortes[$cohorte] ?? 0) + 1;
            }
            $result[$grupo] = [
                'personas' => $this->seguridadService->protegerConteo($rows->count()),
                'con_informacion_laboral' => $safeN, 'trabajan' => $safeEmpleo,
                'tasa_empleo' => $n && !$safeN['suprimido'] && !$safeEmpleo['suprimido'] && ($n-$empleados === 0 || $n-$empleados >= 5) ? round(100*$empleados/$n,1) : null,
                'cohortes' => $this->seguridadService->procesarDistribucionSegura($cohortes),
            ];
        }
        return ['grupos' => $result, 'personas_unicas' => $dataset->unique('user_id')->count(),
            'nota' => 'Cada tasa usa respuestas laborales del propio grupo. Una persona con ambos egresos puede aparecer en FID y PPD; el conjunto la cuenta una vez. Las cohortes duales requieren fecha de egreso atribuida a cada grupo.'];
    }

    protected function generarComparativaProgramas(Collection $dataset): array
    {
        $programas = [
            'Inicial' => ['total' => 0, 'trabajan' => 0, 'docencia' => 0, 'titulados' => 0],
            'Primaria EIB' => ['total' => 0, 'trabajan' => 0, 'docencia' => 0, 'titulados' => 0],
        ];

        foreach ($dataset as $item) {
            $progKey = str_contains($item['programa'], 'EIB') ? 'Primaria EIB' : (str_contains($item['programa'], 'Inicial') ? 'Inicial' : null);
            if (!$progKey) continue;

            $programas[$progKey]['total']++;
            if ($item['situacion_laboral'] === 'Trabaja' || str_contains($item['situacion_laboral'], 'docencia')) {
                $programas[$progKey]['trabajan']++;
            }
            if ($item['docencia_directa'] === 'Docencia directa en aula') {
                $programas[$progKey]['docencia']++;
            }
            if (in_array($item['estado_titulacion'], ['Licenciado', 'Bachiller', 'Tesis aprobada'])) {
                $programas[$progKey]['titulados']++;
            }
        }

        return [
            'Inicial' => [
                'total' => $programas['Inicial']['total'],
                'tasa_empleo' => $programas['Inicial']['total'] > 0 ? round(($programas['Inicial']['trabajan'] / $programas['Inicial']['total']) * 100, 1) : 0,
                'tasa_docencia' => $programas['Inicial']['total'] > 0 ? round(($programas['Inicial']['docencia'] / $programas['Inicial']['total']) * 100, 1) : 0,
                'tasa_titulacion' => $programas['Inicial']['total'] > 0 ? round(($programas['Inicial']['titulados'] / $programas['Inicial']['total']) * 100, 1) : 0,
            ],
            'Primaria EIB' => [
                'total' => $programas['Primaria EIB']['total'],
                'tasa_empleo' => $programas['Primaria EIB']['total'] > 0 ? round(($programas['Primaria EIB']['trabajan'] / $programas['Primaria EIB']['total']) * 100, 1) : 0,
                'tasa_docencia' => $programas['Primaria EIB']['total'] > 0 ? round(($programas['Primaria EIB']['docencia'] / $programas['Primaria EIB']['total']) * 100, 1) : 0,
                'tasa_titulacion' => $programas['Primaria EIB']['total'] > 0 ? round(($programas['Primaria EIB']['titulados'] / $programas['Primaria EIB']['total']) * 100, 1) : 0,
            ]
        ];
    }

    /**
     * 4. DEMANDAS FORMATIVAS Y ACOMPAÑAMIENTO (Filiación para toma de decisiones)
     */
    public function obtenerDemandasFormativas(array $filtros = []): array
    {
        $ids = $this->obtenerDatasetAnalitico($filtros)->pluck('user_id');
        $actualizaciones = Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion','verificado')->orderByDesc('id')->get()->unique('user_id');

        $temas = [];
        $disposicionCompartir = [];
        $apoyoTitulacion = [];

        foreach ($actualizaciones as $act) {
            $t = $act->temas_interes ?: 'No especificado';
            $temas[$t] = ($temas[$t] ?? 0) + 1;

            $c = $act->puedo_compartir ?: 'Sin respuesta';
            $disposicionCompartir[$c] = ($disposicionCompartir[$c] ?? 0) + 1;

            $tit = $act->apoyo_titulacion ?: 'No especificado';
            $apoyoTitulacion[$tit] = ($apoyoTitulacion[$tit] ?? 0) + 1;
        }

        arsort($temas);
        arsort($disposicionCompartir);
        arsort($apoyoTitulacion);

        return [
            'temas_priorizados' => $this->seguridadService->procesarDistribucionSegura($temas),
            'disposicion_compartir' => $this->seguridadService->procesarDistribucionSegura($disposicionCompartir),
            'apoyo_titulacion' => $this->seguridadService->procesarDistribucionSegura($apoyoTitulacion),
        ];
    }

    /**
     * Construye el dataset analítico plano normalizado para filtros cruzados.
     */
    protected function obtenerDatasetAnalitico(array $filtros = []): Collection
    {
        $query = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'seguimientoTitulacion.estadoTitulacion']);

        // Filtro: Programa
        if (!empty($filtros['programa'])) {
            $query->where(function ($q) use ($filtros) {
                foreach (['programa','alumno.programa','alumnoB.programa'] as $rel) $q->orWhereHas($rel, fn ($p) => $p->where('nombre','like','%'.$filtros['programa'].'%'));
            });
        }

        $clasificacion = app(GrupoFormacionService::class);
        $clasificacion->aplicar($query, $filtros['grupo_formacion'] ?? null);
        $egresados = $query->get();

        $userIds = $egresados->pluck('id')->toArray();
        $actualizaciones = Actualizacion::whereIn('user_id', $userIds)->where('estado_verificacion', 'verificado')->orderBy('id')->get()->keyBy('user_id');
        $episodiosActivos = EpisodioLaboral::whereIn('user_id', $userIds)->where('es_actual', true)->orderBy('id')->get()->keyBy('user_id');

        return $egresados->map(function ($u) use ($actualizaciones, $episodiosActivos, $filtros, $clasificacion) {
            $alumno = $clasificacion->ficha($u, $filtros['grupo_formacion'] ?? null);
            $grupo = $clasificacion->resolver($u);
            $tit = $u->seguimientoTitulacion;
            $act = $actualizaciones->get($u->id);
            $ep = $episodiosActivos->get($u->id);

            // 1. Programa
            $progNombre = 'Por verificar';
            $programaInstitucional = $clasificacion->programa($u, $filtros['grupo_formacion'] ?? null);
            if ($programaInstitucional) $progNombre = $programaInstitucional->nombre;

            // 2. Cohorte
            $cohorte = 'Por verificar';
            if (count($grupo['grupos']) > 1 && in_array($filtros['grupo_formacion'] ?? null, ['FID','PPD'], true)) {
                $fechas = collect($grupo['trayectorias_confirmadas'])->where('grupo',$filtros['grupo_formacion'])->pluck('fecha_egreso')->filter()->sort();
                $cohorte = $fechas->isNotEmpty() ? substr($fechas->last(),0,4) : 'Por verificar';
            } elseif ($act && $act->cohorte_egreso) {
                $cohorte = $act->cohorte_egreso;
            } elseif ($tit && $tit->periodo_estudios) {
                $cohorte = $tit->periodo_estudios;
            }

            // 3. Titulación
            $titulacion = $tit?->estadoTitulacion?->nombre ?: (in_array($act?->titulo_lic, ['Titulado','Licenciado/a','Licenciado'], true) ? 'Licenciado' : (in_array($act?->titulo_bach, ['Obtenido','Registrado'], true) ? 'Bachiller' : 'Por verificar'));

            // 4. Situación Laboral
            $trabajaDeclarado = $act?->trabaja;
            if ($ep) {
                $situacion = 'Trabaja';
            } elseif ($trabajaDeclarado === 'Trabaja' || str_starts_with(strtolower($trabajaDeclarado ?: ''), 'trabaja')) {
                $situacion = 'Trabaja';
            } elseif ($trabajaDeclarado === 'No trabaja') {
                $situacion = 'No trabaja';
            } else {
                $situacion = 'Sin información confirmada';
            }

            // 5. Sector y Contrato
            $sector = $ep?->sector ?: ($act?->sector ?: 'No declarado');
            $contrato = $ep?->contrato ?: ($act?->contrato ?: 'No declarado');

            // 6. Docencia y Educación
            $docenciaDirecta = ($ep && $ep->es_docencia_directa) ? 'Docencia directa en aula' : 'Otra función / No docencia';
            $trabajoEducativo = ($ep && $ep->es_empleo_educativo) ? 'Empleo en el sector educativo' : 'Otro sector no educativo';

            // 7. EIB
            $esEib = ($ep && $ep->es_empleo_educativo && ($act?->eib === 'Sí' || !empty($act?->quechua)))
                || ($act && $act->eib === 'Sí')
;
            $eibLabel = $esEib ? 'Docencia Intercultural Bilingüe (EIB)' : 'Educación Regular / No EIB';

            // 8. Territorios
            $depRes = strtoupper(trim($act?->res_departamento ?: ($alumno?->departamento ?: 'NO DECLARADO')));
            $provRes = strtoupper(trim($act?->res_provincia ?: ($alumno?->provincia ?: 'NO DECLARADO')));
            $depTrab = strtoupper(trim($ep?->trab_departamento ?: ($act?->trab_departamento ?: 'NO DECLARADO')));
            $provTrab = strtoupper(trim($ep?->trab_provincia ?: ($act?->trab_provincia ?: 'NO DECLARADO')));

            // 9. Tiempo desde egreso
            $tiempoEgreso = 'Por verificar';
            if (preg_match('/(20\d{2})/', $cohorte, $mCohorte)) {
                $anioEgreso = (int) $mCohorte[1];
                $anios = date('Y') - $anioEgreso;
                if ($anios <= 1) {
                    $tiempoEgreso = 'Menos de 1 año';
                } elseif ($anios <= 2) {
                    $tiempoEgreso = '1 a 2 años';
                } elseif ($anios <= 4) {
                    $tiempoEgreso = '3 a 4 años';
                } else {
                    $tiempoEgreso = '5 o más años';
                }
            }

            // 10. Campaña y Necesidades
            $campana = $act?->periodo_campana ?: 'Sin actualización';
            $necesidades = $act?->temas_interes ?: 'No especificadas';

            return [
                'grupos_formacion' => $grupo['grupos'],
                'grupo_formacion' => $grupo['etiqueta'],
                'trayectorias_confirmadas' => $grupo['trayectorias_confirmadas'],
                'user_id' => $u->id,
                'programa' => $progNombre,
                'cohorte' => $cohorte,
                'estado_titulacion' => $titulacion,
                'situacion_laboral' => $situacion,
                'sector' => $sector,
                'contrato' => $contrato,
                'docencia_directa' => $docenciaDirecta,
                'trabajo_educativo' => $trabajoEducativo,
                'eib' => $eibLabel,
                'departamento_residencia' => $depRes,
                'provincia_residencia' => $provRes,
                'departamento_trabajo' => $depTrab,
                'provincia_trabajo' => $provTrab,
                'tiempo_egreso' => $tiempoEgreso,
                'campana' => $campana,
                'necesidades_formacion' => $necesidades,
            ];
        })->filter(function ($item) use ($filtros) {
            // Aplicación dinámica de filtros acumulados
            if (!empty($filtros['cohorte']) && $item['cohorte'] !== $filtros['cohorte']) {
                return false;
            }
            if (!empty($filtros['situacion_laboral']) && $item['situacion_laboral'] !== $filtros['situacion_laboral']) {
                return false;
            }
            if (!empty($filtros['docencia_directa']) && $item['docencia_directa'] !== $filtros['docencia_directa']) {
                return false;
            }
            if (!empty($filtros['eib']) && $item['eib'] !== $filtros['eib']) {
                return false;
            }
            if (!empty($filtros['departamento']) && !str_contains($item['departamento_residencia'], strtoupper($filtros['departamento']))) {
                return false;
            }
            if (!empty($filtros['provincia']) && !str_contains($item['provincia_residencia'], strtoupper($filtros['provincia']))) {
                return false;
            }
            if (!empty($filtros['sector']) && $item['sector'] !== $filtros['sector']) {
                return false;
            }
            if (!empty($filtros['contrato']) && $item['contrato'] !== $filtros['contrato']) {
                return false;
            }
            return true;
        });
    }
}
