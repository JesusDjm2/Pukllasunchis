{{-- Chip «RD PTI» para el personal (cabecera de la ficha, pantalla de generar RD). La RD que aprueba el
     PTI da inicio a todo el proceso. Completa: «RD PTI 088-2021» (abre su PDF si lo tiene). Incompleta
     (viene del Excel sin año) o sin RD: en ámbar, lleva al paso del PTI para completarla.
     Espera $seguimiento y $personaId (id del usuario, para el enlace a su ficha). --}}
@php
    $rdPtiDato = \App\Services\Titulacion\RdPti::de($seguimiento);
    $rdPtiCompletar = route('titulacion.index', ['ver' => $personaId, 'tab' => 'proceso', 'paso' => 'pti_datos']);
@endphp
@switch($rdPtiDato['estado'])
    @case('completa')
        @if ($rdPtiDato['enlace'])
            <a href="{{ $rdPtiDato['enlace'] }}" target="_blank" rel="noopener" class="tc-exp tc-rdpti" title="RD que aprueba el PTI{{ $rdPtiDato['fecha'] ? ' · '.$rdPtiDato['fecha']->format('d/m/Y') : '' }} · abrir el PDF">
                <i class="fas fa-file-signature"></i> RD PTI {{ $rdPtiDato['mostrar'] }}
            </a>
        @else
            <span class="tc-exp tc-rdpti" title="RD que aprueba el PTI{{ $rdPtiDato['fecha'] ? ' · '.$rdPtiDato['fecha']->format('d/m/Y') : '' }} · sin PDF">
                <i class="fas fa-file-signature"></i> RD PTI {{ $rdPtiDato['mostrar'] }}
            </span>
        @endif
        @break
    @case('incompleta')
        <a href="{{ $rdPtiCompletar }}" class="tc-exp tc-exp--falta tc-rdpti"
           title="Dato del Excel: «{{ $rdPtiDato['texto'] }}». Falta el año{{ $rdPtiDato['anio_rd'] ? ' (su fecha indica '.$rdPtiDato['anio_rd'].')' : '' }}: complételo en el paso del PTI.">
            <i class="fas fa-file-signature"></i> RD PTI {{ $rdPtiDato['mostrar'] }} · falta año
        </a>
        @break
    @default
        <a href="{{ $rdPtiCompletar }}" class="tc-exp tc-exp--falta tc-rdpti" title="Todavía no tiene registrada la RD que aprueba su PTI">
            <i class="fas fa-file-signature"></i> Sin RD de PTI
        </a>
@endswitch
