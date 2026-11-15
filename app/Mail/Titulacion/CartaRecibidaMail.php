<?php

namespace App\Mail\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Respuesta automática de Secretaría al estudiante que envía la solicitud de carta de aceptación:
 * recibida, respuesta en 3 días hábiles. Propiedades protected: si fueran public, irían solas a la vista.
 */
class CartaRecibidaMail extends Mailable
{
    public function __construct(protected CartaAceptacion $carta, protected User $persona) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(Remitente::direccion('secretaria'), Remitente::nombre('secretaria')),
            replyTo: [Remitente::direccion('secretaria')],
            subject: 'Solicitud de carta de aceptación recibida',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.carta-recibida',
            with: [
                'saludo' => AvisoVencimientoPtiMail::saludo($this->persona),
                'carta' => $this->carta,
                'mensaje' => $this->carta->mensajeRecepcion(),
                'enlace' => route('mi-titulacion.carta'),
            ],
        );
    }
}
