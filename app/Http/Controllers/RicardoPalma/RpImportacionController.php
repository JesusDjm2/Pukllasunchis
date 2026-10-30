<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Exports\RicardoPalma\PlantillaActaRpExport;
use App\Http\Controllers\Controller;
use App\Imports\RicardoPalma\ActaConsolidadaRpImport;
use App\Models\RicardoPalma\RpImportacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RpImportacionController extends Controller
{
    public function index(): View
    {
        $importaciones = RpImportacion::with('usuario')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.ricardo-palma.importar.index', compact('importaciones'));
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
            Excel::import($import, $file);

            // La vista arma la tarjeta de resultado (cifras, actas cargadas y filas con error)
            return redirect()->route('rp.importar.index')
                ->with('rp_resultado', $import->getResultado() + ['archivo' => $originalName]);
        } catch (\Throwable $e) {
            return redirect()->route('rp.importar.index')->with('error', 'Ocurrió un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function descargarPlantilla(): BinaryFileResponse
    {
        return Excel::download(new PlantillaActaRpExport(), 'plantilla_acta_consolidada_ricardo_palma.xlsx');
    }
}
