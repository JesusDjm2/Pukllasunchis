<?php

namespace App\Mail\Titulacion;

use App\Models\User;
use App\Services\Titulacion\VigenciaPti;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correos de la Unidad de Investigación avisando que el PTI está por vencer:
 * 'recordatorio' (al año de inscrito) y 'urgente' (pocos meses antes).
 * Sin ShouldQueue: no hay worker de colas en producción.
 *
 * Las propiedades son protected a propósito: si fueran public, Laravel las
 * pasaría solas a la vista y pisarían las variables de with().
 */
class AvisoVencimientoPtiMail extends Mailable
{
    public const REMITENTE = 'Unidad de Investigación — EESP Pukllasunchis';

    public function __construct(
        protected User $persona,
        protected VigenciaPti $vigencia,
        protected string $tipo,
    ) {}

    public function envelope(): Envelope
    {
        $vence = VigenciaPti::mesAnio($this->vigencia->vencimiento);

        return new Envelope(
            from: new Address(Remitente::direccion('investigacion'), self::REMITENTE),
            replyTo: [Remitente::direccion('investigacion')],
            subject: $this->tipo === 'urgente'
                ? "URGENTE: su Plan de Trabajo de Investigación (PTI) vence a finales de {$vence}"
                : 'Recordatorio: vigencia de su Plan de Trabajo de Investigación (PTI)',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.titulacion.aviso-pti-'.$this->tipo,
            with: [
                'saludo' => self::saludo($this->persona),
                'inscripcion' => VigenciaPti::mesAnio($this->vigencia->inscripcion),
                'vence' => VigenciaPti::mesAnio($this->vigencia->vencimiento),
                'tiempoRestante' => $this->vigencia->tiempoRestanteTexto(),
                'whatsapp' => '972577170',
            ],
        );
    }

    /**
     * "Estimada egresada María Quispe," / "Estimado estudiante Juan Huamán," /
     * "Estimado(a) egresado(a) ..." cuando la ficha no registra el género.
     */
    public static function saludo(User $persona): string
    {
        $genero = mb_strtolower((string) ($persona->alumno?->genero ?: $persona->genero));
        $f = str_starts_with($genero, 'f');
        $m = str_starts_with($genero, 'm');
        $egresado = $persona->condicion === 'Egresado';

        $trato = $f ? 'Estimada' : ($m ? 'Estimado' : 'Estimado(a)');
        $rol = $egresado
            ? ($f ? 'egresada' : ($m ? 'egresado' : 'egresado(a)'))
            : 'estudiante';
        $nombre = trim(mb_convert_case(trim((string) $persona->name), MB_CASE_TITLE).' '.mb_convert_case(trim((string) $persona->apellidos), MB_CASE_TITLE));

        return "{$trato} {$rol} {$nombre}";
    }
}
