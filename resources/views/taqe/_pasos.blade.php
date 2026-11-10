{{-- Avance de un grupo de Investigaciones en 3 pasos (Equipo → Evidencias → Trabajo final). Común a todas las vistas.
     Espera: $grupo (con alumnos, evidencias y finales cargados). --}}
@php
    $aceptados = $grupo->alumnos->where('pivot.estado', 'aceptado')->count();
    $nEvid = $grupo->evidencias->count();
    $nFinal = $grupo->finales->count();
    $pasos = [
        ['Equipo', $aceptados > 1, $aceptados.' '.($aceptados === 1 ? 'integrante' : 'integrantes')],
        ['Evidencias', $nEvid > 0, $nEvid.' '.($nEvid === 1 ? 'elemento' : 'elementos')],
        ['Trabajo final', $grupo->estaEntregado(), $grupo->estaEntregado() ? 'Entregado' : ($nFinal ? $nFinal.' sin entregar' : 'Pendiente')],
    ];
@endphp
<ol class="tq-pasos">
    @foreach ($pasos as [$nombrePaso, $hecho, $detalle])
        <li class="{{ $hecho ? 'is-hecho' : '' }}">
            <span class="tq-paso-marca">@if ($hecho)<i class="fas fa-check"></i>@else{{ $loop->iteration }}@endif</span>
            <div><b>{{ $nombrePaso }}</b><small>{{ $detalle }}</small></div>
        </li>
    @endforeach
</ol>
