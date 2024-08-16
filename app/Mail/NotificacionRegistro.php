<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionRegistro extends Mailable
{
    use Queueable, SerializesModels;
    public $alumno;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($alumno)
    {
        $this->alumno = $alumno;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Registro Completado')
                    ->view('email.registro')
                    ->with([
                        'nombres' => $this->alumno->nombres,
                        'apellidos' => $this->alumno->apellidos,
                        'dni' => $this->alumno->dni,
                        'num_comprobante' => $this->alumno->num_comprobante,
                        'programa' => $this->alumno->programa->nombre,
                        'ciclo' => $this->alumno->ciclo ? $this->alumno->ciclo->nombre : 'N/A',
                    ]);
    }

}
