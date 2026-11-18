<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Titulacion\Concerns\ImportaFormularioGoogle;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Services\Titulacion\ArchivosRd;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\ImportarSolicitudesAmpliacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SolicitudAmpliacionController extends Controller
{
    use ImportaFormularioGoogle;

    protected function importador(): string
    {
        return ImportarSolicitudesAmpliacion::class;
    }

    protected function importacion(): array
    {
        return [
            'kicker' => 'Secretaría · Ampliaciones de PTI',
            'titulo' => 'Ampliaciones de PTI',
            'sub' => 'Las solicitudes de ampliación o extensión de plazo de PTI hechas por Google Forms o Excel se registran con su fecha original. Las que ya cuentan con R.D. registrada quedan como <strong>atendidas</strong>; las pendientes quedan <strong>por atender</strong>.',
            'bandeja' => 'titulacion.solicitudes-ampliacion.index',
            'ruta' => 'titulacion.solicitudes-ampliacion.importar',
            'estados' => ['atendida' => 'Atendidas (tienen R.D.)', 'recibida' => 'Quedan por atender'],
        ];
    }

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:'.implode(',', array_keys(Solicitud::ESTADOS)),
            'origen' => 'nullable|in:'.implode(',', array_keys(Solicitud::ORIGENES)),
            'anio' => 'nullable|integer|min:2000|max:2100',
            'resaltar' => 'nullable|integer',
        ]);

        $q = Solicitud::deTipo(Solicitud::AMPLIACION_PTI)->with(['solicitante', 'seguimiento.pti', 'atendidoPor']);
        if (filled($filtros['q'] ?? null)) {
            $t = '%'.trim($filtros['q']).'%';
            $q->where(fn ($w) => $w->where('titulo', 'like', $t)->orWhere('correo', 'like', $t)->orWhere('celulares', 'like', $t)
                ->orWhere('integrantes', 'like', $t)
                ->orWhereHas('solicitante', fn ($s) => $s->where('dni', 'like', $t)->orWhere('apellidos', 'like', $t)->orWhere('name', 'like', $t))
                ->orWhereHas('seguimiento', fn ($s) => $s->where('numero_expediente', 'like', $t)));
        }
        $q->when($filtros['estado'] ?? null, fn ($w, $e) => $w->where('estado', $e))
            ->when($filtros['origen'] ?? null, fn ($w, $o) => $w->where('origen', $o))
            ->when($filtros['anio'] ?? null, fn ($w, $a) => $w->whereYear('created_at', $a));

        $base = Solicitud::deTipo(Solicitud::AMPLIACION_PTI);
        $conteo = [
            'recibida' => (clone $base)->where('estado', 'recibida')->count(),
            'atendida' => (clone $base)->where('estado', 'atendida')->count(),
            'observada' => (clone $base)->where('estado', 'observada')->count(),
        ];

        $anios = (clone $base)->selectRaw('DISTINCT YEAR(created_at) as anio')->orderByDesc('anio')->pluck('anio')->filter();
        $registros = $q->orderByRaw("FIELD(estado, 'recibida', 'observada', 'atendida')")
            ->orderByDesc('created_at')->paginate(20)->withQueryString();

        $puedeAtender = AreasTitulacion::puede(AreasTitulacion::SECRETARIA);
        $puedeEditar = $puedeAtender;

        return view('titulacion.solicitudes-ampliacion.index', [
            'registros' => $registros,
            'conteo' => collect($conteo),
            'filtros' => $filtros,
            'anios' => $anios,
            'puedeAtender' => $puedeAtender,
            'puedeEditar' => $puedeEditar,
        ]);
    }

    /**
     * Secretaría atiende la solicitud de ampliación emitiendo/registrando la R.D. correspondiente.
     */
    public function atender(Request $request, Solicitud $solicitud)
    {
        abort_unless(AreasTitulacion::puede(AreasTitulacion::SECRETARIA), 403);
        abort_unless($solicitud->tipo === Solicitud::AMPLIACION_PTI, 404);

        $data = $request->validate([
            'numero_rd' => 'required|string|max:255',
            'fecha_rd' => 'nullable|date',
            'vencimiento_ampliado' => 'required|date',
            'pdf' => 'nullable|file|mimes:pdf|max:15360',
            'archivo' => 'nullable|url|max:500',
            'observacion' => 'nullable|string|max:1500',
            'aplicar_grupo' => 'nullable|boolean',
        ], [
            'numero_rd.required' => 'Ingrese el número de la R.D. de ampliación.',
            'vencimiento_ampliado.required' => 'Indique la nueva fecha de vencimiento que otorga la R.D.',
        ]);

        $archivo = $request->hasFile('pdf')
            ? ArchivosRd::guardarSubido($request->file('pdf'))
            : ($data['archivo'] ?? null);

        $seguimiento = $solicitud->seguimiento ?? $solicitud->solicitante?->seguimientoTitulacion;

        DB::transaction(function () use ($solicitud, $seguimiento, $data, $archivo, $request) {
            // Si tiene seguimiento y PTI, registrar la ampliación en la ficha:
            if ($seguimiento && $seguimiento->pti) {
                $pti = $seguimiento->pti;
                $pti->update([
                    'rd_ampliacion' => $data['numero_rd'],
                    'fecha_ampliacion' => $data['fecha_rd'] ?? null,
                    'vencimiento_ampliado' => $data['vencimiento_ampliado'],
                ]);

                // Registrar o actualizar la Resolución de tipo AmpliacionPti:
                $clave = GrupoTitulacion::claveRd($data['numero_rd']);
                $existente = $seguimiento->resoluciones()->where('tipo', 'AmpliacionPti')->get()
                    ->first(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === $clave);

                $detalle = 'PTI N° '.$pti->numero.' ampliado hasta el '.Carbon::parse($data['vencimiento_ampliado'])->format('d/m/Y');
                if ($existente) {
                    $existente->update(array_filter(['fecha' => $data['fecha_rd'] ?? null, 'archivo' => $archivo, 'detalle' => $detalle]));
                } else {
                    $seguimiento->resoluciones()->create([
                        'tipo' => 'AmpliacionPti',
                        'numero_rd' => $data['numero_rd'],
                        'fecha' => $data['fecha_rd'] ?? null,
                        'archivo' => $archivo,
                        'detalle' => $detalle,
                        'agrupa' => true,
                    ]);
                }

                // Propagar a compañeros de grupo si está marcado:
                if ($request->boolean('aplicar_grupo')) {
                    $companeros = GrupoTitulacion::companeros($seguimiento);
                    foreach ($companeros as $comp) {
                        $compSeg = $comp->seguimientoTitulacion;
                        if (! $compSeg || ! $compSeg->pti) {
                            continue;
                        }
                        $compSeg->pti->update([
                            'rd_ampliacion' => $data['numero_rd'],
                            'fecha_ampliacion' => $data['fecha_rd'] ?? null,
                            'vencimiento_ampliado' => $data['vencimiento_ampliado'],
                        ]);

                        $compExistente = $compSeg->resoluciones()->where('tipo', 'AmpliacionPti')->get()
                            ->first(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === $clave);
                        if ($compExistente) {
                            $compExistente->update(array_filter(['fecha' => $data['fecha_rd'] ?? null, 'archivo' => $archivo, 'detalle' => $detalle]));
                        } else {
                            $compSeg->resoluciones()->create([
                                'tipo' => 'AmpliacionPti',
                                'numero_rd' => $data['numero_rd'],
                                'fecha' => $data['fecha_rd'] ?? null,
                                'archivo' => $archivo,
                                'detalle' => $detalle,
                                'agrupa' => true,
                            ]);
                        }
                    }
                }
            }

            // Marcar solicitud como atendida:
            $solicitud->update([
                'estado' => 'atendida',
                'atendido_por' => auth()->id(),
                'atendido_en' => now(),
                'observacion' => $data['observacion'] ?? $solicitud->observacion,
                'datos' => array_merge($solicitud->datos ?? [], [
                    'numero_rd' => $data['numero_rd'],
                    'fecha_rd' => $data['fecha_rd'] ?? null,
                    'vencimiento_ampliado' => $data['vencimiento_ampliado'],
                ]),
            ]);
        });

        return redirect()->route('titulacion.solicitudes-ampliacion.index', ['resaltar' => $solicitud->id])
            ->with('success', 'Ampliación registrada con R.D. '.$data['numero_rd'].'. El nuevo vencimiento ha sido actualizado.');
    }

    /**
     * Observar la solicitud.
     */
    public function observar(Request $request, Solicitud $solicitud)
    {
        abort_unless(AreasTitulacion::puede(AreasTitulacion::SECRETARIA), 403);
        abort_unless($solicitud->tipo === Solicitud::AMPLIACION_PTI, 404);

        $data = $request->validate([
            'observacion' => 'required|string|max:1500',
        ], [
            'observacion.required' => 'Indique qué debe corregir el estudiante.',
        ]);

        $solicitud->update([
            'estado' => 'observada',
            'observacion' => $data['observacion'],
            'atendido_por' => auth()->id(),
            'atendido_en' => now(),
        ]);

        return redirect()->route('titulacion.solicitudes-ampliacion.index', ['resaltar' => $solicitud->id])
            ->with('success', 'Solicitud observada. El estudiante verá la observación en su intranet.');
    }

    /**
     * Descargar o ver archivo adjunto.
     */
    public function archivo(Request $request, Solicitud $solicitud, string $cual, int $n)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria']) || $solicitud->incluye($user), 403);

        $nombre = Solicitud::locales($solicitud->valorArchivo($cual))[$n] ?? null;
        abort_unless($nombre && Storage::disk('local')->exists(Solicitud::CARPETA.'/'.$nombre), 404);

        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $tipos = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return response()->file(Storage::disk('local')->path(Solicitud::CARPETA.'/'.$nombre), [
            'Content-Type' => $tipos[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => (in_array($ext, ['doc', 'docx'], true) ? 'attachment' : 'inline').'; filename="'.basename($nombre).'"',
        ]);
    }
}
