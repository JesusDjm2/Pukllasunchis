<?php

namespace App\Http\Controllers;

use App\Services\SeguimientoEgresado\ObservatorioTerritorialService;
use App\Services\SeguimientoEgresado\SeguimientoAnaliticaService;
use App\Services\SeguimientoEgresado\SeguimientoAutorizacionService;
use Illuminate\Http\Request;
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
     * Vista principal del Laboratorio de Inteligencia y Observatorio Territorial.
     */
    public function index(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        $variables = $this->analiticaService->getVariablesPermitidas();
        $universos = $this->analiticaService->calcularUniversos();

        // Matriz por defecto: Programa x Situación Laboral
        $varFilaDefecto = 'programa';
        $varColumnaDefecto = 'situacion_laboral';
        $matrizInicial = $this->analiticaService->generarMatrizCruzada($varFilaDefecto, $varColumnaDefecto);

        // Análisis longitudinal y observatorio territorial inicial
        $longitudinalInicial = $this->analiticaService->generarAnalisisLongitudinal();
        $territorialInicial = $this->territorialService->obtenerReporteTerritorial();
        $demandasInicial = $this->analiticaService->obtenerDemandasFormativas();

        return view('seguimiento_egresados.laboratorio', compact(
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
        $filtros = $request->input('filtros', []);

        try {
            $matriz = $this->analiticaService->generarMatrizCruzada($varFila, $varColumna, $filtros);
            $universos = $this->analiticaService->calcularUniversos($filtros);
            $longitudinal = $this->analiticaService->generarAnalisisLongitudinal($filtros);
            $territorial = $this->territorialService->obtenerReporteTerritorial($filtros);
            $demandas = $this->analiticaService->obtenerDemandasFormativas($filtros);

            return response()->json([
                'success' => true,
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
     * Exportación estadística segura (CSV) con supresión y notas metodológicas.
     */
    public function exportar(Request $request)
    {
        $this->autorizarAccesoLaboratorio();

        if (!$this->authService->puedeExportarAnalitica(auth()->user())) {
            abort(403, 'No tienes permiso para exportar datos del Laboratorio Analítico.');
        }

        $varFila = $request->input('fila', 'programa');
        $varColumna = $request->input('columna', 'situacion_laboral');
        $filtros = json_decode($request->input('filtros', '[]'), true) ?: [];

        $matriz = $this->analiticaService->generarMatrizCruzada($varFila, $varColumna, $filtros);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="puklla_analitica_' . $varFila . '_x_' . $varColumna . '_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return new StreamedResponse(function () use ($matriz, $varFila, $varColumna) {
            $handle = fopen('php://output', 'w');

            // BOM para compatibilidad con Excel en UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado institucional y advertencias
            fputcsv($handle, ['ESCUELA DE EDUCACIÓN SUPERIOR PEDAGÓGICA PUKLLASUNCHIS']);
            fputcsv($handle, ['SISTEMA DE SEGUIMIENTO DE EGRESADOS — LABORATORIO DE INTELIGENCIA']);
            fputcsv($handle, ['Cruce Analítico:', $matriz['dimension_fila']['nombre'] . ' x ' . $matriz['dimension_columna']['nombre']]);
            fputcsv($handle, ['Fecha de corte:', $matriz['fecha_corte']]);
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
}
