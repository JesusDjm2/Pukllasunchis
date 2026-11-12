{{-- Pestañas de la sección «Solicitudes» de Titulación (App\Services\Titulacion\BandejasSolicitudes): «Por atender»
     y una por trámite, con cuántas hay pendientes. Va dentro de un .al-ui, bajo la cabecera de cada bandeja. --}}
@php
    use App\Services\Titulacion\BandejasSolicitudes;
    $pendientesBandeja = BandejasSolicitudes::pendientes();
    $enPorAtender = request()->routeIs('titulacion.solicitudes.index');
@endphp
<nav class="al-tabs" aria-label="Solicitudes de titulación">
    <a class="al-tab {{ $enPorAtender ? 'is-activa' : '' }}" href="{{ route('titulacion.solicitudes.index') }}" @if ($enPorAtender) aria-current="page" @endif>
        <i class="fas fa-inbox"></i> Por atender
        @if (array_sum($pendientesBandeja))<span class="al-tab-marca">{{ array_sum($pendientesBandeja) }}</span>@endif
    </a>
    @foreach (BandejasSolicitudes::PESTANAS as $clave => [$titulo, $icono, $ruta, $patron, $area])
        @php $actual = request()->routeIs($patron) && ! request()->routeIs('*.importar*'); @endphp
        <a class="al-tab {{ $actual ? 'is-activa' : '' }}" href="{{ route($ruta) }}" title="La atiende {{ $area }}" @if ($actual) aria-current="page" @endif>
            <i class="fas {{ $icono }}"></i> {{ $titulo }}
            @if ($pendientesBandeja[$clave] ?? 0)<span class="al-tab-marca">{{ $pendientesBandeja[$clave] }}</span>@endif
        </a>
    @endforeach
</nav>
