{{-- Resumen rápido del panel. Variables: $docente, $metricas --}}
@php
    $tarjetas = [
        [
            'titulo'  => 'Cursos a tu cargo',
            'valor'   => $metricas['cursos'],
            'detalle' => $metricas['cursosFid'] . ' FID · ' . $metricas['cursosPpd'] . ' PPD',
            'icono'   => 'fa-layer-group',
            'color'   => '#4e73df',
            'url'     => '#tablafid',
        ],
        [
            'titulo'  => 'Cursos asincrónicos',
            'valor'   => $metricas['asincronicos'],
            'detalle' => 'A tu cargo',
            'icono'   => 'fa-chalkboard',
            'color'   => '#36b9cc',
            'url'     => route('ce.docente.index'),
        ],
        [
            'titulo'  => 'Incidencias',
            'valor'   => $metricas['incidencias'],
            'detalle' => $metricas['incidenciasPendientes'] . ' pendientes',
            'icono'   => 'fa-clipboard-list',
            'color'   => '#f6c23e',
            'url'     => route('docente.incidencias.index', $docente->id),
        ],
        [
            'titulo'  => 'Notificaciones',
            'valor'   => $metricas['notificaciones'],
            'detalle' => 'sin leer',
            'icono'   => 'fa-bell',
            'color'   => '#1cc88a',
            'url'     => route('notificaciones.index'),
        ],
    ];
@endphp
<div class="row docente-dash-metricas mb-3 mb-md-4">
    @foreach ($tarjetas as $tarjeta)
        <div class="col-6 col-lg-3 mb-2 mb-lg-0">
            <a class="docente-ui-card docente-dash-metrica" href="{{ $tarjeta['url'] }}">
                <span class="docente-dash-metrica-icono" style="background: {{ $tarjeta['color'] }};">
                    <i class="fas {{ $tarjeta['icono'] }}"></i>
                </span>
                <span class="docente-dash-metrica-cuerpo">
                    <span class="docente-dash-metrica-valor">{{ $tarjeta['valor'] }}</span>
                    <span class="docente-dash-metrica-titulo">{{ $tarjeta['titulo'] }}</span>
                    <span class="docente-dash-metrica-detalle">{{ $tarjeta['detalle'] }}</span>
                </span>
            </a>
        </div>
    @endforeach
</div>
