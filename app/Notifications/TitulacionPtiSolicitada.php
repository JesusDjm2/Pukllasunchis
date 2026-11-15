<?php

namespace App\Notifications;

use App\Models\Titulacion\Solicitud;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un estudiante envía desde su intranet la solicitud de aprobación de su PTI: llega SOLO a la campanita del
 * Área de Investigación (sin correo) y abre la bandeja con la solicitud resaltada. Sin ShouldQueue: no hay
 * worker en producción.
 */
class TitulacionPtiSolicitada extends Notification
{
    public function __construct(public Solicitud $solicitud, public User $solicitante)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'alumno_nombre' => $this->solicitante->apellidos.', '.$this->solicitante->name,
            'titulo' => Str::limit((string) $this->solicitud->titulo, 80),
            'persona_id' => $this->solicitante->id,
            'url' => route('titulacion.solicitudes-pti.index', ['resaltar' => $this->solicitud->id]),
        ];
    }
}
