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
        // 1. Padrón Institucional Total (users.condicion = 'Egresado')
        $padronTotal = User::where('condicion', 'Egresado')->count();

        // 2. Elegibles (no inhabilitados, con programa o cohorte verificable)
        $elegiblesQuery = User::where('condicion', 'Egresado')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'inhabilitado'));
        $elegiblesTotal = $elegiblesQuery->count();

        // 3. Contactados (con llamadas registradas o invitaciones enviadas)
        $userIdsConLlamada = Llamada::whereNotNull('user_id')->pluck('user_id');
        $userIdsConInvitacion = InvitacionCampana::whereNotNull('user_id')->pluck('user_id');
        $contactadosIds = $userIdsConLlamada->concat($userIdsConInvitacion)->unique();
        $contactadosTotal = $contactadosIds->count();

        // 4. Respondientes (con al menos una actualización registrada)
        $respondientesIds = Actualizacion::whereNotNull('user_id')->pluck('user_id')->unique();
        $respondientesTotal = $respondientesIds->count();

        // 5. Con información laboral válida
        $conLaboralIds = EpisodioLaboral::whereNotNull('user_id')->pluck('user_id')
            ->concat(Actualizacion::whereNotNull('user_id')->whereNotNull('trabaja')->pluck('user_id'))
            ->unique();
        $conLaboralTotal = $conLaboralIds->count();

        // 6. Con información territorial válida (residencia o trabajo)
        $conTerritorialTotal = Actualizacion::whereNotNull('user_id')
            ->where(function ($q) {
                $q->whereNotNull('res_departamento')->orWhereNotNull('trab_departamento');
            })->distinct('user_id')->count('user_id');

        // 7. Con trayectoria longitudinal (>= 2 campañas respondidas o >= 2 episodios)
        $userIdsMultiCampana = Actualizacion::whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT periodo_campana) >= 2')
            ->pluck('user_id');
        $userIdsMultiEpisodio = EpisodioLaboral::whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(id) >= 2')
            ->pluck('user_id');
        $longitudinalesTotal = $userIdsMultiCampana->concat($userIdsMultiEpisodio)->unique()->count();

        // Tasas e indicadores con denominadores explícitos
        $tasaCobertura = $elegiblesTotal > 0 ? round(($contactadosTotal / $elegiblesTotal) * 100, 1) : 0;
        $tasaRespuesta = $contactadosTotal > 0 ? round(($respondientesTotal / $contactadosTotal) * 100, 1) : 0;

        // Tasa de inserción en docencia directa (sobre respondientes)
        $docenciaDirectaCount = EpisodioLaboral::where('es_actual', true)
            ->where('es_docencia_directa', true)
            ->distinct('user_id')
            ->count('user_id');
        $tasaDocenciaDirecta = $respondientesTotal > 0 ? round(($docenciaDirectaCount / $respondientesTotal) * 100, 1) : 0;

        // Titulados (Licenciado o Bachiller en seguimiento de titulación o actualización)
        $tituladosCount = User::where('condicion', 'Egresado')
            ->whereHas('seguimientoTitulacion.estadoTitulacion', function ($q) {
                $q->whereIn('clave', ['bachiller', 'licenciado', 'tesis_aprobada', 'ti_aprobado']);
            })->count();
        $tasaTitulacion = $padronTotal > 0 ? round(($tituladosCount / $padronTotal) * 100, 1) : 0;

        return [
            'padron_total' => $padronTotal,
            'total_padron' => $padronTotal,
            'elegibles' => $elegiblesTotal,
            'elegibles_seguimiento' => $elegiblesTotal,
            'contactados' => $contactadosTotal,
            'respondientes' => $respondientesTotal,
            'no_respondientes' => max(0, $padronTotal - $respondientesTotal),
            'con_info_laboral' => $conLaboralTotal,
            'con_info_laboral_valida' => $conLaboralTotal,
            'con_info_territorial' => $conTerritorialTotal,
            'longitudinales' => $longitudinalesTotal,
            'longitudinales_multiperiodo' => $longitudinalesTotal,
            'tasa_respuesta_pct' => $tasaRespuesta,
            'cobertura_padron_pct' => $padronTotal > 0 ? round(($contactadosTotal / $padronTotal) * 100, 1) : 0,
            'advertencias_metodologicas' => [
                'La falta de respuesta en la campaña corresponde a no respondientes y bajo ningún supuesto se asume desempleo.',
                'Los denominadores expresan exclusivamente egresados con reporte confirmado.'
            ],
            'indicadores' => [
                'tasa_cobertura' => $tasaCobertura, // Contactados / Elegibles
                'tasa_respuesta' => $tasaRespuesta, // Respondientes / Contactados
                'tasa_docencia_directa' => $tasaDocenciaDirecta, // Docencia / Respondientes
                'tasa_titulacion' => $tasaTitulacion, // Titulados / Padrón
                'docencia_directa_absoluta' => $docenciaDirectaCount,
                'titulados_absoluto' => $tituladosCount,
            ],
            'advertencias' => [
                'no_respondio_no_es_desempleo' => 'La falta de respuesta en la campaña no equivale a desempleo. Los denominadores expresan exclusivamente egresados con reporte confirmado.',
                'cobertura_cuestion' => $contactadosTotal === 0 ? 'Fase de contacto en despliegue. Las tasas se consolidarán al avanzar las llamadas de entrevistadores.' : null,
            ]
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
            'comparativa_programas' => $this->generarComparativaProgramas($dataset),
            'advertencia_cobertura' => 'Las variaciones entre cohortes recientes y antiguas pueden reflejar diferencias en la tasa de respuesta y no necesariamente menor éxito laboral.',
        ];
    }

    /**
     * Comparación específica entre programas Inicial vs Primaria EIB.
     */
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
        $actualizaciones = Actualizacion::whereNotNull('user_id')->latest()->get();

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
            $query->whereHas('alumno.programa', function ($q) use ($filtros) {
                $q->where('nombre', 'like', '%' . $filtros['programa'] . '%');
            });
        }

        $egresados = $query->get();

        $userIds = $egresados->pluck('id')->toArray();
        $actualizaciones = Actualizacion::whereIn('user_id', $userIds)->latest()->get()->keyBy('user_id');
        $episodiosActivos = EpisodioLaboral::whereIn('user_id', $userIds)->where('es_actual', true)->latest()->get()->keyBy('user_id');

        return $egresados->map(function ($u) use ($actualizaciones, $episodiosActivos, $filtros) {
            $alumno = $u->alumno;
            $tit = $u->seguimientoTitulacion;
            $act = $actualizaciones->get($u->id);
            $ep = $episodiosActivos->get($u->id);

            // 1. Programa
            $progNombre = 'Por verificar';
            if ($alumno && $alumno->programa) {
                $progNombre = str_replace('Programa ', '', $alumno->programa->nombre);
            }

            // 2. Cohorte
            $cohorte = 'Por verificar';
            if ($act && $act->cohorte_egreso) {
                $cohorte = $act->cohorte_egreso;
            } elseif ($tit && $tit->periodo_estudios) {
                $cohorte = $tit->periodo_estudios;
            } elseif (preg_match('/(20\d{2})/', $u->email, $m)) {
                $cohorte = $m[1];
            }

            // 3. Titulación
            $titulacion = $tit?->estadoTitulacion?->nombre ?: ($act?->titulo_lic ? 'Licenciado' : ($act?->titulo_bach ? 'Bachiller' : 'En proceso'));

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
                || str_contains($progNombre, 'EIB');
            $eibLabel = $esEib ? 'Docencia Intercultural Bilingüe (EIB)' : 'Educación Regular / No EIB';

            // 8. Territorios
            $depRes = strtoupper(trim($act?->res_departamento ?: ($alumno?->departamento ?: 'CUSCO')));
            $provRes = strtoupper(trim($act?->res_provincia ?: ($alumno?->provincia ?: 'CUSCO')));
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
            $campana = $act?->periodo_campana ?: '2026-II';
            $necesidades = $act?->temas_interes ?: 'No especificadas';

            return [
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
