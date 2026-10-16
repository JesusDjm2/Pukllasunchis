<?php

namespace App\Notifications;

use App\Models\Alumno;
use App\Models\TaqeGrupo;
use Illuminate\Notifications\Notification;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class TaqeMiembroAcepto extends Notification
{
    public function __construct(public TaqeGrupo $grupo, public Alumno $queAcepto)
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
            'taqe_grupo_id' => $this->grupo->id,
            'grupo_nombre' => $this->grupo->nombre,
            'curso_nombre' => $this->grupo->curso->nombre,
            'alumno_nombre' => $this->queAcepto->apellidos.', '.$this->queAcepto->nombres,
            'url' => route('taqe.index'),
        ];
    }
}
