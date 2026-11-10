<?php

namespace App\Mail\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Al Área de Investigación: un estudiante solicitó la carta de aceptación de la IE. Lleva los datos
 * del formulario y el comprobante de pago adjunto; «Responder» va al correo institucional del estudiante.
 * Sale por la cuenta del sistema. Propiedades protected: si fueran public, irían solas a la vista.
 */
class CartaSolicitadaMail extends Mailable
{
    public function __construct(protected CartaAceptacion $carta, protected User $solicitante) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->carta->correo, trim($this->solicitante->name.' '.$this->solicitante->apellidos))],
            subject: 'Solicitud de carta de aceptación — '.trim($this->solicitante->apellidos.', '.$this->solicitante->name),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.carta-solicitada',
            with: [
                'carta' => $this->carta,
                'formacion' => CartaAceptacion::PROGRAMAS_FORMACION[$this->carta->programa_formacion] ?? $this->carta->programa_formacion,
                'enlaceFicha' => route('titulacion.index', ['ver' => $this->solicitante->id, 'tab' => 'proceso', 'paso' => 'pti_carta']),
            ],
        );
    }

    public function attachments(): array
    {
        $ruta = CartaAceptacion::ruta($this->carta->comprobante);

        return $ruta ? [Attachment::fromPath($ruta)->as('Comprobante de pago.'.pathinfo($ruta, PATHINFO_EXTENSION))] : [];
    }
}
