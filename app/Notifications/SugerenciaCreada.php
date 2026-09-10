<?php

namespace App\Notifications;

use App\Mail\SugerenciaMail;
use App\Models\Sugerencia;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SugerenciaCreada extends Notification
{
    public function __construct(public Sugerencia $sugerencia)
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

        if (! empty($notifiable->correoNotificacion())) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): SugerenciaMail
    {
        return (new SugerenciaMail($this->sugerencia))->to($notifiable->correoNotificacion());
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable): array
    {
        $sugerencia = $this->sugerencia;
        $alumno = $sugerencia->alumno;

        $url = $notifiable->hasRole(['admin', 'super-admin'])
            ? route('admin.seguimiento', ['tab' => 'sugerencias'])
            : route('tutor.dashboard', ['tab' => 'sugerencias', 'sugerencia' => $sugerencia->id]);

        return [
            'sugerencia_id' => $sugerencia->id,
            'ciclo_id' => $sugerencia->ciclo_id,
            'alumno_nombre' => $alumno ? $alumno->apellidos.', '.$alumno->nombres : $sugerencia->nombre_alumno,
            'resumen' => Str::limit($sugerencia->mensaje, 120),
            'url' => $url,
        ];
    }
}
