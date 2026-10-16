{{-- Integrantes del grupo de titulación (misma RD o misma acta; ver
     GrupoTitulacion). Espera $persona (User dueño de la ficha) y $companeros
     (los demás, con seguimientoTitulacion.estadoTitulacion). Con $paraGestor,
     cada compañero trae un enlace para abrir su propia ficha en el panel. --}}
@php
    $paraGestor = $paraGestor ?? false;
    $integrantesGrupo = collect([$persona])->concat($companeros)->filter()->unique('id');
@endphp
@if ($companeros->isEmpty())
    <div class="text-muted small py-2">
        <i class="fas fa-user mr-1"></i>
        {{ $paraGestor ? 'Sin grupo registrado' : 'No figuras en un grupo' }}: presenta de forma individual, o el grupo aún no está registrado
        (el grupo se reconoce por compartir la misma RD o la misma acta).
    </div>
@else
    <p class="small text-muted mb-2">Integrantes que comparten la misma RD o acta de titulación.</p>
    <div class="mt-grupo">
        @foreach ($integrantesGrupo as $integrante)
            @php
                $esEl = $integrante->id === $persona?->id;
                $estadoIntegrante = $integrante->seguimientoTitulacion?->estadoTitulacion;
            @endphp
            <div class="mt-grupo__item {{ $esEl ? 'mt-grupo__item--yo' : '' }}">
                <div class="mt-grupo__avatar">{{ mb_strtoupper(mb_substr($integrante->name, 0, 1).mb_substr($integrante->apellidos, 0, 1)) }}</div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="font-weight-bold">
                        {{ $integrante->apellidos }}, {{ $integrante->name }}
                        @if ($esEl) <span class="text-muted font-weight-normal">({{ $paraGestor ? 'esta ficha' : 'tú' }})</span> @endif
                    </div>
                    @if ($integrante->email) <div class="small text-muted text-truncate"><i class="far fa-envelope mr-1"></i>{{ $integrante->email }}</div> @endif
                    @if ($paraGestor && ! $esEl)
                        <a class="small" href="{{ route('titulacion.index', array_filter(['search' => $integrante->dni, 'ver' => $integrante->id])) }}">
                            <i class="fas fa-external-link-alt fa-xs mr-1"></i>Abrir su ficha
                        </a>
                    @endif
                </div>
                @if ($estadoIntegrante)
                    <span class="badge badge-{{ \App\Services\Titulacion\AvanceTitulacion::badge($estadoIntegrante->clave) }} align-self-center">{{ $estadoIntegrante->nombre }}</span>
                @endif
            </div>
        @endforeach
    </div>
@endif
