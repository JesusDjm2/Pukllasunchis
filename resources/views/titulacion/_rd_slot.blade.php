{{-- Casillero de una RD dentro de una etapa del proceso (pestaña Proceso de la ficha).
     Muestra la RD registrada (número, fecha y PDF) o "Pendiente", y las acciones:
     Generar (plantilla del sistema, ver PlantillasRd) y Subir el PDF firmado.
     Espera: $etiqueta, $egresado; opcionales $res (Resolucion), $numeroCampo/$fechaCampo
     (RD anotada solo en el campo de la etapa), $plantilla (clave de PlantillasRd), $tipoRd (tipo sin plantilla). --}}
@php
    use App\Services\Titulacion\PlantillasRd;

    $res = $res ?? null;
    $numero = $res?->numero_rd ?? ($numeroCampo ?? null);
    $fechaRd = $res?->fecha ?? ($fechaCampo ?? null);
    $plantilla = $plantilla ?? null;
    $generable = $plantilla && PlantillasRd::disponible($plantilla);
    // Orden del proceso (ReglasProceso): sin RD todavía y con el paso anterior pendiente, no se
    // puede generar ni registrar. Lo ya registrado se ve y se corrige siempre.
    $tipoSlot = $tipoRd ?? ($plantilla ? (PlantillasRd::PLANTILLAS[$plantilla]['tipo'] ?? null) : null);
    // PDF guardado por el sistema o enlace externo (Drive u otro): los dos se abren en otra pestaña.
    $esEnlace = $res?->archivo && ! \App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($res->archivo);
    $idArchivo = 'rdarchivo-'.$egresado->id.'-'.($res?->id ?? 0);
    $bloqueoSlot = (! $numero && $tipoSlot && isset($avance))
        ? \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_RD[$tipoSlot] ?? null)
        : null;
    // Registrar una RD emitida fuera del sistema (o adjuntar el PDF de una ya anotada), con el tipo del casillero.
    $puedeRd = \App\Services\Titulacion\AreasTitulacion::puede('secretaria');
    $subible = $tipoSlot && ! $res && ! $bloqueoSlot && $puedeRd;
    $idSubir = 'rdsubir-'.$egresado->id.'-'.$tipoSlot;
    // Secretaría envía la RD por correo; si no tiene documento, lo sube al enviarla. $sinCorreo: el paso lo muestra aparte.
    $tabVolver = $tabVolver ?? 'proceso';
    $conCorreo = $res && filled($res->numero_rd) && ! ($sinCorreo ?? false) && $puedeRd;
    $idCorreo = 'rdcorreo-'.$egresado->id.'-'.($res?->id ?? 0);
@endphp
<div class="ft-rd-slot {{ $numero ? 'is-registrada' : '' }} {{ $bloqueoSlot ? 'is-bloqueada' : '' }}">
    <div class="ft-rd-slot__info">
        <span class="ft-rd-slot__label">{{ $etiqueta }}</span>
        @if ($numero)
            <span class="ft-rd-slot__numero">{{ $numero }}</span>
            <span class="ft-rd-slot__meta">
                {{ $fechaRd?->format('d/m/Y') ?? 'sin fecha' }}
                @if ($res?->archivo)
                    {{-- El documento se abre con el botón .ft-doc de las acciones (igual que en Historial). --}}
                    @if ($res->correo_enviado_en)
                        · <span title="Enviada por correo por Secretaría"><i class="fas fa-paper-plane fa-xs"></i> correo enviado el {{ $res->correo_enviado_en->format('d/m/Y') }}</span>
                    @endif
                @else
                    · <span title="{{ $res ? 'Suba el PDF firmado' : 'Anotada en el formulario de la etapa: regístrela en Historial para adjuntar su PDF' }}">sin PDF</span>
                @endif
            </span>
        @else
            @if ($bloqueoSlot)
                <span class="ft-rd-slot__pendiente"><i class="fas fa-lock fa-xs mr-1"></i>Disponible cuando esté: {{ $bloqueoSlot }}</span>
            @else
                <span class="ft-rd-slot__pendiente">Pendiente</span>
            @endif
        @endif
    </div>
    <div class="ft-rd-slot__acciones">
        @if ($res?->archivo)
            @php $textoDoc = \App\Services\Titulacion\ArchivosRd::etiqueta($res->archivo); @endphp
            <a href="{{ $res->archivo }}" target="_blank" rel="noopener" class="ft-doc {{ $esEnlace ? 'ft-doc--enlace' : 'ft-doc--pdf' }}"
               title="Abrir en otra pestaña" aria-label="{{ $textoDoc }} de la RD {{ $numero }}">
                <span class="ft-doc__icono"><i class="fas {{ $esEnlace ? ($textoDoc === 'Ver en Drive' ? 'fa-cloud' : 'fa-link') : 'fa-file-pdf' }}"></i></span>
                <span class="ft-doc__texto">{{ $textoDoc }}</span>
                <i class="fas fa-external-link-alt ft-doc__salida" aria-hidden="true"></i>
            </a>
        @endif
        @if (! $puedeRd)
        @elseif ($plantilla && $bloqueoSlot)
            <span class="btn btn-sm btn-outline-secondary disabled" title="Primero debe estar registrado: {{ $bloqueoSlot }}" aria-disabled="true">
                <i class="fas fa-lock mr-1"></i>Generar RD
            </span>
        @elseif ($plantilla)
            @if ($generable)
                <a href="{{ route('titulacion.rd.crear', [$egresado->id, $plantilla]) }}" class="btn btn-sm {{ $numero ? 'btn-outline-primary' : 'btn-primary' }}">
                    <i class="fas fa-file-signature mr-1"></i>{{ $numero ? 'Generar otra' : 'Generar RD' }}
                </a>
            @else
                <span class="btn btn-sm btn-outline-secondary disabled" title="Falta cargar el modelo oficial de esta resolución" aria-disabled="true">
                    <i class="fas fa-file-signature mr-1"></i>Generar RD
                </span>
            @endif
        @endif
        @if ($subible)
            <button type="button" class="btn btn-sm btn-outline-secondary js-abrir" data-abrir="{{ $idSubir }}"
                    title="{{ $numero ? 'Registrar el PDF de esta RD' : 'Registrar una RD emitida fuera del sistema, con su PDF o enlace' }}">
                <i class="fas fa-upload mr-1"></i>{{ $numero ? 'Adjuntar PDF' : 'Subir RD' }}
            </button>
        @endif
        @if ($conCorreo)
            <button type="button" class="btn btn-sm btn-outline-primary js-abrir" data-abrir="{{ $idCorreo }}" title="Secretaría envía esta RD por correo a los estudiantes">
                <i class="fas fa-paper-plane mr-1"></i>{{ $res->correo_enviado_en ? 'Enviar de nuevo' : 'Enviar por correo' }}
            </button>
        @endif
        @if ($res && $puedeRd)
            <button type="button" class="btn btn-sm btn-outline-secondary js-abrir" data-abrir="{{ $idArchivo }}"
                    title="{{ $res->archivo ? 'Subir de nuevo el PDF (firmado) o cambiar el enlace; también para su grupo' : 'Subir el PDF o pegar su enlace; también para su grupo' }}">
                <i class="fas fa-upload mr-1"></i>{{ $res->archivo ? 'Subir de nuevo' : 'Subir PDF o enlace' }}
            </button>
        @endif
    </div>
</div>
@if ($res && $puedeRd)
    {{-- Cambia solo el archivo de esta RD: la RD (número y fecha) se conserva; las RD no se eliminan. --}}
    <details class="ft-desplegable ft-rd-subir" id="{{ $idArchivo }}">
        <summary>{{ $res->archivo ? 'Subir de nuevo el PDF o cambiar el enlace' : 'Subir el PDF o pegar el enlace' }} · RD {{ $numero }}</summary>
        <form method="POST" action="{{ route('titulacion.rd.subir', $res->id) }}" enctype="multipart/form-data" class="mt-2">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.rd.subir'))>
            @csrf
            <input type="hidden" name="tab" value="{{ $tabVolver }}">
            <div class="form-row">
                <div class="form-group col-md-6 mb-2">
                    <label class="small font-weight-bold mb-0">PDF de la RD</label>
                    <input type="file" name="pdf" accept="application/pdf" class="form-control-file form-control-sm">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label class="small font-weight-bold mb-0">…o enlace</label>
                    <input type="url" name="archivo" class="form-control form-control-sm" placeholder="https://drive.google.com/…" value="{{ $esEnlace ? $res->archivo : '' }}">
                </div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i>Guardar archivo</button>
        </fieldset>
</form>
    </details>
@endif
@if ($conCorreo)
    <details class="ft-desplegable ft-rd-subir" id="{{ $idCorreo }}">
        <summary>Enviar por correo · {{ $numero }}</summary>
        @include('titulacion._rd_correo', ['idC' => $idCorreo.'-f'])
    </details>
@endif
@if ($subible)
    <details class="ft-desplegable ft-rd-subir" id="{{ $idSubir }}">
        <summary>{{ $numero ? 'Adjuntar el PDF de la RD '.$numero : 'Subir una RD ya emitida: '.$etiqueta }}</summary>
        <div class="mt-2">@include('titulacion._form_resolucion', ['res' => null, 'tipoFijo' => $tipoSlot, 'numeroInicial' => $numero, 'fechaInicial' => $fechaRd])</div>
    </details>
@endif
