<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Programa;
use App\Services\PanelInstitucional;
use Illuminate\Http\Request;

/** Panel institucional de seguimiento de estudiantes (admin y super-admin). */
class PanelSeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['programa_id', 'ciclo_id']);
        $panel = PanelInstitucional::calcular($filtros);

        return view('admin.expediente.panel', [
            'panel' => $panel,
            'filtros' => $filtros,
            'programas' => Programa::orderBy('modalidad')->orderBy('id')->get(),
            'ciclos' => Ciclo::with('programa')->whereHas('alumnos')->orderBy('programa_id')->orderBy('id')->get(),
        ]);
    }

    /** Una lista del panel en CSV (Excel lo abre directo; BOM para que respete las tildes). */
    public function exportar(Request $request, string $lista)
    {
        abort_unless(isset(PanelInstitucional::LISTAS[$lista]), 404);
        $filas = PanelInstitucional::calcular($request->only(['programa_id', 'ciclo_id']))['listas'][$lista];
        $nombre = 'seguimiento_' . $lista . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($filas, $lista) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Apellidos', 'Nombres', 'DNI', 'Correo', 'Programa', 'Ciclo', 'Perfil', PanelInstitucional::LISTAS[$lista][0]], ';');
            foreach ($filas as $f) {
                fputcsv($out, [
                    $f->u->apellidos, $f->u->name, $f->u->dni, $f->u->email,
                    $f->u->programa->nombre ?? '', $f->u->ciclo->nombre ?? '', $f->u->perfil, $f->detalle,
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
