<?php

namespace App\Notifications;

use App\Models\Titulacion\Solicitud;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un estudiante solicita prórroga de vigencia de su PTI: llega a la campanita de Secretaría
 * General (sin correo) y abre la bandeja de ampliaciones con la solicitud resaltada.
 * Sin ShouldQueue: no hay worker en producción.
 */
class TitulacionAmpliacionSolicitada extends Notification
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
            'meses' => $this->solicitud->datos['meses_solicitados'] ?? 6,
            'persona_id' => $this->solicitante->id,
            'url' => route('titulacion.solicitudes-ampliacion.index', ['resaltar' => $this->solicitud->id]),
        ];
    }
}
