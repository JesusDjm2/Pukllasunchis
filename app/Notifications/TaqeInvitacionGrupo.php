<?php

namespace App\Notifications;

use App\Models\TaqeGrupo;
use Illuminate\Notifications\Notification;

// Nota: NO implementa ShouldQueue a propósito. El proyecto usa
// QUEUE_CONNECTION=database pero no hay ningún worker (`queue:work`) corriendo
// en producción, así que un Notification encolado se queda parado para
// siempre en la tabla `jobs` y nunca llega a `notifications` — el alumno
// jamás ve la invitación. `via()` solo escribe en BD (sin mail), es una
// operación rápida: no hay motivo real para encolarla.
class TaqeInvitacionGrupo extends Notification
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
        $creador = $this->grupo->creador;

        return [
            'taqe_grupo_id' => $this->grupo->id,
            'grupo_nombre' => $this->grupo->nombre,
            'curso_nombre' => $this->grupo->curso->nombre,
            'invitado_por' => $creador ? $creador->apellidos.', '.$creador->nombres : 'Un compañero',
            'url' => route('taqe.index'),
        ];
    }
}
