<?php

namespace App\Services;

use App\Mail\MensajeAlumnoMail;
use App\Support\ContactoAlumno;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envío de un mensaje del docente/tutor a uno o varios de SUS alumnos. Quien llama pasa la
 * lista de alumnos permitidos (calculada en el servidor); aquí solo se envía a los que el
 * formulario eligió DENTRO de esa lista, así un id inventado en el POST no llega a nadie.
 *
 * Síncrono a propósito: en producción no hay worker de colas.
 */
class MensajeAlumnos
{
    /** Gmail rechaza mensajes con demasiados destinatarios: se parte en tandas. */
    private const POR_TANDA = 80;

    /** Valida el formulario y envía. Devuelve el mensaje flash para la vista. */
    public static function desdeFormulario(Request $request, Collection $permitidos, string $remitenteNombre, ?string $remitenteCorreo, string $contexto): array
    {
        $datos = $request->validate([
            'asunto' => 'required|string|max:150',
            'mensaje' => 'required|string|max:5000',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*' => 'string|max:30',
        ], [
            'destinatarios.required' => 'Elige al menos un alumno.',
            'asunto.required' => 'Escribe el asunto del mensaje.',
            'mensaje.required' => 'Escribe el mensaje.',
        ]);

        /** @var Collection<int, ContactoAlumno> $elegidos */
        $elegidos = $permitidos->filter(fn (ContactoAlumno $c) => in_array($c->clave, $datos['destinatarios'], true));
        if ($elegidos->isEmpty()) {
            return ['error', 'Los alumnos elegidos no pertenecen a esta lista.'];
        }
        $correos = $elegidos->pluck('correo')->filter()->map(fn ($c) => trim(strtolower($c)))
            ->filter(fn ($c) => filter_var($c, FILTER_VALIDATE_EMAIL))->unique()->values();

        if ($correos->isEmpty()) {
            return ['error', 'Ninguno de los alumnos elegidos tiene un correo válido registrado.'];
        }

        try {
            self::enviar($correos, $datos['asunto'], $datos['mensaje'], $remitenteNombre, $remitenteCorreo, $contexto);
        } catch (\Throwable $e) {
            Log::error('MensajeAlumnos: fallo al enviar', ['remitente' => $remitenteCorreo, 'error' => $e->getMessage()]);

            return ['error', 'No se pudo enviar el correo. Inténtalo de nuevo en unos minutos; si persiste, avisa a soporte.'];
        }

        Log::info('MensajeAlumnos: enviado', [
            'remitente' => $remitenteCorreo, 'contexto' => $contexto,
            'destinatarios' => $correos->count(), 'asunto' => $datos['asunto'],
        ]);

        $sinCorreo = $elegidos->count() - $correos->count();

        return ['success', $correos->count() === 1
            ? 'Mensaje enviado a '.$elegidos->first()->nombre.'.'
            : 'Mensaje enviado a '.$correos->count().' alumnos.'
                .($sinCorreo > 0 ? ' ('.$sinCorreo.' sin correo válido no lo recibieron.)' : '')];
    }

    /**
     * Un destinatario: va directo a él. Varios: van en copia oculta (nadie ve los correos de
     * los demás) y el docente queda como destinatario visible, así le queda una copia.
     */
    public static function enviar(Collection $correos, string $asunto, string $mensaje, string $remitenteNombre, ?string $remitenteCorreo, string $contexto): void
    {
        $mail = fn () => new MensajeAlumnoMail($asunto, $mensaje, $remitenteNombre, $remitenteCorreo, $contexto);

        if ($correos->count() === 1) {
            Mail::to($correos->first())->send($mail());

            return;
        }

        foreach ($correos->chunk(self::POR_TANDA) as $tanda) {
            Mail::to($remitenteCorreo ?: config('mail.from.address'))
                ->bcc($tanda->values()->all())
                ->send($mail());
        }
    }
}
