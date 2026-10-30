<?php

namespace App\Mail\Titulacion;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correo de la Unidad de Investigación a un integrante del jurado de la exposición del TI:
 * el gestor escribe el asunto y el mensaje (la ficha propone uno con el grupo, el título, la
 * fecha y los enlaces). Uno por destinatario. Sin ShouldQueue: no hay worker de colas en prod.
 * Propiedades protected: si fueran public, Laravel las pasaría solas a la vista.
 */
class CorreoJuradoMail extends Mailable
{
    public function __construct(
        protected string $asunto,
        protected string $mensaje,
        protected string $nombreJurado,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(Remitente::direccion('investigacion'), AvisoVencimientoPtiMail::REMITENTE),
            replyTo: [Remitente::direccion('investigacion')],
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.correo-jurado',
            with: ['nombreJurado' => $this->nombreJurado, 'mensaje' => $this->mensaje],
        );
    }
}
