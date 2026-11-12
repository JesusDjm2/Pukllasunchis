<?php

namespace App\Notifications;

use App\Models\Titulacion\Solicitud;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Un tesista pide desde su intranet la revisión de originalidad (Turnitin) de su TI o su Tesis: campanita de
 * Secretaría e Investigación (sin correo); abre la bandeja con la solicitud resaltada. Sin ShouldQueue.
 */
class TitulacionOriginalidadSolicitada extends Notification
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
            'etapa' => Solicitud::ETAPAS[$this->solicitud->etapa()] ?? '',
            'persona_id' => $this->solicitante->id,
            'url' => route('titulacion.solicitudes-originalidad.index', ['resaltar' => $this->solicitud->id]),
        ];
    }
}
