<?php

namespace App\Http\Controllers;

use App\Models\BolsaTrabajoOferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BolsaTrabajoOfertaController extends Controller
{
    private const ALLOWED_HTML_TAGS = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote>';

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'detalles' => 'required|string|max:50000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $ruta = null;
        if ($request->hasFile('imagen')) {
            $dir = public_path('img/bolsa-trabajo-ofertas');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $archivo = $request->file('imagen');
            $nombre = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo->getClientOriginalName());
            $archivo->move($dir, $nombre);
            $ruta = 'img/bolsa-trabajo-ofertas/'.$nombre;
        }

        BolsaTrabajoOferta::create([
            'nombre' => $validated['nombre'],
            'detalles' => $this->sanitizeDetalles($validated['detalles']),
            'imagen' => $ruta,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
        ]);

        $bolsaQuery = array_filter([
            'anio' => $request->input('bolsa_listado_anio'),
            'mes' => $request->input('bolsa_listado_mes'),
        ], fn ($v) => $v !== null && $v !== '');

        $redirect = $request->input('redirect_to') === 'bolsa'
            ? redirect()->route('bolsa', $bolsaQuery)->with('success', 'Tu registro fue enviado correctamente.')
            : redirect()->route('index')->with('success', 'Tu registro fue enviado correctamente. Puedes verlo en Bolsa de trabajo.');

        return $redirect;
    }

    public function index(Request $request)
    {
        $this->ensureAdminBolsa();

        $query = BolsaTrabajoOferta::query()
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id');

        if ($request->filled('anio')) {
            $query->where('anio', (int) $request->anio);
        }
        if ($request->filled('mes')) {
            $query->where('mes', (int) $request->mes);
        }

        $ofertas = $query->get();
        $anios = BolsaTrabajoOferta::query()->select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $docente = auth()->user()?->docente;

        return view('bolsa.ofertas.index', compact('ofertas', 'anios', 'mesesNombres', 'docente'));
    }

    public function edit(BolsaTrabajoOferta $oferta)
    {
        $this->ensureAdminBolsa();

        $docente = auth()->user()?->docente;

        return view('bolsa.ofertas.edit', compact('oferta', 'docente'));
    }

    public function update(Request $request, BolsaTrabajoOferta $oferta)
    {
        $this->ensureAdminBolsa();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'detalles' => 'required|string|max:50000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'detalles' => $this->sanitizeDetalles($validated['detalles']),
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
        ];

        if ($request->hasFile('imagen')) {
            if ($oferta->imagen && File::exists(public_path($oferta->imagen))) {
                File::delete(public_path($oferta->imagen));
            }
            $dir = public_path('img/bolsa-trabajo-ofertas');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $archivo = $request->file('imagen');
            $nombre = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo->getClientOriginalName());
            $archivo->move($dir, $nombre);
            $data['imagen'] = 'img/bolsa-trabajo-ofertas/'.$nombre;
        }

        $oferta->update($data);

        return redirect()->route('bolsa-trabajo.ofertas.index', $request->only(['anio', 'mes']))
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, BolsaTrabajoOferta $oferta)
    {
        $this->ensureAdminBolsa();

        if ($oferta->imagen && File::exists(public_path($oferta->imagen))) {
            File::delete(public_path($oferta->imagen));
        }
        $oferta->delete();

        return redirect()->route('bolsa-trabajo.ofertas.index', $request->only(['anio', 'mes']))
            ->with('success', 'Registro eliminado.');
    }

    private function ensureAdminBolsa(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->hasAnyRole(['admin', 'adminB', 'docente']))) {
            abort(403);
        }
    }

    private function sanitizeDetalles(string $html): string
    {
        return strip_tags($html, self::ALLOWED_HTML_TAGS);
    }
}
