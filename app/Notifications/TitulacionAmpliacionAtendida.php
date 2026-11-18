<?php

namespace App\Notifications;

use App\Models\Titulacion\Solicitud;
use Illuminate\Notifications\Notification;

/**
 * Secretaría atendió u observó la solicitud de ampliación de PTI: aviso a la campanita
 * del estudiante solicitante (y compañeros de grupo si fue aprobada).
 * Sin ShouldQueue: no hay worker en producción.
 */
class TitulacionAmpliacionAtendida extends Notification
{
    public function __construct(public Solicitud $solicitud)
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
            'estado' => $this->solicitud->estado,
            'numero_rd' => $this->solicitud->datos['numero_rd'] ?? null,
            'observacion' => $this->solicitud->observacion,
            'vencimiento_ampliado' => $this->solicitud->datos['vencimiento_ampliado'] ?? null,
            'url' => route('mi-titulacion.ampliacion-pti'),
        ];
    }
}
