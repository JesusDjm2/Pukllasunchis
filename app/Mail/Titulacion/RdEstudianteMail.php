<?php

namespace App\Mail\Titulacion;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correo de Secretaría al estudiante con una de sus RD: el PDF adjunto (guardado por el sistema) o
 * su enlace (si la RD es un enlace externo). El gestor edita el asunto y el mensaje, que es HTML
 * ya limpio (MensajesRd::limpiar).
 * Sin ShouldQueue: no hay worker de colas en prod.
 * Propiedades protected: si fueran public, Laravel las pasaría solas a la vista.
 */
class RdEstudianteMail extends Mailable
{
    public function __construct(
        protected string $asunto,
        protected string $mensaje,
        protected string $saludo,
        protected ?string $rutaPdf = null,
        protected ?string $enlaceRd = null,
        protected string $nombrePdf = 'RD.pdf',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(Remitente::direccion('secretaria'), Remitente::nombre('secretaria')),
            replyTo: [Remitente::direccion('secretaria')],
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.rd-estudiante',
            text: 'mail.titulacion.rd-estudiante-texto',
            with: [
                'saludo' => $this->saludo, 'mensaje' => $this->mensaje, 'mensajeTexto' => \App\Services\Titulacion\MensajesRd::aTexto($this->mensaje),
                'enlaceRd' => $this->enlaceRd, 'adjunta' => (bool) $this->rutaPdf, 'nombrePdf' => $this->nombrePdf, 'firma' => Remitente::firma('secretaria'),
            ],
        );
    }

    public function attachments(): array
    {
        return $this->rutaPdf
            ? [Attachment::fromPath($this->rutaPdf)->as($this->nombrePdf)->withMime('application/pdf')]
            : [];
    }
}
