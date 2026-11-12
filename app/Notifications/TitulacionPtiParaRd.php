<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Investigación inició el proceso de un estudiante o grupo (PTI registrado): a Secretaría le toca emitir la RD de
 * aprobación del PTI. Solo campanita (sin correo); abre la ficha del titular. Sin ShouldQueue: no hay worker.
 */
class TitulacionPtiParaRd extends Notification
{
    /** @param  array<int, string>  $integrantes  «Apellidos, Nombres (N° expediente)» */
    public function __construct(public User $titular, public string $titulo, public array $integrantes)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'alumno_nombre' => $this->titular->apellidos.', '.$this->titular->name,
            'persona_id' => $this->titular->id,
            'titulo' => Str::limit($this->titulo, 80),
            'integrantes' => $this->integrantes,
            'url' => route('titulacion.index', ['ver' => $this->titular->id, 'tab' => 'proceso']),
        ];
    }
}
