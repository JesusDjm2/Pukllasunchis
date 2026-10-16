<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mensaje que un docente (desde su curso) o un tutor (desde su ciclo) escribe a sus alumnos.
 * Sale de la cuenta del sistema —no tenemos acceso al buzón de cada docente— pero con el
 * nombre del docente como remitente y su correo como Reply-To: si el alumno responde, la
 * respuesta le llega directamente al docente.
 *
 * Propiedades protected a propósito: las públicas de un Mailable se pasan solas a la vista
 * y pisarían las variables del mismo nombre (ver NotificacionRegistro).
 */
class MensajeAlumnoMail extends Mailable
{
    public function __construct(
        protected string $asunto,
        protected string $mensaje,
        protected string $remitenteNombre,
        protected ?string $remitenteCorreo,
        protected string $contexto,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), $this->remitenteNombre.' · EESP Pukllasunchis'),
            replyTo: $this->remitenteCorreo ? [new Address($this->remitenteCorreo, $this->remitenteNombre)] : [],
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.mensaje-alumno',
            with: [
                'asunto' => $this->asunto,
                'mensaje' => $this->mensaje,
                'remitenteNombre' => $this->remitenteNombre,
                'remitenteCorreo' => $this->remitenteCorreo,
                'contexto' => $this->contexto,
            ],
        );
    }
}
