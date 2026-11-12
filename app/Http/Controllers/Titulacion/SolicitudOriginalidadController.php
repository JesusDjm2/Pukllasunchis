<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Titulacion\Concerns\ImportaFormularioGoogle;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Notifications\TitulacionOriginalidadAtendida;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\ImportarSolicitudesOriginalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bandeja de solicitudes de revisión de originalidad (Turnitin), del TI o de la Tesis. La atiende el Área de
 * Investigación: sube el reporte de Turnitin (PDF) con el % de similitud (apto hasta Solicitud::SIMILITUD_MAXIMA)
 * y el resultado llega a los tesistas (campanita e intranet). También se puede observar. Por ahora la mayoría
 * llega del formulario de Google: se importa (super-admin).
 */
class SolicitudOriginalidadController extends Controller
{
    use ImportaFormularioGoogle;

    protected function importador(): string
    {
        return ImportarSolicitudesOriginalidad::class;
    }

    protected function importacion(): array
    {
        return [
            'kicker' => 'Investigación · Revisión de originalidad',
            'titulo' => 'Revisión de originalidad',
            'sub' => 'Las solicitudes de revisión de originalidad (Turnitin) del TI y de la Tesis hechas con el formulario de Google se registran con su fecha original. El reporte se enviaba por correo, así que no está en el Excel: quedan <strong>atendidas fuera del sistema</strong> si la persona ya avanzó a un paso posterior o tienen más de '.ImportarSolicitudesOriginalidad::DIAS_ANTIGUA.' días; las recientes quedan <strong>por atender</strong>.',
            'bandeja' => 'titulacion.solicitudes-originalidad.index',
            'ruta' => 'titulacion.solicitudes-originalidad.importar',
            'estados' => ['atendida' => 'Atendidas fuera del sistema', 'recibida' => 'Quedan por atender'],
        ];
    }

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:'.implode(',', array_keys(Solicitud::ESTADOS)),
            'etapa' => 'nullable|in:'.implode(',', array_keys(Solicitud::ETAPAS)),
            'resultado' => 'nullable|in:apto,no_apto,sin_reporte',
            'origen' => 'nullable|in:'.implode(',', array_keys(Solicitud::ORIGENES)),
            'anio' => 'nullable|integer|min:2000|max:2100',
            'resaltar' => 'nullable|integer',
        ]);

        $q = Solicitud::deTipo(Solicitud::ORIGINALIDAD)->with(['solicitante', 'seguimiento', 'atendidoPor']);
        if (filled($filtros['q'] ?? null)) {
            $t = '%'.trim($filtros['q']).'%';
            $q->where(fn ($w) => $w->where('titulo', 'like', $t)->orWhere('correo', 'like', $t)->orWhere('celulares', 'like', $t)
                ->orWhere('integrantes', 'like', $t)
                ->orWhereHas('solicitante', fn ($s) => $s->where('dni', 'like', $t)->orWhere('apellidos', 'like', $t)->orWhere('name', 'like', $t))
                ->orWhereHas('seguimiento', fn ($s) => $s->where('numero_expediente', 'like', $t)));
        }
        $q->when($filtros['estado'] ?? null, fn ($w, $e) => $w->where('estado', $e))
            ->when($filtros['etapa'] ?? null, fn ($w, $e) => $w->where('datos->etapa', $e))
            ->when($filtros['origen'] ?? null, fn ($w, $o) => $w->where('origen', $o))
            ->when($filtros['anio'] ?? null, fn ($w, $a) => $w->whereYear('created_at', $a))
            ->when($filtros['resultado'] ?? null, fn ($w, $r) => match ($r) {
                'apto' => $w->whereNotNull('datos->similitud')->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(datos, '$.similitud')) AS DECIMAL(5,2)) <= ?", [Solicitud::SIMILITUD_MAXIMA]),
                'no_apto' => $w->whereNotNull('datos->similitud')->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(datos, '$.similitud')) AS DECIMAL(5,2)) > ?", [Solicitud::SIMILITUD_MAXIMA]),
                default => $w->where('estado', 'atendida')->whereNull('datos->similitud'),
            });

        $base = Solicitud::deTipo(Solicitud::ORIGINALIDAD);

        return view('titulacion.solicitudes-originalidad.index', [
            'registros' => $q->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString(),
            'conteo' => (clone $base)->selectRaw('estado, COUNT(*) total')->groupBy('estado')->pluck('total', 'estado'),
            'anios' => (clone $base)->selectRaw('DISTINCT YEAR(created_at) a')->orderByDesc('a')->pluck('a'),
            'filtros' => $filtros,
            'resaltar' => isset($filtros['resaltar']) ? (int) $filtros['resaltar'] : null,
            'puedeAtender' => AreasTitulacion::puedeRuta('titulacion.solicitudes-originalidad.atender'),
            'puedeEditar' => AreasTitulacion::puedeRuta('titulacion.solicitudes.editar'),
        ]);
    }

    /** Investigación sube el reporte de Turnitin y el % de similitud: la solicitud queda atendida y se avisa a los tesistas. */
    public function atender(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'similitud' => 'required|numeric|min:0|max:100',
            'reporte' => 'required|file|mimes:pdf|max:10240',
            'mensaje' => 'nullable|string|max:1500',
        ], [
            'reporte.required' => 'Adjunte el reporte de Turnitin en PDF.',
            'reporte.mimes' => 'El reporte debe ser PDF.',
        ], ['similitud' => '% de similitud']);
        $volver = redirect()->route('titulacion.solicitudes-originalidad.index', ['resaltar' => $solicitud->id]);
        if ($solicitud->tipo !== Solicitud::ORIGINALIDAD || $solicitud->estado !== 'recibida') {
            return $volver->with('error', 'Esta solicitud ya fue atendida.');
        }

        $nombre = Str::uuid().'.pdf';
        $request->file('reporte')->storeAs(Solicitud::CARPETA, $nombre, 'local');
        $solicitud->update([
            'estado' => 'atendida',
            'datos' => array_merge($solicitud->datos ?? [], ['similitud' => round((float) $data['similitud'], 2), 'reporte' => 'local:'.$nombre]),
            'observacion' => filled($data['mensaje'] ?? null) ? trim($data['mensaje']) : null,
            'atendido_por' => $request->user()->id,
            'atendido_en' => now(),
        ]);
        $this->avisarTesistas($solicitud);

        return $volver->with('success', 'Reporte enviado: '.$solicitud->similitud().' % de similitud ('.($solicitud->esApto() ? 'apto' : 'no apto: debe corregir y solicitar otra revisión').'). Los tesistas lo ven en su intranet.');
    }

    public function observar(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate(['observacion' => 'required|string|min:5|max:1500'], [], ['observacion' => 'observación']);
        $volver = redirect()->route('titulacion.solicitudes-originalidad.index', ['resaltar' => $solicitud->id]);
        if ($solicitud->tipo !== Solicitud::ORIGINALIDAD || $solicitud->estado !== 'recibida') {
            return $volver->with('error', 'Esta solicitud ya fue atendida.');
        }
        $solicitud->update(['estado' => 'observada', 'observacion' => trim($data['observacion']), 'atendido_por' => $request->user()->id, 'atendido_en' => now()]);
        $this->avisarTesistas($solicitud);

        return $volver->with('success', 'Solicitud observada: los tesistas verán la observación en su intranet y podrán corregirla y enviarla de nuevo.');
    }

    /**
     * Aviso en la campanita de cada tesista reconocido (sin correo). Solo con la revisión de originalidad abierta en la
     * intranet (config titulacion.originalidad_alumnos): si no, el aviso llevaría a una página que el alumno aún no tiene.
     */
    private function avisarTesistas(Solicitud $s): void
    {
        if (! config('titulacion.originalidad_alumnos')) {
            return;
        }
        foreach (User::whereIn('id', array_unique(array_filter([$s->solicitante_id, ...$s->idsIntegrantes()])))->get() as $u) {
            try {
                $u->notify(new TitulacionOriginalidadAtendida($s));
            } catch (\Throwable $e) {
                Log::warning('No se pudo avisar el resultado de originalidad', ['solicitud' => $s->id, 'user' => $u->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
