<?php

namespace App\Mail;

use App\Models\Alumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NotificacionRegistro extends Mailable
{
    use Queueable, SerializesModels;
    public $alumno;

    // Protegidas a propósito: Mailable expone automáticamente las propiedades PÚBLICAS a la vista
    // por su nombre, y pisaría los strings que se pasan explícitamente vía with() con el valor
    // crudo de la propiedad (p.ej. el objeto completo, serializado como JSON al hacer cast a string).
    protected $periodo;

    protected $paraAlumno;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\PeriodoActual|\App\Models\PeriodoActualPpd|null  $periodo
     * @param  bool  $paraAlumno  true si este envío va dirigido al propio alumno (cambia el saludo inicial)
     * @return void
     */
    public function __construct($alumno, $periodo = null, $paraAlumno = false)
    {
        $this->alumno = $alumno;
        $this->periodo = $periodo;
        $this->paraAlumno = $paraAlumno;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // El voucher vigente vive en la matrícula del período (Matricula/MatriculaPpd), no en el
        // campo legado Alumno::num_comprobante (que es del registro inicial, no por período).
        $matricula = $this->periodo ? $this->alumno->matriculaEnPeriodo($this->periodo->id) : null;

        if ($this->paraAlumno) {
            $tituloMensaje = '¡Completaste tu matrícula con éxito!';
            $subtituloMensaje = 'Tu matrícula ha quedado registrada correctamente en el sistema. Aquí tienes el resumen de tus datos:';
        } else {
            $tituloMensaje = 'Nueva matrícula completada';
            $subtituloMensaje = 'Un(a) estudiante acaba de completar su ficha de matrícula en el sistema. Estos son los detalles:';
        }

        $mail = $this->subject('Registro Completado')
                    ->view('email.registro')
                    ->with([
                        'nombres' => $this->alumno->nombres,
                        'apellidos' => $this->alumno->apellidos,
                        'dni' => $this->alumno->dni,
                        'num_comprobante' => $matricula?->comprobante ?? $this->alumno->num_comprobante,
                        'programa' => $this->alumno->programa->nombre,
                        'ciclo' => $this->alumno->ciclo ? $this->alumno->ciclo->nombre : 'N/A',
                        'periodo' => $this->periodo?->nombre ?? 'N/A',
                        'tituloMensaje' => $tituloMensaje,
                        'subtituloMensaje' => $subtituloMensaje,
                    ]);

        // Al alumno, además del correo, se le adjunta su Ficha de matrícula en PDF (mismo generador
        // que usa el botón "Descargar PDF" de la vista de ficha). Solo existe esta plantilla para
        // FID (Alumno) — PPD no tiene un equivalente todavía.
        if ($this->paraAlumno && $this->alumno instanceof Alumno) {
            $this->alumno->loadMissing(['ciclo.cursos', 'cursos', 'programa']);

            $pdf = Pdf::loadView('alumnos.vistasAlumnos.pdf', [
                'alumno' => $this->alumno,
                'periodoActual' => $this->periodo,
            ]);

            $nombreArchivo = 'Ficha_matricula_'.Str::slug($this->alumno->apellidos.'_'.$this->alumno->nombres).'.pdf';
            $mail->attachData($pdf->output(), $nombreArchivo, ['mime' => 'application/pdf']);
        }

        return $mail;
    }

}
