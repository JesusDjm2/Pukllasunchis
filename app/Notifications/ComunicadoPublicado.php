<?php

namespace App\Notifications;

use App\Models\Comunicado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * A todos los alumnos, cuando se publica un comunicado nuevo (ver
 * `ComunicadoController::store`). Se envía en bulk con
 * `Notification::send($alumnos, ...)`.
 */
class ComunicadoPublicado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comunicado $comunicado)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable): array
    {
        return [
            'comunicado_id' => $this->comunicado->id,
            'titulo' => $this->comunicado->titulo,
            'mensaje' => 'Nuevo comunicado: '.Str::limit($this->comunicado->titulo, 80),
        ];
    }
}
