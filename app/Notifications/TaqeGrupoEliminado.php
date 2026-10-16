<?php

namespace App\Notifications;

use App\Models\TaqeGrupo;
use Illuminate\Notifications\Notification;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class TaqeGrupoEliminado extends Notification
{
    public function __construct(public TaqeGrupo $grupo)
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
            'grupo_nombre' => $this->grupo->nombre,
            'curso_nombre' => $this->grupo->curso->nombre,
            'url' => route('taqe.index'),
        ];
    }
}
