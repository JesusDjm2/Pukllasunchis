<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudInformesMail extends Mailable
{
    use SerializesModels;

    public function __construct(public array $datos) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de informes — '.$this->datos['programa_texto'].' — '.$this->datos['nombre'],
            replyTo: [new Address($this->datos['email'], $this->datos['nombre'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.solicitud-informes',
            with: ['datos' => $this->datos, 'whatsapp' => $this->enlaceWhatsapp()],
        );
    }

    // Sin prefijo internacional se asume Perú (+51).
    private function enlaceWhatsapp(): string
    {
        $tel = trim($this->datos['telefono']);
        $numero = preg_replace('/\D/', '', $tel);
        if (! str_starts_with($tel, '+') && strlen($numero) === 9) {
            $numero = '51'.$numero;
        }

        return 'https://wa.me/'.$numero;
    }

    public function attachments(): array
    {
        return [];
    }
}
