<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\BitacoraIntentoFallido;
use App\Models\BitacoraSesion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BitacoraAccesosController extends Controller
{
    public function index()
    {
        $sesiones = BitacoraSesion::orderByDesc('login_at')->paginate(30);

        return view('admin.bitacora-accesos.index', compact('sesiones'));
    }

    public function activos()
    {
        $lifetime = (int) config('session.lifetime');

        $filas = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes($lifetime)->timestamp)
            ->orderByDesc('last_activity')
            ->get();

        $usuarios = User::with('roles')
            ->whereIn('id', $filas->pluck('user_id')->unique())
            ->get()
            ->keyBy('id');

        $conectados = $filas->map(function ($fila) use ($usuarios) {
            return [
                'usuario' => $usuarios->get($fila->user_id),
                'ultima_actividad' => Carbon::createFromTimestamp($fila->last_activity),
            ];
        });

        return view('admin.bitacora-accesos.activos', compact('conectados'));
    }

    public function fallidos()
    {
        $intentos = BitacoraIntentoFallido::with('usuario')->orderByDesc('created_at')->paginate(30);

        return view('admin.bitacora-accesos.fallidos', compact('intentos'));
    }
}
