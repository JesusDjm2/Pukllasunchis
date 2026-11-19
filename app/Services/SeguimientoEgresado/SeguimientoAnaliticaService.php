<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\InvitacionCampana;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeguimientoAnaliticaService
{
    public const FUENTES_DATOS = [
        'seguimiento_vigente' => 'Seguimiento vigente',
        'encuesta_depurada_2026' => 'Encuesta depurada 2026',
    ];

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
     * Retorna las fuentes de datos disponibles en el Laboratorio.
     */
    public function getFuentesDatos(): array
    {
        return self::FUENTES_DATOS;
    }

    /**
     * Normaliza y valida el código de fuente de datos.
     */
    public function normalizarFuente(?string $fuente): string
    {
        $f = strtolower(trim((string) ($fuente ?: 'seguimiento_vigente')));
        if (!array_key_exists($f, self::FUENTES_DATOS)) {
            throw new \InvalidArgumentException('Fuente de datos no válida.');
        }
        return $f;
    }

    /**
     * Calcula la diferencia en meses entre una fecha válida de egreso y una fecha válida de inicio del primer empleo.
     * Retorna null si falta cualquiera de las dos fechas o si no son fechas válidas posteriores/iguales al egreso.
     */
    public function calcularTiempoPrimerEmpleoMeses(?string $fechaEgreso, ?string $fechaInicioEmpleo): ?float
    {
        if (empty($fechaEgreso) || empty($fechaInicioEmpleo)) {
            return null;
        }

        try {
            $egreso = Carbon::parse($fechaEgreso)->startOfDay();
            $inicio = Carbon::parse($fechaInicioEmpleo)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        if ($inicio->lt($egreso)) {
            return null;
        }

        $dias = $egreso->diffInDays($inicio);
        return round($dias / 30.4375, 1);
    }

    /**
     * 1. CÁLCULO RIGUROSO DE UNIVERSOS ANALÍTICOS SEPARADOS
     */
    public function calcularUniversos(array $filtros = []): array
    {
        $fuente = $this->normalizarFuente($filtros['fuente_datos'] ?? null);
        $grupoFormacion = app(GrupoFormacionService::class)->normalizar($filtros['grupo_formacion'] ?? null);

        if ($fuente === 'encuesta_depurada_2026') {
            $ctx = $this->obtenerContextoEncuestaDepurada2026($filtros);
            $dataset = $ctx['personas'];
            $registrosVinculados = $ctx['registros_vinculados'];
            $personasUnicas = $dataset->count();
            $ids = $dataset->pluck('user_id')->unique()->values();

            $elegiblesTotal = $ids->isNotEmpty()
                ? User::whereIn('id', $ids)->whereDoesntHave('roles', fn ($q) => $q->where('name', 'inhabilitado'))->count()
                : 0;
            $contactadosTotal = $personasUnicas;
            $respondientesTotal = $dataset->where('tiene_respuesta', true)->count();
            $conLaboralTotal = $dataset->where('tiene_info_laboral', true)->count();
            $sinDatoLaboralTotal = $personasUnicas - $conLaboralTotal;
            $conTerritorialTotal = $dataset->filter(fn ($i) => $i['departamento_residencia'] !== 'NO DECLARADO' || $i['departamento_trabajo'] !== 'NO DECLARADO')->count();
            $docenciaDirectaCount = $dataset->where('docencia_directa', 'Docencia directa en aula')->count();
            $empleoEducativoCount = $dataset->where('trabajo_educativo', 'Empleo en el sector educativo')->count();
            $tituladosCount = $dataset->filter(fn ($i) => $this->esTituladoAcreditado($i['estado_titulacion']))->count();
            $casosTiempoTotal = $dataset->filter(fn ($i) => $i['tiempo_primer_empleo_meses'] !== null)->count();

            $multiCampana = $ids->isNotEmpty()
                ? Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->groupBy('user_id')->havingRaw('COUNT(DISTINCT periodo_campana) >= 2')->pluck('user_id')
                : collect();
            $multiObs = collect($ctx['duplicados_detalle'])->pluck('user_id');
            $longitudinalesTotal = $multiCampana->concat($multiObs)->unique()->count();

            $pct = fn ($n, $d) => $d > 0 ? round(100 * $n / $d, 1) : null;
            $tasaCobertura = $pct($contactadosTotal, $elegiblesTotal);
            $tasaRespuesta = $pct($respondientesTotal, $personasUnicas);

            return [
                'fuente_datos' => $fuente,
                'fuente_etiqueta' => self::FUENTES_DATOS[$fuente],
                'fecha_corte' => $ctx['fecha_corte'],
                'registros_vinculados' => $registrosVinculados,
                'personas_unicas' => $personasUnicas,
                'diferencia_registros_personas' => $registrosVinculados - $personasUnicas,
                'duplicados_fuente' => $ctx['duplicados_detalle'],
                'explicacion_diferencia' => $ctx['explicacion_diferencia'],
                'diferencia_explicacion' => $ctx['explicacion_diferencia'],
                'padron_total' => $personasUnicas,
                'total_padron' => $personasUnicas,
                'elegibles' => $elegiblesTotal,
                'elegibles_seguimiento' => $elegiblesTotal,
                'contactados' => $contactadosTotal,
                'respondientes' => $respondientesTotal,
                'invitaciones_enviadas' => $ids->isNotEmpty() ? InvitacionCampana::whereIn('user_id', $ids)->distinct()->count('user_id') : 0,
                'no_respondientes' => $personasUnicas - $respondientesTotal,
                'sin_dato_laboral' => $sinDatoLaboralTotal,
                'con_info_laboral' => $conLaboralTotal,
                'con_info_laboral_valida' => $conLaboralTotal,
                'con_info_territorial' => $conTerritorialTotal,
                'casos_tiempo_primer_empleo' => $casosTiempoTotal,
                'longitudinales' => $longitudinalesTotal,
                'longitudinales_multiperiodo' => $longitudinalesTotal,
                'tasa_respuesta_pct' => $tasaRespuesta,
                'cobertura_padron_pct' => $pct($contactadosTotal, $personasUnicas),
                'grupo_formacion' => $grupoFormacion,
                'denominadores' => [
                    'vinculos_conciliados' => $registrosVinculados,
                    'personas_unicas' => $personasUnicas,
                    'cobertura' => $elegiblesTotal,
                    'respuesta' => $personasUnicas,
                    'docencia_directa' => $conLaboralTotal,
                    'retencion_educativa' => $conLaboralTotal,
                    'tiempo_primer_empleo' => $casosTiempoTotal,
                    'titulacion' => $personasUnicas,
                ],
                'denominadores_detalle' => [
                    'vinculos_conciliados' => "{$registrosVinculados} registros con vínculo confirmado en Conciliación histórica 2026",
                    'personas_unicas' => "{$personasUnicas} personas únicas (users.id)",
                    'respuesta' => "{$personasUnicas} personas únicas vinculadas en la fuente filtrada",
                    'docencia_directa' => "{$conLaboralTotal} personas únicas con situación laboral declarada ('Trabaja' o 'No trabaja')",
                    'retencion_educativa' => "{$conLaboralTotal} personas únicas con situación laboral declarada ('Trabaja' o 'No trabaja')",
                    'tiempo_primer_empleo' => "{$casosTiempoTotal} casos con fechas válidas de egreso e inicio del primer empleo",
                    'titulacion' => "{$personasUnicas} personas únicas vinculadas a registros académicos oficiales",
                ],
                'advertencias_metodologicas' => [
                    $ctx['explicacion_diferencia'],
                    "La situación laboral declarada conserva su corte de origen: {$ctx['fecha_corte']}.",
                    'Las observaciones con situación laboral «Sin dato» no entran en el denominador de tasas laborales y la ausencia de respuesta no equivale a desempleo.',
                ],
                'indicadores' => [
                    'tasa_cobertura' => $tasaCobertura,
                    'tasa_respuesta' => $tasaRespuesta,
                    'tasa_docencia_directa' => $pct($docenciaDirectaCount, $conLaboralTotal),
                    'tasa_retencion_educativa' => $pct($empleoEducativoCount, $conLaboralTotal),
                    'tasa_titulacion' => $pct($tituladosCount, $personasUnicas),
                    'docencia_directa_absoluta' => $docenciaDirectaCount,
                    'empleo_educativo_absoluto' => $empleoEducativoCount,
                    'titulados_absoluto' => $tituladosCount,
                ],
                'advertencias' => [
                    'no_respondio_no_es_desempleo' => 'Sin respuesta o Sin dato no equivale a desempleo.',
                    'cobertura_cuestion' => !$personasUnicas ? 'No existen registros conciliados en Encuesta depurada 2026 para los filtros seleccionados.' : null,
                ],
            ];
        }

        // Fuente: seguimiento_vigente
        $dataset = $this->obtenerDatasetAnalitico($filtros);
        $ids = $dataset->pluck('user_id')->unique()->values();
        $padronTotal = $ids->count();
        $elegiblesIds = $ids->isNotEmpty()
            ? User::whereIn('id', $ids)->whereDoesntHave('roles', fn ($q) => $q->where('name', 'inhabilitado'))->pluck('id')
            : collect();
        $elegiblesTotal = $elegiblesIds->count();
        $contactadosIds = $elegiblesIds->isNotEmpty()
            ? Llamada::whereIn('user_id', $elegiblesIds)->where('es_contacto_efectivo', true)->pluck('user_id')->unique()
            : collect();
        $contactadosTotal = $contactadosIds->count();
        $respondientesTotal = $dataset->where('tiene_respuesta', true)->count();
        $conLaboralTotal = $dataset->where('tiene_info_laboral', true)->count();
        $conTerritorialTotal = $ids->isNotEmpty()
            ? Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')
                ->where(fn ($q) => $q->whereNotNull('res_departamento')->orWhereNotNull('trab_departamento'))->distinct()->count('user_id')
            : 0;
        $multiCampana = $ids->isNotEmpty()
            ? Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->groupBy('user_id')->havingRaw('COUNT(DISTINCT periodo_campana) >= 2')->pluck('user_id')
            : collect();
        $multiEmpleo = $ids->isNotEmpty()
            ? EpisodioLaboral::whereIn('user_id', $ids)->groupBy('user_id')->havingRaw('COUNT(id) >= 2')->pluck('user_id')
            : collect();
        $longitudinalesTotal = $multiCampana->concat($multiEmpleo)->unique()->count();
        $docenciaDirectaCount = $dataset->where('docencia_directa', 'Docencia directa en aula')->count();
        $empleoEducativoCount = $dataset->where('trabajo_educativo', 'Empleo en el sector educativo')->count();
        $tituladosCount = $dataset->filter(fn ($i) => $this->esTituladoAcreditado($i['estado_titulacion']))->count();
        $casosTiempoTotal = $dataset->filter(fn ($i) => $i['tiempo_primer_empleo_meses'] !== null)->count();

        $pct = fn ($n, $d) => $d > 0 ? round(100 * $n / $d, 1) : null;
        $tasaCobertura = $pct($contactadosTotal, $elegiblesTotal);
        $tasaRespuesta = $pct($respondientesTotal, $padronTotal);
        $fechaCorteVigente = now()->toDateString();

        return [
            'fuente_datos' => $fuente,
            'fuente_etiqueta' => self::FUENTES_DATOS[$fuente],
            'fecha_corte' => $fechaCorteVigente,
            'registros_vinculados' => $respondientesTotal,
            'personas_unicas' => $padronTotal,
            'diferencia_registros_personas' => 0,
            'duplicados_fuente' => [],
            'explicacion_diferencia' => "En Seguimiento vigente el padrón filtrado comprende {$padronTotal} personas únicas (users.id), de las cuales {$respondientesTotal} cuentan con ficha de actualización verificada y {$conLaboralTotal} registran situación laboral válida.",
            'diferencia_explicacion' => "En Seguimiento vigente el padrón filtrado comprende {$padronTotal} personas únicas (users.id), de las cuales {$respondientesTotal} cuentan con ficha de actualización verificada y {$conLaboralTotal} registran situación laboral válida.",
            'padron_total' => $padronTotal,
            'total_padron' => $padronTotal,
            'elegibles' => $elegiblesTotal,
            'elegibles_seguimiento' => $elegiblesTotal,
            'contactados' => $contactadosTotal,
            'respondientes' => $respondientesTotal,
            'invitaciones_enviadas' => $ids->isNotEmpty() ? InvitacionCampana::whereIn('user_id', $ids)->distinct()->count('user_id') : 0,
            'no_respondientes' => $padronTotal - $respondientesTotal,
            'sin_dato_laboral' => $padronTotal - $conLaboralTotal,
            'con_info_laboral' => $conLaboralTotal,
            'con_info_laboral_valida' => $conLaboralTotal,
            'con_info_territorial' => $conTerritorialTotal,
            'casos_tiempo_primer_empleo' => $casosTiempoTotal,
            'longitudinales' => $longitudinalesTotal,
            'longitudinales_multiperiodo' => $longitudinalesTotal,
            'tasa_respuesta_pct' => $tasaRespuesta,
            'cobertura_padron_pct' => $pct($contactadosTotal, $padronTotal),
            'grupo_formacion' => $grupoFormacion,
            'denominadores' => [
                'vinculos_conciliados' => $respondientesTotal,
                'personas_unicas' => $padronTotal,
                'cobertura' => $elegiblesTotal,
                'respuesta' => $padronTotal,
                'docencia_directa' => $conLaboralTotal,
                'retencion_educativa' => $conLaboralTotal,
                'tiempo_primer_empleo' => $casosTiempoTotal,
                'titulacion' => $padronTotal,
            ],
            'denominadores_detalle' => [
                'vinculos_conciliados' => "{$respondientesTotal} fichas de seguimiento verificadas",
                'personas_unicas' => "{$padronTotal} personas únicas en el padrón filtrado (users.id)",
                'respuesta' => "{$padronTotal} personas únicas del padrón filtrado",
                'docencia_directa' => "{$conLaboralTotal} personas únicas con información laboral válida",
                'retencion_educativa' => "{$conLaboralTotal} personas únicas con información laboral válida",
                'tiempo_primer_empleo' => "{$casosTiempoTotal} casos con fechas válidas de egreso e inicio del primer empleo",
                'titulacion' => "{$padronTotal} personas únicas del padrón filtrado",
            ],
            'advertencias_metodologicas' => [
                'Las invitaciones e intentos no son contactos efectivos.',
                'Respuesta sobre padrón filtrado; docencia y retención educativa sobre personas con información laboral válida. Sin denominador se informa Sin dato.',
                'La ausencia de respuesta no equivale a desempleo.',
            ],
            'indicadores' => [
                'tasa_cobertura' => $tasaCobertura,
                'tasa_respuesta' => $tasaRespuesta,
                'tasa_docencia_directa' => $pct($docenciaDirectaCount, $conLaboralTotal),
                'tasa_retencion_educativa' => $pct($empleoEducativoCount, $conLaboralTotal),
                'tasa_titulacion' => $pct($tituladosCount, $padronTotal),
                'docencia_directa_absoluta' => $docenciaDirectaCount,
                'empleo_educativo_absoluto' => $empleoEducativoCount,
                'titulados_absoluto' => $tituladosCount,
            ],
            'advertencias' => [
                'no_respondio_no_es_desempleo' => 'Sin respuesta no equivale a desempleo.',
                'cobertura_cuestion' => !$contactadosTotal ? 'No se han registrado contactos efectivos en este conjunto.' : null,
            ],
        ];
    }

    /**
     * 2. MOTOR DE MATRICES CRUZADAS BIDIMENSIONALES (FILAS × COLUMNAS)
     */
    public function generarMatrizCruzada(string $varFila, string $varColumna, array $filtros = []): array
    {
        if (!isset($this->variablesPermitidas[$varFila]) || !isset($this->variablesPermitidas[$varColumna])) {
            throw new \InvalidArgumentException('Una o ambas dimensiones seleccionadas no están en la lista permitida.');
        }

        $fuente = $this->normalizarFuente($filtros['fuente_datos'] ?? null);
        $dataset = $this->obtenerDatasetAnalitico($filtros);

        $registrosVinculados = $dataset->count();
        $fechaCorte = now()->toDateString();
        $explicacionFuente = null;

        if ($fuente === 'encuesta_depurada_2026') {
            $ctx = $this->obtenerContextoEncuestaDepurada2026($filtros);
            $registrosVinculados = $ctx['registros_vinculados'];
            $fechaCorte = $ctx['fecha_corte'];
            $explicacionFuente = $ctx['explicacion_diferencia'];
        }

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

        $resultadoSeguro = $this->seguridadService->procesarMatrizSegura(
            $matriz,
            $totalesFilas,
            $totalesColumnas,
            $totalGeneral
        );

        $notaMetodologica = $fuente === 'encuesta_depurada_2026'
            ? "Fuente: Encuesta depurada 2026 ({$fechaCorte}). Vínculos conciliados: {$registrosVinculados} registros; tabulación sobre {$totalGeneral} personas únicas (users.id). " . ($explicacionFuente ?: '')
            : 'Conteo exacto de personas únicas (users.id), incluyendo desgloses con valores menores a 5.';

        return [
            'fuente_datos' => $fuente,
            'fuente_etiqueta' => self::FUENTES_DATOS[$fuente],
            'registros_vinculados' => $registrosVinculados,
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
            'matriz_cruda' => $matriz,
            'matriz_segura' => $resultadoSeguro['matriz'],
            'totales_filas' => $resultadoSeguro['totales_filas'],
            'totales_columnas' => $resultadoSeguro['totales_columnas'],
            'total_general' => is_array($resultadoSeguro['total_general']) ? ($resultadoSeguro['total_general']['valor'] ?? $totalGeneral) : $resultadoSeguro['total_general'],
            'suprimido_general' => is_array($resultadoSeguro['total_general']) ? ($resultadoSeguro['total_general']['suprimido'] ?? false) : false,
            'reglas_privacidad' => $resultadoSeguro['reglas_aplicadas'],
            'fecha_corte' => $fechaCorte,
            'nota_metodologica' => trim($notaMetodologica),
            'nota_privacidad' => trim($notaMetodologica),
        ];
    }

    /**
     * 3. INTELIGENCIA LONGITUDINAL: EVOLUCIÓN INTERCOHORTES Y CALIDAD DE INSERCIÓN
     * Reutilizado de forma idéntica para la tabla en pantalla, actualización AJAX y exportación Excel (.xlsx).
     */
    public function generarAnalisisLongitudinal(array $filtros = []): array
    {
        $fuente = $this->normalizarFuente($filtros['fuente_datos'] ?? null);
        $grupoFormacion = app(GrupoFormacionService::class)->normalizar($filtros['grupo_formacion'] ?? null);

        $registrosPorCohorte = [];
        $totalRegistrosVinculados = 0;
        $fechaCorte = now()->toDateString();
        $explicacionDiferencia = null;

        if ($fuente === 'encuesta_depurada_2026') {
            $ctx = $this->obtenerContextoEncuestaDepurada2026($filtros);
            $dataset = $ctx['personas'];
            $registrosPorCohorte = $ctx['registros_por_cohorte'];
            $totalRegistrosVinculados = $ctx['registros_vinculados'];
            $fechaCorte = $ctx['fecha_corte'];
            $explicacionDiferencia = $ctx['explicacion_diferencia'];
        } else {
            $dataset = $this->obtenerDatasetAnalitico($filtros);
            $totalRegistrosVinculados = $dataset->where('tiene_respuesta', true)->count();
            $explicacionDiferencia = "En Seguimiento vigente el padrón filtrado registra {$dataset->count()} personas únicas y {$totalRegistrosVinculados} respondientes verificados.";
        }

        $cohortes = [];

        foreach ($dataset as $item) {
            $cohorte = $item['cohorte'] ?: 'Por verificar';
            if (!isset($cohortes[$cohorte])) {
                $cohortes[$cohorte] = [
                    'cohorte' => $cohorte,
                    'total_egresados' => 0,
                    'respondientes' => 0,
                    'con_info_laboral' => 0,
                    'trabajan' => 0,
                    'docencia_directa' => 0,
                    'empleo_educativo' => 0,
                    'eib' => 0,
                    'nombrados' => 0,
                    'contratados' => 0,
                    'titulados' => 0,
                    'tiempos_primer_empleo' => [],
                    'programas' => [
                        'Inicial' => 0,
                        'Primaria EIB' => 0,
                        'Otros' => 0,
                    ],
                ];
            }

            $cohortes[$cohorte]['total_egresados']++;

            if (!empty($item['tiene_respuesta'])) {
                $cohortes[$cohorte]['respondientes']++;
            }

            if (!empty($item['tiene_info_laboral'])) {
                $cohortes[$cohorte]['con_info_laboral']++;
            }

            if ($item['situacion_laboral'] === 'Trabaja' || str_starts_with(strtolower((string) $item['situacion_laboral']), 'trabaja')) {
                $cohortes[$cohorte]['trabajan']++;
            }

            if ($item['docencia_directa'] === 'Docencia directa en aula') {
                $cohortes[$cohorte]['docencia_directa']++;
            }

            if ($item['trabajo_educativo'] === 'Empleo en el sector educativo') {
                $cohortes[$cohorte]['empleo_educativo']++;
            }

            if ($item['eib'] === 'Docencia Intercultural Bilingüe (EIB)') {
                $cohortes[$cohorte]['eib']++;
            }

            if ($item['contrato'] === 'Nombrado' || str_contains((string) $item['contrato'], 'Nombrado')) {
                $cohortes[$cohorte]['nombrados']++;
            } elseif (str_contains((string) $item['contrato'], 'Contrato') || str_contains((string) $item['contrato'], 'Contratado')) {
                $cohortes[$cohorte]['contratados']++;
            }

            if ($this->esTituladoAcreditado($item['estado_titulacion'])) {
                $cohortes[$cohorte]['titulados']++;
            }

            if ($item['tiempo_primer_empleo_meses'] !== null) {
                $cohortes[$cohorte]['tiempos_primer_empleo'][] = (float) $item['tiempo_primer_empleo_meses'];
            }

            if (str_contains((string) $item['programa'], 'Inicial')) {
                $cohortes[$cohorte]['programas']['Inicial']++;
            } elseif (str_contains((string) $item['programa'], 'EIB') || str_contains((string) $item['programa'], 'Primaria')) {
                $cohortes[$cohorte]['programas']['Primaria EIB']++;
            } else {
                $cohortes[$cohorte]['programas']['Otros']++;
            }
        }

        // Ordenar cohortes cronológicamente dejando "Por verificar" al final
        uksort($cohortes, function ($a, $b) {
            if ($a === 'Por verificar') return 1;
            if ($b === 'Por verificar') return -1;
            return strcmp((string) $a, (string) $b);
        });

        $cohortesReporte = [];
        $acumTotalEgr = 0;
        $acumResp = 0;
        $acumConLab = 0;
        $acumTrab = 0;
        $acumDoc = 0;
        $acumEdu = 0;
        $acumEib = 0;
        $acumTit = 0;
        $acumNombrados = 0;
        $acumContratados = 0;
        $acumTiempos = [];

        foreach ($cohortes as $key => $c) {
            $regCohorte = $fuente === 'encuesta_depurada_2026'
                ? ($registrosPorCohorte[$key] ?? $c['total_egresados'])
                : $c['respondientes'];

            $filaFormateada = $this->construirFilaCohorte($c, $regCohorte, $fuente);
            $cohortesReporte[] = $filaFormateada;

            $acumTotalEgr += $c['total_egresados'];
            $acumResp += $c['respondientes'];
            $acumConLab += $c['con_info_laboral'];
            $acumTrab += $c['trabajan'];
            $acumDoc += $c['docencia_directa'];
            $acumEdu += $c['empleo_educativo'];
            $acumEib += $c['eib'];
            $acumTit += $c['titulados'];
            $acumNombrados += $c['nombrados'];
            $acumContratados += $c['contratados'];
            foreach ($c['tiempos_primer_empleo'] as $tVal) {
                $acumTiempos[] = $tVal;
            }
        }

        $totalesFila = $this->construirFilaCohorte([
            'cohorte' => 'TOTAL',
            'total_egresados' => $acumTotalEgr,
            'respondientes' => $acumResp,
            'con_info_laboral' => $acumConLab,
            'trabajan' => $acumTrab,
            'docencia_directa' => $acumDoc,
            'empleo_educativo' => $acumEdu,
            'eib' => $acumEib,
            'nombrados' => $acumNombrados,
            'contratados' => $acumContratados,
            'titulados' => $acumTit,
            'tiempos_primer_empleo' => $acumTiempos,
            'programas' => [
                'Inicial' => $dataset->filter(fn ($i) => str_contains((string) $i['programa'], 'Inicial'))->count(),
                'Primaria EIB' => $dataset->filter(fn ($i) => str_contains((string) $i['programa'], 'EIB') || str_contains((string) $i['programa'], 'Primaria'))->count(),
                'Otros' => $dataset->filter(fn ($i) => !str_contains((string) $i['programa'], 'Inicial') && !str_contains((string) $i['programa'], 'EIB') && !str_contains((string) $i['programa'], 'Primaria'))->count(),
            ],
        ], $totalRegistrosVinculados, $fuente);

        $resumenTitulacion = [
            'licenciado' => $dataset->filter(fn ($i) => in_array($i['estado_titulacion'], ['Licenciado', 'Licenciada/a', 'Titulado(a)', 'Titulado'], true))->count(),
            'tesis_aprobada' => $dataset->filter(fn ($i) => in_array($i['estado_titulacion'], ['Bachiller', 'Tesis aprobada', 'Trabajo de investigación aprobado', 'PTI aprobado'], true))->count(),
            'pti_en_proceso' => $dataset->filter(fn ($i) => str_contains(strtolower((string) $i['estado_titulacion']), 'proceso') || str_contains(strtolower((string) $i['estado_titulacion']), 'trámite') || str_contains(strtolower((string) $i['estado_titulacion']), 'tramite'))->count(),
        ];

        return [
            'fuente_datos' => $fuente,
            'fuente_etiqueta' => self::FUENTES_DATOS[$fuente],
            'fecha_corte' => $fechaCorte,
            'filtros_aplicados' => [
                'fuente_datos' => $fuente,
                'fuente_etiqueta' => self::FUENTES_DATOS[$fuente],
                'grupo_formacion' => $grupoFormacion,
                'programa' => !empty($filtros['programa']) ? (string) $filtros['programa'] : 'Todos los programas',
                'cohorte' => !empty($filtros['cohorte']) ? (string) $filtros['cohorte'] : 'Todas las cohortes',
                'docencia_directa' => !empty($filtros['docencia_directa']) ? (string) $filtros['docencia_directa'] : 'Todos',
            ],
            'registros_vinculados' => $totalRegistrosVinculados,
            'personas_unicas' => $dataset->count(),
            'diferencia_conteos' => $totalRegistrosVinculados - $dataset->count(),
            'explicacion_diferencia' => $explicacionDiferencia,
            'denominadores_metodologia' => [
                'respondientes' => 'Personas únicas de la cohorte en la fuente seleccionada (Padrón Egresados)',
                'docencia_directa' => 'Personas únicas de la cohorte con situación laboral declarada (Trabaja o No trabaja)',
                'tiempo_primer_empleo' => 'Casos de la cohorte con fechas válidas de egreso e inicio del primer empleo',
                'retencion_educativa' => 'Personas únicas de la cohorte con situación laboral declarada (Trabaja o No trabaja)',
                'tasa_titulacion' => 'Personas únicas de la cohorte vinculadas a registros académicos oficiales',
            ],
            'cohortes' => $cohortesReporte,
            'totales' => $totalesFila,
            'titulacion' => $resumenTitulacion,
            'comparativa_grupos' => $this->compararGrupos($dataset),
            'comparativa_programas' => $this->generarComparativaProgramas($dataset),
            'advertencia_cobertura' => 'Las tasas laborales se calculan exclusivamente sobre personas únicas con información laboral válida. Cuando no existen casos en el denominador se reporta «Sin dato»; cuando el denominador existe y no hay casos positivos se reporta «0».',
        ];
    }

    /**
     * Construye una fila de cohorte con numeradores, denominadores y textos canónicos para pantalla y Excel (.xlsx).
     */
    protected function construirFilaCohorte(array $c, int $registrosVinculados, string $fuente): array
    {
        $tot = (int) $c['total_egresados'];
        $resp = (int) $c['respondientes'];
        $conLab = (int) $c['con_info_laboral'];
        $trab = (int) $c['trabajan'];
        $doc = (int) $c['docencia_directa'];
        $edu = (int) $c['empleo_educativo'];
        $eib = (int) $c['eib'];
        $tit = (int) $c['titulados'];
        $tiempos = $c['tiempos_primer_empleo'] ?? [];
        $nCasosTiempo = count($tiempos);

        $tasaRespuesta = $tot > 0 ? round(($resp / $tot) * 100, 1) : null;
        $tasaEmpleo = $conLab > 0 ? round(($trab / $conLab) * 100, 1) : null;
        $tasaDocencia = $conLab > 0 ? round(($doc / $conLab) * 100, 1) : null;
        $tasaRetencionEdu = $conLab > 0 ? round(($edu / $conLab) * 100, 1) : null;
        $tasaEib = $conLab > 0 ? round(($eib / $conLab) * 100, 1) : null;
        $tasaTitulacion = $tot > 0 ? round(($tit / $tot) * 100, 1) : null;
        $tiempoPromedio = $nCasosTiempo > 0 ? round(array_sum($tiempos) / $nCasosTiempo, 1) : null;

        // Textos canónicos verificables (idénticos en pantalla y Excel)
        if ($fuente === 'encuesta_depurada_2026' && $registrosVinculados !== $tot) {
            $padronTexto = "{$tot} personas ({$registrosVinculados} vínculos)";
        } else {
            $padronTexto = (string) $tot;
        }

        if ($tot === 0) {
            $respondientesTexto = '0 (0/0)';
        } else {
            $respondientesTexto = "{$resp} ({$resp}/{$tot} · {$tasaRespuesta}%)";
        }

        if ($conLab === 0) {
            $docenciaTexto = 'Sin dato (0/0)';
            $retencionTexto = 'Sin dato (0/0)';
        } else {
            $docenciaTexto = "{$tasaDocencia}% ({$doc}/{$conLab})";
            $retencionTexto = "{$tasaRetencionEdu}% ({$edu}/{$conLab})";
        }

        if ($nCasosTiempo === 0) {
            $tiempoTexto = 'Sin dato (0 casos)';
        } else {
            $etiquetaCasos = $nCasosTiempo === 1 ? '1 caso' : "{$nCasosTiempo} casos";
            $tiempoTexto = "{$tiempoPromedio} meses ({$etiquetaCasos})";
        }

        if ($tot === 0) {
            $titulacionTexto = 'Sin dato (0/0)';
        } else {
            $titulacionTexto = "{$tasaTitulacion}% ({$tit}/{$tot})";
        }

        return [
            'cohorte' => $c['cohorte'],
            'registros_vinculados' => $registrosVinculados,
            'total_egresados' => $tot,
            'respondientes' => $resp,
            'denominador_respondientes' => $tot,
            'tasa_respuesta' => $tasaRespuesta,
            'con_info_laboral' => $conLab,
            'trabajan' => $trab,
            'docencia_directa' => $doc,
            'denominador_docencia_directa' => $conLab,
            'tasa_docencia_directa' => $tasaDocencia,
            'en_docencia_pct' => $tasaDocencia,
            'casos_tiempo_primer_empleo' => $nCasosTiempo,
            'casos_tiempo_empleo' => $nCasosTiempo,
            'denominador_tiempo_primer_empleo' => $nCasosTiempo,
            'tiempo_promedio_primer_empleo_meses' => $tiempoPromedio,
            'empleo_educativo' => $edu,
            'denominador_retencion_educativa' => $conLab,
            'retencion_educativa_pct' => $tasaRetencionEdu,
            'tasa_empleo' => $tasaEmpleo,
            'tasa_eib' => $tasaEib,
            'titulados' => $tit,
            'denominador_titulacion' => $tot,
            'tasa_titulacion' => $tasaTitulacion,
            'tasa_titulacion_pct' => $tasaTitulacion,
            'nombrados' => $c['nombrados'],
            'contratados' => $c['contratados'],
            'programas' => $c['programas'],
            // Textos canónicos compartidos entre Blade, AJAX y Excel (.xlsx)
            'padron_texto' => $padronTexto,
            'respondientes_texto' => $respondientesTexto,
            'docencia_directa_texto' => $docenciaTexto,
            'tiempo_primer_empleo_texto' => $tiempoTexto,
            'retencion_educativa_texto' => $retencionTexto,
            'tasa_titulacion_texto' => $titulacionTexto,
        ];
    }

    /**
     * Determina si el estado de titulación corresponde a un grado o título acreditado.
     */
    protected function esTituladoAcreditado(?string $estado): bool
    {
        $e = strtolower(trim((string) $estado));
        if ($e === '' || $e === 'por verificar' || $e === 'sin dato') {
            return false;
        }
        return in_array($e, [
            'licenciado', 'licenciada/a', 'licenciado/a', 'titulado', 'titulado(a)', 'titulado/a',
            'bachiller', 'tesis aprobada', 'trabajo de investigación aprobado',
        ], true);
    }

    /**
     * Comparación entre grupos de formación FID vs PPD.
     */
    public function compararGrupos(Collection $dataset): array
    {
        $result = [];
        foreach (['FID', 'PPD', 'POR_VERIFICAR'] as $grupo) {
            $rows = $dataset->filter(fn ($r) => app(GrupoFormacionService::class)->coincide($r['grupos_formacion'] ?? [], $grupo))->unique('user_id');
            $valid = $rows->where('tiene_info_laboral', true);
            $n = $valid->count();
            $empleados = $valid->where('situacion_laboral', 'Trabaja')->count();
            $safeN = $this->seguridadService->protegerConteo($n);
            $safeEmpleo = $this->seguridadService->protegerConteo($empleados);
            $cohortes = [];
            foreach ($rows as $row) {
                $dates = collect($row['trayectorias_confirmadas'] ?? [])->where('grupo', $grupo)->pluck('fecha_egreso')->filter()->sort()->values();
                $cohorte = $dates->isNotEmpty() ? substr($dates->last(), 0, 4) : (count($row['grupos_formacion'] ?? []) === 1 ? $row['cohorte'] : 'Por verificar');
                $cohortes[$cohorte] = ($cohortes[$cohorte] ?? 0) + 1;
            }
            $result[$grupo] = [
                'personas' => $this->seguridadService->protegerConteo($rows->count()),
                'con_informacion_laboral' => $safeN,
                'trabajan' => $safeEmpleo,
                'tasa_empleo' => $n > 0 ? round(100 * $empleados / $n, 1) : null,
                'cohortes' => $this->seguridadService->procesarDistribucionSegura($cohortes),
            ];
        }
        return [
            'grupos' => $result,
            'personas_unicas' => $dataset->unique('user_id')->count(),
            'nota' => 'Cada tasa usa respuestas laborales del propio grupo. Una persona con ambos egresos puede aparecer en FID y PPD; el conjunto la cuenta una vez. Las cohortes duales requieren fecha de egreso atribuida a cada grupo.',
        ];
    }

    protected function generarComparativaProgramas(Collection $dataset): array
    {
        $programas = [
            'Inicial' => ['total' => 0, 'con_lab' => 0, 'trabajan' => 0, 'docencia' => 0, 'titulados' => 0],
            'Primaria EIB' => ['total' => 0, 'con_lab' => 0, 'trabajan' => 0, 'docencia' => 0, 'titulados' => 0],
        ];

        foreach ($dataset as $item) {
            $progKey = (str_contains((string) $item['programa'], 'EIB') || str_contains((string) $item['programa'], 'Primaria'))
                ? 'Primaria EIB'
                : (str_contains((string) $item['programa'], 'Inicial') ? 'Inicial' : null);
            if (!$progKey) {
                continue;
            }

            $programas[$progKey]['total']++;
            if (!empty($item['tiene_info_laboral'])) {
                $programas[$progKey]['con_lab']++;
            }
            if ($item['situacion_laboral'] === 'Trabaja' || str_contains(strtolower((string) $item['situacion_laboral']), 'trabaja')) {
                $programas[$progKey]['trabajan']++;
            }
            if ($item['docencia_directa'] === 'Docencia directa en aula') {
                $programas[$progKey]['docencia']++;
            }
            if ($this->esTituladoAcreditado($item['estado_titulacion'])) {
                $programas[$progKey]['titulados']++;
            }
        }

        $res = [];
        foreach (['Inicial', 'Primaria EIB'] as $pKey) {
            $tot = $programas[$pKey]['total'];
            $conLab = $programas[$pKey]['con_lab'];
            $doc = $programas[$pKey]['docencia'];
            $trab = $programas[$pKey]['trabajan'];
            $tit = $programas[$pKey]['titulados'];

            $tasaDoc = $conLab > 0 ? round(($doc / $conLab) * 100, 1) : null;
            $tasaEmp = $conLab > 0 ? round(($trab / $conLab) * 100, 1) : null;
            $tasaTit = $tot > 0 ? round(($tit / $tot) * 100, 1) : null;

            $res[$pKey] = [
                'total' => $tot,
                'con_info_laboral' => $conLab,
                'docencia' => $doc,
                'tasa_empleo' => $tasaEmp,
                'tasa_docencia' => $tasaDoc,
                'en_docencia_pct' => $tasaDoc,
                'en_docencia_texto' => $conLab > 0 ? "{$tasaDoc}% ({$doc}/{$conLab})" : 'Sin dato (0/0)',
                'tasa_titulacion' => $tasaTit,
            ];
        }

        return $res;
    }

    /**
     * 4. DEMANDAS FORMATIVAS Y ACOMPAÑAMIENTO
     */
    public function obtenerDemandasFormativas(array $filtros = []): array
    {
        $fuente = $this->normalizarFuente($filtros['fuente_datos'] ?? null);
        $dataset = $this->obtenerDatasetAnalitico($filtros);
        $ids = $dataset->pluck('user_id');

        if ($fuente === 'encuesta_depurada_2026') {
            $temas = [];
            foreach ($dataset as $item) {
                $t = trim((string) ($item['necesidades_formacion'] ?? ''));
                if ($t === '' || $t === 'No especificadas' || $t === 'No especificado') {
                    $temas['No especificado'] = ($temas['No especificado'] ?? 0) + 1;
                } else {
                    $temas[mb_substr($t, 0, 90)] = ($temas[mb_substr($t, 0, 90)] ?? 0) + 1;
                }
            }
            arsort($temas);
            $temasList = [];
            foreach ($temas as $tKey => $tCnt) {
                if ($tKey === 'No especificado') continue;
                $temasList[] = ['tema' => $tKey, 'solicitudes' => $tCnt];
            }
            $casosUrgentesTotal = $ids->isNotEmpty()
                ? Caso::whereIn('user_id', $ids)->whereIn('estado', ['Por revisar', 'En atención', 'Pendiente'])->count()
                : 0;

            return [
                'temas_formativos' => array_slice($temasList, 0, 12),
                'casos_atencion_urgente' => ['total' => $casosUrgentesTotal],
                'temas_priorizados' => $this->seguridadService->procesarDistribucionSegura($temas),
                'disposicion_compartir' => [],
                'apoyo_titulacion' => [],
            ];
        }

        $actualizaciones = $ids->isNotEmpty()
            ? Actualizacion::whereIn('user_id', $ids)->where('estado_verificacion', 'verificado')->orderByDesc('id')->get()->unique('user_id')
            : collect();

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

        $temasList = [];
        foreach ($temas as $tKey => $tCnt) {
            if ($tKey === 'No especificado') continue;
            $temasList[] = ['tema' => $tKey, 'solicitudes' => $tCnt];
        }
        $casosUrgentesTotal = $ids->isNotEmpty()
            ? Caso::whereIn('user_id', $ids)->whereIn('estado', ['Por revisar', 'En atención', 'Pendiente'])->count()
            : 0;

        return [
            'temas_formativos' => array_slice($temasList, 0, 12),
            'casos_atencion_urgente' => ['total' => $casosUrgentesTotal],
            'temas_priorizados' => $this->seguridadService->procesarDistribucionSegura($temas),
            'disposicion_compartir' => $this->seguridadService->procesarDistribucionSegura($disposicionCompartir),
            'apoyo_titulacion' => $this->seguridadService->procesarDistribucionSegura($apoyoTitulacion),
        ];
    }

    /**
     * Construye el contexto verificable de la fuente «encuesta_depurada_2026» a partir de sus observaciones históricas conciliadas.
     * Separa explícitamente los registros vinculados (113 sin filtros) de las personas únicas (112 sin filtros).
     */
    public function obtenerContextoEncuestaDepurada2026(array $filtros = []): array
    {
        $fechaCorteDefault = ConciliacionHistoricoService::FUENTES_DEFINIDAS['encuesta_depurada_2026']['fecha_corte_etiqueta']
            ?? 'Corte 2026 (fecha exacta no consignada)';

        if (!Schema::hasTable('egresado_historico_observaciones') || !Schema::hasTable('egresado_historico_registros')) {
            return [
                'fuente_datos' => 'encuesta_depurada_2026',
                'fecha_corte' => $fechaCorteDefault,
                'registros_vinculados' => 0,
                'personas_unicas' => 0,
                'registros_por_cohorte' => [],
                'duplicados_detalle' => [],
                'explicacion_diferencia' => 'Sin tablas de conciliación histórica disponibles.',
                'observaciones' => collect(),
                'personas' => collect(),
            ];
        }

        $filasObs = DB::table('egresado_historico_observaciones as o')
            ->join('egresado_historico_fuentes as f', 'f.id', '=', 'o.fuente_id')
            ->join('egresado_historico_registros as r', 'r.id', '=', 'o.registro_origen_id')
            ->where('f.codigo', 'encuesta_depurada_2026')
            ->where('r.estado_conciliacion', 'vinculo_confirmado')
            ->orderBy('o.id')
            ->get([
                'o.*',
                'f.codigo as fuente_codigo',
                'f.nombre as fuente_nombre',
                'r.fila_origen',
                'r.estado_conciliacion',
                'r.fecha_fuente',
            ]);

        if ($filasObs->isEmpty()) {
            return [
                'fuente_datos' => 'encuesta_depurada_2026',
                'fecha_corte' => $fechaCorteDefault,
                'registros_vinculados' => 0,
                'personas_unicas' => 0,
                'registros_por_cohorte' => [],
                'duplicados_detalle' => [],
                'explicacion_diferencia' => 'No existen registros vinculados para Encuesta depurada 2026.',
                'observaciones' => collect(),
                'personas' => collect(),
            ];
        }

        $userIds = $filasObs->pluck('user_id')->unique()->values()->all();
        $clasificacion = app(GrupoFormacionService::class);
        $usuarios = User::whereIn('id', $userIds)
            ->with(array_merge(GrupoFormacionService::RELACIONES, ['seguimientoTitulacion.estadoTitulacion', 'ciclo']))
            ->get()
            ->keyBy('id');

        $actualizaciones = Actualizacion::whereIn('user_id', $userIds)
            ->where('estado_verificacion', 'verificado')
            ->orderBy('id')
            ->get()
            ->keyBy('user_id');

        $primerosEpisodiosConFecha = EpisodioLaboral::whereIn('user_id', $userIds)
            ->whereNotNull('fecha_inicio')
            ->orderBy('fecha_inicio')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($eps) => $eps->first());

        $fechaCorteEfectiva = $filasObs->first()->fecha_corte ?: ($filasObs->first()->fecha_fuente ?: $fechaCorteDefault);

        // Primero determinar la cohorte y programa canónicos por persona única (la observación verificada más reciente por user_id)
        $canonicaPorUser = [];
        foreach ($filasObs as $obs) {
            $canonicaPorUser[$obs->user_id] = $obs;
        }

        $cohorteCanonicaPorUser = [];
        foreach ($canonicaPorUser as $uid => $obsCan) {
            $attrCan = json_decode($obsCan->atributos_normalizados ?? '{}', true) ?: [];
            $cRawCan = trim((string) ($attrCan['cohorte_declarada_fuente'] ?? ''));
            if (preg_match('/(20\d{2})/', $cRawCan, $mCan)) {
                $cohorteCanonicaPorUser[$uid] = $mCan[1];
            } else {
                $uObj = $usuarios->get($uid);
                $evEg = $uObj?->trayectoriaEventos?->where('tipo', 'egreso')?->first();
                $cohorteCanonicaPorUser[$uid] = ($evEg && $evEg->fecha_inicio)
                    ? $evEg->fecha_inicio->format('Y')
                    : 'Por verificar';
            }
        }

        $observacionesMapeadas = $filasObs->map(function ($obs) use (
            $usuarios,
            $actualizaciones,
            $primerosEpisodiosConFecha,
            $clasificacion,
            $filtros,
            $cohorteCanonicaPorUser,
            $fechaCorteDefault
        ) {
            /** @var User|null $u */
            $u = $usuarios->get($obs->user_id);
            if (!$u) {
                return null;
            }

            $attr = json_decode($obs->atributos_normalizados ?? '{}', true) ?: [];
            $grupo = $clasificacion->resolver($u);
            $grupos = $grupo['grupos'];
            if (in_array($obs->trayectoria_tipo, ['FID', 'PPD'], true) && !in_array($obs->trayectoria_tipo, $grupos, true)) {
                $grupos[] = $obs->trayectoria_tipo;
                sort($grupos);
            }

            $alumno = $clasificacion->ficha($u, $filtros['grupo_formacion'] ?? null);
            $tit = $u->seguimientoTitulacion;
            $act = $actualizaciones->get($u->id);
            $epConFecha = $primerosEpisodiosConFecha->get($u->id);

            // 1. Programa (prioriza registro académico oficial y conserva correspondencia)
            $programaInstitucional = $clasificacion->programa($u, $filtros['grupo_formacion'] ?? null);
            $progNombre = $programaInstitucional?->nombre ?: 'Por verificar';
            if ($progNombre === 'Por verificar' && !empty($attr['programa_declarado_fuente'])) {
                $pFuente = trim((string) $attr['programa_declarado_fuente']);
                if (stripos($pFuente, 'Inicial') !== false) {
                    $progNombre = 'Programa Inicial';
                } elseif (stripos($pFuente, 'EIB') !== false || stripos($pFuente, 'Primaria') !== false) {
                    $progNombre = 'Programa Primaria EIB';
                }
            }

            // 2. Cohorte declarada en el registro vs cohorte canónica de la persona
            $cRaw = trim((string) ($attr['cohorte_declarada_fuente'] ?? ''));
            $cohorteRegistro = preg_match('/(20\d{2})/', $cRaw, $mCoh)
                ? $mCoh[1]
                : ($cohorteCanonicaPorUser[$u->id] ?? 'Por verificar');
            $cohortePersona = $cohorteCanonicaPorUser[$u->id] ?? $cohorteRegistro;

            // 3. Estado de titulación (registro académico oficial prioritario)
            $sitAcadFuente = trim((string) ($attr['situacion_academica_fuente'] ?? ''));
            $titulacion = $tit?->estadoTitulacion?->nombre
                ?: (in_array($sitAcadFuente, ['Licenciada/a', 'Licenciado', 'Titulado(a)', 'Titulado'], true)
                    ? 'Licenciado'
                    : (stripos($sitAcadFuente, 'Bachiller') !== false ? 'Bachiller' : 'Por verificar'));

            // 4. Situación laboral declarada en la encuesta con su fecha o corte de origen
            $situacion = $obs->situacion_laboral_declarada ?: 'Sin dato';
            $fechaCorteOrigen = $obs->fecha_corte ?: ($obs->fecha_fuente ?: $fechaCorteDefault);
            $tieneInfoLaboral = in_array($situacion, ['Trabaja', 'No trabaja'], true);

            // 5. Sector y Contrato declarados en la encuesta
            $sector = $obs->sector_declarado ?: ($tieneInfoLaboral ? 'No declarado' : 'Sin dato');
            $contrato = !empty($attr['modalidad_contrato_historico'])
                ? (string) $attr['modalidad_contrato_historico']
                : ($obs->tipo_practica_declarada ?: ($tieneInfoLaboral ? 'No declarado' : 'Sin dato'));

            // 6. Docencia directa y Empleo educativo según la observación de la encuesta
            $tipoPractica = (string) ($obs->tipo_practica_declarada ?? '');
            $insercionEdu = (string) ($obs->insercion_educativa_declarada ?? '');

            if (!$tieneInfoLaboral) {
                $docenciaDirecta = 'Sin dato';
                $trabajoEducativo = 'Sin dato';
            } else {
                $esDoc = stripos($tipoPractica, 'Docencia directa') !== false || stripos($tipoPractica, 'Docencia en aula') !== false;
                $docenciaDirecta = $esDoc ? 'Docencia directa en aula' : 'Otra función / No docencia';

                $esEdu = $insercionEdu === 'En educación'
                    || in_array($tipoPractica, ['Docencia directa', 'Auxiliar / apoyo pedagógico', 'Gestión educativa', 'Educación no formal / comunitaria'], true);
                $trabajoEducativo = $esEdu ? 'Empleo en el sector educativo' : 'Otro sector no educativo';
            }

            // 7. EIB
            $esEib = $tieneInfoLaboral
                && $trabajoEducativo === 'Empleo en el sector educativo'
                && (stripos($progNombre, 'EIB') !== false || $act?->eib === 'Sí' || !empty($act?->quechua));
            $eibLabel = !$tieneInfoLaboral
                ? 'Sin dato'
                : ($esEib ? 'Docencia Intercultural Bilingüe (EIB)' : 'Educación Regular / No EIB');

            // 8. Territorios
            $depRes = strtoupper(trim($act?->res_departamento ?: ($alumno?->departamento ?: 'NO DECLARADO')));
            $provRes = strtoupper(trim($act?->res_provincia ?: ($alumno?->provincia ?: 'NO DECLARADO')));
            $depTrab = strtoupper(trim($act?->trab_departamento ?: (!empty($obs->lugar_trabajo_texto_origen) ? 'CUSCO' : 'NO DECLARADO')));
            $provTrab = strtoupper(trim($act?->trab_provincia ?: 'NO DECLARADO'));

            // 9. Tiempo desde egreso
            $tiempoEgreso = 'Por verificar';
            if (preg_match('/(20\d{2})/', $cohortePersona, $mAnio)) {
                $anios = (int) date('Y') - (int) $mAnio[1];
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

            // 10. Fechas válidas para cálculo de tiempo al primer empleo (sin valores de respaldo)
            $fechaEgresoValida = $this->obtenerFechaEgresoValida($u, $grupo, $filtros['grupo_formacion'] ?? null);
            $fechaInicioEmpleoValida = $epConFecha?->fecha_inicio?->format('Y-m-d')
                ?: ($act?->inicio_empleo ? Carbon::parse($act->inicio_empleo)->format('Y-m-d') : null);
            $tiempoPrimerEmpleoMeses = $this->calcularTiempoPrimerEmpleoMeses($fechaEgresoValida, $fechaInicioEmpleoValida);

            return [
                'observacion_id' => (int) $obs->id,
                'registro_origen_id' => (int) $obs->registro_origen_id,
                'fila_origen' => (string) $obs->fila_origen,
                'fuente_datos' => 'encuesta_depurada_2026',
                'fecha_corte_origen' => $fechaCorteOrigen,
                'observada_en' => $obs->observada_en,
                'grupos_formacion' => $grupos,
                'grupo_formacion' => $grupos ? implode(' y ', $grupos) : 'Por verificar',
                'trayectorias_confirmadas' => $grupo['trayectorias_confirmadas'],
                'user_id' => (int) $u->id,
                'programa' => $progNombre,
                'cohorte' => $cohortePersona,
                'cohorte_registro' => $cohorteRegistro,
                'estado_titulacion' => $titulacion,
                'situacion_laboral' => $situacion,
                'tiene_respuesta' => true,
                'tiene_info_laboral' => $tieneInfoLaboral,
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
                'campana' => 'Encuesta 2026 (' . $fechaCorteOrigen . ')',
                'necesidades_formacion' => $obs->necesidades_formacion_original ?: ($obs->sugerencias_original ?: 'No especificadas'),
                'fecha_egreso_valida' => $fechaEgresoValida,
                'fecha_inicio_empleo_valida' => $fechaInicioEmpleoValida,
                'tiempo_primer_empleo_meses' => $tiempoPrimerEmpleoMeses,
            ];
        })->filter()->values();

        // Consolidar primero el registro canónico por persona única (última observación verificada en el lote)
        $personasCanonicas = $observacionesMapeadas
            ->groupBy('user_id')
            ->map(fn (Collection $grupoObs) => $grupoObs->last())
            ->filter(fn (array $item) => $this->cumpleFiltrosAnaliticos($item, $filtros, $clasificacion))
            ->values();

        $userIdsFiltrados = $personasCanonicas->pluck('user_id')->flip();

        // Registros vinculados que pertenecen a las personas del subconjunto filtrado
        // (o que coinciden con la cohorte de registro cuando se filtra por cohorte)
        $observacionesFiltradas = $observacionesMapeadas->filter(function (array $obsItem) use ($filtros, $clasificacion, $userIdsFiltrados) {
            if (!$clasificacion->coincide($obsItem['grupos_formacion'] ?? [], $filtros['grupo_formacion'] ?? 'TODOS')) {
                return false;
            }
            if (!empty($filtros['cohorte'])) {
                $coincideCohorte = ($obsItem['cohorte_registro'] === (string) $filtros['cohorte'])
                    || ($obsItem['cohorte'] === (string) $filtros['cohorte'] && $userIdsFiltrados->has($obsItem['user_id']));
                if (!$coincideCohorte) {
                    return false;
                }
                // Si la observación coincide por cohorte_registro (p.ej. FILA-25 en 2025), verificar resto de filtros sobre la persona canónica
                $filtrosSinCohorte = $filtros;
                unset($filtrosSinCohorte['cohorte']);
                return $this->cumpleFiltrosAnaliticos($obsItem, $filtrosSinCohorte, $clasificacion);
            }
            return $userIdsFiltrados->has($obsItem['user_id']);
        })->values();

        $registrosPorCohorte = [];
        foreach ($observacionesFiltradas as $obsF) {
            $cKey = $obsF['cohorte_registro'] ?: $obsF['cohorte'];
            $registrosPorCohorte[$cKey] = ($registrosPorCohorte[$cKey] ?? 0) + 1;
        }

        $registrosVinculados = $observacionesFiltradas->count();
        $personasUnicas = $personasCanonicas->count();

        $duplicadosDetalle = [];
        foreach ($observacionesFiltradas->groupBy('user_id') as $uid => $listaObs) {
            if ($listaObs->count() > 1) {
                $duplicadosDetalle[] = [
                    'user_id' => (int) $uid,
                    'registros' => $listaObs->count(),
                    'filas_origen' => $listaObs->pluck('fila_origen')->values()->all(),
                    'cohortes_declaradas' => $listaObs->pluck('cohorte_registro')->values()->all(),
                ];
            }
        }

        $diferencia = $registrosVinculados - $personasUnicas;
        if ($diferencia > 0) {
            $partesDup = array_map(function ($d) {
                return 'usuario #' . $d['user_id'] . ' (' . implode(' y ', $d['filas_origen']) . ')';
            }, $duplicadosDetalle);
            $detalleTexto = !empty($partesDup) ? implode('; ', $partesDup) : "{$diferencia} registro(s) adicional(es) de una misma persona";
            $explicacionDiferencia = "Conciliación histórica 2026 registra {$registrosVinculados} observaciones con vínculo confirmado que corresponden a {$personasUnicas} personas únicas (diferencia de {$diferencia} registro: {$detalleTexto}). El conteo de vínculos conserva los {$registrosVinculados} registros conciliados y cada indicador por persona única utiliza como denominador las {$personasUnicas} personas únicas (o aquellas con situación laboral declarada en tasas laborales).";
        } else {
            $explicacionDiferencia = "Los {$registrosVinculados} registros vinculados corresponden a {$personasUnicas} personas únicas en este corte.";
        }

        return [
            'fuente_datos' => 'encuesta_depurada_2026',
            'fecha_corte' => $fechaCorteEfectiva,
            'registros_vinculados' => $registrosVinculados,
            'personas_unicas' => $personasUnicas,
            'registros_por_cohorte' => $registrosPorCohorte,
            'duplicados_detalle' => $duplicadosDetalle,
            'explicacion_diferencia' => $explicacionDiferencia,
            'observaciones' => $observacionesFiltradas,
            'personas' => $personasCanonicas,
        ];
    }

    /**
     * Obtiene la fecha exacta de egreso (Y-m-d) si y solo si está acreditada con fecha válida en trayectoria_eventos.
     */
    protected function obtenerFechaEgresoValida(User $u, array $grupoMeta, ?string $filtroGrupo = null): ?string
    {
        $trayectorias = collect($grupoMeta['trayectorias_confirmadas'] ?? []);
        if (in_array($filtroGrupo, ['FID', 'PPD'], true)) {
            $fechasMod = $trayectorias->where('grupo', $filtroGrupo)->pluck('fecha_egreso')->filter()->sort();
            if ($fechasMod->isNotEmpty()) {
                return (string) $fechasMod->last();
            }
        }

        $fechasTodas = $trayectorias->pluck('fecha_egreso')->filter()->sort();
        if ($fechasTodas->isNotEmpty()) {
            return (string) $fechasTodas->last();
        }

        $evEgreso = $u->trayectoriaEventos->where('tipo', 'egreso')->whereNotNull('fecha_inicio')->sortBy('fecha_inicio')->last();
        return $evEgreso?->fecha_inicio?->format('Y-m-d');
    }

    /**
     * Evalúa los filtros analíticos sobre un registro normalizado.
     */
    protected function cumpleFiltrosAnaliticos(array $item, array $filtros, GrupoFormacionService $clasificacion): bool
    {
        if (!$clasificacion->coincide($item['grupos_formacion'] ?? [], $filtros['grupo_formacion'] ?? 'TODOS')) {
            return false;
        }

        if (!empty($filtros['programa'])) {
            $fp = trim((string) $filtros['programa']);
            if (strtoupper($fp) === 'PPD') {
                $esPpd = in_array('PPD', $item['grupos_formacion'] ?? [], true)
                    || stripos((string) $item['programa'], 'PPD') !== false
                    || stripos((string) $item['programa'], 'Profesionalización') !== false;
                if (!$esPpd) {
                    return false;
                }
            } elseif (stripos((string) $item['programa'], $fp) === false) {
                return false;
            }
        }

        if (!empty($filtros['cohorte']) && (string) $item['cohorte'] !== (string) $filtros['cohorte']) {
            return false;
        }

        if (!empty($filtros['situacion_laboral']) && $item['situacion_laboral'] !== $filtros['situacion_laboral']) {
            return false;
        }

        if (!empty($filtros['docencia_directa'])) {
            $fd = strtolower(trim((string) $filtros['docencia_directa']));
            if (in_array($fd, ['si', 'sí', 'docencia directa en aula'], true)) {
                if ($item['docencia_directa'] !== 'Docencia directa en aula') {
                    return false;
                }
            } elseif (in_array($fd, ['no', 'otra función / no docencia', 'otras áreas / no aula'], true)) {
                if ($item['docencia_directa'] === 'Docencia directa en aula') {
                    return false;
                }
            } elseif ($item['docencia_directa'] !== $filtros['docencia_directa']) {
                return false;
            }
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
    }

    /**
     * Construye el dataset analítico plano normalizado por persona única según la fuente seleccionada.
     */
    protected function obtenerDatasetAnalitico(array $filtros = []): Collection
    {
        $fuente = $this->normalizarFuente($filtros['fuente_datos'] ?? null);
        if ($fuente === 'encuesta_depurada_2026') {
            return $this->obtenerContextoEncuestaDepurada2026($filtros)['personas'];
        }

        $query = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'alumnoB.programa', 'seguimientoTitulacion.estadoTitulacion', 'ciclo']);

        if (!empty($filtros['programa']) && strtoupper(trim((string) $filtros['programa'])) !== 'PPD') {
            $query->where(function ($q) use ($filtros) {
                foreach (['programa', 'alumno.programa', 'alumnoB.programa'] as $rel) {
                    $q->orWhereHas($rel, fn ($p) => $p->where('nombre', 'like', '%' . $filtros['programa'] . '%'));
                }
            });
        }

        $clasificacion = app(GrupoFormacionService::class);
        $clasificacion->aplicar($query, $filtros['grupo_formacion'] ?? null);
        $egresados = $query->get();

        $userIds = $egresados->pluck('id')->toArray();
        $actualizaciones = Actualizacion::whereIn('user_id', $userIds)->where('estado_verificacion', 'verificado')->orderBy('id')->get()->keyBy('user_id');
        $episodiosActivos = EpisodioLaboral::whereIn('user_id', $userIds)->where('es_actual', true)->orderBy('id')->get()->keyBy('user_id');
        $primerosEpisodiosConFecha = EpisodioLaboral::whereIn('user_id', $userIds)
            ->whereNotNull('fecha_inicio')
            ->orderBy('fecha_inicio')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($eps) => $eps->first());

        return $egresados->map(function ($u) use ($actualizaciones, $episodiosActivos, $primerosEpisodiosConFecha, $filtros, $clasificacion) {
            $alumno = $clasificacion->ficha($u, $filtros['grupo_formacion'] ?? null);
            $grupo = $clasificacion->resolver($u);
            $tit = $u->seguimientoTitulacion;
            $act = $actualizaciones->get($u->id);
            $ep = $episodiosActivos->get($u->id);
            $epConFecha = $primerosEpisodiosConFecha->get($u->id);

            // 1. Programa
            $progNombre = 'Por verificar';
            $programaInstitucional = $clasificacion->programa($u, $filtros['grupo_formacion'] ?? null);
            if ($programaInstitucional) {
                $progNombre = $programaInstitucional->nombre;
            }

            // 2. Cohorte
            $cohorte = 'Por verificar';
            if (count($grupo['grupos']) > 1 && in_array($filtros['grupo_formacion'] ?? null, ['FID', 'PPD'], true)) {
                $fechas = collect($grupo['trayectorias_confirmadas'])->where('grupo', $filtros['grupo_formacion'])->pluck('fecha_egreso')->filter()->sort();
                $cohorte = $fechas->isNotEmpty() ? substr($fechas->last(), 0, 4) : 'Por verificar';
            } elseif ($act && $act->cohorte_egreso) {
                $cohorte = $act->cohorte_egreso;
            } elseif ($u->trayectoriaEventos->where('tipo', 'egreso')->isNotEmpty()) {
                $evEg = $u->trayectoriaEventos->where('tipo', 'egreso')->first();
                if ($evEg && $evEg->fecha_inicio) {
                    $cohorte = $evEg->fecha_inicio->format('Y');
                }
            } elseif ($u->ciclo?->nombre && preg_match_all('/(20\d{2})/', $u->ciclo->nombre, $mCic)) {
                $cohorte = (string) end($mCic[1]);
            } elseif ($tit && $tit->periodo_estudios) {
                $cohorte = preg_match_all('/(20\d{2})/', (string) $tit->periodo_estudios, $mPer) ? (string) end($mPer[1]) : $tit->periodo_estudios;
            }

            // 3. Titulación
            $titulacion = $tit?->estadoTitulacion?->nombre ?: (in_array($act?->titulo_lic, ['Titulado', 'Licenciado/a', 'Licenciado'], true) ? 'Licenciado' : (in_array($act?->titulo_bach, ['Obtenido', 'Registrado'], true) ? 'Bachiller' : 'Por verificar'));

            // 4. Situación Laboral
            $trabajaDeclarado = $act?->trabaja;
            $trabFicha = strtolower(trim((string) ($alumno?->trabajas ?? '')));
            if ($ep) {
                $situacion = 'Trabaja';
            } elseif ($trabajaDeclarado === 'Trabaja' || str_starts_with(strtolower($trabajaDeclarado ?: ''), 'trabaja')) {
                $situacion = 'Trabaja';
            } elseif ($trabajaDeclarado === 'No trabaja') {
                $situacion = 'No trabaja';
            } elseif (in_array($trabFicha, ['sí', 'si', '1', 'true'], true)) {
                $situacion = 'Trabaja';
            } elseif (in_array($trabFicha, ['no', 'todavía no', 'todavia no', '0'], true)) {
                $situacion = 'No trabaja';
            } else {
                $situacion = 'Sin información confirmada';
            }

            $tieneRespuesta = $act !== null;
            $tieneInfoLaboral = in_array($situacion, ['Trabaja', 'No trabaja'], true);

            // 5. Sector y Contrato
            $sectorFicha = trim((string) ($alumno?->sector_laboral ?? ''));
            if (in_array(strtolower($sectorFicha), ['', 'ninguno', 'no trabajo', 'elegir', '--'], true)) {
                $sectorFicha = null;
            }
            $sector = $ep?->sector ?: ($act?->sector ?: ($sectorFicha ?: 'No declarado'));
            $contrato = $ep?->contrato ?: ($act?->contrato ?: 'No declarado');

            // 6. Docencia y Educación
            $docenciaDirecta = ($ep && $ep->es_docencia_directa) ? 'Docencia directa en aula' : 'Otra función / No docencia';
            $trabajoEducativo = ($ep && $ep->es_empleo_educativo) ? 'Empleo en el sector educativo' : 'Otro sector no educativo';

            // 7. EIB
            $esEib = ($ep && $ep->es_empleo_educativo && ($act?->eib === 'Sí' || !empty($act?->quechua)))
                || ($act && $act->eib === 'Sí');
            $eibLabel = $esEib ? 'Docencia Intercultural Bilingüe (EIB)' : 'Educación Regular / No EIB';

            // 8. Territorios
            $depRes = strtoupper(trim($act?->res_departamento ?: ($alumno?->departamento ?: 'NO DECLARADO')));
            $provRes = strtoupper(trim($act?->res_provincia ?: ($alumno?->provincia ?: 'NO DECLARADO')));
            $depTrab = strtoupper(trim($ep?->trab_departamento ?: ($act?->trab_departamento ?: 'NO DECLARADO')));
            $provTrab = strtoupper(trim($ep?->trab_provincia ?: ($act?->trab_provincia ?: 'NO DECLARADO')));

            // 9. Tiempo desde egreso
            $tiempoEgreso = 'Por verificar';
            if (preg_match('/(20\d{2})/', (string) $cohorte, $mCohorte)) {
                $anioEgreso = (int) $mCohorte[1];
                $anios = (int) date('Y') - $anioEgreso;
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

            // 11. Fechas válidas para tiempo al primer empleo (sin valores de respaldo)
            $fechaEgresoValida = $this->obtenerFechaEgresoValida($u, $grupo, $filtros['grupo_formacion'] ?? null);
            $fechaInicioEmpleoValida = $epConFecha?->fecha_inicio?->format('Y-m-d')
                ?: ($act?->inicio_empleo ? Carbon::parse($act->inicio_empleo)->format('Y-m-d') : null);
            $tiempoPrimerEmpleoMeses = $this->calcularTiempoPrimerEmpleoMeses($fechaEgresoValida, $fechaInicioEmpleoValida);

            return [
                'fuente_datos' => 'seguimiento_vigente',
                'fecha_corte_origen' => $act?->fecha_ultima_confirmacion?->format('Y-m-d') ?: now()->toDateString(),
                'grupos_formacion' => $grupo['grupos'],
                'grupo_formacion' => $grupo['etiqueta'],
                'trayectorias_confirmadas' => $grupo['trayectorias_confirmadas'],
                'user_id' => $u->id,
                'programa' => $progNombre,
                'cohorte' => $cohorte,
                'estado_titulacion' => $titulacion,
                'situacion_laboral' => $situacion,
                'tiene_respuesta' => $tieneRespuesta,
                'tiene_info_laboral' => $tieneInfoLaboral,
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
                'fecha_egreso_valida' => $fechaEgresoValida,
                'fecha_inicio_empleo_valida' => $fechaInicioEmpleoValida,
                'tiempo_primer_empleo_meses' => $tiempoPrimerEmpleoMeses,
            ];
        })->filter(fn ($item) => $this->cumpleFiltrosAnaliticos($item, $filtros, $clasificacion))->values();
    }
}
