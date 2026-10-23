<?php

namespace App\Http\Controllers;

use App\Models\Minkarikuy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MinkarikuyController extends Controller
{
    public function index()
    {
        $registros = Minkarikuy::orderByDesc('activo')->orderByDesc('fecha')->orderByDesc('id')->get();

        return view('admin.minkarikuy.index', compact('registros'));
    }

    public function create()
    {
        return view('admin.minkarikuy.create', ['minkarikuy' => new Minkarikuy()]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        if ($data['activo']) {
            Minkarikuy::where('activo', true)->update(['activo' => false]);
        }

        $minkarikuy = Minkarikuy::create($data);

        return redirect()->route('admin.minkarikuy.index')
            ->with('success', "Mink'arikuy \"{$minkarikuy->nombre}\" creado. Su QR ya está listo para descargar o imprimir.");
    }

    public function edit(Minkarikuy $minkarikuy)
    {
        return view('admin.minkarikuy.edit', compact('minkarikuy'));
    }

    public function update(Request $request, Minkarikuy $minkarikuy)
    {
        $data = $this->validar($request);

        if ($data['activo']) {
            Minkarikuy::where('activo', true)->where('id', '!=', $minkarikuy->id)->update(['activo' => false]);
        }

        $minkarikuy->update($data);

        return redirect()->route('admin.minkarikuy.index')
            ->with('success', 'Registro actualizado. Si cambiaste el enlace, el QR ya apunta al nuevo.');
    }

    /** Publica (o retira) un registro en la página web: solo puede haber uno activo. */
    public function activar(Minkarikuy $minkarikuy)
    {
        if ($minkarikuy->activo) {
            $minkarikuy->update(['activo' => false]);

            return back()->with('success', "\"{$minkarikuy->nombre}\" ya no se muestra en la página web.");
        }

        Minkarikuy::where('activo', true)->update(['activo' => false]);
        $minkarikuy->update(['activo' => true]);

        return back()->with('success', "\"{$minkarikuy->nombre}\" es ahora el Mink'arikuy publicado en la página web.");
    }

    /** Póster imprimible / descargable con el QR (mismo diseño que los QR de tutoría). */
    public function qr(Request $request, Minkarikuy $minkarikuy)
    {
        abort_unless($minkarikuy->url, 404);

        return view('admin.minkarikuy.qr', [
            'minkarikuy'  => $minkarikuy,
            'tipo'        => 'minkarikuy',
            'titulo'      => $minkarikuy->nombre,
            'descripcion' => 'Escanea este código con la cámara de tu celular para unirte. ¡Te esperamos!',
            'url'         => $minkarikuy->url,
            'contexto'    => $minkarikuy->cuando,
        ]);
    }

    public function destroy(Minkarikuy $minkarikuy)
    {
        if ($minkarikuy->imagen && File::exists(public_path('img/minkarikuy/'.$minkarikuy->imagen))) {
            File::delete(public_path('img/minkarikuy/'.$minkarikuy->imagen));
        }
        $minkarikuy->delete();

        return redirect()->route('admin.minkarikuy.index')
            ->with('success', 'Registro eliminado.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'fecha'  => 'required|date',
            'hora'   => 'required|date_format:H:i',
            'url'    => 'required|url|max:500',
            'activo' => 'nullable|boolean',
        ], [
            'url.required' => 'El enlace de redirección es obligatorio: con él se genera el código QR.',
            'url.url'      => 'El enlace debe ser una dirección completa, por ejemplo https://meet.google.com/abc-defg-hij',
        ]);

        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
