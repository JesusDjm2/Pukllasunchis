<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Exports\RicardoPalma\ActaConsolidadaRpExport;
use App\Http\Controllers\Controller;
use App\Models\RicardoPalma\RpActa;
use App\Models\RicardoPalma\RpActaEstudiante;
use App\Models\RicardoPalma\RpPeriodo;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RpActaController extends Controller
{
    public function index(Request $request): View
    {
        $periodoId = $request->get('periodo_id');
        $programaId = $request->get('programa_id');
        $semestre = $request->get('semestre');

        $query = RpActa::with(['periodo', 'programa'])->withCount('calificaciones');

        if ($periodoId) {
            $query->where('periodo_id', $periodoId);
        }
        if ($programaId) {
            $query->where('programa_id', $programaId);
        }
        if ($semestre) {
            $query->where('numero_semestre', $semestre);
        }

        $actas = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $periodos = RpPeriodo::orderByDesc('anio')->orderBy('semestre')->get();
        $programas = RpPrograma::where('activo', true)->orderBy('nombre')->get();

        return view('admin.ricardo-palma.actas.index', compact('actas', 'periodos', 'programas', 'periodoId', 'programaId', 'semestre'));
    }

    public function show(RpActa $acta): View
    {
        $datos = $acta->matriz();

        return view('admin.ricardo-palma.actas.show', $datos);
    }

    public function imprimir(RpActa $acta): View
    {
        $datos = $acta->matriz();

        return view('admin.ricardo-palma.actas.imprimir', $datos);
    }

    public function exportarExcel(RpActa $acta)
    {
        $numero = $acta->numero_acta ?: 'Acta_' . $acta->id;
        $periodo = $acta->periodo?->codigo ?: 'Historico';
        $nombreArchivo = 'Acta_Consolidada_' . $numero . '_' . $periodo . '.xlsx';
        $nombreArchivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nombreArchivo);

        return Excel::download(new ActaConsolidadaRpExport($acta), $nombreArchivo);
    }

    /** Editar el acta: sus datos, las notas y la condición/observación de cada alumno. */
    public function editar(RpActa $acta): View
    {
        return view('admin.ricardo-palma.actas.editar', $acta->matriz());
    }

    public function actualizar(Request $request, RpActa $acta): RedirectResponse
    {
        $datos = $request->validate([
            'numero_acta' => 'nullable|string|max:50',
            'seccion' => [
                'required', 'string', 'max:30',
                // Periodo + carrera + semestre + sección + turno identifican el acta
                Rule::unique('rp_actas')->ignore($acta->id)->where(fn ($q) => $q
                    ->where('periodo_id', $acta->periodo_id)->where('programa_id', $acta->programa_id)
                    ->where('numero_semestre', $acta->numero_semestre)->where('turno', $request->input('turno'))),
            ],
            'turno' => 'required|string|max:30',
            'fecha_emision' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',
            'notas' => 'array',
            'notas.*' => 'nullable|numeric|min:0|max:20',
            'obs_alumno' => 'array',
            'obs_alumno.*' => 'nullable|string|max:255',
        ], [
            'seccion.unique' => 'Ya existe otra acta de este periodo, carrera y semestre con esa sección y turno.',
            'notas.*.numeric' => 'Las notas deben ser números de 0 a 20.',
            'notas.*.min' => 'Las notas deben estar entre 0 y 20.',
            'notas.*.max' => 'Las notas deben estar entre 0 y 20.',
        ]);

        DB::transaction(function () use ($acta, $datos, $request) {
            $acta->update([
                'numero_acta' => ($datos['numero_acta'] ?? null) ?: null,
                'seccion' => mb_strtoupper(trim($datos['seccion'])),
                'turno' => mb_strtoupper(trim($datos['turno'])),
                'fecha_emision' => ($datos['fecha_emision'] ?? null) ?: null,
                'observaciones' => ($datos['observaciones'] ?? null) ?: null,
            ]);

            // Notas: llegan como notas[id de la calificación]; el puntaje se recalcula con los créditos del curso
            $notas = $request->input('notas', []);
            foreach ($acta->calificaciones()->with('curso')->get() as $calif) {
                if (! array_key_exists($calif->id, $notas)) {
                    continue;
                }
                $valor = $notas[$calif->id];
                $nota = ($valor === null || $valor === '') ? null : round((float) $valor, 2);
                if ($nota === ($calif->calificacion === null ? null : (float) $calif->calificacion)) {
                    continue;
                }
                $obs = $calif->observacion;
                // APROBADO/DESAPROBADO se recalculan; RETIRADO, LICENCIA u otra observación se respetan
                if (in_array($obs, [null, 'APROBADO', 'DESAPROBADO'], true)) {
                    $obs = $nota === null ? null : ($nota >= 11 ? 'APROBADO' : 'DESAPROBADO');
                }
                $calif->update([
                    'calificacion' => $nota,
                    'puntaje' => $nota === null ? null : round($nota * ($calif->curso?->creditos ?? 0), 2),
                    'observacion' => $obs,
                ]);
            }

            // Observación por alumno (vacía = se borra)
            foreach ($request->input('obs_alumno', []) as $estudianteId => $valor) {
                $obs = trim((string) $valor);
                $clave = ['acta_id' => $acta->id, 'estudiante_id' => (int) $estudianteId];
                if ($obs === '') {
                    RpActaEstudiante::where($clave)->delete();
                } else {
                    RpActaEstudiante::updateOrCreate($clave, ['observacion' => $obs]);
                }
            }
        });

        return redirect()->route('rp.actas.show', $acta)->with('success', 'Acta actualizada.');
    }

    /** Elimina el acta con sus notas; los estudiantes se conservan (pueden figurar en otras actas). */
    public function eliminar(RpActa $acta): RedirectResponse
    {
        $nombre = $acta->numero_acta ? 'N° '.$acta->numero_acta : 'sin número';
        $acta->delete();

        return redirect()->route('rp.actas.index')->with('success', "Se eliminó el acta {$nombre} con sus notas.");
    }
}
