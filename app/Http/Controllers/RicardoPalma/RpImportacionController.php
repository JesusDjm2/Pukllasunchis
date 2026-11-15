<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Exports\RicardoPalma\PlantillaActaFisicaRp;
use App\Exports\RicardoPalma\PlantillaActaRpExport;
use App\Http\Controllers\Controller;
use App\Imports\RicardoPalma\ActaConsolidadaRpImport;
use App\Imports\RicardoPalma\ActaFisicaRpImport;
use App\Models\RicardoPalma\RpImportacion;
use App\Services\RicardoPalma\BorradorLecturaRp;
use App\Services\RicardoPalma\LectorActaGemini;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class RpImportacionController extends Controller
{
    public function index(): View
    {
        $importaciones = RpImportacion::with('usuario')
            ->orderByDesc('id')
            ->paginate(15);

        $borradores = BorradorLecturaRp::todos();
        $iaConfigurada = LectorActaGemini::configurado();

        return view('admin.ricardo-palma.importar.index', compact('importaciones', 'borradores', 'iaConfigurada'));
    }

    public function importarActa(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.mimes' => 'El archivo debe ser de formato Excel (.xlsx, .xls) o CSV.',
            'archivo.max' => 'El archivo no debe exceder los 20MB.',
        ]);

        $file = $request->file('archivo');
        $originalName = $file->getClientOriginalName();

        try {
            $import = new ActaConsolidadaRpImport($originalName, auth()->id());

            // Hojas con la forma del acta física (plantilla actual) o tabla por filas (plantilla anterior / IA)
            $hojasActa = $this->hojasConFormatoActa($file);
            if ($hojasActa) {
                $errores = [];
                $filas = ActaFisicaRpImport::aFilas($hojasActa, $errores);
                foreach ($errores as $mensaje) {
                    $import->error(null, $mensaje);
                }
                $import->collection(collect($filas));
            } else {
                Excel::import($import, $file);
            }

            // La vista arma la tarjeta de resultado (cifras, actas cargadas y filas con error)
            return redirect()->route('rp.importar.index')
                ->with('rp_resultado', $import->getResultado() + ['archivo' => $originalName]);
        } catch (\Throwable $e) {
            return redirect()->route('rp.importar.index')->with('error', 'Ocurrió un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Plantilla con la forma del acta física (por defecto) o, con ?formato=filas, la tabla
     * de una fila por alumno y curso.
     */
    public function descargarPlantilla(Request $request): Response
    {
        if ($request->query('formato') === 'filas') {
            return Excel::download(new PlantillaActaRpExport(), 'plantilla_acta_por_filas_ricardo_palma.xlsx');
        }

        $libro = PlantillaActaFisicaRp::generar();

        return response()->streamDownload(function () use ($libro) {
            (new Xlsx($libro))->save('php://output');
        }, 'plantilla_acta_consolidada_ricardo_palma.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Hojas del archivo con la forma del acta física (nombre => filas crudas); vacío si no hay. */
    private function hojasConFormatoActa($file): array
    {
        if (! in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'xls'], true)) {
            return [];
        }

        $lector = IOFactory::createReaderForFile($file->getRealPath());
        $lector->setReadDataOnly(true);
        $libro = $lector->load($file->getRealPath());

        $hojas = [];
        foreach ($libro->getWorksheetIterator() as $hoja) {
            // Sin calcular fórmulas: las columnas calculadas no se leen
            $filas = $hoja->toArray(null, false, false, false);
            if (ActaFisicaRpImport::esFormatoActa($filas)) {
                $hojas[$hoja->getTitle()] = $filas;
            }
        }

        return $hojas;
    }
}
