<?php

namespace App\Notifications;

use App\Mail\IncidenciaMail;
use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IncidenciaCreada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Incidencia $incidencia)
    {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        // El correo a administración ya se envía aparte (copia directa a
        // NOTIF_EMAIL_ADMIN desde el controlador), así que a admin/super-admin
        // solo les generamos la alerta en el sistema, sin duplicar el correo.
        if ($notifiable->hasRole(['admin', 'super-admin'])) {
            return $channels;
        }

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): IncidenciaMail
    {
        return (new IncidenciaMail($this->incidencia))->to($notifiable->email);
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable): array
    {
        $incidencia = $this->incidencia;
        $alumno = $incidencia->alumno;
        $reporter = $incidencia->docente
            ? ($incidencia->docente->user->apellidos.', '.$incidencia->docente->user->name)
            : ($incidencia->nombre_docente ?? 'Desconocido');

        $url = $notifiable->hasRole(['admin', 'super-admin'])
            ? route('admin.seguimiento', ['tab' => 'incidencias'])
            : route('tutor.dashboard', ['tab' => 'incidencias', 'incidencia' => $incidencia->id]);

        return [
            'incidencia_id' => $incidencia->id,
            'ciclo_id' => $incidencia->ciclo_id,
            'alumno_nombre' => $alumno ? $alumno->apellidos.', '.$alumno->nombres : null,
            'reportado_por' => $reporter,
            'fecha' => optional($incidencia->fecha)->format('d/m/Y'),
            'resumen' => Str::limit($incidencia->reporte, 120),
            'url' => $url,
        ];
    }
}
