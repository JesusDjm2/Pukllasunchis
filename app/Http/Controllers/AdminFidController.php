<?php

namespace App\Http\Controllers;

use App\Models\AdminFid;
use App\Models\AdminPpd;
use App\Models\PostulantesRegular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminFidController extends Controller
{
    public function index()
    {
        $adminFids = AdminFid::withCount('postulantes')->orderBy('anio', 'desc')->get();
        $adminsppd = AdminPpd::all();
        return view('alumnos.postulantes.admision.index', compact('adminFids', 'adminsppd'));
    }

    public function create()
    {
        return view('alumnos.postulantes.admision.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:2015|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'actual' => 'nullable|boolean',
        ]);

        // Si se marca como activo, desactivar los demás
        $estado = $request->boolean('estado');

        if ($estado) {
            AdminFid::where('estado', true)->update(['estado' => false]);
        }

        AdminFid::create([
            'nombre' => $request->nombre,
            'anio' => $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $estado,
        ]);

        return redirect()->route('admin-fids.index')->with('success', 'Periodo creado correctamente.');
    }

    public function show(AdminFid $adminFid)
    {
        return view('alumnos.postulantes.admision.show', compact('adminFid'));
    }

    public function edit(AdminFid $adminFid)
    {
        return view('alumnos.postulantes.admision.edit', compact('adminFid'));
    }

    public function update(Request $request, AdminFid $adminFid)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:2015|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'estado' => 'nullable|boolean',
        ]);

        $estado = $request->boolean('estado');

        if ($estado) {
            // Desactivar cualquier otro periodo activo
            AdminFid::where('estado', true)
                ->where('id', '!=', $adminFid->id)
                ->update(['estado' => false]);
        }

        $adminFid->update([
            'nombre' => $request->nombre,
            'anio' => $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $estado,
        ]);

        return redirect()->route('admin-fids.index')->with('success', 'Periodo actualizado correctamente.');
    }

    public function destroy(AdminFid $adminFid)
    {
        $adminFid->delete();

        return redirect()->route('admin-fids.index')->with('success', 'Periodo eliminado correctamente.');
    }

    public function asociarSinRelacion(AdminFid $adminFid)
    {
        $sinRelacion = PostulantesRegular::whereNull('admin_fids_id')->count();
        if ($sinRelacion === 0) {
            return redirect()->route('admin-fids.index')
                ->with('info', 'No hay postulantes sin relación para asociar.');
        }
        PostulantesRegular::whereNull('admin_fids_id')
            ->update(['admin_fids_id' => $adminFid->id]);

        return redirect()->route('admin-fids.index')
            ->with('success', "Se asociaron {$sinRelacion} postulantes al periodo '{$adminFid->nombre}'.");
    }

    public function verPostulantes(AdminFid $adminFid)
    {
        $periodos = AdminFid::orderByDesc('anio')->get();
        $postulantes = $adminFid->postulantes()->orderBy('apellidos')->get();

        // 1️⃣ Beca vs No Beca
        $beca = (clone $adminFid->postulantes())
            ->selectRaw("
            SUM(CASE WHEN estudio_beca != '0' THEN 1 ELSE 0 END) as con_beca,
            SUM(CASE WHEN estudio_beca = '0' THEN 1 ELSE 0 END) as sin_beca
        ")
            ->first();

        // 2️⃣ Género
        $genero = (clone $adminFid->postulantes())
            ->selectRaw('
            SUM(CASE WHEN genero = 1 THEN 1 ELSE 0 END) as masculino,
            SUM(CASE WHEN genero = 0 THEN 1 ELSE 0 END) as femenino
        ')
            ->first();

        // 3️⃣ Top 10 colegios del periodo
        $colegios = (clone $adminFid->postulantes())
            ->select('colegio', DB::raw('COUNT(*) as total'))
            ->groupBy('colegio')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 4️⃣ Lengua 1
        $lengua1 = (clone $adminFid->postulantes())
            ->select('lengua_1', DB::raw('COUNT(*) as total'))
            ->groupBy('lengua_1')
            ->get();

        // 5️⃣ Lengua 2
        $lengua2 = (clone $adminFid->postulantes())
            ->select('lengua_2', DB::raw('COUNT(*) as total'))
            ->groupBy('lengua_2')
            ->get();

        return view('alumnos.postulantes.admision.ver', compact(
            'adminFid',
            'postulantes',
            'beca',
            'genero',
            'colegios',
            'lengua1',
            'lengua2',
            'periodos'
        ));
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'dni' => 'required|digits:8',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.config('services.apiperu.token'),
        ])->post(config('services.apiperu.url').'/dni', [
            'dni' => $request->dni,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la consulta al servicio externo',
            ], 500);
        }

        return response()->json($response->json());
    }
}
