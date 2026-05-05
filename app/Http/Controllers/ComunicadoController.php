<?php

namespace App\Http\Controllers;

use App\Models\Comunicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ComunicadoController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $query = Comunicado::query()
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id');

        if ($request->filled('anio')) {
            $query->where('anio', (int) $request->anio);
        }
        if ($request->filled('mes')) {
            $query->where('mes', (int) $request->mes);
        }

        $comunicados = $query->get();
        $anios = Comunicado::query()->select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('comunicados.index', compact('comunicados', 'anios', 'mesesNombres'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
            'fecha_publicacion' => 'required|date',
        ]);

        $archivoSubido = $request->file('archivo');
        $ext = strtolower((string) $archivoSubido->getClientOriginalExtension());
        $tipo = $ext === 'pdf' ? 'pdf' : 'imagen';

        $dir = public_path('docs/comunicados');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $baseNombre = Str::slug((string) $validated['titulo'], '-');
        if ($baseNombre === '') {
            $baseNombre = 'comunicado';
        }
        $nombre = $baseNombre.'.'.$ext;
        $i = 1;
        while (File::exists($dir.DIRECTORY_SEPARATOR.$nombre)) {
            $nombre = $baseNombre.'-'.$i.'.'.$ext;
            $i++;
        }
        $archivoSubido->move($dir, $nombre);

        Comunicado::create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo' => 'docs/comunicados/'.$nombre,
            'archivo_tipo' => $tipo,
            'fecha_publicacion' => $validated['fecha_publicacion'],
        ]);

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado registrado correctamente.');
    }

    public function edit(Comunicado $comunicado)
    {
        $this->ensureAdmin();

        return view('comunicados.edit', compact('comunicado'));
    }

    public function update(Request $request, Comunicado $comunicado)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
            'fecha_publicacion' => 'required|date',
        ]);

        $data = [
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_publicacion' => $validated['fecha_publicacion'],
        ];

        if ($request->hasFile('archivo')) {
            if ($comunicado->archivo && File::exists(public_path($comunicado->archivo))) {
                File::delete(public_path($comunicado->archivo));
            }
            $archivoSubido = $request->file('archivo');
            $ext = strtolower((string) $archivoSubido->getClientOriginalExtension());
            $tipo = $ext === 'pdf' ? 'pdf' : 'imagen';
            $dir = public_path('docs/comunicados');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $baseNombre = Str::slug((string) $validated['titulo'], '-');
            if ($baseNombre === '') {
                $baseNombre = 'comunicado';
            }
            $nombre = $baseNombre.'.'.$ext;
            $i = 1;
            while (File::exists($dir.DIRECTORY_SEPARATOR.$nombre)) {
                $nombre = $baseNombre.'-'.$i.'.'.$ext;
                $i++;
            }
            $archivoSubido->move($dir, $nombre);
            $data['archivo'] = 'docs/comunicados/'.$nombre;
            $data['archivo_tipo'] = $tipo;
        }

        $comunicado->update($data);

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado actualizado correctamente.');
    }

    public function destroy(Comunicado $comunicado)
    {
        $this->ensureAdmin();

        if ($comunicado->archivo && File::exists(public_path($comunicado->archivo))) {
            File::delete(public_path($comunicado->archivo));
        }
        $comunicado->delete();

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado eliminado correctamente.');
    }

    private function ensureAdmin(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }
    }
}
