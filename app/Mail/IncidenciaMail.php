<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        $alumno = $this->incidencia->alumno;
        return new Envelope(
            subject: 'Nueva incidencia registrada — '
                .($alumno ? $alumno->apellidos.', '.$alumno->nombres : 'Alumno'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.incidencia',
            with: ['incidencia' => $this->incidencia],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
