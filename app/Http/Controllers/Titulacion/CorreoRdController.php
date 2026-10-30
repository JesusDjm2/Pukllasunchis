<?php

namespace App\Http\Controllers\Titulacion;

use App\Mail\Titulacion\RdEstudianteMail;
use App\Mail\Titulacion\Remitente;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\Tesis;
use App\Services\Titulacion\ArchivosRd;
use App\Services\Titulacion\MensajesRd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Secretaría envía una RD por correo a los estudiantes que comprende: cada uno recibe su propio
 * correo con el PDF adjunto (si la RD es un enlace externo, va el enlace).
 */
class CorreoRdController extends TitulacionController
{
    public function enviar(Request $request, Resolucion $resolucion)
    {
        $data = $request->validate([
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*' => 'integer',
            'asunto' => 'required|string|max:200',
            'mensaje' => 'required|string|max:20000',
            'tab' => 'nullable|in:proceso,resoluciones,tramites',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'pdf.mimes' => 'El documento debe ser un archivo PDF.',
            'pdf.max' => 'El PDF no debe pesar más de 10 MB.',
            'destinatarios.required' => 'Elija al menos un estudiante.',
            'asunto.required' => 'Escriba el asunto del correo.',
            'mensaje.required' => 'Escriba el mensaje.',
        ]);
        $mensaje = MensajesRd::limpiar($data['mensaje']);
        if (trim(strip_tags($mensaje)) === '') {
            return back()->withErrors(['mensaje' => 'Escriba el mensaje.'])->withInput();
        }
        // Solo quienes comprende esta RD, aunque se altere el formulario.
        $elegidos = collect($data['destinatarios'])->map(fn ($id) => (int) $id);
        $destinos = MensajesRd::destinatarios($resolucion)->filter(fn ($r) => $elegidos->contains($r->seguimiento->user_id));

        // PDF subido al enviar: se adjunta y queda guardado como el documento de la RD de quienes lo reciben.
        $subido = $request->hasFile('pdf') ? ArchivosRd::guardarSubido($request->file('pdf')) : null;

        [$enviados, $fallidos] = [[], []];
        foreach ($destinos as $rd) {
            $u = $rd->seguimiento->user;
            $nombre = trim(mb_convert_case(trim((string) $u->name), MB_CASE_TITLE).' '.mb_convert_case(trim((string) $u->apellidos), MB_CASE_TITLE));
            if (! filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                $fallidos[] = $nombre.' (sin correo registrado)';
                continue;
            }
            $documento = $subido ?: MensajesRd::documento($rd, $resolucion);
            $archivo = ArchivosRd::nombreDesdeUrl($documento);
            $ruta = $archivo ? ArchivosRd::ruta($archivo) : null;
            if (! $documento || ($archivo && ! $ruta)) {
                $fallidos[] = $nombre.' (la RD no tiene documento: suba el PDF al enviarla)';
                continue;
            }
            try {
                Mail::mailer(Remitente::mailer('secretaria'))->to($u->email)->send(new RdEstudianteMail(
                    $data['asunto'], $mensaje, \App\Mail\Titulacion\AvisoVencimientoPtiMail::saludo($u), $ruta, $archivo ? null : $documento, MensajesRd::nombreArchivo($rd),
                ));
                $rd->update(['correo_enviado_en' => now()] + ($subido ? ['archivo' => $subido] : []));
                if ($rd->tipo === 'Tesis') {
                    Tesis::where('seguimiento_id', $rd->seguimiento_id)->update(['correo_rd_tesis_enviado_en' => now()]);
                }
                $enviados[] = $nombre;
            } catch (\Throwable $e) {
                report($e);
                $fallidos[] = $nombre.' (no se pudo enviar)';
            }
        }

        $texto = $enviados ? 'RD enviada por correo a '.implode(', ', $enviados).'.' : 'No se envió ningún correo.';
        if ($fallidos) {
            $texto .= ' Sin enviar: '.implode('; ', $fallidos).'.';
        }

        return $this->volverAlAcordeon($resolucion->seguimiento->user_id, $texto, $data['tab'] ?? 'proceso', $fallidos ? ($enviados ? 'warning' : 'error') : 'success');
    }
}
