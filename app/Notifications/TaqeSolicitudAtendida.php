<?php

namespace App\Notifications;

use App\Models\TaqeSolicitudAyuda;
use Illuminate\Notifications\Notification;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class TaqeSolicitudAtendida extends Notification
{
    public function __construct(public TaqeSolicitudAyuda $solicitud)
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
            'grupo_nombre' => $this->solicitud->grupo_nombre,
            'motivo' => $this->solicitud->motivoLabel(),
            'url' => route('taqe.index'),
        ];
    }
}
