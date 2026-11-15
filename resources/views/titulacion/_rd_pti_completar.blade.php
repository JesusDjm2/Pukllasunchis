{{-- Completar el número y el año de la RD del PTI cuando vino del Excel (nombre de archivo, sin año) o falta.
     Lo hace Secretaría; se corrige en todo el grupo (RdPtiController::completar). El año sugerido sale de la
     fecha de la RD o, si no hay, de la fecha del PTI: es un estimado que acierta ~87%, hay que verificarlo.
     Hereda $seguimiento, $egresado, $avance (de _proceso). --}}
@php
    $rdPtiC = \App\Services\Titulacion\RdPti::de($seguimiento);
    $puedeCompletar = \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.rdpti.completar');
    $anioSugerido = $rdPtiC['anio'] ?? $rdPtiC['anio_rd'] ?? $rdPtiC['anio_pti'];
    $anioEstimado = ! $rdPtiC['anio'] && ! $rdPtiC['anio_rd'] && $rdPtiC['anio_pti'];
    $idC = 'rdptic-'.$egresado->id;
@endphp
@if ($rdPtiC['estado'] !== 'completa' && $avance->hitos['pti_inscrito']['logrado'])
    <div class="tp-rdpti-completar" id="{{ $idC }}">
        <div class="tp-rdpti-completar__cab">
            <i class="fas fa-exclamation-circle"></i>
            @if ($rdPtiC['estado'] === 'incompleta')
                <span>La RD del PTI vino del Excel sin año: <code>{{ \Illuminate\Support\Str::limit($rdPtiC['texto'], 80) }}</code></span>
            @else
                <span>Todavía no tiene registrada la RD que aprueba su PTI.</span>
            @endif
        </div>
        @if ($puedeCompletar)
            <form method="POST" action="{{ route('titulacion.rdpti.completar', $egresado->id) }}" class="tp-form tp-rdpti-completar__form">
                @csrf
                <label>N° de RD
                    <input type="text" name="numero" value="{{ old('numero', $rdPtiC['numero']) }}" class="form-control form-control-sm" inputmode="numeric" maxlength="4" required>
                </label>
                <span class="tp-rdpti-completar__guion">-</span>
                <label>Año
                    <input type="number" name="anio" value="{{ old('anio', $anioSugerido) }}" class="form-control form-control-sm" min="1990" max="{{ now()->year + 1 }}" required>
                </label>
                <button type="submit" class="btn btn-sm tp-btn"><i class="fas fa-check mr-1"></i>Guardar RD del PTI</button>
            </form>
            <div class="small text-muted mt-1">
                @if ($anioEstimado)
                    <i class="fas fa-info-circle mr-1"></i><strong>El año {{ $anioSugerido }} es un estimado</strong> por la fecha de inscripción del PTI (a veces la RD sale el año siguiente): verifíquelo en la RD.
                @elseif ($anioSugerido)
                    <i class="fas fa-info-circle mr-1"></i>Año tomado de la fecha de la RD.
                @else
                    <i class="fas fa-info-circle mr-1"></i>Escriba el año que figura en la RD.
                @endif
                Se guarda para todo el grupo; si el número ya es de otro grupo, el sistema lo avisa y no lo guarda.
            </div>
        @else
            <div class="small text-muted mt-1">Lo completa Secretaría.</div>
        @endif
    </div>
@endif
