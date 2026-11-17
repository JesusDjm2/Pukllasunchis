{{-- Formulario de titulación abierto por personal de la institución (admin, docente, tutor, Secretaría,
     Investigación…) sin proceso de titulación propio. Página simple: explica para quién es y lo devuelve a su
     panel; quien atiende solicitudes ve también el acceso a la bandeja correspondiente (carta, pti u originalidad). --}}
@php
    $cfg = match ($tramite ?? 'carta') {
        'pti' => [
            'titulo' => 'Solicitud de aprobación del PTI',
            'kicker' => 'Proceso de titulación · Plan de Trabajo de Investigación',
            'h1' => 'Solicitud de aprobación del PTI y asignación de asesor',
            'sub' => 'Formulario con el que los estudiantes (FID desde Ciclo IX, PPD y egresados) presentan su Plan de Trabajo de Investigación para iniciar su proceso de titulación.',
            'cuando' => 'al iniciar su proceso de titulación (FID desde Ciclo IX, PPD o egresados).',
            'bandeja' => 'Solicitudes de PTI',
            'area' => 'el Área de Investigación y Secretaría',
            'ruta' => route('titulacion.solicitudes-pti.index'),
            'boton' => 'Ver solicitudes de PTI',
        ],
        'originalidad' => [
            'titulo' => 'Revisión de originalidad (Turnitin)',
            'kicker' => 'Proceso de titulación · Trabajo de Investigación y Tesis',
            'h1' => 'Revisión de originalidad (Turnitin)',
            'sub' => 'Formulario con el que los tesistas solicitan la verificación de similitud (Turnitin) de su Trabajo de Investigación o de su Tesis.',
            'cuando' => 'cuando culminan su Trabajo de Investigación o su Tesis dentro de su proceso de titulación.',
            'bandeja' => 'Solicitudes de originalidad',
            'area' => 'el Área de Investigación',
            'ruta' => route('titulacion.solicitudes-originalidad.index'),
            'boton' => 'Ver solicitudes de originalidad',
        ],
        default => [
            'titulo' => 'Carta de aceptación',
            'kicker' => 'Proceso de titulación · Trabajo de Investigación',
            'h1' => 'Carta de aceptación de la Institución Educativa',
            'sub' => 'Formulario con el que los estudiantes, con su PTI aprobado, solicitan a Secretaría la carta para la Institución Educativa donde harán su investigación.',
            'cuando' => 'después de que su Plan de Trabajo de Investigación tiene la RD de aprobación.',
            'bandeja' => 'Registros de cartas',
            'area' => 'Secretaría',
            'ruta' => route('titulacion.cartas.index'),
            'boton' => 'Ver solicitudes de carta',
        ],
    };
@endphp
@extends('titulacion.carta._simple')
@section('titulo', $cfg['titulo'])
@section('contenido')
@include('alumnos.partials.ui')
<div class="al-ui cr cr-solo">
    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">{{ $cfg['kicker'] }}</span>
                <h1>{{ $cfg['h1'] }}</h1>
                <p class="al-hero-sub">{{ $cfg['sub'] }}</p>
            </div>
        </div>
    </section>

    <div class="al-card">
        <div class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-user-graduate"></i></span>
            <div>
                <h2>Este formulario es para estudiantes</h2>
                <p>Hola, {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim((string) $user->name))) }}. Ingresaste con una cuenta del personal de la institución, que no tiene un proceso de titulación propio.</p>
            </div>
        </div>
        <div class="al-card-cuerpo">
            <div class="al-aviso is-info mb-0"><i class="fas fa-info-circle"></i><div>
                Cada estudiante lo llena con <strong>su cuenta institucional</strong> {{ $cfg['cuando'] }}
                @if ($puedeRegistros)
                    Las solicitudes que envían llegan a <strong>{{ $cfg['bandeja'] }}</strong>, donde {{ $cfg['area'] }} las revisa y atiende.
                @else
                    Si un estudiante te consulta, compártele el enlace de esta página.
                @endif
            </div></div>
        </div>
        <div class="al-form-pie">
            @if ($puedeRegistros)
                <a href="{{ $cfg['ruta'] }}" class="al-btn al-btn-borde"><i class="fas fa-list-ul"></i> {{ $cfg['boton'] }}</a>
            @endif
            <a href="{{ $panel }}" class="al-btn al-btn-oro"><i class="fas fa-arrow-left"></i> Volver a mi panel</a>
        </div>
    </div>
</div>
@endsection
