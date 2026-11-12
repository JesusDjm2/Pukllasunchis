<?php

namespace App\Notifications;

use App\Models\Titulacion\Solicitud;
use Illuminate\Notifications\Notification;

/**
 * Resultado de la revisión de originalidad (o su observación) para cada tesista: campanita, abre su solicitud en
 * la intranet. Sin ShouldQueue: no hay worker en producción.
 */
class TitulacionOriginalidadAtendida extends Notification
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
            'etapa' => Solicitud::ETAPAS[$this->solicitud->etapa()] ?? '',
            'similitud' => $this->solicitud->similitud(),
            'apto' => $this->solicitud->esApto(),
            'url' => route('mi-titulacion.originalidad'),
        ];
    }
}
