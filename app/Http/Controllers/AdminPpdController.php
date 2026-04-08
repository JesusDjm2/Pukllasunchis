<?php

namespace App\Http\Controllers;

use App\Models\AdminPpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPpdController extends Controller
{   
    public function create()
    {
        return view('alumnos.postulantes.admision.ppd.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'boolean',
        ]);

        AdminPpd::create($data);

        return redirect()
            ->route('admin-fids.index')
            ->with('success', 'Proceso PPD creado correctamente.');
    }

    public function edit(AdminPpd $admin_ppd)
    {
        return view('alumnos.postulantes.admision.ppd.edit', compact('admin_ppd'));
    }
    public function update(Request $request, AdminPpd $admin_ppd)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'boolean',
        ]);

        $data['estado'] = $request->boolean('estado');

        DB::transaction(function () use ($data, $admin_ppd) {

            // 👉 Si este proceso será el actual, desactiva todos los demás
            if ($data['estado']) {
                AdminPpd::where('id', '!=', $admin_ppd->id)
                    ->update(['estado' => false]);
            }

            $admin_ppd->update($data);
        });

        return redirect()
            ->route('admin-fids.index')
            ->with('success', 'Proceso de admisión actualizado correctamente.');
    }

    public function destroy(AdminPpd $admin_ppd)
    {
        $admin_ppd->delete();

        return redirect()
            ->route('admin-fids.index')
            ->with('success', 'Proceso PPD eliminado correctamente.');
    }
}
