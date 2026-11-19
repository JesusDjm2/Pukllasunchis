<?php

namespace App\Http\Controllers;

use App\Services\SeguimientoEgresado\GrupoFormacionService;
use App\Services\SeguimientoEgresado\ObservatorioTerritorialService;
use App\Services\SeguimientoEgresado\SeguimientoAnaliticaService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeguimientoLaboratorioController extends Controller
{
    protected SeguimientoAnaliticaService $analiticaService;
    protected ObservatorioTerritorialService $territorialService;
    protected SeguimientoAutorizacionService $authService;

    public function __construct(
        SeguimientoAnaliticaService $analiticaService,
        ObservatorioTerritorialService $territorialService,
        SeguimientoAutorizacionService $authService
    ) {
        $this->analiticaService = $analiticaService;
        $this->territorialService = $territorialService;
        $this->authService = $authService;
    }

    /**
     * Verifica autorización institucional para acceder al Laboratorio de Inteligencia.
     */
    protected function autorizarAccesoLaboratorio(): void
    {
        if (!auth()->check()) {
            abort(401, 'No autenticado.');
        }

        $user = auth()->user();

        if (!$this->authService->puedeAccederLaboratorio($user)) {
            abort(403, 'No tienes autorización para acceder al Laboratorio de Inteligencia Institucional.');
        }
    }

    /**
     * Construye y valida el arreglo de filtros aplicable desde un Request.
     */
    protected function extraerFiltros(Request $request, array $base = []): array
    {
        $raw = array_merge(
            $request->only(['fuente_datos', 'grupo_formacion', 'programa', 'cohorte', 'docencia_directa']),
            $base
        );

        $fuenteDatos = $this->analiticaService->normalizarFuente($raw['fuente_datos'] ?? null);
        $grupoFormacion = app(GrupoFormacionService::class)->normalizar($raw['grupo_formacion'] ?? null);

        $filtros = [
            'fuente_datos' => $fuenteDatos,
            'grupo_formacion' => $grupoFormacion,
        ];

        foreach (['programa', 'cohorte', 'docencia_directa'] as $campo) {
            if (isset($raw[$campo]) && trim((string) $raw[$campo]) !== '') {
                $filtros[$campo] = trim((string) $raw[$campo]);
            }
        }

        return $filtros;
    }

    /**
     * Vista principal del Laboratorio de Inteligencia y Observatorio Territorial.
     */
    public function index(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        $request->validate([
            'fuente_datos' => 'nullable|in:seguimiento_vigente,encuesta_depurada_2026',
            'grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR',
            'programa' => 'nullable|string|max:100',
            'cohorte' => 'nullable|string|max:40',
            'docencia_directa' => 'nullable|string|max:80',
        ]);

        $filtros = $this->extraerFiltros($request);
        $fuenteDatos = $filtros['fuente_datos'];
        $fuentesDatos = $this->analiticaService->getFuentesDatos();
        $grupoFormacion = $filtros['grupo_formacion'];
        $variables = $this->analiticaService->getVariablesPermitidas();
        $universos = $this->analiticaService->calcularUniversos($filtros);

        // Matriz por defecto: Programa x Situación Laboral
        $varFilaDefecto = 'programa';
        $varColumnaDefecto = 'situacion_laboral';
        $matrizInicial = $this->analiticaService->generarMatrizCruzada($varFilaDefecto, $varColumnaDefecto, $filtros);

        // Análisis longitudinal y observatorio territorial inicial
        $longitudinalInicial = $this->analiticaService->generarAnalisisLongitudinal($filtros);
        $territorialInicial = $this->territorialService->obtenerReporteTerritorial($filtros);
        $demandasInicial = $this->analiticaService->obtenerDemandasFormativas($filtros);

        return view('seguimiento_egresados.laboratorio', compact(
            'fuenteDatos',
            'fuentesDatos',
            'grupoFormacion',
            'filtros',
            'variables',
            'universos',
            'varFilaDefecto',
            'varColumnaDefecto',
            'matrizInicial',
            'longitudinalInicial',
            'territorialInicial',
            'demandasInicial'
        ));
    }

    /**
     * Endpoint API para recálculo dinámico de analítica y filtros cruzados (POST).
     */
    public function analiticaAjax(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        $varFila = $request->input('fila', 'programa');
        $varColumna = $request->input('columna', 'situacion_laboral');
        $request->validate([
            'filtros' => 'nullable|array',
            'filtros.fuente_datos' => 'nullable|in:seguimiento_vigente,encuesta_depurada_2026',
            'filtros.grupo_formacion' => 'nullable|in:TODOS,FID,PPD,COMPARAR,POR_VERIFICAR',
        ]);
        $filtros = $this->extraerFiltros($request, $request->input('filtros', []) ?: []);

        try {
            $matriz = $this->analiticaService->generarMatrizCruzada($varFila, $varColumna, $filtros);
            $universos = $this->analiticaService->calcularUniversos($filtros);
            $longitudinal = $this->analiticaService->generarAnalisisLongitudinal($filtros);
            $territorial = $this->territorialService->obtenerReporteTerritorial($filtros);
            $demandas = $this->analiticaService->obtenerDemandasFormativas($filtros);

            return response()->json([
                'success' => true,
                'filtros' => $filtros,
                'universos' => $universos,
                'matriz' => $matriz,
                'longitudinal' => $longitudinal,
                'territorial' => $territorial,
                'demandas' => $demandas,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al calcular la matriz analítica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportación estadística segura: CSV para el cruce dinámico o XLSX para el informe por cohorte.
     */
    public function exportar(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        if (!$this->authService->puedeExportarAnalitica(auth()->user())) {
            abort(403, 'No tienes permiso para exportar datos del Laboratorio Analítico.');
        }

        if ($request->input('formato') === 'xlsx' || $request->input('tipo') === 'cohortes') {
            return $this->exportarInformeCohortes($request);
        }

        $varFila = $request->input('fila', 'programa');
        $varColumna = $request->input('columna', 'situacion_laboral');
        $filtrosJson = json_decode($request->input('filtros', '[]'), true) ?: [];
        if (!is_array($filtrosJson)) {
            abort(422, 'Filtros no válidos.');
        }
        $filtros = $this->extraerFiltros($request, $filtrosJson);

        $matriz = $this->analiticaService->generarMatrizCruzada($varFila, $varColumna, $filtros);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="puklla_analitica_' . $filtros['fuente_datos'] . '_' . $varFila . '_x_' . $varColumna . '_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return new StreamedResponse(function () use ($matriz, $filtros) {
            $handle = fopen('php://output', 'w');

            // BOM para compatibilidad con Excel en UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado institucional y advertencias
            fputcsv($handle, ['ESCUELA DE EDUCACIÓN SUPERIOR PEDAGÓGICA PUKLLASUNCHIS']);
            fputcsv($handle, ['SISTEMA DE SEGUIMIENTO DE EGRESADOS — LABORATORIO DE INTELIGENCIA']);
            fputcsv($handle, ['Fuente de datos:', $matriz['fuente_etiqueta'] ?? $filtros['fuente_datos']]);
            fputcsv($handle, ['Cruce Analítico:', $matriz['dimension_fila']['nombre'] . ' x ' . $matriz['dimension_columna']['nombre']]);
            fputcsv($handle, ['Grupo de formación:', $filtros['grupo_formacion'] ?? 'TODOS']);
            fputcsv($handle, ['Fecha de corte:', $matriz['fecha_corte']]);
            fputcsv($handle, ['Registros vinculados:', $matriz['registros_vinculados'] ?? $matriz['denominador_personas']]);
            fputcsv($handle, ['Denominador:', $matriz['denominador_personas'] . ' personas únicas']);
            fputcsv($handle, ['Nota metodológica:', $matriz['nota_metodologica']]);
            fputcsv($handle, []);

            // Fila de cabecera de la tabla
            $cabecera = array_merge([$matriz['dimension_fila']['nombre']], $matriz['dimension_columna']['categorias'], ['TOTAL FILA']);
            fputcsv($handle, $cabecera);

            // Filas de datos
            foreach ($matriz['matriz_segura'] as $filaNom => $cols) {
                $linea = [$filaNom];
                foreach ($matriz['dimension_columna']['categorias'] as $colNom) {
                    $celda = $cols[$colNom] ?? ['valor' => 0];
                    $linea[] = $celda['valor'];
                }
                $linea[] = $matriz['totales_filas'][$filaNom]['valor'] ?? 0;
                fputcsv($handle, $linea);
            }

            // Fila de totales de columnas
            $lineaTotales = ['TOTAL COLUMNA'];
            foreach ($matriz['dimension_columna']['categorias'] as $colNom) {
                $lineaTotales[] = $matriz['totales_columnas'][$colNom]['valor'] ?? 0;
            }
            $totalGenValor = is_array($matriz['total_general']) ? ($matriz['total_general']['valor'] ?? 0) : $matriz['total_general'];
            $lineaTotales[] = $totalGenValor;
            fputcsv($handle, $lineaTotales);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Exportar informe por cohorte (.xlsx) reproduciendo exactamente la misma fuente, filtros, filas,
     * valores, denominadores y fecha de corte que aparecen en la tabla «Inteligencia Longitudinal y Calidad de Inserción».
     */
    public function exportarInformeCohortes(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        if (!$this->authService->puedeExportarAnalitica(auth()->user())) {
            abort(403, 'No tienes permiso para exportar datos del Laboratorio Analítico.');
        }

        $filtrosJson = json_decode($request->input('filtros', '[]'), true) ?: [];
        if (!is_array($filtrosJson)) {
            abort(422, 'Filtros no válidos.');
        }
        $filtros = $this->extraerFiltros($request, $filtrosJson);

        // Reutiliza exactamente el mismo cálculo que la pantalla
        $longitudinal = $this->analiticaService->generarAnalisisLongitudinal($filtros);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Informe por Cohorte');

        // Encabezado y metadatos de trazabilidad (Filas 1 a 12)
        $sheet->setCellValue('A1', 'ESCUELA DE EDUCACIÓN SUPERIOR PEDAGÓGICA PUKLLASUNCHIS');
        $sheet->setCellValue('A2', 'LABORATORIO DE INTELIGENCIA — INTELIGENCIA LONGITUDINAL Y CALIDAD DE INSERCIÓN POR COHORTE');
        $sheet->setCellValue('A3', 'Fuente de datos:');
        $sheet->setCellValue('B3', $longitudinal['fuente_etiqueta']);
        $sheet->setCellValue('C3', $longitudinal['fuente_datos']);
        $sheet->setCellValue('A4', 'Fecha / corte de origen:');
        $sheet->setCellValue('B4', $longitudinal['fecha_corte']);
        $sheet->setCellValue('A5', 'Grupo de formación:');
        $sheet->setCellValue('B5', $longitudinal['filtros_aplicados']['grupo_formacion']);
        $sheet->setCellValue('A6', 'Filtro Programa:');
        $sheet->setCellValue('B6', $longitudinal['filtros_aplicados']['programa']);
        $sheet->setCellValue('A7', 'Filtro Cohorte:');
        $sheet->setCellValue('B7', $longitudinal['filtros_aplicados']['cohorte']);
        $sheet->setCellValue('A8', 'Filtro Docencia Directa:');
        $sheet->setCellValue('B8', $longitudinal['filtros_aplicados']['docencia_directa']);
        $sheet->setCellValue('A9', 'Registros vinculados:');
        $sheet->setCellValue('B9', (int) $longitudinal['registros_vinculados']);
        $sheet->setCellValue('A10', 'Personas únicas (users.id):');
        $sheet->setCellValue('B10', (int) $longitudinal['personas_unicas']);
        $sheet->setCellValue('A11', 'Explicación de conteos:');
        $sheet->setCellValue('B11', (string) $longitudinal['explicacion_diferencia']);
        $sheet->setCellValue('A12', 'Denominadores por indicador:');
        $sheet->setCellValue(
            'B12',
            'Respondientes y Tasa de Titulación: personas únicas de la cohorte (Padrón Egresados) | En Docencia Directa y Retención en Educación: personas únicas con situación laboral declarada | Tiempo al 1.ᵉʳ Empleo: casos con fechas válidas de egreso e inicio del primer empleo'
        );

        $sheet->getStyle('A1:A12')->getFont()->setBold(true);

        // Fila 14: Cabecera de la tabla (A..G idénticas a la pantalla; H..O desglose numérico verificable de numeradores y denominadores)
        $headers = [
            'A14' => 'Cohorte',
            'B14' => 'Padrón Egresados',
            'C14' => 'Respondientes',
            'D14' => 'En Docencia Directa',
            'E14' => 'Tiempo al 1.ᵉʳ Empleo',
            'F14' => 'Retención en Educación',
            'G14' => 'Tasa de Titulación',
            'H14' => 'Personas únicas (n)',
            'I14' => 'Registros vinculados (n)',
            'J14' => 'Respondientes (n)',
            'K14' => 'Denominador laboral (n)',
            'L14' => 'Docencia directa (n)',
            'M14' => 'Casos tiempo 1.er empleo (n)',
            'N14' => 'Empleo educativo (n)',
            'O14' => 'Titulados (n)',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle('A14:O14')->getFont()->setBold(true);
        $sheet->getStyle('A14:O14')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEDF2F7');

        $rowNum = 15;
        $filasParaEscribir = $longitudinal['cohortes'] ?? [];
        foreach ($filasParaEscribir as $fila) {
            $this->escribirFilaCohorteExcel($sheet, $rowNum, $fila);
            $rowNum++;
        }

        // Fila de TOTAL general (reproduce el pie de tabla en pantalla)
        if (isset($longitudinal['totales'])) {
            $this->escribirFilaCohorteExcel($sheet, $rowNum, $longitudinal['totales']);
            $sheet->getStyle("A{$rowNum}:O{$rowNum}")->getFont()->setBold(true);
            $sheet->getStyle("A{$rowNum}:O{$rowNum}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF8FAFC');
        }

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A14:O' . $rowNum)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $fileName = 'puklla_informe_cohortes_' . $filtros['fuente_datos'] . '_' . strtolower($filtros['grupo_formacion']) . '_' . date('Ymd_His') . '.xlsx';

        $responseHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $responseHeaders);
    }

    /**
     * Escribe una fila de cohorte o total en la hoja Excel con los mismos textos y denominadores de la pantalla.
     */
    protected function escribirFilaCohorteExcel($sheet, int $rowNum, array $fila): void
    {
        $sheet->setCellValue("A{$rowNum}", (string) $fila['cohorte']);
        $sheet->setCellValue("B{$rowNum}", (string) $fila['padron_texto']);
        $sheet->setCellValue("C{$rowNum}", (string) $fila['respondientes_texto']);
        $sheet->setCellValue("D{$rowNum}", (string) $fila['docencia_directa_texto']);
        $sheet->setCellValue("E{$rowNum}", (string) $fila['tiempo_primer_empleo_texto']);
        $sheet->setCellValue("F{$rowNum}", (string) $fila['retencion_educativa_texto']);
        $sheet->setCellValue("G{$rowNum}", (string) $fila['tasa_titulacion_texto']);

        // Desglose numérico exacto de numeradores y denominadores
        $sheet->setCellValue("H{$rowNum}", (int) $fila['total_egresados']);
        $sheet->setCellValue("I{$rowNum}", (int) $fila['registros_vinculados']);
        $sheet->setCellValue("J{$rowNum}", (int) $fila['respondientes']);
        $sheet->setCellValue("K{$rowNum}", (int) $fila['con_info_laboral']);
        $sheet->setCellValue("L{$rowNum}", (int) $fila['docencia_directa']);
        $sheet->setCellValue("M{$rowNum}", (int) $fila['casos_tiempo_primer_empleo']);
        $sheet->setCellValue("N{$rowNum}", (int) $fila['empleo_educativo']);
        $sheet->setCellValue("O{$rowNum}", (int) $fila['titulados']);
    }
}
