{{-- Formulario para que Secretaría envíe una RD por correo a los estudiantes que comprende.
     Espera: $res (Resolucion con documento), $idC (prefijo de los id), $tabVolver. --}}
@php
    use App\Mail\Titulacion\Remitente;
    use App\Services\Titulacion\ArchivosRd;
    use App\Services\Titulacion\MensajesRd;

    $destinosRd = MensajesRd::destinatarios($res);
    [$asuntoRd, $mensajeRd] = MensajesRd::sugerido($res);
    $hayDocumento = MensajesRd::documentoDisponible($res);
    $esPdfSistema = $hayDocumento && ArchivosRd::nombreDesdeUrl($res->archivo);
    $nombreDe = fn ($u) => trim(mb_convert_case(trim((string) $u->name), MB_CASE_TITLE).' '.mb_convert_case(trim((string) $u->apellidos), MB_CASE_TITLE));
@endphp
<form method="POST" action="{{ route('titulacion.rd.correo', $res->id) }}" class="tp-form mt-2" id="{{ $idC }}" enctype="multipart/form-data"
    data-confirm="Se enviará el correo con la RD a los estudiantes seleccionados." data-confirm-titulo="¿Enviar el correo?" data-confirm-boton="Sí, enviar">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.rd.correo'))>
    @csrf
    <input type="hidden" name="tab" value="{{ $tabVolver }}">
    <div class="small mb-2"><span class="tp-label d-inline">De:</span> {{ Remitente::nombre('secretaria') }} &lt;{{ Remitente::direccion('secretaria') }}&gt;</div>
    @unless (Remitente::cuentaConfigurada('secretaria'))
        <div class="tp-aviso tp-aviso--espera mb-2"><i class="fas fa-exclamation-triangle"></i><div>Falta conectar la cuenta de Secretaría: por ahora el estudiante verá como remitente la cuenta del sistema, aunque sus respuestas sí llegarán a {{ Remitente::direccion('secretaria') }}.</div></div>
    @endunless
    <span class="tp-label d-block">Para</span>
    @foreach ($destinosRd as $rdU)
        @php
            $u = $rdU->seguimiento->user;
            $correoU = filter_var($u->email, FILTER_VALIDATE_EMAIL) ? $u->email : null;
        @endphp
        <div class="custom-control custom-checkbox mb-1">
            <input type="checkbox" class="custom-control-input" id="{{ $idC }}-u{{ $u->id }}" name="destinatarios[]" value="{{ $u->id }}" @checked($correoU) @disabled(! $correoU)>
            <label class="custom-control-label small" for="{{ $idC }}-u{{ $u->id }}">
                {{ $nombreDe($u) }}
                <span class="text-muted">— {{ $correoU ?: 'sin correo registrado' }}{{ $rdU->correo_enviado_en ? ' · enviado el '.$rdU->correo_enviado_en->format('d/m/Y') : '' }}</span>
            </label>
        </div>
    @endforeach
    <div class="form-group mt-2 mb-2">
        <label class="tp-label" for="{{ $idC }}-asunto">Asunto</label>
        <input type="text" name="asunto" id="{{ $idC }}-asunto" class="form-control" maxlength="200" required value="{{ $asuntoRd }}">
    </div>
    <div class="form-group mb-2">
        <label class="tp-label" for="{{ $idC }}-mensaje">Mensaje</label>
        <textarea name="mensaje" id="{{ $idC }}-mensaje" class="form-control js-editor-rd" rows="9" required>{{ $mensajeRd }}</textarea>
    </div>
    <span class="tp-label d-block">Documento que se envía</span>
    @if ($esPdfSistema)
        <div class="tp-adjunto is-pdf"><i class="fas fa-file-pdf"></i><div>
            <strong>Se adjuntará el PDF de la RD</strong>
            <span>{{ MensajesRd::nombreArchivo($res) }} · <a href="{{ $res->archivo }}" target="_blank" rel="noopener">Revisar el PDF</a></span>
        </div></div>
    @elseif ($hayDocumento)
        <div class="tp-adjunto is-enlace"><i class="fas fa-link"></i><div>
            <strong>Se enviará el enlace a la RD, no un archivo adjunto</strong>
            <span>La RD está guardada como enlace: el correo llevará un botón para abrirla. Verifique que esté compartida. <a href="{{ $res->archivo }}" target="_blank" rel="noopener">Probar el enlace</a></span>
            <label class="tp-adjunto__subir">Para adjuntar el archivo en lugar del enlace, suba el PDF (opcional, reemplaza el enlace):
                <input type="file" name="pdf" accept="application/pdf" class="form-control-file form-control-sm">
            </label>
        </div></div>
    @else
        <div class="tp-adjunto is-falta"><i class="fas fa-exclamation-triangle"></i><div>
            <strong>Esta RD no tiene documento</strong>
            <span>Suba el PDF para adjuntarlo al correo; también quedará guardado en la RD.</span>
            <label class="tp-adjunto__subir">PDF de la RD (máx. 10 MB) *
                <input type="file" name="pdf" accept="application/pdf" class="form-control-file form-control-sm" required>
            </label>
        </div></div>
    @endif
    <div class="small text-muted mb-2">Cada estudiante recibe su propio correo.</div>
    <button type="submit" class="btn tp-btn"><i class="fas fa-paper-plane mr-1"></i>Enviar correo</button>
</fieldset>
</form>
