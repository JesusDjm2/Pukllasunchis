<?php

namespace App\Notifications;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un estudiante solicita la carta de aceptación de la Institución Educativa: llega SOLO a la campanita
 * de Secretaría (sin correo) y abre los registros con la solicitud resaltada. Sin ShouldQueue: no hay
 * worker en producción.
 */
class TitulacionCartaSolicitada extends Notification
{
    public function __construct(public CartaAceptacion $carta, public User $solicitante)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'carta_id' => $this->carta->id,
            'alumno_nombre' => $this->solicitante->apellidos.', '.$this->solicitante->name,
            'institucion' => Str::limit($this->carta->institucion, 80),
            'persona_id' => $this->solicitante->id,
            // Abre los registros de solicitudes con esta resaltada.
            'url' => route('titulacion.cartas.index', ['resaltar' => $this->carta->lote]),
        ];
    }
}
