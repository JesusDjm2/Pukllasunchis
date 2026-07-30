<?php

namespace App\Mail;

use App\Models\Reclamo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReclamoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reclamo $reclamo, public bool $paraInstitucion = false)
    {
    }

    public function build()
    {
        $pdf = Pdf::loadView('pdf.reclamo', ['reclamo' => $this->reclamo]);

        $vista = $this->paraInstitucion ? 'mail.reclamo-institucion' : 'mail.reclamo';
        $asunto = $this->paraInstitucion
            ? 'Nuevo '.strtolower($this->reclamo->tipo_reclamacion).' recibido — N° '.$this->reclamo->numero_reclamo
            : 'Constancia de '.$this->reclamo->tipo_reclamacion.' N° '.$this->reclamo->numero_reclamo;

        $mail = $this
            ->subject($asunto)
            ->markdown($vista, ['reclamo' => $this->reclamo])
            ->attachData($pdf->output(), 'Constancia_Reclamo_'.$this->reclamo->numero_reclamo.'.pdf');

        if ($this->reclamo->adjunto) {
            $ruta = public_path('reclamos/'.$this->reclamo->adjunto);
            if (file_exists($ruta)) {
                $mail->attach($ruta, [
                    'as' => 'Evidencia_'.$this->reclamo->numero_reclamo.'.'.pathinfo($ruta, PATHINFO_EXTENSION),
                ]);
            }
        }

        return $mail;
    }
}
