{{-- Correo al jurado: elige destinatarios y edita el mensaje propuesto con el grupo, el título, la
     fecha y los enlaces. Un correo por docente. Tres usos ($motivo):
       'expo'         jurado de la exposición del TI (paso «Jurado» del TI)
       'dictamen'     dictaminantes de la Tesis (paso «Dictaminantes»)
       'sustentacion' jurado de la sustentación de la Tesis (paso «Jurado de la sustentación»)
     Espera: $jurado (participantes), $idC, $egresado, $integrantes, $tesis, $pti, $asesoresTi; opcional $motivo. --}}
@if ($jurado->isNotEmpty())
    @php
        $motivo = $motivo ?? 'expo';
        $esTesis = $motivo !== 'expo';
        $estudiantes = collect([$egresado])->merge($integrantes ?? collect())
            ->map(fn ($u) => trim(mb_convert_case(trim((string) $u->name), MB_CASE_TITLE).' '.mb_convert_case(trim((string) $u->apellidos), MB_CASE_TITLE)))
            ->filter()->implode(', ');
        $fechaLarga = fn ($f) => $f ? $f->locale('es')->translatedFormat('j \d\e F \d\e Y') : 'por confirmar';
        $titulo = $tesis?->titulo ?: $pti?->titulo;
        $asesor = $asesoresTi->map(fn ($p) => $p->asesorNombre())->filter()->implode(', ') ?: ($pti?->asesorNombre() ?: ($tesis?->asesorNombre() ?: '—'));
        [$intro, $cierre, $asunto] = match ($motivo) {
            'dictamen' => [
                'Por medio del presente le informamos que ha sido designado(a) como dictaminante de la siguiente Tesis:',
                'Le agradeceremos revisar la tesis y compartir su dictamen. Ante cualquier consulta, puede responder a este correo.',
                'Dictamen de Tesis',
            ],
            'sustentacion' => [
                'Por medio del presente le informamos que usted integra el jurado de la sustentación de la siguiente Tesis:',
                'Le agradeceremos revisar la tesis antes de la sustentación. Ante cualquier consulta, puede responder a este correo.',
                'Jurado de sustentación de Tesis',
            ],
            default => [
                'Por medio del presente le informamos que usted integra el jurado de la exposición del siguiente Trabajo de Investigación (TI):',
                'Le agradeceremos revisar el trabajo antes de la exposición. Ante cualquier consulta, puede responder a este correo.',
                'Jurado de exposición del Trabajo de Investigación',
            ],
        };
        $lineas = ['• Título: '.($titulo ?: '—'), '• Estudiante(s): '.($estudiantes ?: '—'), '• Asesor(a): '.$asesor];
        if ($esTesis) {
            if ($motivo === 'sustentacion') {
                $lineas[] = '• Fecha de sustentación: '.$fechaLarga($tesis?->fecha_sustentacion);
            }
            $lineas[] = '• Enlace de la Tesis: '.($tesis?->link_tesis ?: '—');
            foreach ($motivo === 'sustentacion' ? (array) ($tesis?->enlaces_dictamen ?? []) : [] as $n => $enlaceDictamen) {
                $lineas[] = '• Dictamen '.($n + 1).': '.(is_array($enlaceDictamen) ? ($enlaceDictamen['url'] ?? '') : $enlaceDictamen);
            }
        } else {
            $lineas[] = '• Fecha de exposición: '.$fechaLarga($tesis?->fecha_exposicion);
            $lineas[] = '• Enlace del TI: '.($tesis?->link_ti ?: '—');
            if ($pti?->link_iti) {
                $lineas[] = '• Informe ITI del asesor: '.$pti->link_iti;
            }
        }
        $mensajeJurado = $intro."\n\n".implode("\n", $lineas)."\n\n".$cierre;
    @endphp
    <details class="tp-mas mt-3">
        <summary><i class="fas fa-envelope mr-1"></i>{{ $motivo === 'dictamen' ? 'Enviar correo a los dictaminantes' : 'Enviar correo al jurado' }}</summary>
        <form method="POST" action="{{ route('titulacion.ti.correo-jurado', $egresado->id) }}" class="tp-form mt-2" id="{{ $idC }}"
            data-confirm="Se enviará el correo a los docentes seleccionados." data-confirm-titulo="¿Enviar el correo?" data-confirm-boton="Sí, enviar">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.ti.correo-jurado'))>
            @csrf
            <input type="hidden" name="etapa" value="{{ $esTesis ? 'tesis' : 'ti' }}">
            <span class="tp-label d-block">Para</span>
            @foreach ($jurado as $p)
                @php $correoP = $p->correo(); @endphp
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="{{ $idC }}-d{{ $p->id }}" name="destinatarios[]" value="{{ $p->id }}" @checked($correoP) @disabled(! $correoP)>
                    <label class="custom-control-label small" for="{{ $idC }}-d{{ $p->id }}">
                        {{ $p->asesorNombre() }}@if ($p->cargo) <span class="text-muted">· {{ $p->cargo }}</span>@endif
                        <span class="text-muted">— {{ $correoP ?: 'sin correo registrado' }}</span>
                    </label>
                </div>
            @endforeach
            <div class="form-group mt-2 mb-2">
                <label class="tp-label" for="{{ $idC }}-asunto">Asunto</label>
                <input type="text" name="asunto" id="{{ $idC }}-asunto" class="form-control" maxlength="200" required
                    value="{{ $asunto }}{{ $titulo ? ' — '.\Illuminate\Support\Str::limit($titulo, 80) : '' }}">
            </div>
            <div class="form-group mb-2">
                <label class="tp-label" for="{{ $idC }}-mensaje">Mensaje</label>
                <textarea name="mensaje" id="{{ $idC }}-mensaje" class="form-control" rows="9" maxlength="5000" required>{{ $mensajeJurado }}</textarea>
            </div>
            <div class="small text-muted mb-2"><i class="fas fa-info-circle mr-1"></i>Cada docente recibe su propio correo, que empieza con «Estimado(a) docente …». Si responden, la respuesta llega a {{ \App\Mail\Titulacion\Remitente::direccion('investigacion') }}.</div>
            <button type="submit" class="btn tp-btn"><i class="fas fa-paper-plane mr-1"></i>Enviar correo</button>
        </fieldset>
</form>
    </details>
@endif
