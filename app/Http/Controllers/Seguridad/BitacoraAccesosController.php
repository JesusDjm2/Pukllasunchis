<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\BitacoraIntentoFallido;
use App\Models\BitacoraSesion;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function fallidos(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));
        $cuenta = in_array($request->query('cuenta'), ['real', 'inexistente'], true) ? $request->query('cuenta') : '';

        $intentos = BitacoraIntentoFallido::with('usuario')
            ->when($buscar !== '', fn ($q) => $q->where('email_intentado', 'like', '%' . addcslashes($buscar, '%_\\') . '%'))
            ->when($cuenta === 'real', fn ($q) => $q->whereNotNull('user_id'))
            ->when($cuenta === 'inexistente', fn ($q) => $q->whereNull('user_id'))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $resumen = [
            'total' => BitacoraIntentoFallido::count(),
            'ultimas24' => BitacoraIntentoFallido::where('created_at', '>=', now()->subDay())->count(),
            'reales' => BitacoraIntentoFallido::whereNotNull('user_id')->count(),
            'inexistentes' => BitacoraIntentoFallido::whereNull('user_id')->count(),
        ];

        // Correos con varios fallos en la última semana: contraseña olvidada o alguien probando una cuenta ajena.
        $insistentes = BitacoraIntentoFallido::select('email_intentado', DB::raw('MAX(user_id) as user_id'), DB::raw('COUNT(*) as veces'), DB::raw('MAX(created_at) as ultimo'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('email_intentado')
            ->having('veces', '>=', 3)
            ->orderByDesc('veces')
            ->limit(8)
            ->get();
        $usuariosInsistentes = User::whereIn('id', $insistentes->pluck('user_id')->filter())->get()->keyBy('id');

        return view('admin.bitacora-accesos.fallidos', compact('intentos', 'resumen', 'insistentes', 'usuariosInsistentes', 'buscar', 'cuenta'));
    }
}
