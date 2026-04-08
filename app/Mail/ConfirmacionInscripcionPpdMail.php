<?php

namespace App\Mail;

use App\Models\AdminPpd;
use App\Models\PostulantesPpd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacionInscripcionPpdMail extends Mailable
{
    use Queueable, SerializesModels;

    public $postulante;
    public $adminPpd;

    public function __construct(PostulantesPpd $postulante, AdminPpd $adminPpd)
    {
        $this->postulante = $postulante;
        $this->adminPpd = $adminPpd;
    }

    public function build()
    {
        $pdf = Pdf::loadView('emails.constanciappdpdf', [
            'postulante' => $this->postulante,
            'adminPpd' => $this->adminPpd,
        ]);

        return $this->subject('Constancia de Inscripción - Profesionalización Docente')
            ->view('emails.confirmacion-inscripcion-ppd')
            ->with([
                'postulante' => $this->postulante,
                'adminPpd' => $this->adminPpd,
                'firmaDirector' => 'https://eesppukllasunchis.edu.pe/img/firma-director.png',
            ])
            ->attachData(
                $pdf->output(),
                'Constancia_'.$this->postulante->constancia.'.pdf'
            );
    }
}
