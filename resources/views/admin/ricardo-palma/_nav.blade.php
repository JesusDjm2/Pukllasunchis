{{-- Secciones del módulo Ricardo Palma (antes eran submenús del menú lateral). Va dentro de un .al-ui, bajo la cabecera. --}}
@php
    $seccionesRp = [
        ['rp.dashboard', 'rp.dashboard', 'fas fa-home', 'Inicio'],
        ['rp.actas.index', 'rp.actas.*', 'fas fa-file-invoice', 'Actas consolidadas'],
        ['rp.estudiantes.index', 'rp.estudiantes.*', 'fas fa-user-graduate', 'Estudiantes'],
        ['rp.programas.index', 'rp.programas.*', 'fas fa-layer-group', 'Carreras'],
        ['rp.importar.index', 'rp.importar.*', 'fas fa-file-upload', 'Carga masiva'],
    ];
@endphp
<nav class="al-tabs" aria-label="Secciones de Ricardo Palma">
    @foreach ($seccionesRp as [$ruta, $patron, $icono, $texto])
        @php $actual = request()->routeIs($patron); @endphp
        <a class="al-tab {{ $actual ? 'is-activa' : '' }}" href="{{ route($ruta) }}" @if ($actual) aria-current="page" @endif>
            <i class="{{ $icono }}"></i> {{ $texto }}
        </a>
    @endforeach
</nav>
