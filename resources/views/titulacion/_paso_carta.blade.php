{{-- Paso «Carta de aceptación de la IE» (cierra la etapa PTI). Lo gestiona Secretaría; Investigación solo
     lo ve. El estudiante la solicita desde su intranet (Recibida); Secretaría la revisa y responde en
     «Registros» (Aprobada / Observada). Con la aprobación se abre el TI · Bachiller. Vale para todo el grupo.
     Espera: $seguimiento, $id, $avance y $egresado (los da _proceso). --}}
@php
    $solicitud = $seguimiento->cartasAceptacion->first();
    $puedeAtender = \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.cartas.atender');
    $archivoUrl = fn ($cual) => route('mi-titulacion.carta.archivo', [$solicitud->id, $cual]);
    // Se da por cumplida sin solicitud: el grupo ya estaba en el TI antes de que existiera el trámite.
    $porAvance = ! $solicitud && ($avance->hitos['pti_carta']['logrado'] ?? false);
    // Enlace del formulario para compartirlo (cada estudiante entra con su cuenta y ve el suyo).
    $enlaceForm = route('mi-titulacion.carta');
    $mensajeWa = "Hola, para solicitar la carta de aceptación de la Institución Educativa donde harás tu investigación, ingresa con tu correo institucional a:\n".$enlaceForm."\nAdjunta el comprobante de pago del derecho de trámite (S/ 10).";
    $fechaCorta = fn ($f) => $f?->format('d/m/Y');
@endphp

@unless ($porAvance)
    <div class="tp-carta-barra">
        <span><i class="fas fa-stamp mr-1"></i>Lo gestiona <strong>Secretaría</strong>{{ $puedeAtender ? '' : ' · usted lo ve en modo lectura' }}.</span>
        <a href="{{ route('titulacion.cartas.index', array_filter(['resaltar' => $solicitud?->lote])) }}" class="btn btn-sm tp-btn">
            <i class="fas fa-list-ul mr-1"></i>{{ $solicitud ? ($puedeAtender && $solicitud->estado === 'enviada' ? 'Revisar en registros' : 'Ver en registros') : 'Registros de solicitudes' }}
        </a>
    </div>
    <div class="tp-compartir">
        <span class="tp-compartir__eti"><i class="fas fa-link"></i> Enlace del formulario</span>
        <input type="text" class="form-control form-control-sm" value="{{ $enlaceForm }}" readonly onclick="this.select()" aria-label="Enlace del formulario de la carta de aceptación">
        <button type="button" class="btn btn-sm tp-compartir__btn js-tc-copiar" data-copiar="{{ $enlaceForm }}" title="Copiar enlace"><i class="fas fa-copy"></i><span>Copiar</span></button>
        <a href="https://wa.me/?text={{ rawurlencode($mensajeWa) }}" target="_blank" rel="noopener" class="btn btn-sm tp-compartir__btn tp-compartir__btn--wa" title="Enviar por WhatsApp"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>
        <small class="tp-compartir__ayuda">Cada estudiante entra con su cuenta institucional y ve su propia solicitud.</small>
    </div>
@endunless

{{-- Estados: Recibida → Aprobada (respuesta enviada) / Observada --}}
<ol class="tp-carta-estados" id="{{ $id }}">
    <li class="{{ $solicitud ? 'is-hecho' : 'is-actual' }}">
        <span><i class="fas {{ $solicitud ? 'fa-check' : 'fa-inbox' }}"></i></span>
        <div><strong>Recibida</strong><small>{{ $solicitud ? 'El '.$fechaCorta($solicitud->created_at).' · '.$solicitud->solicitante?->name.' '.$solicitud->solicitante?->apellidos.($solicitud->origen === 'formulario' ? ' (formulario de Google)' : ($solicitud->origen === 'manual' ? ' (registrada a mano)' : '')) :($porAvance ? 'No aplica' : 'A la espera del estudiante') }}</small></div>
    </li>
    <li class="{{ match (true) { $porAvance || $solicitud?->estado === 'emitida' => 'is-hecho', $solicitud?->estado === 'observada' => 'is-observada', (bool) $solicitud => 'is-actual', default => '' } }}">
        <span><i class="fas {{ $solicitud?->estado === 'observada' ? 'fa-undo' : ($porAvance || $solicitud?->estado === 'emitida' ? 'fa-check' : 'fa-paper-plane') }}"></i></span>
        <div>
            <strong>{{ $solicitud?->estado === 'observada' ? 'Observada' : 'Respuesta de aprobación' }}</strong>
            <small>
                @if ($porAvance) Se da por cumplida: el grupo ya estaba en el TI antes del trámite
                @elseif ($solicitud?->estado === 'emitida') Enviada el {{ $fechaCorta($solicitud->atendido_en) }}
                @elseif ($solicitud?->estado === 'observada') El {{ $fechaCorta($solicitud->atendido_en) }}: el estudiante debe corregirla y enviarla de nuevo
                @elseif ($solicitud) Falta enviarla
                @else —
                @endif
            </small>
        </div>
    </li>
    <li class="{{ $porAvance || $solicitud?->estado === 'emitida' ? 'is-hecho' : '' }}">
        <span><i class="fas fa-graduation-cap"></i></span>
        <div><strong>TI · Bachiller</strong><small>{{ $porAvance || $solicitud?->estado === 'emitida' ? 'Abierto' : 'Se abre con la aprobación' }}</small></div>
    </li>
</ol>

@if ($solicitud)
    <dl class="tp-carta-datos">
        <div><dt>Institución Educativa</dt><dd>{{ $solicitud->institucion }}</dd></div>
        @if ($solicitud->director)<div><dt>Director(a)</dt><dd>{{ $solicitud->director }}</dd></div>@endif
        <div><dt>Grupo</dt><dd>{{ $solicitud->grupo }}</dd></div>
        @if ($solicitud->celular)<div><dt>Contacto</dt><dd>{{ $solicitud->correo }} · {{ $solicitud->celular }}</dd></div>@endif
        @if ($solicitud->programa_estudios)<div><dt>Programa</dt><dd>{{ $solicitud->programa_estudios }} · {{ $solicitud->programa_formacion }}</dd></div>@endif
        @if ($solicitud->titulo)<div class="tp-carta-ancho"><dt>Título del trabajo</dt><dd>“{{ $solicitud->titulo }}”</dd></div>@endif
        @if ($solicitud->observacion && $solicitud->estado !== 'enviada')
            <div class="tp-carta-ancho"><dt>{{ $solicitud->estado === 'observada' ? 'Observación enviada' : 'Respuesta enviada' }}</dt><dd class="tp-carta-mensaje">{{ $solicitud->observacion }}</dd></div>
        @endif
    </dl>

    <div class="d-flex flex-wrap mb-2" style="gap:.5rem;">
        @if ($solicitud->comprobante)
            <a href="{{ $archivoUrl('comprobante') }}" target="_blank" rel="noopener" class="tp-enlace mb-0"><i class="fas fa-file-invoice-dollar"></i><span>Comprobante de pago</span><i class="fas fa-external-link-alt"></i></a>
        @endif
        @if ($solicitud->carta)
            <a href="{{ $archivoUrl('carta') }}" target="_blank" rel="noopener" class="tp-enlace mb-0"><i class="fas fa-file-pdf"></i><span>Carta adjunta</span><i class="fas fa-external-link-alt"></i></a>
        @endif
    </div>

    @if ($solicitud->estado === 'enviada')
        <div class="tp-aviso tp-aviso--plazo">
            <i class="fas fa-hourglass-half"></i>
            <div>
                Recibida el {{ $fechaCorta($solicitud->created_at) }}. Secretaría debe revisar los datos y el comprobante y enviar la respuesta
                desde <strong>Registros</strong> a más tardar el <strong>{{ $fechaCorta($solicitud->fechaLimite()) }}</strong> ({{ \App\Models\Titulacion\CartaAceptacion::DIAS_RESPUESTA }} días hábiles).
            </div>
        </div>
    @endif
@elseif (! $porAvance)
    <div class="tp-aviso tp-aviso--plazo">
        <i class="fas fa-envelope-open-text"></i>
        <div>
            El estudiante todavía no la solicita. Comparta el enlace de arriba: desde su intranet, con su correo institucional, llena el
            formulario y adjunta el comprobante. Al enviarlo, la solicitud queda <strong>Recibida</strong>, el estudiante recibe la respuesta automática y a Secretaría le llega un aviso en el sistema.
        </div>
    </div>
    @if ($puedeAtender && $avance->hitos['pti_aprobado']['logrado'])
        <details class="tp-carta-externa">
            <summary><i class="fas fa-file-import mr-1"></i>¿Ya la tramitó fuera del sistema? Registrarla como aprobada</summary>
            <form method="POST" action="{{ route('titulacion.cartas.externa', $egresado->id) }}" enctype="multipart/form-data" class="tp-form mt-2">
                @csrf
                <div class="form-group mb-2">
                    <label class="tp-label" for="{{ $id }}-ext-ie">Institución Educativa</label>
                    <input type="text" name="institucion" id="{{ $id }}-ext-ie" class="form-control" maxlength="300" required>
                </div>
                <div class="form-group mb-2">
                    <label class="tp-label" for="{{ $id }}-ext-pdf">Carta en PDF (opcional)</label>
                    <input type="file" name="carta" id="{{ $id }}-ext-pdf" class="form-control-file" accept="application/pdf,.pdf">
                </div>
                <div class="small text-muted mb-2">Para quien la obtuvo antes (formulario de Google, en papel…). Queda aprobada para todo el grupo y se abre el TI · Bachiller. No se envía correo.</div>
                <button type="submit" class="btn tp-btn tp-btn--claro"><i class="fas fa-check mr-1"></i>Registrar como aprobada</button>
            </form>
        </details>
    @endif
@endif
