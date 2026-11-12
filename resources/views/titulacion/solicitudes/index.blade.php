{{-- Solicitudes de Titulación › Por atender (SolicitudesController): lo pendiente de todos los trámites, de lo más
     antiguo a lo más reciente; cada fila abre la solicitud en la pestaña de su trámite. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Services\Titulacion\BandejasSolicitudes;
        use App\Services\Titulacion\DiasHabiles;
        $porTipo = $pendientes->countBy('tipo');
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-inbox"></i> Titulación · Solicitudes</div>
                <h1 class="tl-hero__titulo">Por atender</h1>
                <p class="tl-hero__sub">Las solicitudes pendientes de todos los trámites, de la más antigua a la más reciente. Cada una se atiende en la pestaña de su trámite.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <span class="tl-stat tl-stat--oro"><span class="tl-stat__num">{{ $pendientes->count() }}</span><span class="tl-stat__eti">Por atender</span></span>
                    @foreach (BandejasSolicitudes::PESTANAS as $clave => [$titulo])
                        <a href="{{ route(BandejasSolicitudes::PESTANAS[$clave][2], $clave === 'cartas' ? ['estado' => 'enviada'] : ['estado' => 'recibida']) }}" class="tl-stat tl-stat--link">
                            <span class="tl-stat__num">{{ $porTipo[$clave] ?? 0 }}</span><span class="tl-stat__eti">{{ $titulo }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        <div class="al-ui">
            @include('titulacion.solicitudes._pestanas')
        </div>

        <section class="tl-tabla-card">
            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead>
                        <tr><th>Recibida</th><th>Trámite</th><th>Quién</th><th>Detalle</th><th><span class="sr-only">Abrir</span></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($pendientes as $p)
                            @php
                                [$titulo, $icono, , , $area] = BandejasSolicitudes::PESTANAS[$p['tipo']];
                                $quedan = $p['limite'] ? DiasHabiles::restantes($p['limite']) : null;
                            @endphp
                            <tr class="ca-fila">
                                <td class="ca-c-fecha">
                                    <strong>{{ $p['fecha']->format('d/m/Y') }}</strong>
                                    <small>{{ $p['fecha']->diffForHumans() }}</small>
                                    @if ($quedan !== null)
                                        <span class="ca-plazo {{ $quedan < 0 ? 'is-vencido' : ($quedan === 0 ? 'is-hoy' : '') }}">
                                            {{ $quedan < 0 ? 'Venció el '.$p['limite']->format('d/m') : ($quedan === 0 ? 'Vence hoy' : 'Hasta el '.$p['limite']->format('d/m')) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ca-persona">
                                        <strong><i class="fas {{ $icono }} mr-1"></i>{{ $titulo }}</strong>
                                        <small>La atiende {{ $area }}</small>
                                    </div>
                                </td>
                                <td>{{ $p['quien'] }}@if ($p['origen'] === 'formulario')<span class="ca-origen">Formulario de Google</span>@endif</td>
                                <td><small>{{ $p['detalle'] }}</small></td>
                                <td class="ca-acciones"><a href="{{ $p['url'] }}" class="btn btn-sm tp-btn">Abrir <i class="fas fa-arrow-right fa-xs ml-1"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-check-circle mr-1"></i>No hay solicitudes pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
