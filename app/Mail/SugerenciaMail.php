<?php

namespace App\Mail;

use App\Models\Sugerencia;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SugerenciaMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Sugerencia $sugerencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva sugerencia recibida — Ciclo '.($this->sugerencia->ciclo?->nombre ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.sugerencia',
            with: ['sugerencia' => $this->sugerencia],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
