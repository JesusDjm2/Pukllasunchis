<?php

namespace App\Notifications;

use App\Mail\TutoriaMail;
use App\Models\Tutoria;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TutoriaCreada extends Notification
{
    public function __construct(public Tutoria $tutoria)
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

    public function toMail($notifiable): TutoriaMail
    {
        return (new TutoriaMail($this->tutoria))->to($notifiable->correoNotificacion());
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable): array
    {
        $tutoria = $this->tutoria;
        $alumno = $tutoria->alumno;

        $url = $notifiable->hasRole(['admin', 'super-admin'])
            ? route('admin.seguimiento', ['tab' => 'tutorias'])
            : route('tutor.dashboard', ['tab' => 'tutorias', 'tutoria' => $tutoria->id]);

        return [
            'tutoria_id' => $tutoria->id,
            'ciclo_id' => $tutoria->ciclo_id,
            'alumno_nombre' => $alumno ? $alumno->apellidos.', '.$alumno->nombres : $tutoria->nombre_alumno,
            'urgencia' => $tutoria->urgencia,
            'resumen' => Str::limit($tutoria->motivo, 120),
            'url' => $url,
        ];
    }
}
