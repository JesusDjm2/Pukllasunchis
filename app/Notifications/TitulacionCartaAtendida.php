<?php

namespace App\Notifications;

use App\Models\Titulacion\CartaAceptacion;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * El Área de Investigación subió la carta de aceptación o la observó: aviso a cada integrante del
 * grupo (lleva a la vista de la solicitud). Sin ShouldQueue: no hay worker en producción.
 */
class TitulacionCartaAtendida extends Notification
{
    public function __construct(public CartaAceptacion $carta)
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
            'estado' => $this->carta->estado,
            'institucion' => Str::limit($this->carta->institucion, 80),
            'url' => route('mi-titulacion.carta'),
        ];
    }
}
