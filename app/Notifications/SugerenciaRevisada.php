<?php

namespace App\Notifications;

use App\Models\Sugerencia;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

// Sin ShouldQueue a propósito: no hay worker de colas corriendo en
// producción (ver nota en TaqeInvitacionGrupo). Solo escribe en BD, es rápida.
class SugerenciaRevisada extends Notification
{
    public function __construct(public Sugerencia $sugerencia)
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
        $sugerencia = $this->sugerencia;

        return [
            'sugerencia_id' => $sugerencia->id,
            'ciclo_id' => $sugerencia->ciclo_id,
            'atendido_por' => optional($sugerencia->atendidoPor)->name,
            'resumen' => Str::limit($sugerencia->mensaje, 120),
            'notas_atencion' => $sugerencia->notas_atencion,
            'url' => route('alumnos.index', ['tab' => 'solicitudes']),
        ];
    }
}
