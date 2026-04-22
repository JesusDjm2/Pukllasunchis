<?php

namespace App\Http\Controllers;

use App\Models\Minkarikuy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MinkarikuyController extends Controller
{
    public function index()
    {
        $registros = Minkarikuy::latest()->get();
        return view('admin.minkarikuy.index', compact('registros'));
    }

    public function create()
    {
        return view('admin.minkarikuy.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'fecha'  => 'required|date',
            'hora'   => 'required|date_format:H:i',
            'imagen' => 'nullable|image|max:4096',
            'url'    => 'nullable|url|max:500',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('img/minkarikuy'), $name);
            $data['imagen'] = $name;
        }

        if ($data['activo']) {
            Minkarikuy::where('activo', true)->update(['activo' => false]);
        }

        Minkarikuy::create($data);

        return redirect()->route('admin.minkarikuy.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(Minkarikuy $minkarikuy)
    {
        return view('admin.minkarikuy.edit', compact('minkarikuy'));
    }

    public function update(Request $request, Minkarikuy $minkarikuy)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'fecha'  => 'required|date',
            'hora'   => 'required|date_format:H:i',
            'imagen' => 'nullable|image|max:4096',
            'url'    => 'nullable|url|max:500',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('imagen')) {
            if ($minkarikuy->imagen && File::exists(public_path('img/minkarikuy/'.$minkarikuy->imagen))) {
                File::delete(public_path('img/minkarikuy/'.$minkarikuy->imagen));
            }
            $file = $request->file('imagen');
            $name = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('img/minkarikuy'), $name);
            $data['imagen'] = $name;
        } else {
            unset($data['imagen']);
        }

        if ($data['activo']) {
            Minkarikuy::where('activo', true)->where('id', '!=', $minkarikuy->id)->update(['activo' => false]);
        }

        $minkarikuy->update($data);

        return redirect()->route('admin.minkarikuy.index')
            ->with('success', 'Registro actualizado correctamente.');
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
}
