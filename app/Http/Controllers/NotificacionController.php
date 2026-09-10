<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
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
