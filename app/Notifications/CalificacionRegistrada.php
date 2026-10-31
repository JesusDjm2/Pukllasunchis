<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Al alumno, cuando se registra o actualiza una nota suya (Parcial 1/2 o
 * Promedio — ver `App\Observers\NotaParcialObserver`, que dispara esto sin
 * importar por cuál de los 8 flujos de `CalificacionController` haya
 * entrado la nota).
 */
class CalificacionRegistrada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $parcial,
        public ?int $cursoId,
        public string $cursoNombre,
    ) {
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
            'curso_id' => $this->cursoId,
            'curso_nombre' => $this->cursoNombre,
            'parcial' => $this->parcial,
            'mensaje' => "Se registró tu nota de {$this->parcial} en {$this->cursoNombre}.",
        ];
    }
}
