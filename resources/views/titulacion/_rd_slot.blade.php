{{-- Casillero de una RD dentro de una etapa del proceso (pestaña Proceso de la ficha).
     Muestra la RD registrada (número, fecha y PDF) o "Pendiente", y las acciones:
     Generar (plantilla del sistema, ver PlantillasRd) y Subir el PDF firmado.
     Espera: $etiqueta, $egresado; opcionales $res (Resolucion), $numeroCampo/$fechaCampo
     (RD anotada solo en el campo de la etapa), $plantilla (clave de PlantillasRd). --}}
@php
    use App\Services\Titulacion\PlantillasRd;

    $res = $res ?? null;
    $numero = $res?->numero_rd ?? ($numeroCampo ?? null);
    $fechaRd = $res?->fecha ?? ($fechaCampo ?? null);
    $plantilla = $plantilla ?? null;
    $generable = $plantilla && PlantillasRd::disponible($plantilla);
    // Orden del proceso (ReglasProceso): sin RD todavía y con el paso anterior pendiente, no se
    // puede generar ni registrar. Lo ya registrado se ve y se corrige siempre.
    $tipoSlot = $plantilla ? (PlantillasRd::PLANTILLAS[$plantilla]['tipo'] ?? null) : null;
    $bloqueoSlot = (! $numero && $tipoSlot && isset($avance))
        ? \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_RD[$tipoSlot] ?? null)
        : null;
    // Registrar una RD emitida fuera del sistema (o adjuntar el PDF de una ya anotada), con el tipo del casillero.
    $subible = $tipoSlot && ! $res && ! $bloqueoSlot;
    $idSubir = 'rdsubir-'.$egresado->id.'-'.$tipoSlot;
@endphp
<div class="ft-rd-slot {{ $numero ? 'is-registrada' : '' }} {{ $bloqueoSlot ? 'is-bloqueada' : '' }}">
    <div class="ft-rd-slot__info">
        <span class="ft-rd-slot__label">{{ $etiqueta }}</span>
        @if ($numero)
            <span class="ft-rd-slot__numero">{{ $numero }}</span>
            <span class="ft-rd-slot__meta">
                {{ $fechaRd?->format('d/m/Y') ?? 'sin fecha' }}
                @if ($res?->archivo)
                    · <a href="{{ $res->archivo }}" target="_blank" rel="noopener"><i class="fas fa-file-pdf mr-1"></i>Ver PDF</a>
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
        @if ($plantilla && $bloqueoSlot)
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
        @if ($res)
            <form method="POST" action="{{ route('titulacion.rd.subir', $res->id) }}" enctype="multipart/form-data" class="d-inline">
                @csrf
                <input type="hidden" name="tab" value="proceso">
                <label class="btn btn-sm btn-outline-secondary mb-0" title="Reemplaza el PDF de esta RD (también para su grupo)">
                    <i class="fas fa-upload mr-1"></i>{{ $res->archivo ? 'Subir firmada' : 'Subir PDF' }}
                    <input type="file" name="pdf" accept="application/pdf" hidden onchange="this.form.submit()">
                </label>
            </form>
        @endif
    </div>
</div>
@if ($subible)
    <details class="ft-desplegable ft-rd-subir" id="{{ $idSubir }}">
        <summary>{{ $numero ? 'Adjuntar el PDF de la RD '.$numero : 'Subir una RD ya emitida: '.$etiqueta }}</summary>
        <div class="mt-2">@include('titulacion._form_resolucion', ['res' => null, 'tipoFijo' => $tipoSlot, 'numeroInicial' => $numero, 'fechaInicial' => $fechaRd])</div>
    </details>
@endif
