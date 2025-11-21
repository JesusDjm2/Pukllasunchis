<?php

namespace App\Mail;

use App\Models\AdminFid;
use App\Models\PostulantesRegular;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacionInscripcionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $postulante;

    public $adminFid;

    public function __construct(PostulantesRegular $postulantesRegular, AdminFid $adminFid)
    {
        $this->postulante = $postulantesRegular;
        $this->adminFid = $adminFid;
    }

    public function build()
    {
        $pdf = Pdf::loadView('emails.constanciafidpdf', ['postulante' => $this->postulante, 'adminFid' => $this->adminFid]);
        return $this->subject('Constancia de Inscripción - FID')
            ->view('emails.confirmacion-inscripcion')
            ->with([
                'postulante' => $this->postulante,
                'adminFid' => $this->adminFid,
                'firmaDirector' => 'https://eesppukllasunchis.edu.pe/img/firma-director.png',
            ])
            ->attachData($pdf->output(), 'Constancia_'.$this->postulante->constancia.'.pdf');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación Inscripción - FID',
        );
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.confirmacion-inscripcion',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
