<?php

namespace App\Notifications;

use App\Models\Tutoria;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class TutoriaAtendida extends Notification
{
    public function __construct(public Tutoria $tutoria)
    {
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
        $tutoria = $this->tutoria;

        return [
            'tutoria_id' => $tutoria->id,
            'ciclo_id' => $tutoria->ciclo_id,
            'atendido_por' => optional($tutoria->atendidoPor)->name,
            'resumen' => Str::limit((string) $tutoria->motivo, 120),
            'notas_atencion' => $tutoria->notas_atencion,
            'url' => route('alumnos.index', ['tab' => 'solicitudes']),
        ];
    }
}
