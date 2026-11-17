<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Secretaría habilitó al alumno (excepción) para solicitar la aprobación de su PTI antes de lo que marca su ciclo:
 * aviso en su campanita, abre el formulario. Sin ShouldQueue: no hay worker en producción.
 */
class TitulacionPtiHabilitada extends Notification
{
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return ['url' => route('mi-titulacion.solicitud-pti')];
    }
}
