<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Bandeja completa: solo las notificaciones del usuario autenticado,
     * con filtro opcional ?filtro=no-leidas.
     */
    public function index(Request $request)
    {
        $user         = $request->user();
        $soloNoLeidas = $request->query('filtro') === 'no-leidas';

        $notificaciones = $user->notifications()
            ->when($soloNoLeidas, fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notificaciones.index', [
            'notificaciones' => $notificaciones,
            'soloNoLeidas'   => $soloNoLeidas,
            'total'          => $user->notifications()->count(),
            'noLeidas'       => $user->unreadNotifications()->count(),
        ]);
    }

    public function marcarLeida(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function eliminar(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notificación eliminada.');
    }

    public function ir(string $id)
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);

        if (is_null($notificacion->read_at)) {
            $notificacion->markAsRead();
        }

        return redirect()->to($notificacion->data['url'] ?? route('home'));
    }

    public function marcarTodasLeidas(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back();
    }
}
