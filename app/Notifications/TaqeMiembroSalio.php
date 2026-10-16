<?php

namespace App\Notifications;

use App\Models\Alumno;
use App\Models\TaqeGrupo;
use Illuminate\Notifications\Notification;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class TaqeMiembroSalio extends Notification
{
    public function __construct(public TaqeGrupo $grupo, public Alumno $queSalio, public bool $huboCambioDeOrganizador = false)
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
        // Si el que salió era el organizador, el rol pasó a otro integrante
        // aceptado; a ESE integrante en particular se le avisa que ahora es
        // el organizador del grupo (comparando contra el alumno del User
        // notificado, no contra el grupo ya actualizado en memoria).
        $esNuevoOrganizador = $this->huboCambioDeOrganizador
            && $notifiable->alumno
            && $this->grupo->fresh()->creador_alumno_id === $notifiable->alumno->id;

        return [
            'taqe_grupo_id' => $this->grupo->id,
            'grupo_nombre' => $this->grupo->nombre,
            'curso_nombre' => $this->grupo->curso->nombre,
            'alumno_nombre' => $this->queSalio->apellidos.', '.$this->queSalio->nombres,
            'nuevo_organizador' => $esNuevoOrganizador,
            'url' => route('taqe.index'),
        ];
    }
}
