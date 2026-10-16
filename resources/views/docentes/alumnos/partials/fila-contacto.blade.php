{{-- Una fila de alumno. Espera: $c (App\Support\ContactoAlumno), $puedeEscribir (bool),
     $firma (['nombre' => 'Lourdes', 'rol' => 'tu docente de X', 'rolRef' => 'docente']) para los
     saludos prellenados de WhatsApp: cercanos, con primer nombre, sin perder quién escribe.
     Opcional: $detalles ([clave => Alumno]) añade el botón "Ficha" con la ficha completa (tutor). --}}
@php
    $alumnoDetalle = isset($detalles) ? ($detalles[$c->clave] ?? null) : null;
    $idDetalle = 'pkAlDet-'.$c->clave;
    $waAlumno = $c->whatsapp('Hola '.$c->primerNombre().', ¿cómo estás? Soy '.$firma['nombre'].', '.$firma['rol'].'.');
    $waRef = $c->whatsappReferencia('Hola, buen día. Soy '.$firma['nombre'].', '.$firma['rolRef'].' de '.$c->primerNombre().' en la EESP Pukllasunchis.');
    $telRef = \App\Support\Telefono::llamar($c->numeroReferencia);
    $buscar = mb_strtolower($c->nombre.' '.$c->correo.' '.$c->numero);
@endphp
<div class="pk-al-item" data-buscar="{{ $buscar }}">
<div class="pk-al-fila {{ $puedeEscribir ? '' : 'sin-check' }}">
    @if ($puedeEscribir)
        <input type="checkbox" class="pk-al-check" value="{{ $c->clave }}" data-nombre="{{ $c->nombre }}"
            aria-label="Elegir a {{ $c->nombre }}" @disabled(! $c->correo)
            title="{{ $c->correo ? 'Elegir para escribirle' : 'Sin correo registrado' }}">
    @endif

    @if ($c->foto)
        <img src="{{ $c->foto }}" alt="Foto de {{ $c->nombre }}" class="pk-al-avatar" loading="lazy"
            onclick="pkAlFoto(this.src, @js($c->nombre))" oncontextmenu="return false;">
    @else
        <div class="pk-al-iniciales" aria-hidden="true">{{ $c->iniciales() }}</div>
    @endif

    <div class="min-w-0">
        <div class="pk-al-nombre">{{ $c->nombre }}</div>
        @if ($c->etiquetas)
            <div class="pk-al-etiquetas">
                @foreach ($c->etiquetas as [$texto, $variante])
                    <span class="pk-al-etq {{ $variante }}">{{ $texto }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="pk-al-contacto">
        <div class="pk-al-dato">
            <i class="fas fa-envelope"></i>
            @if ($c->correo)
                <a href="mailto:{{ $c->correo }}" title="Abrir en tu programa de correo">{{ $c->correo }}</a>
            @else
                <span class="pk-al-vacio">Sin correo</span>
            @endif
        </div>
        <div class="pk-al-dato">
            <i class="fas fa-mobile-alt"></i>
            @if ($c->numero)
                <span>{{ $c->numero }}</span>
            @else
                <span class="pk-al-vacio">Sin celular</span>
            @endif
        </div>
        @if ($c->numeroReferencia)
            <div class="pk-al-dato">
                <i class="fas fa-user-friends"></i>
                <span class="pk-al-ref">Ref.</span>
                <span>{{ $c->numeroReferencia }}</span>
                @if ($waRef)
                    <a href="{{ $waRef }}" target="_blank" rel="noopener" class="pk-al-btn wa mini" title="WhatsApp al contacto de referencia">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                @endif
                @if ($telRef)
                    <a href="{{ $telRef }}" class="pk-al-btn tel mini" title="Llamar al contacto de referencia">
                        <i class="fas fa-phone"></i>
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="pk-al-acciones">
        @if ($puedeEscribir && $c->correo)
            <button type="button" class="pk-al-btn correo" onclick="pkAlEscribirA(@js($c->clave), @js($c->nombre))" title="Escribirle un correo desde el sistema">
                <i class="fas fa-paper-plane"></i><span class="d-none d-xl-inline">Correo</span>
            </button>
        @endif
        @if ($waAlumno)
            <a href="{{ $waAlumno }}" target="_blank" rel="noopener" class="pk-al-btn wa" title="Abrir WhatsApp con {{ $c->primerNombre() }}">
                <i class="fab fa-whatsapp"></i><span class="d-none d-xl-inline">WhatsApp</span>
            </a>
        @endif
        @if ($alumnoDetalle)
            <button type="button" class="pk-al-btn tel" data-toggle="collapse" data-target="#{{ $idDetalle }}"
                aria-expanded="false" aria-controls="{{ $idDetalle }}" title="Ver la ficha completa">
                <i class="fas fa-id-card"></i><span class="d-none d-xl-inline">Ficha</span>
            </button>
        @endif
    </div>
</div>
@if ($alumnoDetalle)
    <div class="collapse pk-al-detalle" id="{{ $idDetalle }}">
        @include('tutor.partials.detalle-alumno', ['alumno' => $alumnoDetalle])
    </div>
@endif
</div>
