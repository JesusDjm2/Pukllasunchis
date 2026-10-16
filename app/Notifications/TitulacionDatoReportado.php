<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un egresado/alumno avisa desde "Mi proceso de titulación" que algún dato
 * suyo no coincide. Llega a la campanita de los gestores de titulación.
 * Sin ShouldQueue a propósito: en producción no hay worker de colas.
 */
class TitulacionDatoReportado extends Notification
{
    public function __construct(public User $persona, public string $mensaje)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'persona_id' => $this->persona->id,
            'alumno_nombre' => $this->persona->apellidos.', '.$this->persona->name,
            'resumen' => Str::limit($this->mensaje, 120),
            'mensaje' => $this->mensaje,
            // search por DNI deja a la persona en la 1ª página del listado; ver= abre su detalle.
            'url' => route('titulacion.index', array_filter(['search' => $this->persona->dni, 'ver' => $this->persona->id])),
        ];
    }
}
