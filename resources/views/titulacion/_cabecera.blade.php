{{-- Cabecera de la ficha: quién es, en qué va y cómo contactarle de un toque. Iniciales, nombre,
     expediente, programa, estado; vigencia del PTI como barra (ámbar/roja al acercarse); integrantes
     del grupo; llamar / WhatsApp / correo con los datos de su ficha FID o PPD.
     Hereda $egresado, $seguimiento, $avance, $vigencia, $sugerido, $integrantes, $estadoClave. --}}
@php
    $fichaPersona = $egresado->alumno ?? $egresado->alumnoB;
    // Mismo celular que en la tabla: el de la ficha o, si no tiene, el de la cuenta.
    $telefonoTexto = trim((string) ($fichaPersona?->numero ?: $egresado->telefono));
    $celular = preg_replace('/\D/', '', $telefonoTexto);
    $whatsapp = strlen($celular) === 9 ? '51'.$celular : (strlen($celular) >= 11 ? $celular : null);
    $iniciales = fn ($u) => mb_strtoupper(mb_substr((string) $u->name, 0, 1).mb_substr((string) $u->apellidos, 0, 1));
    $tono = [
        'licenciado' => 'ok', 'bachiller' => 'ok', 'tesis_aprobada' => 'info', 'ti_aprobado' => 'info',
        'pti_aprobado' => 'info', 'pti_en_proceso' => 'warn', 'sin_pti' => 'muted',
    ][$estadoClave] ?? 'muted';

    // Vigencia del PTI (hasta el Bachiller; después ya no se muestra la barra).
    $mostrarVigencia = $vigencia->conocida() && in_array($estadoClave, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado'], true);
    $pctVigencia = 0;
    $tonoVigencia = 'ok';
    if ($mostrarVigencia) {
        $desde = $vigencia->inscripcion ?? $vigencia->vencimiento->copy()->subYears(2);
        $total = max(1, $desde->diffInDays($vigencia->vencimiento));
        $pctVigencia = (int) min(100, max(0, round($desde->diffInDays(now(), false) * 100 / $total)));
        $tonoVigencia = $vigencia->vencido() ? 'mal' : ($sugerido === 'urgente' ? 'mal' : ($pctVigencia >= 75 ? 'warn' : 'ok'));
    }
@endphp
<header class="tc">
    <div class="tc-persona">
        <span class="tc-avatar">{{ $iniciales($egresado) }}</span>
        <div class="tc-id">
            <div class="tc-nombre">{{ $egresado->apellidos }}, {{ $egresado->name }}</div>
            <div class="tc-meta">
                @if ($seguimiento->numero_expediente)
                    <span class="tc-exp">Exp. N° {{ $seguimiento->numero_expediente }}</span>
                @else
                    <span class="tc-exp tc-exp--falta">Sin expediente</span>
                @endif
                {{-- RD que aprueba el PTI: da inicio al proceso, siempre a la vista (ver RdPti). --}}
                @include('titulacion._rd_pti_chip', ['seguimiento' => $seguimiento, 'personaId' => $egresado->id])
                <span>{{ $egresado->ciclo?->programa?->nombre ?? $egresado->programa?->nombre ?? 'Sin programa' }}</span>
                @if ($egresado->ciclo) <span class="tc-punto">{{ preg_match('/^[IVX]+$/', trim($egresado->ciclo->nombre)) ? 'Ciclo '.$egresado->ciclo->nombre : $egresado->ciclo->nombre }}</span> @endif
            </div>
            {{-- Datos de identificación y contacto, a la vista y copiables con un clic (como en la tabla). --}}
            <div class="tc-datos">
                <button type="button" class="tc-dato js-tc-copiar" data-copiar="{{ $egresado->dni }}" title="Copiar DNI" @disabled(blank($egresado->dni))>
                    <i class="far fa-id-card"></i><span class="tc-dato__eti">DNI</span><span>{{ $egresado->dni ?: '—' }}</span>
                </button>
                @if ($telefonoTexto !== '')
                    <button type="button" class="tc-dato js-tc-copiar" data-copiar="{{ $telefonoTexto }}" title="Copiar celular">
                        <i class="fas fa-mobile-alt"></i><span>{{ $telefonoTexto }}</span>
                    </button>
                @endif
                @if ($egresado->email)
                    <button type="button" class="tc-dato js-tc-copiar" data-copiar="{{ $egresado->email }}" title="Copiar correo">
                        <i class="far fa-envelope"></i><span class="tc-dato__correo">{{ $egresado->email }}</span>
                    </button>
                @endif
            </div>
        </div>
        <span class="tc-estado tc-estado--{{ $tono }}">{{ $avance->estadoNombre() }}</span>
    </div>

    <div class="tc-fila">
        @if ($mostrarVigencia)
            <div class="tc-vigencia tc-vigencia--{{ $tonoVigencia }}" title="Inscrito {{ $vigencia->inscripcion?->format('d/m/Y') ?? '—' }} · vence {{ $vigencia->vencimiento->format('d/m/Y') }}">
                <div class="tc-vigencia__txt">
                    <i class="fas fa-hourglass-half"></i>
                    @if ($vigencia->vencido())
                        <strong>PTI vencido</strong> el {{ $vigencia->vencimiento->format('d/m/Y') }}
                    @else
                        PTI vence en <strong>{{ $vigencia->tiempoRestanteTexto() }}</strong> <span class="tc-suave">· {{ $vigencia->vencimiento->format('d/m/Y') }}</span>
                    @endif
                </div>
                <div class="tc-vigencia__barra"><span style="width: {{ $pctVigencia }}%"></span></div>
            </div>
        @endif

        @if ($integrantes->isNotEmpty())
            <label for="{{ $tabId }}-datos" class="tc-grupo" title="Ver el grupo">
                <span class="tc-grupo__avatares">
                    <span class="tc-mini tc-mini--yo" title="{{ $egresado->apellidos }}, {{ $egresado->name }}">{{ $iniciales($egresado) }}</span>
                    @foreach ($integrantes->take(4) as $int)
                        <span class="tc-mini" title="{{ $int->apellidos }}, {{ $int->name }}">{{ $iniciales($int) }}</span>
                    @endforeach
                    @if ($integrantes->count() > 4) <span class="tc-mini">+{{ $integrantes->count() - 4 }}</span> @endif
                </span>
                <span class="tc-grupo__txt">Grupo de {{ $integrantes->count() + 1 }}</span>
            </label>
        @else
            <span class="tc-grupo tc-grupo--solo"><i class="fas fa-user"></i> Individual</span>
        @endif

        <div class="tc-contacto">
            {{-- Solo acciones: los datos ya se ven (y se copian) bajo el nombre. --}}
            @if ($celular)
                <a href="tel:{{ $celular }}" class="tc-btn" title="Llamar al {{ $telefonoTexto }}"><i class="fas fa-phone"></i><span>Llamar</span></a>
            @endif
            @if ($whatsapp)
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="tc-btn tc-btn--wa" title="Escribir por WhatsApp"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>
            @endif
            @if ($egresado->email)
                <a href="mailto:{{ $egresado->email }}" class="tc-btn" title="Escribir a {{ $egresado->email }}"><i class="fas fa-paper-plane"></i><span>Correo</span></a>
            @endif
            @if (! $celular && ! $egresado->email)
                <span class="tc-suave small">Sin datos de contacto</span>
            @endif
        </div>
    </div>
</header>
