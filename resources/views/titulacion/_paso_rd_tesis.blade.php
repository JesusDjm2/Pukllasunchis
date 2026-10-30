{{-- Paso «RD de aprobación de la Tesis» (30/10), en orden: 1) pago del trámite de aprobación de
     sustentación, 2) RD (se genera o se sube), 3) Secretaría envía la RD por correo al estudiante.
     Hereda de _proceso: $egresado, $seguimiento, $tesis, $avance, $rdDe, $integrantes, $idPaso. --}}
@php
    $tramitePagado = $avance->hitos['tesis_tramite']['logrado'];
    $faltaTramite = \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_CAMPO['tramite_sust_pagado_en']);
    $rdTesis = $rdDe('Tesis');
@endphp

<div class="tp-subtitulo">1 · Pago del trámite de aprobación de sustentación</div>
@if ($tesis?->tramite_sust_pagado_en)
    <div class="tp-enlace-estados">
        <span class="tp-chip tp-chip--hecho"><i class="fas fa-receipt"></i>Pagado el {{ $tesis->tramite_sust_pagado_en->format('d/m/Y') }}{{ $tesis->tramite_sust_recibo ? ' · Recibo '.$tesis->tramite_sust_recibo : '' }}</span>
    </div>
@elseif ($tramitePagado)
    <p class="small text-muted mb-2">Registro anterior: la RD ya estaba emitida, así que el pago se da por hecho.</p>
@elseif ($faltaTramite)
    <div class="tp-aviso"><i class="fas fa-lock"></i><div>Disponible cuando esté: <strong>{{ $faltaTramite }}</strong>.</div></div>
@else
    <div class="tp-aviso tp-aviso--espera"><i class="fas fa-hourglass-half"></i><div><strong>A la espera del pago del trámite.</strong> Cuando el estudiante pague, regístrelo para poder generar la RD.</div></div>
@endif
@unless ($faltaTramite && ! $tesis?->tramite_sust_pagado_en)
    <details class="tp-mas mt-0" @if (! $tramitePagado) open @endif>
        <summary>{{ $tesis?->tramite_sust_pagado_en ? 'Corregir el pago' : 'Registrar el pago' }}</summary>
        <form method="POST" action="{{ route('titulacion.tesis.tramite', $egresado->id) }}" class="tp-form mt-2" id="{{ $idPaso('tramite') }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.tesis.tramite'))>
            @csrf
            <div class="form-row">
                <div class="form-group col-sm-6 mb-2"><label class="tp-label">Fecha de pago</label>
                    <input type="date" name="tramite_sust_pagado_en" class="form-control" value="{{ $tesis?->tramite_sust_pagado_en?->format('Y-m-d') }}" title="Si la deja vacía y anota el recibo, se registra hoy"></div>
                <div class="form-group col-sm-6 mb-2"><label class="tp-label">N° de recibo u operación</label>
                    <input type="text" name="tramite_sust_recibo" class="form-control" maxlength="100" value="{{ $tesis?->tramite_sust_recibo }}"></div>
            </div>
            @include('titulacion._aplicar_grupo', ['idCasilla' => $idPaso('tramite').'-grupo'])
            <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>Guardar pago</button>
            @if ($tesis?->tramite_sust_pagado_en)
                <span class="small text-muted ml-2">Para quitarlo, deje los dos campos vacíos y guarde.</span>
            @endif
        </fieldset>
</form>
    </details>
@endunless

<div class="tp-subtitulo">2 · RD de aprobación de sustentación</div>
@include('titulacion._rd_slot', [
    'etiqueta' => 'RD de aprobación de sustentación', 'res' => $rdTesis, 'plantilla' => 'aprobacion_sustentacion',
    'numeroCampo' => $tesis?->rd_tesis, 'fechaCampo' => $avance->hitos['tesis_aprobada']['fecha'], 'sinCorreo' => true,
])

<div class="tp-subtitulo">3 · Secretaría envía la RD por correo</div>
@if (! $rdTesis)
    <p class="small text-muted mb-0">Cuando la RD esté registrada, Secretaría podrá enviarla por correo al estudiante.</p>
@elseif (! \App\Services\Titulacion\AreasTitulacion::puede('secretaria'))
    <p class="small text-muted mb-0">{{ $rdTesis->correo_enviado_en ? 'Secretaría envió la RD por correo el '.$rdTesis->correo_enviado_en->format('d/m/Y H:i').'.' : 'Secretaría todavía no envía la RD por correo.' }}</p>
@else
    @if ($rdTesis->correo_enviado_en)
        <div class="tp-enlace-estados"><span class="tp-chip tp-chip--hecho"><i class="fas fa-paper-plane"></i>Correo enviado el {{ $rdTesis->correo_enviado_en->format('d/m/Y H:i') }}</span></div>
    @endif
    <details class="tp-mas mt-0" @if (! $rdTesis->correo_enviado_en) open @endif>
        <summary><i class="fas fa-envelope mr-1"></i>{{ $rdTesis->correo_enviado_en ? 'Enviar de nuevo' : 'Enviar correo al estudiante' }}</summary>
        @include('titulacion._rd_correo', ['res' => $rdTesis, 'idC' => $idPaso('correord'), 'tabVolver' => 'proceso'])
    </details>
@endif
