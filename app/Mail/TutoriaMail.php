<?php

namespace App\Mail;

use App\Models\Tutoria;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutoriaMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Tutoria $tutoria) {}

    public function envelope(): Envelope
    {
        $alumno = $this->tutoria->alumno;
        return new Envelope(
            subject: '['.$this->tutoria->urgencia.'] Nueva solicitud de tutoría — '
                .($alumno ? $alumno->apellidos.', '.$alumno->nombres : 'Alumno'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tutoria',
            with: ['tutoria' => $this->tutoria],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
