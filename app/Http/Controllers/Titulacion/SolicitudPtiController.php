<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Solicitud;
use App\Services\Titulacion\AreasTitulacion;
use App\Services\Titulacion\ImportarSolicitudesPti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Solicitudes de aprobación de PTI (el primer formulario del proceso). Por ahora llegan por el formulario de
 * Google: aquí se importan sus respuestas (super-admin) y se ven en una bandeja. Investigación atiende cada una
 * iniciando el proceso desde la solicitud (InicioProcesoController, con los datos ya puestos), y la solicitud
 * queda enlazada a ese proceso.
 */
class SolicitudPtiController extends Controller
{
    private const CARPETA = 'titulacion/importaciones';

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:'.implode(',', array_keys(Solicitud::ESTADOS)),
            'origen' => 'nullable|in:'.implode(',', array_keys(Solicitud::ORIGENES)),
            'programa' => 'nullable|in:'.implode(',', Solicitud::PROGRAMAS_ESTUDIOS),
            'area' => 'nullable|in:'.implode(',', array_keys(Solicitud::AREAS)),
            'anio' => 'nullable|integer|min:2000|max:2100',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
            'resaltar' => 'nullable|integer',
        ]);

        $q = Solicitud::deTipo(Solicitud::PTI)->with(['solicitante.seguimientoTitulacion.pti', 'solicitante.roles', 'seguimiento', 'atendidoPor']);
        if (filled($filtros['q'] ?? null)) {
            $t = '%'.trim($filtros['q']).'%';
            $q->where(fn ($w) => $w->where('titulo', 'like', $t)->orWhere('correo', 'like', $t)->orWhere('celulares', 'like', $t)
                ->orWhere('integrantes', 'like', $t)
                ->orWhereHas('solicitante', fn ($s) => $s->where('dni', 'like', $t)->orWhere('apellidos', 'like', $t)->orWhere('name', 'like', $t))
                ->orWhereHas('seguimiento', fn ($s) => $s->where('numero_expediente', 'like', $t)));
        }
        $q->when($filtros['estado'] ?? null, fn ($w, $e) => $w->where('estado', $e))
            ->when($filtros['origen'] ?? null, fn ($w, $o) => $w->where('origen', $o))
            ->when($filtros['programa'] ?? null, fn ($w, $p) => $w->where('programa_estudios', $p))
            ->when($filtros['area'] ?? null, fn ($w, $a) => $w->where('datos->area', $a))
            ->when($filtros['anio'] ?? null, fn ($w, $a) => $w->whereYear('created_at', $a))
            ->when($filtros['desde'] ?? null, fn ($w, $d) => $w->whereDate('created_at', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($w, $d) => $w->whereDate('created_at', '<=', $d));

        return view('titulacion.solicitudes-pti.index', [
            'registros' => $q->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString(),
            'conteo' => Solicitud::deTipo(Solicitud::PTI)->selectRaw('estado, COUNT(*) total')->groupBy('estado')->pluck('total', 'estado'),
            'filtros' => $filtros,
            'anios' => Solicitud::deTipo(Solicitud::PTI)->selectRaw('DISTINCT YEAR(created_at) a')->orderByDesc('a')->pluck('a'),
            'resaltar' => isset($filtros['resaltar']) ? (int) $filtros['resaltar'] : null,
            'puedeIniciar' => AreasTitulacion::puedeRuta('titulacion.inicio.crear'),
            'puedeEditar' => AreasTitulacion::puedeRuta('titulacion.solicitudes.editar'),
        ]);
    }

    /** Investigación devuelve la solicitud con un comentario: el estudiante la ve en su intranet y la corrige. */
    public function observar(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate(['observacion' => 'required|string|min:5|max:1500'], [], ['observacion' => 'observación']);
        $volver = redirect()->route('titulacion.solicitudes-pti.index', ['resaltar' => $solicitud->id]);
        if ($solicitud->tipo !== Solicitud::PTI || $solicitud->estado !== 'recibida') {
            return $volver->with('error', 'Esta solicitud ya fue atendida.');
        }
        $solicitud->update(['estado' => 'observada', 'observacion' => trim($data['observacion']), 'atendido_por' => $request->user()->id, 'atendido_en' => now()]);

        return $volver->with('success', 'Solicitud observada: el estudiante verá la observación en su intranet y podrá corregirla y enviarla de nuevo.');
    }

    // ── Importar del formulario de Google (super-admin) ─────────────────

    public function importarForm()
    {
        return view('titulacion.solicitudes-pti.importar', ['lectura' => null, 'token' => null]);
    }

    public function importarPrevia(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480'], [
            'excel.required' => 'Elija el Excel o CSV de respuestas del formulario.',
            'excel.mimes' => 'Debe ser un archivo de Excel (.xlsx) o CSV.',
        ]);
        $token = (string) Str::uuid();
        // Se conserva el tipo (xlsx o csv): el lector elige cómo leerlo por la extensión.
        $ext = strtolower($request->file('excel')->getClientOriginalExtension()) === 'csv' ? 'csv' : 'xlsx';
        $request->file('excel')->storeAs(self::CARPETA, 'pti-'.$token.'.'.$ext, 'local');

        return redirect()->route('titulacion.solicitudes-pti.importar.vista', $token);
    }

    public function importarVista(string $token)
    {
        $ruta = $this->ruta($token);
        if (! $ruta) {
            return redirect()->route('titulacion.solicitudes-pti.importar')->with('error', 'El archivo subido ya no está: vuelva a subirlo.');
        }

        return view('titulacion.solicitudes-pti.importar', ['lectura' => ImportarSolicitudesPti::leer($ruta), 'token' => $token]);
    }

    public function importarAplicar(Request $request)
    {
        $data = $request->validate(['token' => 'required|uuid', 'confirmar' => 'accepted'], ['confirmar.accepted' => 'Marque la casilla de confirmación.']);
        $ruta = $this->ruta($data['token']);
        abort_unless($ruta, 404, 'El archivo subido ya no está: vuelva a subir el Excel.');

        $c = ImportarSolicitudesPti::importar(ImportarSolicitudesPti::leer($ruta)['filas']);
        Storage::disk('local')->delete(self::CARPETA.'/'.basename($ruta));

        return redirect()->route('titulacion.solicitudes-pti.index', ['origen' => 'formulario'])
            ->with('success', "Importación lista: {$c['importadas']} solicitudes del formulario de Google ({$c['atendida']} ya tenían su PTI en el sistema; {$c['recibida']} quedaron por atender). No se envió ningún correo.");
    }

    private function ruta(string $token): ?string
    {
        if (! Str::isUuid($token)) {
            return null;
        }
        foreach (['xlsx', 'csv'] as $ext) {
            if (Storage::disk('local')->exists($archivo = self::CARPETA.'/pti-'.$token.'.'.$ext)) {
                return Storage::disk('local')->path($archivo);
            }
        }

        return null;
    }
}
