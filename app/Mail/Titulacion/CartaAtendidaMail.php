<?php

namespace App\Mail\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use App\Services\Titulacion\CartaPresentacion;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Al estudiante: su carta de aceptación ya está lista (va adjunta) o fue observada (con el motivo).
 * Propiedades protected: si fueran public, irían solas a la vista.
 */
class CartaAtendidaMail extends Mailable
{
    public function __construct(protected CartaAceptacion $carta, protected User $persona) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(Remitente::direccion('secretaria'), Remitente::nombre('secretaria')),
            replyTo: [Remitente::direccion('secretaria')],
            subject: $this->carta->estado === 'emitida'
                ? 'Tu carta de aceptación fue aprobada'
                : 'Tu solicitud de carta de aceptación fue observada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.carta-atendida',
            with: [
                'saludo' => AvisoVencimientoPtiMail::saludo($this->persona),
                'carta' => $this->carta,
                'enlace' => route('mi-titulacion.carta'),
            ],
        );
    }

    /** Aprobada: la Carta de Presentación (Word, llena) y el modelo de Carta de Aceptación (Word). */
    public function attachments(): array
    {
        if ($this->carta->estado !== 'emitida') {
            return [];
        }
        $adjuntos = [];
        if ($ruta = CartaAceptacion::ruta($this->carta->carta)) {
            $esWord = str_ends_with($ruta, '.docx');
            $adjuntos[] = Attachment::fromPath($ruta)
                ->as($esWord ? CartaPresentacion::nombreArchivo($this->carta->numero_carta, $this->carta->atendido_en) : 'Carta.pdf')
                ->withMime($esWord ? CartaPresentacion::TIPO_DOCX : 'application/pdf');
        }
        $adjuntos[] = Attachment::fromPath(resource_path(CartaPresentacion::MODELO_ACEPTACION))
            ->as('Modelo de Carta de Aceptación.docx')->withMime(CartaPresentacion::TIPO_DOCX);

        return $adjuntos;
    }
}
