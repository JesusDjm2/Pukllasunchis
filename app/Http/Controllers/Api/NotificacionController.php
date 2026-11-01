<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificacionResource;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        // Orden estrictamente cronológico (más recientes primero) sin excluir
        // ninguna notificación: las leídas permanecen siempre en el historial.
        $notificaciones = $request->user()->notifications()->latest()->paginate(50);

        return response()->json([
            'data' => NotificacionResource::collection($notificaciones),
            'meta' => [
                'current_page' => $notificaciones->currentPage(),
                'last_page' => $notificaciones->lastPage(),
                'total' => $notificaciones->total(),
            ],
        ]);
    }

    public function noLeidasCount(Request $request)
    {
        return response()->json([
            'data' => ['count' => $request->user()->unreadNotifications()->count()],
        ]);
    }

    /**
     * No usa route model binding implícito a propósito: el id de una
     * notificación es un UUID sin dueño declarado en la tabla más allá de
     * `notifiable_id`, así que resolver por id solo (sin escanear al propio
     * usuario) expondría notificaciones ajenas.
     */
    public function marcarLeida(Request $request, string $id)
    {
        $notificacion = $request->user()->notifications()->where('id', $id)->first();

        if (! $notificacion) {
            return response()->json(['message' => 'Notificación no encontrada.'], 404);
        }

        $notificacion->markAsRead();

        return response()->json(['data' => new NotificacionResource($notificacion->fresh())]);
    }

    public function marcarTodasLeidas(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['data' => ['message' => 'Todas las notificaciones marcadas como leídas.']]);
    }
}
