<?php

namespace App\Notifications;

use App\Models\Matricula;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Al alumno, cuando administración verifica el voucher de su matrícula
 * (`AdminController::verificarVoucherMatricula`). Solo in-app por ahora
 * (ver _notes/mobile-app-plan.md — sistema de notificaciones v1).
 */
class MatriculaVoucherVerificado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Matricula $matricula)
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
        $periodoNombre = $this->matricula->periodoActual?->nombre;

        return [
            'matricula_id' => $this->matricula->id,
            'periodo_nombre' => $periodoNombre,
            'mensaje' => $periodoNombre
                ? "Tu comprobante de matrícula del periodo {$periodoNombre} fue verificado."
                : 'Tu comprobante de matrícula fue verificado.',
        ];
    }
}
